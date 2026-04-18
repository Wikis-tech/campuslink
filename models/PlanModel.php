<?php
/**
 * CampusLink - Plan Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class PlanModel extends Model
{
    protected string $table = 'plans';

    // ============================================================
    // Get all plans
    // ============================================================
    public function getAllPlans(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM plans WHERE is_active = 1 ORDER BY vendor_type, amount ASC"
        );
    }

    // ============================================================
    // Get plans for a specific vendor type
    // ============================================================
    public function getByVendorType(string $vendorType): array
    {
        return $this->where(
            "vendor_type = ? AND is_active = 1",
            [$vendorType],
            'amount ASC'
        );
    }

    // ============================================================
    // Get specific plan
    // ============================================================
    public function getPlan(string $vendorType, string $planType): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM plans WHERE vendor_type = ? AND plan_type = ? AND is_active = 1",
            [$vendorType, $planType]
        );
    }

    // ============================================================
    // Validate plan and amount
    // ============================================================
    public function validatePlanAmount(
        string $vendorType,
        string $planType,
        int    $amount
    ): bool {
        $plan = $this->getPlan($vendorType, $planType);
        if (!$plan) return false;
        return (int)$plan['amount'] === $amount;
    }

    // ============================================================
    // Get plan features
    // ============================================================
    public function getPlanFeatures(string $planType): array
    {
        $features = [
            'basic' => [
                'listing'          => true,
                'verified_badge'   => true,
                'whatsapp_button'  => true,
                'call_button'      => true,
                'reviews'          => true,
                'featured'         => false,
                'priority_listing' => false,
                'photo_gallery'    => false,
                'max_photos'       => 1,
            ],
            'premium' => [
                'listing'          => true,
                'verified_badge'   => true,
                'whatsapp_button'  => true,
                'call_button'      => true,
                'reviews'          => true,
                'featured'         => false,
                'priority_listing' => true,
                'photo_gallery'    => true,
                'max_photos'       => 5,
            ],
            'featured' => [
                'listing'          => true,
                'verified_badge'   => true,
                'whatsapp_button'  => true,
                'call_button'      => true,
                'reviews'          => true,
                'featured'         => true,
                'priority_listing' => true,
                'photo_gallery'    => true,
                'max_photos'       => 10,
            ],
        ];

        return $features[$planType] ?? $features['basic'];
    }
}