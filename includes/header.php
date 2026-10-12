<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * Common Navigation Header
 */
require_once __DIR__ . '/../config/db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$logged_user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | ' : ''; ?>COVID-19 Health Portal & STQA Test Suite</title>
    <!-- Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- Favicon (SVG data URI for instant rendering) -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛡️</text></svg>">
</head>
<body>

<header class="navbar">
    <div class="container nav-container">
        <a href="index.php" class="nav-brand">
            <span>🛡️ CovidCare Portal</span>
            <span class="brand-badge">STQA Mini Project</span>
        </a>

        <button class="mobile-toggle" id="mobile-menu-btn" aria-label="Toggle navigation">&#9776;</button>

        <ul class="nav-menu" id="nav-menu">
            <li><a href="index.php" class="nav-link <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="register_slot.php" class="nav-link <?php echo ($current_page === 'register_slot.php') ? 'active' : ''; ?>">Book Slot</a></li>
            <li><a href="comments.php" class="nav-link <?php echo ($current_page === 'comments.php') ? 'active' : ''; ?>">Feedback & Q&A</a></li>
            <li><a href="test_runner.php" class="nav-link qa-badge <?php echo ($current_page === 'test_runner.php') ? 'active' : ''; ?>">🧪 STQA Test Suite</a></li>

            <?php if ($logged_user): ?>
                <li><a href="dashboard.php" class="nav-link <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
                <?php if ($logged_user['role'] === 'admin'): ?>
                    <li><a href="admin.php" class="nav-link <?php echo ($current_page === 'admin.php') ? 'active' : ''; ?>">Admin</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="btn btn-outline btn-sm">Logout (<?php echo htmlspecialchars($logged_user['name']); ?>)</a></li>
            <?php else: ?>
                <li><a href="login.php" class="nav-link <?php echo ($current_page === 'login.php') ? 'active' : ''; ?>">Login</a></li>
                <li><a href="signup.php" class="btn btn-primary btn-sm">Register Account</a></li>
            <?php endif; ?>
        </ul>
    </div>
</header>
<main>
