<?php
/**
 * config.php
 * ----------
 * Database connection configuration for the Order Tracking System.
 * Edit these values to match your local environment.
 *
 * Team 12: Manohar Kota, Bhagya Teja Chalicham, Sai Sarvagna Beeram
 */

// ─────────────────────────────────────────────
// Database Credentials
// ─────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'order_tracking_db');
define('DB_PORT', 3306);

// ─────────────────────────────────────────────
// Application Settings
// ─────────────────────────────────────────────
define('APP_NAME',    'Order Tracking System');
define('APP_URL',     'http://localhost/order-tracking-system');
define('APP_VERSION', '1.0.0');

// ─────────────────────────────────────────────
// User Role Flags
// ─────────────────────────────────────────────
define('ROLE_ADMIN',    1);
define('ROLE_STAFF',    2);
define('ROLE_CUSTOMER', 3);

// ─────────────────────────────────────────────
// Session Configuration
// ─────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─────────────────────────────────────────────
// Database Connection (MySQLi)
// ─────────────────────────────────────────────
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

if ($conn->connect_error) {
    die(json_encode([
        'status'  => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset('utf8mb4');

// ─────────────────────────────────────────────
// Helper: Sanitize Input
// ─────────────────────────────────────────────
function sanitize($conn, $input) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($input))));
}

// ─────────────────────────────────────────────
// Helper: Hash Password (MD5)
// ─────────────────────────────────────────────
function hashPassword($password) {
    return md5($password);
}

// ─────────────────────────────────────────────
// Helper: Redirect
// ─────────────────────────────────────────────
function redirect($url) {
    header("Location: " . APP_URL . "/" . $url);
    exit();
}

// ─────────────────────────────────────────────
// Helper: Check if User is Logged In
// ─────────────────────────────────────────────
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// ─────────────────────────────────────────────
// Helper: Check User Role
// ─────────────────────────────────────────────
function isAdmin() {
    return isLoggedIn() && $_SESSION['user_type'] == ROLE_ADMIN;
}

function isStaff() {
    return isLoggedIn() && $_SESSION['user_type'] == ROLE_STAFF;
}

function isCustomer() {
    return isLoggedIn() && $_SESSION['user_type'] == ROLE_CUSTOMER;
}

// ─────────────────────────────────────────────
// Helper: Require Login
// ─────────────────────────────────────────────
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('index.php');
    }
}

// ─────────────────────────────────────────────
// Helper: Require Role
// ─────────────────────────────────────────────
function requireRole($role) {
    requireLogin();
    if ($_SESSION['user_type'] != $role) {
        redirect('index.php?error=unauthorized');
    }
}
?>
