<?php
/**
 * CampusLink - Subscription Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class SubscriptionModel extends Model
{
    protected string $table = 'subscriptions';

    // ============================================================
    // Create new subscription after payment verified
    // ============================================================
    public function createSubscription(array $data): string|false
    {
        $startDate  = date('Y-m-d H:i:s');
        $expiryDate = date('Y-m-d H:i:s', strtotime("+". SEMESTER_DAYS ." days"));

        return $this->create([
            'vendor_id'      => (int)$data['vendor_id'],
            'plan_type'      => $data['plan_type'],
            'vendor_type'    => $data['vendor_type'],
            'payment_id'     => (int)$data['payment_id'],
            'amount'         => (int)$data['amount'],
            'status'         => 'active',
            'start_date'     => $startDate,
            'expiry_date'    => $expiryDate,
            'reminder_14_sent' => 0,
            'reminder_7_sent'  => 0,
            'reminder_2_sent'  => 0,
            'created_at'     => $startDate,
            'updated_at'     => $startDate,
        ]);
    }

    // ============================================================
    // Get active subscription for vendor
    // ============================================================
    public function getActiveForVendor(int $vendorId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM subscriptions 
             WHERE vendor_id = ? AND status = 'active'
             ORDER BY created_at DESC LIMIT 1",
            [$vendorId]
        );
    }

    // ============================================================
    // Get all subscriptions for vendor
    // ============================================================
    public function getAllForVendor(int $vendorId): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, p.amount as paid_amount
             FROM subscriptions s
             LEFT JOIN payments p ON p.id = s.payment_id
             WHERE s.vendor_id = ?
             ORDER BY s.created_at DESC",
            [$vendorId]
        );
    }

    // ============================================================
    // Get days remaining in subscription
    // ============================================================
    public function getDaysRemaining(int $vendorId): int
    {
        $sub = $this->getActiveForVendor($vendorId);
        if (!$sub) return 0;

        $now    = new DateTime();
        $expiry = new DateTime($sub['expiry_date']);
        $diff   = $now->diff($expiry);

        if ($expiry < $now) return 0;
        return (int)$diff->days;
    }

    // ============================================================
    // Get subscription expiry info
    // ============================================================
    public function getExpiryInfo(int $vendorId): array
    {
        $sub = $this->getActiveForVendor($vendorId);
        if (!$sub) {
            return [
                'has_subscription' => false,
                'days_remaining'   => 0,
                'expiry_date'      => null,
                'is_expired'       => true,
                'in_grace_period'  => false,
            ];
        }

        $now        = new DateTime();
        $expiry     = new DateTime($sub['expiry_date']);
        $isExpired  = $expiry < $now;
        $diff       = $isExpired ? 0 : (int)$now->diff($expiry)->days;

        $graceEnd   = clone $expiry;
        $graceEnd->modify('+' . GRACE_PERIOD_DAYS . ' days');
        $inGrace    = $isExpired && ($now < $graceEnd);

        return [
            'has_subscription' => true,
            'days_remaining'   => $diff,
            'expiry_date'      => $sub['expiry_date'],
            'is_expired'       => $isExpired,
            'in_grace_period'  => $inGrace,
            'subscription'     => $sub,
        ];
    }

    // ============================================================
    // Expire a subscription
    // ============================================================
    public function expire(int $subscriptionId): bool
    {
        return $this->update($subscriptionId, [
            'status'     => 'expired',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Mark reminder as sent
    // ============================================================
    public function markReminderSent(int $subscriptionId, int $days): bool
    {
        $col = match($days) {
            14 => 'reminder_14_sent',
            7  => 'reminder_7_sent',
            2  => 'reminder_2_sent',
            default => null,
        };

        if (!$col) return false;

        return $this->update($subscriptionId, [
            $col         => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Get subscriptions needing reminder (for cron)
    // ============================================================
    public function getNeedingReminder(int $days): array
    {
        $col = match($days) {
            14 => 'reminder_14_sent',
            7  => 'reminder_7_sent',
            2  => 'reminder_2_sent',
            default => null,
        };

        if (!$col) return [];

        return $this->db->fetchAll(
            "SELECT s.*, v.full_name, v.business_name, 
                    COALESCE(v.school_email, v.working_email) as email
             FROM subscriptions s
             JOIN vendors v ON v.id = s.vendor_id
             WHERE s.status = 'active'
             AND s.$col = 0
             AND DATE(s.expiry_date) = DATE(DATE_ADD(NOW(), INTERVAL ? DAY))",
            [$days]
        );
    }

    // ============================================================
    // Get total active subscriptions count
    // ============================================================
    public function countActive(): int
    {
        return $this->count("status = 'active' AND expiry_date >= NOW()");
    }

    // ============================================================
    // Get upgrade request
    // ============================================================
    public function getPendingUpgrade(int $vendorId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM plan_change_requests 
             WHERE vendor_id = ? AND type = 'upgrade' AND status = 'pending'
             ORDER BY created_at DESC LIMIT 1",
            [$vendorId]
        );
    }

    // ============================================================
    // Request plan upgrade
    // ============================================================
    public function requestUpgrade(int $vendorId, string $currentPlan, string $newPlan): string|false
    {
        return $this->db->insert(
            "INSERT INTO plan_change_requests 
             (vendor_id, type, current_plan, requested_plan, status, created_at)
             VALUES (?, 'upgrade', ?, ?, 'pending', NOW())",
            [$vendorId, $currentPlan, $newPlan]
        );
    }

    // ============================================================
    // Request plan downgrade (takes effect after expiry)
    // ============================================================
    public function requestDowngrade(int $vendorId, string $currentPlan, string $newPlan): string|false
    {
        return $this->db->insert(
            "INSERT INTO plan_change_requests 
             (vendor_id, type, current_plan, requested_plan, status, created_at)
             VALUES (?, 'downgrade', ?, ?, 'scheduled', NOW())",
            [$vendorId, $currentPlan, $newPlan]
        );
    }
}