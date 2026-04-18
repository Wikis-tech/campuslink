<?php
/**
 * CampusLink - Saved Vendor Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class SavedVendorModel extends Model
{
    protected string $table = 'saved_vendors';

    // ============================================================
    // Save a vendor
    // ============================================================
    public function save(int $userId, int $vendorId): bool
    {
        if ($this->isSaved($userId, $vendorId)) return true;

        return (bool)$this->create([
            'user_id'    => $userId,
            'vendor_id'  => $vendorId,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Unsave a vendor
    // ============================================================
    public function unsave(int $userId, int $vendorId): bool
    {
        return (bool)$this->db->execute(
            "DELETE FROM saved_vendors WHERE user_id = ? AND vendor_id = ?",
            [$userId, $vendorId]
        );
    }

    // ============================================================
    // Toggle save/unsave
    // ============================================================
    public function toggle(int $userId, int $vendorId): array
    {
        if ($this->isSaved($userId, $vendorId)) {
            $this->unsave($userId, $vendorId);
            return ['saved' => false, 'message' => 'Vendor removed from saved list.'];
        } else {
            $this->save($userId, $vendorId);
            return ['saved' => true, 'message' => 'Vendor saved successfully.'];
        }
    }

    // ============================================================
    // Check if vendor is saved by user
    // ============================================================
    public function isSaved(int $userId, int $vendorId): bool
    {
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM saved_vendors WHERE user_id = ? AND vendor_id = ?",
            [$userId, $vendorId]
        );
    }

    // ============================================================
    // Get saved vendors for user
    // ============================================================
    public function getForUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT sv.*, v.business_name, v.slug, v.description,
                    v.logo, v.plan_type, v.whatsapp_number, v.phone,
                    c.name as category_name,
                    COALESCE(AVG(r.rating), 0) as avg_rating,
                    COUNT(DISTINCT r.id) as review_count
             FROM saved_vendors sv
             JOIN vendors v ON v.id = sv.vendor_id
             LEFT JOIN categories c ON c.id = v.category_id
             LEFT JOIN reviews r ON r.vendor_id = v.id AND r.status = 'approved'
             WHERE sv.user_id = ? AND v.status = 'active'
             GROUP BY sv.id
             ORDER BY sv.created_at DESC",
            [$userId]
        );
    }

    // ============================================================
    // Count saved vendors for user
    // ============================================================
    public function countForUser(int $userId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM saved_vendors WHERE user_id = ?",
            [$userId]
        );
    }
}