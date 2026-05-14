<?php
/**
 * CampusLink - Base Controller
 * Foundation class for all controllers with common rendering and routing functionality.
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class BaseController {
    protected string $viewsPath;
    protected string $adminViewsPath;

    public function __construct() {
        $this->viewsPath = ROOT_PATH . '/views';
        $this->adminViewsPath = ROOT_PATH . '/views/admin';
    }

    /**
     * Render a view with data
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);
        
        // Construct view file path
        $viewFile = $this->viewsPath . '/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            if (APP_ENV === 'development') {
                die("View not found: {$viewFile}");
            }
            return;
        }

        // Include layout header if layout is specified
        if ($layout) {
            $layoutFile = $this->viewsPath . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                ob_start();
                require $viewFile;
                $content = ob_get_clean();
                extract(['content' => $content]);
                require $layoutFile;
            } else {
                require $viewFile;
            }
        } else {
            require $viewFile;
        }
    }

    /**
     * Render an admin view
     */
    protected function renderAdmin(string $view, array $data = [], string $layout = 'admin'): void {
        extract($data);
        
        // Construct view file path
        $viewFile = $this->adminViewsPath . '/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            if (APP_ENV === 'development') {
                die("Admin view not found: {$viewFile}");
            }
            return;
        }

        // Include layout header if layout is specified
        if ($layout) {
            $layoutFile = $this->adminViewsPath . '/layouts/' . $layout . '.php';
            if (file_exists($layoutFile)) {
                ob_start();
                require $viewFile;
                $content = ob_get_clean();
                extract(['content' => $content]);
                require $layoutFile;
            } else {
                require $viewFile;
            }
        } else {
            require $viewFile;
        }
    }

    /**
     * Redirect to another page
     */
    protected function redirect(string $url, string $message = '', string $type = 'success'): void {
        if ($message) {
            Session::setFlash($type, $message);
        }
        
        // Ensure URL is relative to site
        if (!str_starts_with($url, 'http') && !str_starts_with($url, '/')) {
            $url = SITE_URL . '/' . $url;
        } elseif (str_starts_with($url, '/') && !str_starts_with($url, SITE_URL)) {
            $url = SITE_URL . $url;
        }
        
        header("Location: {$url}");
        exit;
    }

    /**
     * Verify CSRF token
     */
    protected function verifyCsrf(): void {
        if (!CSRF::verify()) {
            http_response_code(403);
            die('CSRF token validation failed.');
        }
    }

    /**
     * Get CSRF token
     */
    protected function getCsrfToken(): string {
        return CSRF::generate();
    }
}
