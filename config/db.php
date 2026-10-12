<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * Database Configuration & Connection Handler
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db_host = 'localhost';
$db_port = '3306';
$db_name = 'covid_stqa_db';
$db_user = 'root';
$db_pass = ''; // Default XAMPP/WAMP password is empty string

$pdo = null;
$db_connection_error = null;

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    $db_connection_error = $e->getMessage();
}

/**
 * Helper function to retrieve database connection instance
 * @return PDO|null
 */
function getDB() {
    global $pdo;
    return $pdo;
}

/**
 * Check if the user is authenticated in current session
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the current user has admin privileges
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current logged in user details from session
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id'        => $_SESSION['user_id'],
        'name'      => $_SESSION['user_name'] ?? 'User',
        'email'     => $_SESSION['user_email'] ?? '',
        'role'      => $_SESSION['user_role'] ?? 'user'
    ];
}

/**
 * Sanitize output to prevent XSS (Security Testing requirement)
 * @param string $data
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}
?>
