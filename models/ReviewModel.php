<?php
/**
 * CampusLink - Review Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class ReviewModel extends Model
{
    protected string $table = 'reviews';

    // ============================================================
    // Submit a review
    // ============================================================
    public function submit(array $data): string|false
    {
        return $this->create([
            'vendor_id'  => (int)$data['vendor_id'],
            'user_id'    => (int)$data['user_id'],
            'rating'     => (int)$data['rating'],
            'review'     => Sanitizer::textarea($data['review'], MAX_REVIEW_LENGTH),
            'status'     => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Check if user already reviewed vendor
    // ============================================================
    public function hasReviewed(int $userId, int $vendorId): bool
    {
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM reviews WHERE user_id = ? AND vendor_id = ?",
            [$userId, $vendorId]
        );
    }

    // ============================================================
    // Get review by user and vendor
    // ============================================================
    public function getUserVendorReview(int $userId, int $vendorId): ?array
    {
        return $this->db->fetchOne(
            "SELECT * FROM reviews WHERE user_id = ? AND vendor_id = ? LIMIT 1",
            [$userId, $vendorId]
        );
    }

    // ============================================================
    // Get approved reviews for a vendor
    // ============================================================
    public function getApprovedForVendor(int $vendorId, int $limit = 10, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, u.full_name as user_name, u.level, u.department
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.vendor_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?",
            [$vendorId, $limit, $offset]
        );
    }

    // ============================================================
    // Count approved reviews for vendor
    // ============================================================
    public function countApprovedForVendor(int $vendorId): int
    {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM reviews WHERE vendor_id = ? AND status = 'approved'",
            [$vendorId]
        );
    }

    // ============================================================
    // Get average rating for vendor
    // ============================================================
    public function getAverageRating(int $vendorId): float
    {
        $avg = $this->db->fetchColumn(
            "SELECT ROUND(AVG(rating), 1) FROM reviews 
             WHERE vendor_id = ? AND status = 'approved'",
            [$vendorId]
        );
        return (float)($avg ?? 0);
    }

    // ============================================================
    // Get rating distribution for vendor
    // ============================================================
    public function getRatingDistribution(int $vendorId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT rating, COUNT(*) as count 
             FROM reviews 
             WHERE vendor_id = ? AND status = 'approved'
             GROUP BY rating ORDER BY rating DESC",
            [$vendorId]
        );

        $dist = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
        foreach ($rows as $row) {
            $dist[(int)$row['rating']] = (int)$row['count'];
        }
        return $dist;
    }

    // ============================================================
    // Edit review (only if pending or user's own + before moderation)
    // ============================================================
    public function editReview(int $reviewId, int $userId, array $data): bool
    {
        $review = $this->find($reviewId);
        if (!$review || (int)$review['user_id'] !== $userId) return false;
        if ($review['status'] === 'approved') return false;

        return $this->update($reviewId, [
            'rating'     => (int)$data['rating'],
            'review'     => Sanitizer::textarea($data['review'], MAX_REVIEW_LENGTH),
            'status'     => 'pending',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Delete review (only owner before moderation)
    // ============================================================
    public function deleteByUser(int $reviewId, int $userId): bool
    {
        $review = $this->find($reviewId);
        if (!$review || (int)$review['user_id'] !== $userId) return false;
        if ($review['status'] === 'approved') return false;
        return $this->delete($reviewId);
    }

    // ============================================================
    // Add vendor reply to review
    // ============================================================
    public function addVendorReply(int $reviewId, int $vendorId, string $reply): bool
    {
        $review = $this->find($reviewId);
        if (!$review || (int)$review['vendor_id'] !== $vendorId) return false;
        if ($review['status'] !== 'approved') return false;

        return $this->update($reviewId, [
            'vendor_reply'    => Sanitizer::textarea($reply, 500),
            'vendor_reply_at' => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Approve review (admin)
    // ============================================================
    public function approve(int $reviewId, int $adminId): bool
    {
        return $this->update($reviewId, [
            'status'      => 'approved',
            'moderated_by'=> $adminId,
            'moderated_at'=> date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Reject review (admin)
    // ============================================================
    public function reject(int $reviewId, int $adminId, string $reason = ''): bool
    {
        return $this->update($reviewId, [
            'status'          => 'rejected',
            'rejection_reason'=> $reason,
            'moderated_by'    => $adminId,
            'moderated_at'    => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Report a review as abusive
    // ============================================================
    public function report(int $reviewId, int $reportedBy, string $reason): bool
    {
        $existing = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM review_reports WHERE review_id = ? AND reported_by = ?",
            [$reviewId, $reportedBy]
        );
        if ($existing) return false;

        $this->db->execute(
            "INSERT INTO review_reports (review_id, reported_by, reason, created_at)
             VALUES (?, ?, ?, NOW())",
            [$reviewId, $reportedBy, $reason]
        );

        return true;
    }

    // ============================================================
    // Get pending reviews for admin moderation
    // ============================================================
    public function getPending(int $limit = 25, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, u.full_name as user_name, v.business_name
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             JOIN vendors v ON v.id = r.vendor_id
             WHERE r.status = 'pending'
             ORDER BY r.created_at ASC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    // ============================================================
    // Get reviews by user
    // ============================================================
    public function getByUser(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, v.business_name, v.slug as vendor_slug
             FROM reviews r
             JOIN vendors v ON v.id = r.vendor_id
             WHERE r.user_id = ?
             ORDER BY r.created_at DESC",
            [$userId]
        );
    }

    // ============================================================
    // Get reviews for vendor dashboard (with user info)
    // ============================================================
    public function getForVendorDashboard(int $vendorId): array
    {
        return $this->db->fetchAll(
            "SELECT r.*, u.full_name as user_name
             FROM reviews r
             JOIN users u ON u.id = r.user_id
             WHERE r.vendor_id = ?
             ORDER BY r.created_at DESC",
            [$vendorId]
        );
    }

    // ============================================================
    // Get global average rating
    // ============================================================
    public function getGlobalAverage(): float
    {
        $avg = $this->db->fetchColumn(
            "SELECT ROUND(AVG(rating), 1) FROM reviews WHERE status = 'approved'"
        );
        return (float)($avg ?? 0);
    }
}