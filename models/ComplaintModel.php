<?php
/**
 * CampusLink - Complaint Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class ComplaintModel extends Model
{
    protected string $table = 'complaints';

    // Complaint categories
    const CATEGORIES = [
        'fraud'           => 'Fraud / Scam',
        'poor_service'    => 'Poor Service Quality',
        'no_show'         => 'No Show / Abandoned Order',
        'overcharging'    => 'Overcharging',
        'fake_listing'    => 'Fake or Misleading Listing',
        'harassment'      => 'Harassment or Misconduct',
        'impersonation'   => 'Impersonation',
        'other'           => 'Other',
    ];

    // ============================================================
    // Submit a complaint
    // ============================================================
    public function submit(array $data): string|false
    {
        $ticketId = $this->generateTicketId();

        return $this->create([
            'ticket_id'       => $ticketId,
            'vendor_id'       => (int)$data['vendor_id'],
            'user_id'         => (int)$data['user_id'],
            'category'        => $data['category'],
            'description'     => Sanitizer::textarea($data['description'], MAX_COMPLAINT_LENGTH),
            'evidence_file'   => $data['evidence_file'] ?? null,
            'status'          => 'submitted',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Get complaint by ticket ID
    // ============================================================
    public function findByTicket(string $ticketId): ?array
    {
        return $this->db->fetchOne(
            "SELECT c.*, v.business_name, u.full_name as user_name
             FROM complaints c
             JOIN vendors v ON v.id = c.vendor_id
             JOIN users u ON u.id = c.user_id
             WHERE c.ticket_id = ?",
            [$ticketId]
        );
    }

    // ============================================================
    // Get complaints by user
    // ============================================================
    public function getByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, v.business_name
             FROM complaints c
             JOIN vendors v ON v.id = c.vendor_id
             WHERE c.user_id = ?
             ORDER BY c.created_at DESC",
            [$userId]
        );
    }

    // ============================================================
    // Get complaints against vendor
    // ============================================================
    public function getForVendor(int $vendorId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.full_name as user_name
             FROM complaints c
             JOIN users u ON u.id = c.user_id
             WHERE c.vendor_id = ?
             ORDER BY c.created_at DESC",
            [$vendorId]
        );
    }

    // ============================================================
    // Count verified complaints against vendor
    // ============================================================
    public function countVerified(int $vendorId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM complaints 
             WHERE vendor_id = ? AND status = 'verified'",
            [$vendorId]
        );
    }

    // ============================================================
    // Get all complaints for admin (paginated)
    // ============================================================
    public function getAllPaginated(
        int    $page = 1,
        int    $perPage = 25,
        string $status = ''
    ): array {
        $conditions = [];
        $params     = [];

        if (!empty($status)) {
            $conditions[] = "c.status = ?";
            $params[]      = $status;
        }

        $where  = $conditions ? implode(' AND ', $conditions) : '1';
        $total  = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM complaints c WHERE $where", $params
        );

        $offset   = ($page - 1) * $perPage;
        $params[] = $perPage;
        $params[] = $offset;

        $data = $this->db->fetchAll(
            "SELECT c.*, v.business_name, u.full_name as user_name
             FROM complaints c
             JOIN vendors v ON v.id = c.vendor_id
             JOIN users u ON u.id = c.user_id
             WHERE $where
             ORDER BY c.created_at DESC
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
    // Update complaint status
    // ============================================================
    public function updateStatus(
        int    $complaintId,
        string $status,
        string $adminNote = '',
        int    $adminId = 0
    ): bool {
        return $this->update($complaintId, [
            'status'     => $status,
            'admin_note' => $adminNote,
            'handled_by' => $adminId ?: null,
            'handled_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Add vendor response to complaint
    // ============================================================
    public function addVendorResponse(
        int    $complaintId,
        int    $vendorId,
        string $response
    ): bool {
        $complaint = $this->find($complaintId);
        if (!$complaint || (int)$complaint['vendor_id'] !== $vendorId) return false;

        return $this->update($complaintId, [
            'vendor_response'    => Sanitizer::textarea($response, 1000),
            'vendor_responded_at'=> date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Generate unique ticket ID
    // ============================================================
    private function generateTicketId(): string
    {
        do {
            $ticket = 'CL-' . strtoupper(bin2hex(random_bytes(4)));
        } while ($this->exists('ticket_id', $ticket));

        return $ticket;
    }

    // ============================================================
    // Get complaint categories
    // ============================================================
    public static function getCategories(): array
    {
        return self::CATEGORIES;
    }
}