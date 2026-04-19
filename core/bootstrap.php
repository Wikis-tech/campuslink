<?php

/**

 * CampusLink — Application Bootstrap

 * This is the first file loaded by index.php

 * It sets up everything the app needs to run

 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

// ─────────────────────────────────────────────────────────────────

// 1. ERROR REPORTING

// Set to 0 and comment out the display line when site goes live

// ─────────────────────────────────────────────────────────────────

ini_set('display_errors', 1);

ini_set('display_startup_errors', 1);

error_reporting(E_ALL);

// ─────────────────────────────────────────────────────────────────

// 2. PATH CONSTANTS

// __DIR__ here = /home/.../htdocs/core

// We go one level up (..) to get the root

// ─────────────────────────────────────────────────────────────────


define('ROOT_PATH',        realpath(__DIR__ . '/..'));

define('CORE_PATH',        ROOT_PATH . '/core');

define('VIEWS_PATH',       ROOT_PATH . '/views');

define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');

define('MODELS_PATH',      ROOT_PATH . '/models');

define('ASSETS_PATH',      ROOT_PATH . '/assets');

define('UPLOADS_PATH',     ROOT_PATH . '/assets/uploads');

// ─────────────────────────────────────────────────────────────────

// 3. LOAD CONFIG

// ─────────────────────────────────────────────────────────────────

require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

// ─────────────────────────────────────────────────────────────────

// 4. LOAD CORE CLASSES

// ─────────────────────────────────────────────────────────────────

require_once CORE_PATH . '/DB.php';

require_once CORE_PATH . '/Session.php';

require_once CORE_PATH . '/CSRF.php';

require_once CORE_PATH . '/Auth.php';

require_once CORE_PATH . '/AdminAuth.php';

require_once CORE_PATH . '/Mailer.php';

require_once CORE_PATH . '/helpers.php';

require_once CORE_PATH . '/Sanitizer.php';

// ─────────────────────────────────────────────────────────────────

// 5. LOAD MODELS

// ─────────────────────────────────────────────────────────────────

$modelFiles = glob(MODELS_PATH . '/*.php');

foreach ($modelFiles as $modelFile) {

    require_once $modelFile;

}

// ─────────────────────────────────────────────────────────────────

// 6. START SESSION

// ─────────────────────────────────────────────────────────────────

Session::start();

// ─────────────────────────────────────────────────────────────────

// 7. DATABASE CONNECTION

// Connect once — DB::getInstance() is used everywhere

// ─────────────────────────────────────────────────────────────────

try {

    DB::getInstance();

} catch (Exception $e) {

    // If DB fails, show a clean error — don't expose credentials

    if (defined('APP_DEBUG') && APP_DEBUG) {

        die('<h2>Database Connection Failed</h2><p>' . $e->getMessage() . '</p>');

    }

    die('<h2>Service temporarily unavailable. Please try again later.</h2>');

}

// ─────────────────────────────────────────────────────────────────

// 8. TIMEZONE

// ─────────────────────────────────────────────────────────────────

date_default_timezone_set('Africa/Lagos');

// ─────────────────────────────────────────────────────────────────

// 9. GLOBAL HELPER: Base Controller class

// All controllers extend this

// ─────────────────────────────────────────────────────────────────

if (!class_exists('BaseController')) {

    abstract class BaseController {

        protected DB     $db;

        protected string $viewsPath;

        public function __construct() {

            $this->db        = DB::getInstance();

            $this->viewsPath = VIEWS_PATH;

        }

        /**

         * Render a view inside the correct layout

         */

        protected function render(

            string $view,

            array  $data   = [],

            string $layout = 'main'

        ): void {

            // Make all data variables available in the view

            extract($data);

            // Capture the view output

            ob_start();

            $viewFile = $this->viewsPath . '/' . $view . '.php';

            if (!file_exists($viewFile)) {

                ob_end_clean();

                $this->notFound();

                return;

            }

            require $viewFile;

            $content = ob_get_clean();

            // Load the layout which will echo $content

            $layoutFile = $this->viewsPath . '/layouts/' . $layout . '.php';

            if (file_exists($layoutFile)) {

                require $layoutFile;

            } else {

                // Fallback: just output content without layout

                echo $content;

            }

        }

        /**

         * Redirect to a URL

         */

        protected function redirect(

            string $url,

            string $message = '',

            string $type    = 'success'

        ): void {

            if ($message) {

                Session::setFlash($type, $message);

            }

            // If URL starts with http, use as-is

            // Otherwise prepend SITE_URL

            if (!str_starts_with($url, 'http')) {

                $url = SITE_URL . '/' . ltrim($url, '/');

            }

            header('Location: ' . $url);

            exit;

        }

        /**

         * Return JSON response (for AJAX endpoints)

         */

        protected function json(array $data, int $status = 200): void {

            http_response_code($status);

            header('Content-Type: application/json');

            echo json_encode($data);

            exit;

        }

        /**

         * Require POST method or redirect

         */

        protected function requirePost(string $redirectTo = ''): void {

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

                $this->redirect($redirectTo ?: SITE_URL);

            }

        }

        /**

         * Verify CSRF token or die

         */

     protected function verifyCsrf(): void
{
    $token = $_POST['csrf_token']
          ?? $_SERVER['HTTP_X_CSRF_TOKEN']
          ?? '';

    if (!CSRF::validate($token)) {

                Session::setFlash('error', 'Invalid request. Please try again.');

                $this->redirect($_SERVER['HTTP_REFERER'] ?? SITE_URL);

            }

        }

        /**

         * 404 Not Found

         */

        public function notFound(): void {

            http_response_code(404);

            $this->render('pages/error', ['code' => 404], 'main');

        }

        /**

         * Paginate results

         */

        protected function paginate(int $total, int $perPage = 20): array {

            $page       = max(1, (int)($_GET['page'] ?? 1));

            $totalPages = max(1, (int)ceil($total / $perPage));

            $page       = min($page, $totalPages);

            return [

                'total'        => $total,

                'per_page'     => $perPage,

                'current_page' => $page,

                'total_pages'  => $totalPages,

                'offset'       => ($page - 1) * $perPage,

                'has_prev'     => $page > 1,

                'has_next'     => $page < $totalPages,

                'prev_page'    => $page - 1,

                'next_page'    => $page + 1,

            ];

        }

    }

}