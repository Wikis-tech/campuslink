<?php
defined('CAMPUSLINK') or die('Direct access not permitted.');

class BrowseController extends BaseController {

    public function index(): void {
    $db         = DB::getInstance();
    $search     = clean($_GET['q']        ?? '');
    $categoryId = (int)($_GET['category'] ?? 0);
    $sort       = $_GET['sort']            ?? 'featured';
    $page       = max(1, (int)($_GET['page'] ?? 1));
    $perPage    = 12;

    // Build WHERE clause - show vendors with active subscriptions OR free student vendors
    $where  = ["v.status = 'active' AND ((s.id IS NULL AND v.vendor_type = 'student' AND v.plan_type = 'basic') OR (s.status = 'active' AND s.expiry_date > NOW()))"];
    $params = [];

    if ($search) {
        $where[]  = '(v.business_name LIKE ? OR v.description LIKE ?)';
        $s        = "%{$search}%";
        $params[] = $s;
        $params[] = $s;
    }

    if ($categoryId) {
        $where[]  = 'v.category_id = ?';
        $params[] = $categoryId;
    }

    $whereStr = implode(' AND ', $where);

    // Sort — avg_rating is calculated via subquery, not a column
    $orderBy = match($sort) {
        'rating'  => 'avg_rating DESC, review_count DESC',
        'newest'  => 'v.created_at DESC',
        'alpha'   => 'v.business_name ASC',
        default   => "FIELD(v.plan_type,'featured','premium','basic'), avg_rating DESC",
    };

    // Count total for pagination
    $total = $db->value(
        "SELECT COUNT(*) FROM vendors v 
         LEFT JOIN subscriptions s ON s.vendor_id = v.id 
         WHERE {$whereStr}",
        $params
    );

    error_log("Browse: total count {$total} for where: {$whereStr}");

    $pag    = paginate($total, $perPage);
    $offset = $pag['offset'];

    // Main query — calculates avg_rating and review_count via LEFT JOIN
    $vendors = $db->rows(
        "SELECT v.*,
                c.name AS category_name,
                c.icon AS category_icon,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.id)               AS review_count
           FROM vendors v
           LEFT JOIN subscriptions s ON s.vendor_id = v.id
      LEFT JOIN categories c ON v.category_id = c.id
      LEFT JOIN reviews r    ON r.vendor_id = v.id
          WHERE {$whereStr}
          GROUP BY v.id
          ORDER BY {$orderBy}
          LIMIT {$perPage} OFFSET {$offset}",
        $params
    );

    error_log("Browse: fetched " . count($vendors) . " vendors");

    $categories = $db->rows(
        "SELECT c.*, COUNT(CASE WHEN v.status='active' AND ((s.id IS NULL AND v.vendor_type = 'student' AND v.plan_type = 'basic') OR (s.status = 'active' AND s.expiry_date > NOW())) THEN 1 END) AS vendor_count
           FROM categories c
      LEFT JOIN vendors v ON v.category_id = c.id
      LEFT JOIN subscriptions s ON s.vendor_id = v.id
          WHERE c.is_active = 1
          GROUP BY c.id
          ORDER BY c.sort_order ASC, c.name ASC"
    );

    $currentCategory = $categoryId
        ? $db->row("SELECT * FROM categories WHERE id = ?", [$categoryId])
        : null;
    $pageTitle = 'Browse Vendors';

    // Saved vendor IDs for logged in user
    $savedIds = [];
    $userId   = 0;
    if (Auth::isLoggedIn()) {
        $userId   = Auth::userId();
        $saved    = $db->rows(
            "SELECT vendor_id FROM saved_vendors WHERE user_id = ?",
            [$userId]
        );
        $savedIds = array_column($saved, 'vendor_id');
    }

    $layout = Auth::isLoggedIn() ? 'user' : 'main';
    $extraCss = ['browse.css'];
    $extraJs  = ['browse.js'];

    $this->render('browse/index', compact(
        'vendors', 'categories', 'currentCategory',
        'search', 'categoryId', 'sort', 'pag',
        'savedIds', 'userId', 'pageTitle',
        'extraCss', 'extraJs'
    ), $layout);
}

    public function vendorProfile(string $slug): void
{
    // Require login to view vendor profiles
    if (!Auth::isLoggedIn() && !Auth::isVendorLoggedIn()) {
        Session::setFlash('error', 'Please log in to view vendor profiles.');
        redirect('login?redirect=' . urlencode('browse/' . $slug));
        exit;
    }
    // ... rest of existing code
        $db = DB::getInstance();

        // First try to find active vendor
        $vendor = $db->row(
    "SELECT v.*,
            c.name AS category_name,
            c.icon AS category_icon,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(r.id)               AS review_count
       FROM vendors v
  LEFT JOIN categories c ON v.category_id = c.id
  LEFT JOIN reviews r    ON r.vendor_id = v.id
      WHERE v.slug = ? AND v.status = 'active'
      GROUP BY v.id",
    [$slug]
);

        // If not found and user is logged in as vendor, check if it's their own profile
        if (!$vendor && Auth::isVendorLoggedIn()) {
            $vendor = $db->row(
    "SELECT v.*,
            c.name AS category_name,
            c.icon AS category_icon,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(r.id)               AS review_count
       FROM vendors v
  LEFT JOIN categories c ON v.category_id = c.id
  LEFT JOIN reviews r    ON r.vendor_id = v.id
      WHERE v.slug = ? AND v.vendor_id = ?
      GROUP BY v.id",
    [$slug, Auth::vendorId()]
);
        }

        $pageTitle = $vendor ? $vendor['business_name'] : 'Vendor Profile';

        if (!$vendor) {
            $this->notFound();
            return;
        }

        // Reviews
        $reviewsTotal = $db->value(
            "SELECT COUNT(*) FROM reviews WHERE vendor_id = ?",
            [$vendor['id']]
        );
        $reviewsPag = paginate($reviewsTotal, 5);

        $reviews = $db->rows(
            "SELECT r.*, u.full_name AS user_name, u.level AS user_level,
                    u.department AS user_dept
               FROM reviews r
          LEFT JOIN users u ON r.user_id = u.id
              WHERE r.vendor_id = ?
              ORDER BY r.created_at DESC
              LIMIT 5 OFFSET {$reviewsPag['offset']}",
            [$vendor['id']]
        );

        $avgRating = $vendor['avg_rating'] ?? 0;
        $reviewTotal = $reviewsTotal;
        $currentUserReview = null;
        if (Auth::isLoggedIn()) {
            $currentUserReview = $db->row(
                "SELECT * FROM reviews WHERE vendor_id = ? AND user_id = ? LIMIT 1",
                [$vendor['id'], Auth::userId()]
            );
        }

        // Rating breakdown
        $ratingBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $db->value(
                "SELECT COUNT(*) FROM reviews WHERE vendor_id = ? AND rating = ?",
                [$vendor['id'], $i]
            );
            $ratingBreakdown[$i] = (int)$count;
        }

        // Saved state
        $isSaved  = false;
        $userId   = 0;
        $hasReview = false;

        if (Auth::isLoggedIn()) {
            $userId    = Auth::userId();
            $isSaved   = $db->exists(
                'saved_vendors',
                'user_id = ? AND vendor_id = ?',
                [$userId, $vendor['id']]
            );
            $hasReview = $db->exists(
                'reviews',
                'user_id = ? AND vendor_id = ?',
                [$userId, $vendor['id']]
            );
        }

        $pageTitle = 'CampusLink Vendor Profile - ' . ($vendor['business_name'] ?? 'Vendor');
        $layout = 'vendor-profile';

        $this->render('browse/vendor-profile', compact(
            'vendor', 'reviews', 'reviewsPag', 'ratingBreakdown',
            'isSaved', 'userId', 'hasReview', 'avgRating', 'reviewTotal',
            'currentUserReview', 'pageTitle'
        ), $layout);
    }

    public function categories(): void {
    $db = DB::getInstance();

    $categories = $db->rows(
        "SELECT c.*,
                COUNT(v.id) AS vendor_count
           FROM categories c
      LEFT JOIN vendors v ON v.category_id = c.id
            AND v.status = 'active'
          GROUP BY c.id
          ORDER BY vendor_count DESC"
    );

    $this->render('browse/categories', compact('categories'));
}
}