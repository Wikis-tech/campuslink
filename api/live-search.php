<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';
Security::setHeaders();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Security::jsonError('Method not allowed.', 405);
}

$query = trim(Security::clean($_GET['q'] ?? ''));
$cat   = Security::clean($_GET['cat'] ?? '');
$limit = min((int)($_GET['limit'] ?? 6), 10);

if (strlen($query) < 2) {
    Security::jsonResponse(['results' => [], 'total' => 0]);
}

$pdo = Database::getInstance();

$sql    = "
    SELECT v.id, v.business_name, v.price_range, v.operating_location,
           v.description, v.logo_path, c.name AS category_name, c.slug AS category_slug,
           COALESCE(AVG(r.rating), 0) AS avg_rating,
           COUNT(r.id) AS review_count,
           v.status,
           CASE WHEN s.id IS NOT NULL THEN 1 ELSE 0 END AS is_featured
    FROM vendors v
    JOIN categories c ON c.id = v.category_id
    LEFT JOIN reviews r ON r.vendor_id = v.id AND r.status = 'approved'
    LEFT JOIN subscriptions s ON s.vendor_id = v.id AND s.status = 'active'
                                AND (SELECT p.slug FROM plans p WHERE p.id = s.plan_id) = 'featured_student'
    WHERE v.status = 'active'
      AND v.is_verified = 1
      AND (
          v.business_name LIKE :q1
          OR v.description LIKE :q2
          OR c.name LIKE :q3
      )
";
$params = ['q1' => "%{$query}%", 'q2' => "%{$query}%", 'q3' => "%{$query}%"];

if ($cat) {
    $sql .= " AND c.slug = :cat";
    $params['cat'] = $cat;
}

$sql .= " GROUP BY v.id ORDER BY is_featured DESC, avg_rating DESC LIMIT :limit";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) { $stmt->bindValue(":{$k}", $v); }
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

$results = array_map(function($r) {
    $logoUrl = $r['logo_path']
        ? BASE_URL . '/api/file/' . $r['logo_path']
        : null;
    return [
        'id'            => $r['id'],
        'name'          => Security::escape($r['business_name']),
        'category'      => Security::escape($r['category_name']),
        'category_slug' => $r['category_slug'],
        'price'         => Security::escape($r['price_range'] ?? ''),
        'location'      => Security::escape($r['operating_location'] ?? ''),
        'rating'        => round((float)$r['avg_rating'], 1),
        'reviews'       => (int)$r['review_count'],
        'logo'          => $logoUrl,
        'is_featured'   => (bool)$r['is_featured'],
        'url'           => '/vendor-profile?id=' . $r['id'],
    ];
}, $rows);

Security::jsonResponse([
    'results' => $results,
    'total'   => count($results),
    'query'   => Security::escape($query),
]);