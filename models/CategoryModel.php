<?php
/**
 * CampusLink - Category Model
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Model.php';

class CategoryModel extends Model
{
    protected string $table = 'categories';

    // ============================================================
    // Get all active categories with vendor counts
    // ============================================================
    public function getAllWithCounts(): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, 
                    COUNT(CASE WHEN v.status='active' THEN 1 END) as vendor_count
             FROM categories c
             LEFT JOIN vendors v ON v.category_id = c.id
             WHERE c.is_active = 1
             GROUP BY c.id
             ORDER BY c.sort_order ASC, c.name ASC"
        );
    }

    // ============================================================
    // Get category by slug
    // ============================================================
    public function findBySlug(string $slug): ?array
    {
        return $this->db->fetchOne(
            "SELECT c.*,
                    COUNT(CASE WHEN v.status='active' THEN 1 END) as vendor_count
             FROM categories c
             LEFT JOIN vendors v ON v.category_id = c.id
             WHERE c.slug = ? AND c.is_active = 1
             GROUP BY c.id",
            [$slug]
        );
    }

    // ============================================================
    // Get categories for dropdown
    // ============================================================
    public function getForSelect(): array
    {
        return $this->db->fetchAll(
            "SELECT id, name, slug FROM categories 
             WHERE is_active = 1 
             ORDER BY sort_order ASC, name ASC"
        );
    }

    // ============================================================
    // Create category
    // ============================================================
    public function createCategory(array $data): string|false
    {
        return $this->create([
            'name'       => $data['name'],
            'slug'       => Sanitizer::slug($data['name']),
            'icon'       => $data['icon'] ?? '🏪',
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ============================================================
    // Update category
    // ============================================================
    public function updateCategory(int $id, array $data): bool
    {
        return $this->update($id, [
            'name'       => $data['name'],
            'slug'       => Sanitizer::slug($data['name']),
            'icon'       => $data['icon'] ?? '🏪',
            'sort_order' => (int)($data['sort_order'] ?? 0),
            'is_active'  => (int)($data['is_active'] ?? 1),
        ]);
    }

    // ============================================================
    // Check slug availability
    // ============================================================
    public function slugAvailable(string $slug, int $excludeId = 0): bool
    {
        return !$this->exists('slug', $slug, $excludeId);
    }
}