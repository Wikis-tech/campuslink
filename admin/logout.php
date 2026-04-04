<?php
declare(strict_types=1);
require_once '../includes/bootstrap.php';

// Clear only admin session keys
unset(
    $_SESSION['admin_id'],
    $_SESSION['admin_email'],
    $_SESSION['admin_role'],
    $_SESSION['admin_name'],
    $_SESSION['role']
);

header('Location: /admin/');
exit;