<?php
/**
 * Daba Magic - Admin Auth Check Middleware
 * Ensures user is authenticated before accessing admin panel routes.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/db_connection.php';
require_once __DIR__ . '/../db_init.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
