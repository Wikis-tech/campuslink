<?php
/**
 * CampusLink - Core Mailer Proxy
 * Loads config/mailer.php and re-exports the Mailer class
 * so controllers can do: require_once 'core/Mailer.php'
 */

defined('CAMPUSLINK') or die('Direct access not permitted.');

if (!class_exists('Mailer')) {
    require_once __DIR__ . '/../config/mailer.php';
}