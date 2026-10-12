<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * User Registration (Sign Up) with Regex & Password Complexity Validation
 */
$page_title = 'Create Account';
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$errors = [];
$success_msg = null;

$form_data = [
    'full_name' => '',
    'email'     => '',
    'phone'     => '',
    'password'  => '',
    'confirm_password' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($form_data as $key => $val) {
        if (isset($_POST[$key])) {
            $form_data[$key] = trim($_POST[$key]);
        }
    }

    // 1. Name Validation
    $regex_name = "/^[A-Za-z]+([ '-][A-Za-z]+)*$/";
    if (empty($form_data['full_name'])) {
        $errors['full_name'] = "Full name is required.";
    } elseif (strlen($form_data['full_name']) < 3 || strlen($form_data['full_name']) > 50) {
        $errors['full_name'] = "Name must be between 3 and 50 characters.";
    } elseif (!preg_match($regex_name, $form_data['full_name'])) {
        $errors['full_name'] = "Name can only contain alphabetic letters and spaces.";
    }

    // 2. Email Validation
    $regex_email = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    if (empty($form_data['email'])) {
        $errors['email'] = "Email is required.";
    } elseif (!preg_match($regex_email, $form_data['email'])) {
        $errors['email'] = "Invalid email format.";
    }

    // 3. Mobile Number Validation
    $regex_phone = "/^(?:\+91[\-\s]?)?[6-9]\d{9}$/";
    if (empty($form_data['phone'])) {
        $errors['phone'] = "Mobile number is required.";
    } elseif (!preg_match($regex_phone, $form_data['phone'])) {
        $errors['phone'] = "Mobile number must be a valid 10-digit number.";
    }

    // 4. Password Complexity Regex (STQA Security Requirement)
    // Min 8 chars, at least 1 uppercase, 1 lowercase, 1 digit, 1 special symbol
    $regex_password = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,32}$/";
    if (empty($form_data['password'])) {
        $errors['password'] = "Password is required.";
    } elseif (!preg_match($regex_password, $form_data['password'])) {
        $errors['password'] = "Password must be 8-32 chars, with at least 1 uppercase, 1 lowercase, 1 number, and 1 special symbol (@$!%*?&).";
    }

    // 5. Password Confirmation
    if ($form_data['password'] !== $form_data['confirm_password']) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    // Check duplicate email & insert
    if (empty($errors)) {
        $db = getDB();
        if ($db) {
            try {
                // Check if email already registered
                $check_stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
                $check_stmt->execute([':email' => $form_data['email']]);
                if ($check_stmt->fetch()) {
                    $errors['email'] = "This email is already registered. Please log in.";
                } else {
                    $password_hash = password_hash($form_data['password'], PASSWORD_BCRYPT);
                    $ins_stmt = $db->prepare("INSERT INTO users (full_name, email, phone, password_hash, role) VALUES (:name, :email, :phone, :hash, 'user')");
                    $ins_stmt->execute([
                        ':name'  => $form_data['full_name'],
                        ':email' => $form_data['email'],
                        ':phone' => $form_data['phone'],
                        ':hash'  => $password_hash
                    ]);

                    $_SESSION['user_id'] = $db->lastInsertId();
                    $_SESSION['user_name'] = $form_data['full_name'];
                    $_SESSION['user_email'] = $form_data['email'];
                    $_SESSION['user_role'] = 'user';

                    header("Location: dashboard.php?signup=success");
                    exit;
                }
            } catch (Exception $e) {
                $errors['general'] = "Account creation failed: " . $e->getMessage();
            }
        } else {
            // Simulated login for offline testing
            $_SESSION['user_id'] = rand(100, 999);
            $_SESSION['user_name'] = $form_data['full_name'];
            $_SESSION['user_email'] = $form_data['email'];
            $_SESSION['user_role'] = 'user';
            header("Location: dashboard.php?simulated=1");
            exit;
        }
    }
}
?>

<div class="container section">
    <div class="form-card">
        <div style="text-align: center; margin-bottom: 24px;">
            <h1 class="section-title">Create Citizen Account</h1>
            <p class="section-desc">Register to manage COVID-19 vaccine slots and test certificates.</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <form action="signup.php" method="POST" data-stqa-validate novalidate>
            <!-- Full Name -->
            <div class="form-group">
                <label class="form-label" for="full_name">Full Name <span class="req">*</span></label>
                <input type="text" id="full_name" name="full_name" 
                       class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" 
                       placeholder="e.g. Aarav Sharma" 
                       value="<?php echo htmlspecialchars($form_data['full_name']); ?>" 
                       data-validate="name" required>
                <div class="invalid-feedback"><?php echo $errors['full_name'] ?? 'Letters and spaces only (3-50 chars).'; ?></div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label class="form-label" for="email">Email Address <span class="req">*</span></label>
                <input type="email" id="email" name="email" 
                       class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                       placeholder="aarav@example.com" 
                       value="<?php echo htmlspecialchars($form_data['email']); ?>" 
                       data-validate="email" required>
                <div class="invalid-feedback"><?php echo $errors['email'] ?? 'Enter a valid RFC standard email.'; ?></div>
            </div>

            <!-- Phone -->
            <div class="form-group">
                <label class="form-label" for="phone">Mobile Number <span class="req">*</span></label>
                <input type="tel" id="phone" name="phone" 
                       class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" 
                       placeholder="10-digit number" 
                       value="<?php echo htmlspecialchars($form_data['phone']); ?>" 
                       data-validate="phone" required>
                <div class="invalid-feedback"><?php echo $errors['phone'] ?? '10 digits starting with 6-9.'; ?></div>
            </div>

            <!-- Password with Strength Evaluator -->
            <div class="form-group">
                <label class="form-label" for="password">Password <span class="req">*</span></label>
                <input type="password" id="password" name="password" 
                       class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                       placeholder="Min 8 chars, 1 uppercase, 1 lower, 1 digit, 1 symbol" 
                       data-validate="password" required>
                <div class="invalid-feedback"><?php echo $errors['password'] ?? 'Requires Upper, Lower, Number, and Special Symbol.'; ?></div>
                
                <!-- Password Strength Meter UI -->
                <div class="password-meter-container">
                    <div class="meter-track">
                        <div class="meter-bar" id="password-strength-meter"></div>
                    </div>
                    <span class="meter-label" id="password-strength-text">Strength: None</span>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label class="form-label" for="confirm_password">Confirm Password <span class="req">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                       placeholder="Re-type password" required>
                <div class="invalid-feedback"><?php echo $errors['confirm_password'] ?? 'Passwords must match.'; ?></div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 10px;">
                Create Account &rarr;
            </button>
        </form>

        <p style="text-align: center; margin-top: 20px; font-size: 0.9rem; color: var(--text-muted);">
            Already have an account? <a href="login.php"><strong>Sign In</strong></a>
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
