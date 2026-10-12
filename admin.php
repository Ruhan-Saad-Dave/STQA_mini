<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * Admin Panel: Manage Registrations & User Feedback
 */
$page_title = 'Admin Management Console';
require_once __DIR__ . '/includes/header.php';

if (!isAdmin()) {
    // If logged in as regular user or guest, restrict access
    echo '<div class="container section"><div class="alert alert-danger"><strong>Access Denied:</strong> This page requires Administrator credentials. Please log in as an administrator (e.g. <code>admin@covidportal.gov.in</code> / <code>Admin@123</code>).</div><p><a href="login.php" class="btn btn-primary">Go to Login</a></p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$db = getDB();
$message = null;

// Handle status updates or deletion actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $db) {
    if (isset($_POST['action']) && $_POST['action'] === 'update_reg_status') {
        $reg_id = (int)$_POST['reg_id'];
        $new_status = $_POST['status'];
        $stmt = $db->prepare("UPDATE registrations SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $new_status, ':id' => $reg_id]);
        $message = "Registration #$reg_id status updated to $new_status.";
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete_comment') {
        $comment_id = (int)$_POST['comment_id'];
        $stmt = $db->prepare("DELETE FROM comments WHERE id = :id");
        $stmt->execute([':id' => $comment_id]);
        $message = "Comment #$comment_id has been removed.";
    }
}

// Fetch lists
$registrations = [];
$comments = [];
$total_users = 0;

if ($db) {
    try {
        $registrations = $db->query("SELECT * FROM registrations ORDER BY created_at DESC")->fetchAll();
        $comments = $db->query("SELECT * FROM comments ORDER BY created_at DESC")->fetchAll();
        $total_users = (int)$db->query("SELECT COUNT(*) AS total FROM users")->fetch()['total'];
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="container section">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 30px;">
        <div>
            <h1 class="section-title">🛡️ Admin Management Console</h1>
            <p class="section-desc">Oversee patient appointment records and community interactions.</p>
        </div>
        <span class="badge badge-admin" style="font-size: 0.9rem; padding: 6px 16px;">Admin Mode Active</span>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Summary Metrics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="card" style="text-align: center; border-left: 4px solid var(--primary);">
            <div style="font-size: 2rem; font-weight: 800; color: var(--primary);"><?php echo count($registrations); ?></div>
            <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Total Registrations</div>
        </div>
        <div class="card" style="text-align: center; border-left: 4px solid var(--secondary);">
            <div style="font-size: 2rem; font-weight: 800; color: var(--secondary);"><?php echo $total_users; ?></div>
            <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Registered Users</div>
        </div>
        <div class="card" style="text-align: center; border-left: 4px solid var(--warning);">
            <div style="font-size: 2rem; font-weight: 800; color: var(--warning);"><?php echo count($comments); ?></div>
            <div style="font-size: 0.85rem; color: var(--text-muted); text-transform: uppercase;">Community Posts</div>
        </div>
    </div>

    <!-- Registrations Table -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-title">📋 Patient Vaccination & RT-PCR Bookings</div>
        <?php if (!empty($registrations)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ref ID</th>
                            <th>Patient Name</th>
                            <th>Mobile / Email</th>
                            <th>ID Number</th>
                            <th>Service Type</th>
                            <th>Date & Slot</th>
                            <th>Status Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $r): ?>
                            <tr>
                                <td><strong>COV-<?php echo str_pad($r['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($r['patient_name']); ?> (Age: <?php echo htmlspecialchars($r['age']); ?>)</td>
                                <td>
                                    <?php echo htmlspecialchars($r['mobile']); ?><br>
                                    <small style="color: var(--text-muted);"><?php echo htmlspecialchars($r['email']); ?></small>
                                </td>
                                <td><code><?php echo htmlspecialchars($r['id_number']); ?></code></td>
                                <td><?php echo htmlspecialchars($r['dose_type']); ?></td>
                                <td><?php echo htmlspecialchars($r['preferred_date']); ?><br><small><?php echo htmlspecialchars($r['time_slot']); ?></small></td>
                                <td>
                                    <form action="admin.php" method="POST" style="display: inline-flex; gap: 6px;">
                                        <input type="hidden" name="action" value="update_reg_status">
                                        <input type="hidden" name="reg_id" value="<?php echo $r['id']; ?>">
                                        <select name="status" class="form-control" style="padding: 4px 8px; font-size: 0.8rem; width: auto;" onchange="this.form.submit()">
                                            <option value="Confirmed" <?php echo ($r['status'] === 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="Completed" <?php echo ($r['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo ($r['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted); padding: 16px 0;">No patient booking records found in database.</p>
        <?php endif; ?>
    </div>

    <!-- Community Comments Moderation -->
    <div class="card">
        <div class="card-title">💬 Community Moderation</div>
        <?php if (!empty($comments)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Author</th>
                            <th>Email</th>
                            <th>Feedback Content</th>
                            <th>Rating</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $cm): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($cm['author_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($cm['author_email']); ?></td>
                                <td style="max-width: 300px;"><?php echo htmlspecialchars($cm['message']); ?></td>
                                <td><?php echo str_repeat('★', (int)($cm['rating'] ?? 5)); ?></td>
                                <td><?php echo date('Y-m-d', strtotime($cm['created_at'])); ?></td>
                                <td>
                                    <form action="admin.php" method="POST" onsubmit="return confirm('Remove this comment?');">
                                        <input type="hidden" name="action" value="delete_comment">
                                        <input type="hidden" name="comment_id" value="<?php echo $cm['id']; ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="color: var(--danger); border-color: var(--danger); padding: 2px 8px;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted); padding: 16px 0;">No comments available for moderation.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
