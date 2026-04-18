<?php
/**
 * CampusLink - Payment Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class PaymentModel extends Model
{
    protected string $table = 'payments';

    // ============================================================
    // Create payment record (before verification)
    // ============================================================
    public function createPending(array $data): string|false
    {
        return $this->create([
            'vendor_id'   => (int)$data['vendor_id'],
            'vendor_type' => $data['vendor_type'],
            'reference'   => $data['reference'],
            'amount'      => (int)$data['amount'],
            'plan_type'   => $data['plan_type'],
            'status'      => 'pending',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Mark payment as verified and successful
    // ============================================================
    public function markSuccess(int $paymentId, array $paystackData): bool
    {
        return $this->update($paymentId, [
            'status'         => 'success',
            'paid_at'        => $paystackData['paid_at'] ?? date('Y-m-d H:i:s'),
            'channel'        => $paystackData['channel'] ?? null,
            'gateway_ref'    => $paystackData['reference'] ?? null,
            'currency'       => $paystackData['currency'] ?? 'NGN',
            'customer_email' => $paystackData['customer_email'] ?? null,
            'verified_at'    => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Mark payment as failed
    // ============================================================
    public function markFailed(int $paymentId, string $reason = ''): bool
    {
        return $this->update($paymentId, [
            'status'      => 'failed',
            'fail_reason' => $reason,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Find by reference (prevent duplicate verification)
    // ============================================================
    public function findByReference(string $reference): ?array
    {
        return $this->findBy('reference', $reference);
    }

    // ============================================================
    // Check if reference already verified
    // ============================================================
    public function isAlreadyVerified(string $reference): bool
    {
        $payment = $this->findByReference($reference);
        return $payment && $payment['status'] === 'success';
    }

    // ============================================================
    // Get all payments for a vendor
    // ============================================================
    public function getForVendor(int $vendorId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM payments 
             WHERE vendor_id = ? 
             ORDER BY created_at DESC",
            [$vendorId]
        );
    }

    // ============================================================
    // Get payment with subscription info
    // ============================================================
    public function getWithSubscription(int $paymentId): ?array
    {
        return $this->db->fetchOne(
            "SELECT p.*, s.start_date, s.expiry_date, s.status as sub_status
             FROM payments p
             LEFT JOIN subscriptions s ON s.payment_id = p.id
             WHERE p.id = ?",
            [$paymentId]
        );
    }

    // ============================================================
    // Get all payments for admin (paginated)
    // ============================================================
    public function getAllPaginated(
        int    $page = 1,
        int    $perPage = 25,
        string $search = '',
        string $status = ''
    ): array {
        $conditions = [];
        $params     = [];

        if (!empty($search)) {
            $conditions[] = "(p.reference LIKE ? OR v.business_name LIKE ?)";
            $params[]      = "%$search%";
            $params[]      = "%$search%";
        }

        if (!empty($status)) {
            $conditions[] = "p.status = ?";
            $params[]      = $status;
        }

        $where  = $conditions ? implode(' AND ', $conditions) : '1';
        $total  = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM payments p
             JOIN vendors v ON v.id = p.vendor_id
             WHERE $where", $params
        );

        $offset   = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;

        $data = $this->db->fetchAll(
            "SELECT p.*, v.business_name, v.vendor_type
             FROM payments p
             JOIN vendors v ON v.id = p.vendor_id
             WHERE $where
             ORDER BY p.created_at DESC
             LIMIT ? OFFSET ?",
            $params
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'current_page' => $page,
            'total_pages'  => (int)ceil($total / $perPage),
        ];
    }

    // ============================================================
    // Get revenue totals for admin dashboard
    // ============================================================
    public function getRevenueSummary(): array
    {
        return $this->db->fetchOne(
            "SELECT 
                SUM(CASE WHEN status='success' THEN amount ELSE 0 END) as total_revenue,
                COUNT(CASE WHEN status='success' THEN 1 END) as successful_payments,
                COUNT(CASE WHEN status='failed' THEN 1 END) as failed_payments,
                COUNT(CASE WHEN status='pending' THEN 1 END) as pending_payments,
                SUM(CASE WHEN status='success' AND MONTH(paid_at)=MONTH(NOW()) THEN amount ELSE 0 END) as this_month_revenue
             FROM payments"
        ) ?? [];
    }

    // ============================================================
    // Get monthly revenue for chart
    // ============================================================
    public function getMonthlyRevenue(int $months = 12): array
    {
        return $this->db->fetchAll(
            "SELECT 
                DATE_FORMAT(paid_at, '%Y-%m') as month,
                SUM(amount) as revenue,
                COUNT(*) as transactions
             FROM payments 
             WHERE status = 'success' 
             AND paid_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
             ORDER BY month ASC",
            [$months]
        );
    }

    // ============================================================
    // Export payments as CSV data array
    // ============================================================
    public function getForExport(string $from = '', string $to = ''): array
    {
        $conditions = ["p.status = 'success'"];
        $params     = [];

        if ($from) {
            $conditions[] = "DATE(p.paid_at) >= ?";
            $params[] = $from;
        }
        if ($to) {
            $conditions[] = "DATE(p.paid_at) <= ?";
            $params[] = $to;
        }

        $where = implode(' AND ', $conditions);

        return $this->db->fetchAll(
            "SELECT p.reference, v.business_name, v.vendor_type,
                    p.plan_type, p.amount, p.channel,
                    p.paid_at, p.customer_email
             FROM payments p
             JOIN vendors v ON v.id = p.vendor_id
             WHERE $where
             ORDER BY p.paid_at DESC",
            $params
        );
    }
}