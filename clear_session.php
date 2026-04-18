<?php
session_start();
unset($_SESSION['admin_logged_in'], $_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
echo 'Admin session cleared' . PHP_EOL;
echo 'Current session data: ' . print_r($_SESSION, true) . PHP_EOL;
?>