<?php
/**
 * CampusLink - Vendor Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class VendorModel extends Model
{
    protected string $table = 'vendors';

    // ============================================================
    // Create student vendor
    // ============================================================
    public function createStudentVendor(array $data): string|false
    {
        return $this->create([
            'vendor_type'        => 'student',
            'full_name'          => $data['full_name'],
            'matric_number'      => strtoupper($data['matric_number']),
            'school_email'       => strtolower($data['school_email']),
            'personal_email'     => strtolower($data['personal_email']),
            'phone'              => $data['phone'],
            'whatsapp_number'    => $data['whatsapp_number'],
            'level'              => $data['level'],
            'department'         => $data['department'],
            'business_name'      => $data['business_name'],
            'slug'               => $this->generateSlug($data['business_name']),
            'category_id'        => (int)$data['category_id'],
            'description'        => $data['description'],
            'price_range'        => $data['price_range'] ?? '',
            'years_experience'   => (int)($data['years_experience'] ?? 0),
            'operating_location' => $data['operating_location'] ?? '',
            'logo'               => $data['logo'] ?? null,
            'id_card_file'       => $data['id_card_file'] ?? null,
            'selfie_file'        => $data['selfie_file'] ?? null,
            'service_photo'      => $data['service_photo'] ?? null,
            'plan_type'          => $data['plan_type'],
            'status'             => 'pending',
            'password'           => Auth::hashPassword($data['password']),
            'email_verified'     => 0,
            'phone_verified'     => 0,
            'terms_accepted'     => 1,
            'terms_version'      => TERMS_VERSION,
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Create community vendor
    // ============================================================
    public function createCommunityVendor(array $data): string|false
    {
        return $this->create([
            'vendor_type'        => 'community',
            'full_name'          => $data['full_name'],
            'working_email'      => strtolower($data['working_email']),
            'phone'              => $data['phone'],
            'whatsapp_number'    => $data['whatsapp_number'],
            'business_name'      => $data['business_name'],
            'slug'               => $this->generateSlug($data['business_name']),
            'category_id'        => (int)$data['category_id'],
            'description'        => $data['description'],
            'business_address'   => $data['business_address'],
            'years_operation'    => (int)($data['years_operation'] ?? 0),
            'price_range'        => $data['price_range'] ?? '',
            'logo'               => $data['logo'] ?? null,
            'cac_certificate'    => $data['cac_certificate'] ?? null,
            'gov_id_file'        => $data['gov_id_file'] ?? null,
            'service_photo'      => $data['service_photo'] ?? null,
            'plan_type'          => $data['plan_type'],
            'status'             => 'pending',
            'password'           => Auth::hashPassword($data['password']),
            'email_verified'     => 0,
            'phone_verified'     => 0,
            'terms_accepted'     => 1,
            'terms_version'      => TERMS_VERSION,
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Find vendor by email (school or working)
    // ============================================================
    public function findByEmail(string $email): ?array
    {
        $email = strtolower($email);
        return $this->db->fetchOne(
            "SELECT * FROM vendors 
             WHERE school_email = ? OR working_email = ? OR personal_email = ?
             LIMIT 1",
            [$email, $email, $email]
        );
    }

    // ============================================================
    // Find vendor by slug
    // ============================================================
    public function findBySlug(string $slug): ?array
    {
        $vendor = $this->db->fetchOne(
            "SELECT v.*, c.name as category_name,
                    COALESCE(AVG(r.rating), 0) as avg_rating,
                    COUNT(DISTINCT r.id) as review_count
             FROM vendors v
             LEFT JOIN categories c ON v.category_id = c.id
             LEFT JOIN reviews r ON r.vendor_id = v.id AND r.status = 'approved'
             WHERE v.slug = ?
             GROUP BY v.id
             LIMIT 1",
            [$slug]
        );

        // Allow viewing own profile even if not active (for pending approval)
        if ($vendor && Auth::isVendorLoggedIn() && Auth::vendorId() === $vendor['id']) {
            return $vendor;
        }

        // For public viewing, must be active
        return ($vendor && $vendor['status'] === 'active') ? $vendor : null;
    }

    // ============================================================
    // Find vendor by matric number
    // ============================================================
    public function findByMatric(string $matric): ?array
    {
        return $this->findBy('matric_number', strtoupper($matric));
    }

    // ============================================================
    // Find vendor by phone
    // ============================================================
    public function findByPhone(string $phone): ?array
    {
        return $this->findBy('phone', $phone);
    }

    // ============================================================
    // Get all active vendors (browse page)
    // ============================================================
    public function getActive(
        int    $limit = 12,
        int    $offset = 0,
        string $categorySlug = '',
        string $search = '',
        string $sortBy = 'plan_priority'
    ): array {
        $params     = [];
        $conditions = ["v.status = 'active'", "s.status = 'active'", "s.expiry_date > NOW()"];

        if (!empty($categorySlug)) {
            $conditions[] = "c.slug = ?";
            $params[]      = $categorySlug;
        }

        if (!empty($search)) {
            $conditions[] = "(v.business_name LIKE ? OR v.description LIKE ? OR c.name LIKE ?)";
            $params[]      = "%$search%";
            $params[]      = "%$search%";
            $params[]      = "%$search%";
        }

        $where = implode(' AND ', $conditions);

        $orderBy = match($sortBy) {
            'rating'    => 'avg_rating DESC, review_count DESC',
            'newest'    => 'v.created_at DESC',
            'name'      => 'v.business_name ASC',
            default     => "FIELD(v.plan_type,'featured','premium','basic'), avg_rating DESC",
        };

        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll(
            "SELECT v.*, c.name as category_name, c.slug as category_slug,
                    COALESCE(AVG(r.rating), 0) as avg_rating,
                    COUNT(DISTINCT r.id) as review_count
             FROM vendors v
             INNER JOIN subscriptions s ON v.id = s.vendor_id
             LEFT JOIN categories c ON v.category_id = c.id
             LEFT JOIN reviews r ON r.vendor_id = v.id AND r.status = 'approved'
             WHERE $where
             GROUP BY v.id
             ORDER BY $orderBy
             LIMIT ? OFFSET ?",
            $params
        );
    }

    // ============================================================
    // Count active vendors with filters
    // ============================================================
    public function countActive(
        string $categorySlug = '',
        string $search = ''
    ): int {
        $params     = [];
        $conditions = ["v.status = 'active'", "s.status = 'active'", "s.expiry_date > NOW()"];

        if (!empty($categorySlug)) {
            $conditions[] = "c.slug = ?";
            $params[]      = $categorySlug;
        }

        if (!empty($search)) {
            $conditions[] = "(v.business_name LIKE ? OR v.description LIKE ? OR c.name LIKE ?)";
            $params[]      = "%$search%";
            $params[]      = "%$search%";
            $params[]      = "%$search%";
        }

        $where = implode(' AND ', $conditions);

        return (int)$this->db->fetchColumn(
            "SELECT COUNT(DISTINCT v.id) FROM vendors v
             INNER JOIN subscriptions s ON v.id = s.vendor_id
             LEFT JOIN categories c ON v.category_id = c.id
             WHERE $where",
            $params
        );
    }

    // ============================================================
    // Get vendors expiring soon (for cron reminders)
    // ============================================================
    public function getExpiringSoon(int $daysAhead): array
    {
        return $this->db->fetchAll(
            "SELECT v.*, s.expiry_date, s.id as subscription_id
             FROM vendors v
             JOIN subscriptions s ON s.vendor_id = v.id AND s.status = 'active'
             WHERE v.status = 'active'
             AND DATE(s.expiry_date) = DATE(DATE_ADD(NOW(), INTERVAL ? DAY))",
            [$daysAhead]
        );
    }

    // ============================================================
    // Send expiry reminders to vendors
    // ============================================================
    public function sendExpiryReminders(int $daysAhead = 10): void
    {
        $expiringVendors = $this->getExpiringSoon($daysAhead);

        foreach ($expiringVendors as $vendor) {
            // Check if reminder was already sent recently (within last 7 days)
            $lastReminder = $this->db->fetchColumn(
                "SELECT created_at FROM notifications
                 WHERE vendor_id = ? AND type = 'expiry_reminder'
                 AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
                 ORDER BY created_at DESC LIMIT 1",
                [$vendor['id']]
            );

            if ($lastReminder) {
                continue; // Skip if reminder was sent recently
            }

            // Send notification
            Notification::sendToVendor(
                $vendor['id'],
                'Subscription Expiring Soon',
                "Your subscription will expire on {$vendor['expiry_date']}. " .
                "Please renew your plan to continue appearing in browse and category listings.",
                Notification::TYPE_EXPIRY_REMINDER,
                'vendor/subscription'
            );

            // Send email if available
            $email = $vendor['school_email'] ?? $vendor['working_email'] ?? '';
            if ($email) {
                require_once __DIR__ . '/../core/Mailer.php';
                $mailer = new Mailer();
                $mailer->sendExpiryReminder(
                    $email,
                    $vendor['full_name'],
                    $vendor['business_name'],
                    $vendor['expiry_date']
                );
            }
        }
    }

    // ============================================================
    // Get vendors past expiry (for deactivation cron)
    // ============================================================
    public function getExpired(): array
    {
        return $this->db->fetchAll(
            "SELECT v.*, s.expiry_date, s.id as subscription_id
             FROM vendors v
             JOIN subscriptions s ON s.vendor_id = v.id AND s.status = 'active'
             WHERE v.status IN ('active', 'grace_period')
             AND s.expiry_date < DATE_SUB(NOW(), INTERVAL ? DAY)",
            [GRACE_PERIOD_DAYS]
        );
    }

    // ============================================================
    // Get vendors in grace period
    // ============================================================
    public function getInGracePeriod(): array
    {
        return $this->db->fetchAll(
            "SELECT v.*, s.expiry_date
             FROM vendors v
             JOIN subscriptions s ON s.vendor_id = v.id AND s.status = 'active'
             WHERE v.status = 'active'
             AND s.expiry_date < NOW()
             AND s.expiry_date >= DATE_SUB(NOW(), INTERVAL ? DAY)",
            [GRACE_PERIOD_DAYS]
        );
    }

    // ============================================================
    // Approve vendor
    // ============================================================
    public function approve(int $vendorId): bool
    {
        return $this->update($vendorId, [
            'status'      => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Activate vendor (after payment verified)
    // ============================================================
    public function activate(int $vendorId): bool
    {
        return $this->update($vendorId, [
            'status'      => 'active',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Deactivate vendor (subscription expired)
    // ============================================================
    public function deactivate(int $vendorId): bool
    {
        return $this->update($vendorId, [
            'status'      => 'inactive',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Reject vendor registration
    // ============================================================
    public function reject(int $vendorId, string $reason): bool
    {
        return $this->update($vendorId, [
            'status'          => 'rejected',
            'rejection_reason'=> $reason,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Suspend vendor
    // ============================================================
    public function suspend(int $vendorId, string $reason): bool
    {
        return $this->update($vendorId, [
            'status'           => 'suspended',
            'suspension_reason'=> $reason,
            'suspended_at'     => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Permanently ban vendor
    // ============================================================
    public function ban(int $vendorId, string $reason): bool
    {
        return $this->update($vendorId, [
            'status'     => 'banned',
            'ban_reason' => $reason,
            'banned_at'  => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Mark as grace period
    // ============================================================
    public function setGracePeriod(int $vendorId): bool
    {
        return $this->update($vendorId, [
            'status'     => 'grace_period',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Update vendor plan
    // ============================================================
    public function updatePlan(int $vendorId, string $plan): bool
    {
        return $this->update($vendorId, [
            'plan_type'  => $plan,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Update profile
    // ============================================================
   // In models/VendorModel.php — find updateProfile() and replace the $allowed array:

public function updateProfile(int $vendorId, array $data): bool
{
    $allowed = [
        'business_name', 'description', 'price_range',
        'operating_location', 'business_address',
        'years_experience', 'years_operation',
        'whatsapp_number', 'phone', 'logo',
        'category_id',   // ← ADD THIS
    ];
    $update = array_intersect_key($data, array_flip($allowed));
    $update['updated_at'] = date('Y-m-d H:i:s');
    return $this->update($vendorId, $update);
}

    // ============================================================
    // Update last login
    // ============================================================
    public function updateLastLogin(int $vendorId): void
    {
        $this->update($vendorId, [
            'last_login' => date('Y-m-d H:i:s'),
            'last_ip'    => getClientIP(),
        ]);
    }

    // ============================================================
    // Get pending vendors for admin
    // ============================================================
    public function getPending(): array
    {
        return $this->db->fetchAll(
            "SELECT v.*, c.name as category_name
             FROM vendors v
             LEFT JOIN categories c ON v.category_id = c.id
             WHERE v.status = 'pending'
             ORDER BY v.created_at ASC"
        );
    }

    // ============================================================
    // Get all vendors for admin (paginated)
    // ============================================================
    public function getAllPaginated(
        int    $page = 1,
        int    $perPage = 25,
        string $search = '',
        string $status = '',
        string $vendorType = ''
    ): array {
        $conditions = [];
        $params     = [];

        if (!empty($search)) {
            $conditions[] = "(v.business_name LIKE ? OR v.full_name LIKE ? OR v.phone LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($status)) {
            $conditions[] = "v.status = ?";
            $params[] = $status;
        }

        if (!empty($vendorType)) {
            $conditions[] = "v.vendor_type = ?";
            $params[] = $vendorType;
        }

        $where = $conditions ? implode(' AND ', $conditions) : '1';

        $total = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM vendors v WHERE $where", $params
        );

        $offset = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;

        $data = $this->db->fetchAll(
            "SELECT v.*, c.name as category_name,
                    COALESCE(AVG(r.rating),0) as avg_rating
             FROM vendors v
             LEFT JOIN categories c ON v.category_id = c.id
             LEFT JOIN reviews r ON r.vendor_id = v.id AND r.status = 'approved'
             WHERE $where
             GROUP BY v.id
             ORDER BY v.created_at DESC
             LIMIT ? OFFSET ?",
            $params
        );

        return [
            'data'         => $data,
            'total'        => $total,
            'current_page' => $page,
            'total_pages'  => (int)ceil($total / $perPage),
            'per_page'     => $perPage,
        ];
    }

    // ============================================================
    // Generate unique slug for vendor
    // ============================================================
    private function generateSlug(string $businessName): string
    {
        $base = Sanitizer::slug($businessName);
        $slug = $base;
        $i    = 1;

        while ($this->exists('slug', $slug)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    // ============================================================
    // Check matric availability
    // ============================================================
    public function matricAvailable(string $matric, int $excludeId = 0): bool
    {
        $exists = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM vendors WHERE matric_number = ? AND id != ?",
            [strtoupper($matric), $excludeId]
        );
        return (int)$exists === 0;
    }

    // ============================================================
    // Get total active vendor count
    // ============================================================
    public function totalActive(): int
    {
        return $this->count("status = 'active'");
    }

    // ============================================================
    // Get complaint count for suspension review check
    // ============================================================
    public function getVerifiedComplaintCount(int $vendorId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM complaints 
             WHERE vendor_id = ? AND status = 'verified'",
            [$vendorId]
        );
    }
}