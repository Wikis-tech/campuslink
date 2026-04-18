<?php
/**
 * CampusLink - Core Application Router
 * Handles URL routing and dispatches to controllers.
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class App
{
    private string $controller = 'HomeController';
    private string $method     = 'index';
    private array  $params     = [];

    private array $routes = [
        // Public routes
        ''                          => ['HomeController',         'index'],
        'home'                      => ['HomeController',         'index'],
        'browse'                    => ['SearchController',       'browse'],
        'categories'                => ['CategoryController',     'index'],
        'category'                  => ['CategoryController',     'view'],
        'how-it-works'              => ['PageController',         'howItWorks'],
        'about'                     => ['PageController',         'about'],
        'contact'                   => ['PageController',         'contact'],

        // Auth routes
        'login'                     => ['AuthController',         'login'],
        'logout'                    => ['AuthController',         'logout'],
        'register'                  => ['AuthController',         'register'],
        'verify-email'              => ['AuthController',         'verifyEmail'],
        'verify-otp'                => ['AuthController',         'verifyOtp'],
        'forgot-password'           => ['AuthController',         'forgotPassword'],
        'reset-password'            => ['AuthController',         'resetPassword'],

        // User routes
        'user/dashboard'            => ['UserController',         'dashboard'],
        'user/profile'              => ['UserController',         'profile'],
        'user/saved-vendors'        => ['UserController',         'savedVendors'],
        'user/my-reviews'           => ['UserController',         'myReviews'],
        'user/my-complaints'        => ['UserController',         'myComplaints'],
        'user/notifications'        => ['UserController',         'notifications'],

        // Vendor public
        'vendor/register'           => ['RegistrationController', 'index'],
        'vendor/register/student'   => ['RegistrationController', 'student'],
        'vendor/register/community' => ['RegistrationController', 'community'],
        'vendor/login'              => ['AuthController',         'vendorLogin'],
        'vendor/payment'            => ['PaymentController',      'index'],
        'vendor/payment/success'    => ['PaymentController',      'success'],
        'vendor/payment/failed'     => ['PaymentController',      'failed'],

        // Vendor dashboard
        'vendor/dashboard'          => ['VendorController',       'dashboard'],
        'vendor/profile'            => ['VendorController',       'profile'],
        'vendor/reviews'            => ['VendorController',       'reviews'],
        'vendor/complaints'         => ['VendorController',       'complaints'],
        'vendor/subscription'       => ['VendorController',       'subscription'],
        'vendor/payment-history'    => ['VendorController',       'paymentHistory'],
        'vendor/notifications'      => ['VendorController',       'notifications'],

        // Reviews & Complaints
        'reviews/submit'            => ['ReviewController',       'submit'],
        'reviews/edit'              => ['ReviewController',       'edit'],
        'reviews/delete'            => ['ReviewController',       'delete'],
        'complaints/submit'         => ['ComplaintController',    'submit'],
        'complaints/track'          => ['ComplaintController',    'track'],

        // Save vendor
        'save-vendor'               => ['SavedVendorController',  'toggle'],

        // Legal pages
        'pages/general-terms'       => ['PageController',         'generalTerms'],
        'pages/user-terms'          => ['PageController',         'userTerms'],
        'pages/vendor-terms'        => ['PageController',         'vendorTerms'],
        'pages/privacy-policy'      => ['PageController',         'privacyPolicy'],
        'pages/refund-policy'       => ['PageController',         'refundPolicy'],
        'pages/suspension-policy'   => ['PageController',         'suspensionPolicy'],
        'pages/complaint-resolution'=> ['PageController',         'complaintResolution'],
        'pages/data-retention'      => ['PageController',         'dataRetention'],

        // Error page
        'error'                     => ['PageController',         'error'],
    ];

    // ============================================================
    // Constructor — parse URL and dispatch
    // ============================================================
    public function __construct()
    {
        $route = $this->parseRoute();
        $this->dispatch($route);
    }

    // ============================================================
    // Parse the route from the URL
    // ============================================================
    private function parseRoute(): string
    {
        // Get route from .htaccess rewrite
        $route = $_GET['route'] ?? '';

        // Sanitize: remove leading/trailing slashes, strip null bytes
        $route = trim($route, '/');
        $route = str_replace("\0", '', $route);

        // Allow only safe characters
        $route = preg_replace('/[^a-zA-Z0-9\-_\/]/', '', $route);

        return $route;
    }

    // ============================================================
    // Dispatch to the correct controller and method
    // ============================================================
    private function dispatch(string $route): void
    {
        // Check for vendor profile route: vendor/{slug}
        if (preg_match('/^vendor\/([a-zA-Z0-9\-_]+)$/', $route, $matches)) {
            // Could be a vendor profile public page
            $slug = $matches[1];
            // Avoid matching known sub-routes
            $knownVendorRoutes = [
                'register','login','payment','dashboard',
                'profile','reviews','complaints','subscription',
                'payment-history','notifications'
            ];
            if (!in_array($slug, $knownVendorRoutes)) {
                $this->loadController('SearchController', 'vendorProfile', [$slug]);
                return;
            }
        }

        // Check defined routes
        if (isset($this->routes[$route])) {
            [$controllerName, $method] = $this->routes[$route];
            $this->loadController($controllerName, $method, $this->params);
            return;
        }

        // 404 fallback
        http_response_code(404);
        $this->loadController('PageController', 'error', ['code' => 404]);
    }

    // ============================================================
    // Load and instantiate controller
    // ============================================================
    private function loadController(
        string $controllerName,
        string $method,
        array  $params = []
    ): void {
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            http_response_code(500);
            die(APP_ENV === 'development'
                ? "Controller file not found: $controllerFile"
                : 'System error. Please try again later.'
            );
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            http_response_code(500);
            die(APP_ENV === 'development'
                ? "Controller class not found: $controllerName"
                : 'System error. Please try again later.'
            );
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $method)) {
            http_response_code(404);
            $this->loadController('PageController', 'error', ['code' => 404]);
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }
}