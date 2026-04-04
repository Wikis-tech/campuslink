<?php
declare(strict_types=1);
require_once 'includes/bootstrap.php';

session_unset();
session_destroy();

// Clear remember-me cookie
setcookie('cl_remember', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'secure'   => APP_CONFIG['session']['secure'],
    'httponly' => true,
    'samesite' => 'Lax',
]);

header('Location: /pages/login?msg=logged_out');
exit;