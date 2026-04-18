<?php

/**

 * CampusLink — Router

 * This file is not directly used by index.php

 * (index.php does its own routing for clarity)

 * But it is kept here as a utility class

 * in case any controller needs to generate URLs

 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

class Router {

    /**

     * Generate a full URL from a path

     *

     * Router::url('/login')         → https://campuslinkd.rf.gd/login

     * Router::url('vendor/dashboard') → https://campuslinkd.rf.gd/vendor/dashboard

     */

    public static function url(string $path = ''): string {

        $path = ltrim($path, '/');

        return $path ? SITE_URL . '/' . $path : SITE_URL;

    }

    /**

     * Redirect to a URL immediately

     */

    public static function redirect(string $path, string $message = '', string $type = 'success'): void {

        if ($message) {

            Session::setFlash($type, $message);

        }

        header('Location: ' . static::url($path));

        exit;

    }

    /**

     * Get the current URL path without query string

     * e.g. returns 'vendor/dashboard' when on /vendor/dashboard?foo=bar

     */

    public static function currentPath(): string {

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $basePath   = dirname($scriptName);
        $basePath   = $basePath === '\\' ? '/' : str_replace('\\', '/', $basePath);

        if ($basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        return strtolower(trim($path, '/'));

    }

    /**

     * Check if the current path matches a given pattern

     * Supports wildcards e.g. 'vendor/*'

     */

    public static function is(string $pattern): bool {

        $current = static::currentPath();

        $pattern = trim($pattern, '/');

        if (str_contains($pattern, '*')) {

            $regex = '#^' . str_replace('*', '.*', preg_quote($pattern, '#')) . '$#';

            return (bool)preg_match($regex, $current);

        }

        return $current === $pattern;

    }

    /**

     * Get a query parameter safely

     */

    public static function query(string $key, mixed $default = null): mixed {

        return isset($_GET[$key]) ? htmlspecialchars(strip_tags($_GET[$key])) : $default;

    }

    /**

     * Get the current HTTP method

     */

    public static function method(): string {

        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

    }

    /**

     * Check if request is POST

     */

    public static function isPost(): bool {

        return static::method() === 'POST';

    }

    /**

     * Check if request is an AJAX request

     */

    public static function isAjax(): bool {

        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])

            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    }

}