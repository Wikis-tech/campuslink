<?php
/**
 * CampusLink - Search & Browse Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/VendorModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/ReviewModel.php';
require_once __DIR__ . '/../models/SavedVendorModel.php';

class SearchController extends Controller
{
    private VendorModel      $vendorModel;
    private CategoryModel    $categoryModel;
    private ReviewModel      $reviewModel;
    private SavedVendorModel $savedModel;

    public function __construct()
    {
        parent::__construct();
        $this->vendorModel   = new VendorModel();
        $this->categoryModel = new CategoryModel();
        $this->reviewModel   = new ReviewModel();
        $this->savedModel    = new SavedVendorModel();
    }

    // ============================================================
    // Browse all vendors
    // ============================================================
    public function browse(): void
    {
        $search   = Sanitizer::text($this->get('q', ''), 100);
        $category = Sanitizer::slug($this->get('category', ''));
        $sortBy   = $this->get('sort', 'plan_priority');
        $page     = max(1, (int)$this->get('page', 1));
        $perPage  = VENDORS_PER_PAGE;
        $offset   = ($page - 1) * $perPage;

        $validSorts = ['plan_priority', 'rating', 'newest', 'name'];
        if (!in_array($sortBy, $validSorts)) $sortBy = 'plan_priority';

        $total   = $this->vendorModel->countActive($category, $search);
        $vendors = $this->vendorModel->getActive($perPage, $offset, $category, $search, $sortBy);
        $categories = $this->categoryModel->getAllWithCounts();

        $pagination = [
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'total_pages'  => (int)ceil($total / $perPage),
            'has_prev'     => $page > 1,
            'has_next'     => ($page * $perPage) < $total,
        ];

        // Get saved vendor IDs for logged-in user
        $savedIds = [];
        if (Auth::isLoggedIn()) {
            $saved = $this->savedModel->getForUser(Auth::userId());
            $savedIds = array_column($saved, 'vendor_id');
        }

        $this->view('browse/index', [
            'pageTitle'  => 'Browse Campus Services - ' . SITE_NAME,
            'vendors'    => $vendors,
            'categories' => $categories,
            'pagination' => $pagination,
            'search'     => $search,
            'category'   => $category,
            'sortBy'     => $sortBy,
            'savedIds'   => $savedIds,
        ]);
    }

    // ============================================================
    // Public Vendor Profile Page
    // ============================================================
    public function vendorProfile(string $slug): void
    {
        $vendor = $this->vendorModel->findBySlug($slug);

        if (!$vendor) {
            http_response_code(404);
            $this->view('pages/error', ['code' => 404, 'pageTitle' => '404 Not Found - ' . SITE_NAME]);
            return;
        }

        $page    = max(1, (int)$this->get('page', 1));
        $reviews = $this->reviewModel->getApprovedForVendor(
            $vendor['id'],
            REVIEWS_PER_PAGE,
            ($page - 1) * REVIEWS_PER_PAGE
        );
        $reviewCount = $this->reviewModel->countApprovedForVendor($vendor['id']);
        $dist        = $this->reviewModel->getRatingDistribution($vendor['id']);
        $avgRating   = $this->reviewModel->getAverageRating($vendor['id']);

        // Check if user already reviewed
        $userReview = null;
        $isSaved    = false;
        if (Auth::isLoggedIn()) {
            $userId = Auth::userId();
            $userReview = $this->reviewModel->getUserVendorReview($userId, $vendor['id']);
            $isSaved    = $this->savedModel->isSaved($userId, $vendor['id']);
        }

        $reviewPagination = [
            'total'        => $reviewCount,
            'per_page'     => REVIEWS_PER_PAGE,
            'current_page' => $page,
            'total_pages'  => (int)ceil($reviewCount / REVIEWS_PER_PAGE),
            'has_prev'     => $page > 1,
            'has_next'     => ($page * REVIEWS_PER_PAGE) < $reviewCount,
        ];

        $this->view('browse/vendor-profile', [
            'pageTitle'        => e($vendor['business_name']) . ' - ' . SITE_NAME,
            'pageDesc'         => truncate($vendor['description'] ?? '', 160),
            'vendor'           => $vendor,
            'reviews'          => $reviews,
            'reviewCount'      => $reviewCount,
            'avgRating'        => $avgRating,
            'dist'             => $dist,
            'userReview'       => $userReview,
            'isSaved'          => $isSaved,
            'reviewPagination' => $reviewPagination,
            'csrfField'        => CSRF::field(),
            'complaintCats'    => ComplaintModel::getCategories(),
        ]);
    }
}