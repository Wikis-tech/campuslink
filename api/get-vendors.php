<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') Security::jsonError('Method not allowed.', 405);

$cat     = Security::clean($_GET['cat']     ?? '');
$type    = Security::clean($_GET['type']    ?? '');
$plan    = Security::clean($_GET['plan']    ?? '');
$sort    = Security::clean($_GET['sort']    ?? 'featured');
$rating  = max(0, min(5, (float)($_GET['rating'] ?? 0)));
$page    = max(1, (int)($_GET['page'] ?? 1));
$per     = min(24, max(6, (int)($_GET['per'] ?? 12)));
$offset  = ($page - 1) * $per;

try {
    $pdo    = Database::getInstance();
    $params = [];
    $where  = ["v.status = 'active'", "v.is_verified = 1"];

    if ($cat) {
        $where[]    = 'c.slug = :cat';
        $params['cat'] = $cat;
    }
    if ($type && in_array($type, ['student', 'community'], true)) {
        $where[]     = 'v.vendor_type = :type';
        $params['type'] = $type;
    }
    if ($plan && in_array($plan, ['basic', 'premium', 'featured'], true)) {
        $where[]     = 'p.slug LIKE :plan';
        $params['plan'] = "%{$plan}%";
    }
    if ($rating > 0) {
        $where[]      = 'COALESCE(AVG(r.rating),0) >= :rating';
        $params['rating'] = $rating;
    }

    $whereSQL = implode(' AND ', $where);

    $orderSQL = match($sort) {
        'rating'  => 'avg_rating DESC',
        'reviews' => 'review_count DESC',
        'newest'  => 'v.created_at DESC',
        'name'    => 'v.business_name ASC',
        default   => 'is_featured DESC, avg_rating DESC',
    };

    // Count total
    $countSQL = "
        SELECT COUNT(DISTINCT v.id) as total
        FROM vendors v
        JOIN categories c ON c.id = v.category_id
        LEFT JOIN subscriptions s ON s.vendor_id = v.id AND s.status = 'active'
        LEFT JOIN plans p ON p.id = s.plan_id
        LEFT JOIN reviews r ON r.vendor_id = v.id AND r.status = 'approved'
        WHERE {$whereSQL}
    ";
    $countStmt = $pdo->prepare($countSQL);
    foreach ($params as $k => $v) $countStmt->bindValue(":{$k}", $v);
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();

    // Fetch vendors
    $sql = "
        SELECT
            v.id, v.business_name, v.vendor_type,
            v.description, v.price_range, v.operating_location,
            v.logo_path, v.service_photo_path, v.phone, v.whatsapp,
            v.created_at,
            c.name AS category_name, c.slug AS category_slug,
            COALESCE(p.slug, '') AS plan_slug,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(DISTINCT r.id) AS review_count,
            CASE WHEN p.slug LIKE 'featured%' THEN 1 ELSE 0 END AS is_featured
        FROM vendors v
        JOIN categories c ON c.id = v.category_id
        LEFT JOIN subscriptions s ON s.vendor_id = v.id AND s.status = 'active'
        LEFT JOIN plans p ON p.id = s.plan_id
        LEFT JOIN reviews r ON r.vendor_id = v.id AND r.status = 'approved'
        WHERE {$whereSQL}
        GROUP BY v.id, c.name, c.slug, p.slug
        ORDER BY {$orderSQL}
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue(":{$k}", $v);
    $stmt->bindValue(':limit',  $per,    PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $vendors = $stmt->fetchAll();

    $vendors = array_map(function($v) {
        return [
            'id'          => $v['id'],
            'name'        => e($v['business_name']),
            'type'        => $v['vendor_type'],
            'category'    => e($v['category_name']),
            'cat_slug'    => $v['category_slug'],
            'desc'        => e(truncate($v['description'], 100)),
            'price'       => e($v['price_range'] ?? ''),
            'location'    => e($v['operating_location'] ?? ''),
            'phone'       => $v['phone'],
            'whatsapp'    => preg_replace('/[^0-9]/', '', $v['whatsapp'] ?? $v['phone']),
            'logo'        => $v['logo_path']
                             ? "/api/image-webp?path=" . urlencode($v['logo_path']) . "&w=120&q=80"
                             : null,
            'photo'       => $v['service_photo_path']
                             ? "/api/image-webp?path=" . urlencode($v['service_photo_path']) . "&w=600&q=75"
                             : null,
            'rating'      => round((float)$v['avg_rating'], 1),
            'reviews'     => (int)$v['review_count'],
            'plan'        => $v['plan_slug'],
            'is_featured' => (bool)$v['is_featured'],
            'url'         => '/vendor-profile?id=' . $v['id'],
            'joined'      => formatDate($v['created_at'], 'M Y'),
        ];
    }, $vendors);

    Security::jsonResponse([
        'success' => true,
        'vendors' => $vendors,
        'total'   => $total,
        'page'    => $page,
        'pages'   => (int)ceil($total / $per),
        'per'     => $per,
    ]);

} catch (\Throwable $e) {
    error_log('[CL GetVendors] ' . $e->getMessage());
    Security::jsonError('Failed to fetch vendors. Please try again.', 500);
}