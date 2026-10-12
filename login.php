<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * User Authentication (Login)
 */
$page_title = 'Account Login';
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Please provide both email address and password.";
    } else {
        $db = getDB();
        if ($db) {
            try {
                $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    if ($user['role'] === 'admin') {
                        header("Location: admin.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit;
                } else {
                    $error = "Invalid email or password combination.";
                }
            } catch (Exception $e) {
                $error = "Authentication query failed: " . $e->getMessage();
            }
        } else {
            // Offline demo fallback authentication
            if ($email === 'admin@covidportal.gov.in' && $password === 'Admin@123') {
                $_SESSION['user_id'] = 1;
                $_SESSION['user_name'] = 'System Administrator';
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'admin';
                header("Location: admin.php");
                exit;
            } elseif ($password === 'User@1234' || $password === 'Admin@123') {
                $_SESSION['user_id'] = 2;
                $_SESSION['user_name'] = 'Demo Citizen';
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'user';
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Invalid credentials. Use demo accounts or connect MySQL database.";
            }
        }
    }
}
?>

<div class="container section">
    <div class="form-card">
        <div style="text-align: center; margin-bottom: 24px;">
            <h1 class="section-title">Sign In to CovidCare</h1>
            <p class="section-desc">Access your vaccination appointment slips and profile.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['logged_out'])): ?>
            <div class="alert alert-info">You have successfully logged out of your session.</div>
        <?php endif; ?>

        <form action="login.php" method="POST" data-stqa-validate novalidate>
            <div class="form-group">
                <label class="form-label" for="email">Email Address <span class="req">*</span></label>
                <input type="email" id="email" name="email" 
                       class="form-control" 
                       placeholder="e.g. admin@covidportal.gov.in" 
                       value="<?php echo htmlspecialchars($email); ?>" 
                       data-validate="email" required>
                <div class="invalid-feedback">Enter a valid email address format.</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password <span class="req">*</span></label>
                <input type="password" id="password" name="password" 
                       class="form-control" 
                       placeholder="Enter account password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;">
                Sign In &rarr;
            </button>
        </form>

        <!-- College Viva Demo Credentials Box -->
        <div style="margin-top: 24px; padding: 16px; background: #f8fafc; border: 1px dashed var(--border-color); border-radius: var(--radius-sm); font-size: 0.85rem;">
            <strong style="color: var(--dark);">🧪 College Evaluation Credentials:</strong>
            <ul style="margin: 8px 0 0 16px; color: var(--text-muted);">
                <li><strong>Admin Account:</strong> <code>admin@covidportal.gov.in</code> / <code>Admin@123</code></li>
                <li><strong>Citizen Account:</strong> <code>aarav.sharma@example.com</code> / <code>User@1234</code></li>
            </ul>
        </div>

        <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
            Don't have an account? <a href="signup.php"><strong>Register Now</strong></a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
