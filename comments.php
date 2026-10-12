<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * Community Feedback & Inquiry Forum (Comments Module)
 */
$page_title = 'Community Feedback & Inquiries';
require_once __DIR__ . '/includes/header.php';

$logged_user = getCurrentUser();
$db = getDB();
$errors = [];
$success_msg = null;

$form_name = $logged_user ? $logged_user['name'] : '';
$form_email = $logged_user ? $logged_user['email'] : '';
$form_message = '';
$form_rating = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_name = trim($_POST['author_name'] ?? '');
    $form_email = trim($_POST['author_email'] ?? '');
    $form_message = trim($_POST['message'] ?? '');
    $form_rating = (int)($_POST['rating'] ?? 5);

    // 1. Name Regex Validation
    $regex_name = "/^[A-Za-z]+([ '-][A-Za-z]+)*$/";
    if (empty($form_name)) {
        $errors['name'] = "Name is required.";
    } elseif (!preg_match($regex_name, $form_name) || strlen($form_name) < 3 || strlen($form_name) > 50) {
        $errors['name'] = "Name must be 3-50 letters only.";
    }

    // 2. Email Regex Validation
    $regex_email = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    if (empty($form_email)) {
        $errors['email'] = "Email is required.";
    } elseif (!preg_match($regex_email, $form_email)) {
        $errors['email'] = "Invalid email format.";
    }

    // 3. Message Validation (Length & Sanitization)
    if (empty($form_message)) {
        $errors['message'] = "Please enter your query or feedback message.";
    } elseif (strlen($form_message) < 10) {
        $errors['message'] = "Message is too short (minimum 10 characters).";
    } elseif (strlen($form_message) > 600) {
        $errors['message'] = "Message exceeds maximum allowed 600 characters.";
    }

    if (empty($errors)) {
        $user_id = $logged_user ? $logged_user['id'] : null;

        if ($db) {
            try {
                $stmt = $db->prepare("INSERT INTO comments (user_id, author_name, author_email, message, rating, status) VALUES (:uid, :name, :email, :msg, :rating, 'approved')");
                $stmt->execute([
                    ':uid'    => $user_id,
                    ':name'   => $form_name,
                    ':email'  => $form_email,
                    ':msg'    => $form_message,
                    ':rating' => $form_rating
                ]);
                $success_msg = "Thank you! Your feedback has been published to the community forum.";
                $form_message = ''; // Reset message
            } catch (Exception $e) {
                $errors['general'] = "Failed to post comment: " . $e->getMessage();
            }
        } else {
            $success_msg = "Feedback accepted! (Simulated mode: database not connected).";
            $form_message = '';
        }
    }
}

// Fetch published comments
$comments = [];
if ($db) {
    try {
        $stmt = $db->query("SELECT * FROM comments WHERE status = 'approved' ORDER BY created_at DESC");
        $comments = $stmt->fetchAll();
    } catch (Exception $e) {
        // Fallback
    }
}

// Provide demo fallback comments if DB is empty/offline
if (empty($comments)) {
    $comments = [
        [
            'author_name' => 'Dr. Sunita Deshmukh',
            'message'     => 'Please remember to maintain social distancing and wear an N-95 mask even after vaccination.',
            'rating'      => 5,
            'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'author_name' => 'Aarav Sharma',
            'message'     => 'Booking process was very smooth and verification took under 5 minutes at the center. Highly recommended!',
            'rating'      => 5,
            'created_at'  => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'author_name' => 'Rohan Mehta',
            'message'     => 'Got my booster dose certificate immediately linked to my portal registration. Thanks!',
            'rating'      => 4,
            'created_at'  => date('Y-m-d H:i:s', strtotime('-3 days'))
        ]
    ];
}
?>

<div class="container section">
    <div class="section-header">
        <h1 class="section-title">Community Feedback & Public Q&A</h1>
        <p class="section-desc">Share your vaccination experience, ask queries, and read reviews from other citizens.</p>
    </div>

    <div class="grid-2">
        <!-- Post Comment Form -->
        <div class="card">
            <h2 class="card-title">✍️ Leave Your Feedback / Query</h2>

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>

            <form action="comments.php" method="POST" data-stqa-validate novalidate>
                <div class="form-group">
                    <label class="form-label" for="author_name">Your Name <span class="req">*</span></label>
                    <input type="text" id="author_name" name="author_name" 
                           class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                           placeholder="e.g. John Doe" 
                           value="<?php echo htmlspecialchars($form_name); ?>" 
                           data-validate="name" required>
                    <div class="invalid-feedback"><?php echo $errors['name'] ?? 'Letters and spaces only.'; ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="author_email">Your Email Address <span class="req">*</span></label>
                    <input type="email" id="author_email" name="author_email" 
                           class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                           placeholder="name@domain.com" 
                           value="<?php echo htmlspecialchars($form_email); ?>" 
                           data-validate="email" required>
                    <div class="invalid-feedback"><?php echo $errors['email'] ?? 'Valid email required.'; ?></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="rating">Experience Rating</label>
                    <select id="rating" name="rating" class="form-control">
                        <option value="5" <?php echo ($form_rating == 5) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5 - Excellent)</option>
                        <option value="4" <?php echo ($form_rating == 4) ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4 - Very Good)</option>
                        <option value="3" <?php echo ($form_rating == 3) ? 'selected' : ''; ?>>⭐⭐⭐ (3 - Average)</option>
                        <option value="2" <?php echo ($form_rating == 2) ? 'selected' : ''; ?>>⭐⭐ (2 - Poor)</option>
                        <option value="1" <?php echo ($form_rating == 1) ? 'selected' : ''; ?>>⭐ (1 - Terrible)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="message">Comment / Question <span class="req">*</span></label>
                    <textarea id="message" name="message" rows="4" 
                              class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                              placeholder="Write your feedback or query (min 10 chars)..." required><?php echo htmlspecialchars($form_message); ?></textarea>
                    <div class="invalid-feedback"><?php echo $errors['message'] ?? 'Please write at least 10 characters.'; ?></div>
                    <small class="form-help">Max 600 characters. Input is sanitized to prevent XSS attacks.</small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">
                    Submit Feedback &rarr;
                </button>
            </form>
        </div>

        <!-- Published Comments List -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--dark);">Community Reviews (<?php echo count($comments); ?>)</h2>
                <span class="badge badge-confirmed">Live Discussion</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($comments as $item): ?>
                    <div class="card" style="padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                            <div>
                                <strong style="color: var(--dark); font-size: 1rem;"><?php echo htmlspecialchars($item['author_name']); ?></strong>
                                <div style="color: #f59e0b; font-size: 0.85rem; margin-top: 2px;">
                                    <?php echo str_repeat('★', (int)($item['rating'] ?? 5)); ?><?php echo str_repeat('☆', 5 - (int)($item['rating'] ?? 5)); ?>
                                </div>
                            </div>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">
                                <?php echo date('M d, Y', strtotime($item['created_at'])); ?>
                            </span>
                        </div>
                        <p style="font-size: 0.95rem; color: var(--text-main); margin-top: 6px;">
                            <?php echo nl2br(htmlspecialchars($item['message'])); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
