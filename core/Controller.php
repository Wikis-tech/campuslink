<?php
/**
 * CampusLink - Legacy Controller compatibility wrapper
 * Extends the newer BaseController so older controllers remain functional.
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Controller extends BaseController
{
    protected Session $session;

    public function __construct()
    {
        parent::__construct();
        $this->session = new Session();
    }

    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        $this->render($view, $data, $layout);
    }

    protected function viewPartial(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = $this->viewsPath . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            if (APP_ENV === 'development') {
                die("View not found: $viewFile");
            }
            return;
        }

        require $viewFile;
    }

    protected function json(array $data, int $code = 200): void
    {
        parent::json($data, $code);
    }

    protected function jsonSuccess(string $message, array $data = []): void
    {
        $this->json(array_merge(['status' => 'success', 'message' => $message], $data));
    }

    protected function jsonError(string $message, int $code = 400, array $data = []): void
    {
        $this->json(array_merge(['status' => 'error', 'message' => $message], $data), $code);
    }

    protected function redirect(string $url, string $message = '', string $type = 'success'): void
    {
        parent::redirect($url, $message, $type);
    }

    protected function redirectWith(string $path, string $type, string $message): void
    {
        Session::setFlash($type, $message);
        parent::redirect($path, $message, $type);
    }

    protected function requireLogin(string $redirectTo = 'login'): void
    {
        if (!Auth::isLoggedIn()) {
            Session::set('intended_url', currentUrl());
            $this->redirectWith($redirectTo, 'error', 'Please log in to continue.');
        }
    }

    protected function requireVendorLogin(): void
    {
        if (!Auth::isVendorLoggedIn()) {
            Session::set('intended_url', currentUrl());
            $this->redirectWith('vendor/login', 'error', 'Please log in to your vendor account.');
        }
    }

    protected function requireAdmin(): void
    {
        if (!Auth::isAdminLoggedIn()) {
            $this->redirect('admin/login');
        }
    }

    protected function requirePost(string $redirectTo = ''): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjax()) {
                $this->jsonError('Method not allowed', 405);
            } else {
                $this->redirect($redirectTo ?: SITE_URL);
            }
        }
    }

    protected function requireAjax(): void
    {
        if (!$this->isAjax()) {
            $this->jsonError('AJAX requests only', 400);
        }
    }

    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        if (!isset($_POST[$key])) {
            return $default;
        }
        return Sanitizer::clean($_POST[$key]);
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        if (!isset($_GET[$key])) {
            return $default;
        }
        return Sanitizer::clean($_GET[$key]);
    }

    protected function rawPost(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    protected function validateCSRF(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

        if (!CSRF::validate($token)) {
            if ($this->isAjax()) {
                $this->jsonError('Invalid security token. Please refresh and try again.', 403);
            } else {
                $this->redirectWith('/', 'error', 'Security validation failed. Please try again.');
            }
        }
    }

    protected function getPagination(int $total, int $perPage, string $pageParam = 'page'): array
    {
        $currentPage = max(1, (int)($this->get($pageParam) ?? 1));
        $totalPages  = max(1, (int)ceil($total / $perPage));
        $currentPage = min($currentPage, $totalPages ?: 1);
        $offset      = ($currentPage - 1) * $perPage;

        return [
            'current_page' => $currentPage,
            'total_pages'  => $totalPages,
            'per_page'     => $perPage,
            'total'        => $total,
            'offset'       => $offset,
            'has_prev'     => $currentPage > 1,
            'has_next'     => $currentPage < $totalPages,
        ];
    }
}
