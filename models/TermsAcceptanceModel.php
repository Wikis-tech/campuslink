<?php
/**
 * CampusLink - Terms Acceptance Model
 * Records every T&C acceptance for legal compliance.
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class TermsAcceptanceModel extends Model
{
    protected string $table = 'terms_acceptance';

    // ============================================================
    // Record terms acceptance
    // ============================================================
    public function record(array $data): string|false
    {
        return $this->create([
            'user_id'        => (int)($data['user_id'] ?? 0),
            'vendor_id'      => (int)($data['vendor_id'] ?? 0),
            'entity_type'    => $data['entity_type'],  // 'user' or 'vendor'
            'terms_type'     => $data['terms_type'],   // 'general','user','vendor','privacy'
            'terms_version'  => $data['terms_version'] ?? TERMS_VERSION,
            'ip_address'     => $data['ip_address'] ?? getClientIP(),
            'user_agent'     => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'accepted_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Record all terms at once (on registration)
    // ============================================================
    public function recordAll(
        int    $entityId,
        string $entityType,
        string $ip
    ): void {
        $termsTypes = ['general', 'privacy'];
        if ($entityType === 'user') {
            $termsTypes[] = 'user';
        } elseif ($entityType === 'vendor') {
            $termsTypes[] = 'vendor';
        }

        foreach ($termsTypes as $type) {
            $this->record([
                'user_id'     => $entityType === 'user' ? $entityId : 0,
                'vendor_id'   => $entityType === 'vendor' ? $entityId : 0,
                'entity_type' => $entityType,
                'terms_type'  => $type,
                'ip_address'  => $ip,
            ]);
        }
    }

    // ============================================================
    // Get all acceptance records for a user
    // ============================================================
    public function getForUser(int $userId): array
    {
        return $this->where("user_id = ? AND entity_type = 'user'", [$userId], 'accepted_at DESC');
    }

    // ============================================================
    // Get all acceptance records for a vendor
    // ============================================================
    public function getForVendor(int $vendorId): array
    {
        return $this->where("vendor_id = ? AND entity_type = 'vendor'", [$vendorId], 'accepted_at DESC');
    }

    // ============================================================
    // Check if user has accepted current version
    // ============================================================
    public function hasAcceptedCurrentVersion(int $entityId, string $entityType): bool
    {
        $col = $entityType === 'user' ? 'user_id' : 'vendor_id';
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM terms_acceptance 
             WHERE $col = ? AND entity_type = ? AND terms_version = ?",
            [$entityId, $entityType, TERMS_VERSION]
        );
    }

    // ============================================================
    // Get acceptance count per terms version (for admin)
    // ============================================================
    public function getSummaryByVersion(): array
    {
        return $this->db->fetchAll(
            "SELECT terms_version, terms_type, entity_type, COUNT(*) as count
             FROM terms_acceptance
             GROUP BY terms_version, terms_type, entity_type
             ORDER BY terms_version DESC"
        );
    }
}