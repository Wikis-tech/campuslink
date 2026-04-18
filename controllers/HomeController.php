<?php
/**
 * CampusLink - Home Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/VendorModel.php';
require_once __DIR__ . '/../models/ReviewModel.php';
require_once __DIR__ . '/../models/SubscriptionModel.php';

class HomeController extends Controller
{
    private CategoryModel     $categoryModel;
    private VendorModel       $vendorModel;
    private ReviewModel       $reviewModel;
    private SubscriptionModel $subscriptionModel;

    public function __construct()
    {
        parent::__construct();
        $this->categoryModel     = new CategoryModel();
        $this->vendorModel       = new VendorModel();
        $this->reviewModel       = new ReviewModel();
        $this->subscriptionModel = new SubscriptionModel();
    }

    // ============================================================
    // Landing page
    // ============================================================
    public function index(): void
    {
        // Stats
        $totalVendors = $this->vendorModel->totalActive();
        $totalUsers   = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE status = 'active'"
        ) ?? 0;
        $activeSubs   = $this->subscriptionModel->countActive();
        $avgRating    = $this->reviewModel->getGlobalAverage();
        $avgRating    = $avgRating > 0 ? number_format($avgRating, 1) : '4.8';

        // Categories with counts
        $categories = $this->categoryModel->getAllWithCounts();

        // Featured vendors
        $featuredVendors = $this->vendorModel->getActive(
            limit: 6,
            offset: 0,
            sortBy: 'plan_priority'
        );

        $this->view('home/index', [
            'pageTitle'       => SITE_NAME . ' - ' . SITE_TAGLINE,
            'pageDesc'        => 'CampusLink connects students with verified campus vendors. Browse, contact, and get things done safely.',
            'totalVendors'    => $totalVendors,
            'totalUsers'      => $totalUsers,
            'activeSubs'      => $activeSubs,
            'avgRating'       => $avgRating,
            'categories'      => $categories,
            'featuredVendors' => $featuredVendors,
        ]);
    }
}