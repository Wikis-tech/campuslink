<?php
/**
 * CampusLink - Category Controller
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../models/VendorModel.php';

class CategoryController extends Controller
{
    private CategoryModel $categoryModel;
    private VendorModel   $vendorModel;

    public function __construct()
    {
        parent::__construct();
        $this->categoryModel = new CategoryModel();
        $this->vendorModel   = new VendorModel();
    }

    // ============================================================
    // All Categories page
    // ============================================================
    public function index(): void
    {
        $categories = $this->categoryModel->getAllWithCounts();

        $this->view('browse/categories', [
            'pageTitle'  => 'All Categories - ' . SITE_NAME,
            'categories' => $categories,
        ]);
    }

    // ============================================================
    // Single Category page
    // ============================================================
    public function view(): void
    {
        $slug = Sanitizer::slug($this->get('slug', ''));

        if (empty($slug)) {
            $this->redirect('categories');
            return;
        }

        $category = $this->categoryModel->findBySlug($slug);

        if (!$category) {
            http_response_code(404);
            $this->view('pages/error', ['code' => 404, 'pageTitle' => '404 - ' . SITE_NAME]);
            return;
        }

        $page    = max(1, (int)$this->get('page', 1));
        $perPage = VENDORS_PER_PAGE;
        $offset  = ($page - 1) * $perPage;
        $total   = $this->vendorModel->countActive($slug);
        $vendors = $this->vendorModel->getActive($perPage, $offset, $slug);

        $this->view('browse/category', [
            'pageTitle'  => e($category['name']) . ' Vendors - ' . SITE_NAME,
            'category'   => $category,
            'vendors'    => $vendors,
            'pagination' => [
                'total'        => $total,
                'current_page' => $page,
                'total_pages'  => (int)ceil($total / $perPage),
                'has_prev'     => $page > 1,
                'has_next'     => ($page * $perPage) < $total,
            ],
        ]);
    }
}