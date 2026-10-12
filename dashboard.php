<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * User Account Dashboard: View Registered Appointments & Queries
 */
$page_title = 'Citizen Dashboard';
require_once __DIR__ . '/includes/header.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user = getCurrentUser();
$db = getDB();
$my_registrations = [];
$my_comments = [];

if ($db) {
    try {
        // Fetch appointments
        $stmt = $db->prepare("SELECT * FROM registrations WHERE user_id = :uid OR email = :email ORDER BY created_at DESC");
        $stmt->execute([':uid' => $user['id'], ':email' => $user['email']]);
        $my_registrations = $stmt->fetchAll();

        // Fetch comments
        $stmt2 = $db->prepare("SELECT * FROM comments WHERE user_id = :uid OR author_email = :email ORDER BY created_at DESC");
        $stmt2->execute([':uid' => $user['id'], ':email' => $user['email']]);
        $my_comments = $stmt2->fetchAll();
    } catch (Exception $e) {
        $db_error = $e->getMessage();
    }
}
?>

<div class="container section">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 30px;">
        <div>
            <h1 class="section-title">Welcome back, <?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="section-desc">Manage your COVID-19 immunization appointments and public community queries.</p>
        </div>
        <div style="display: flex; gap: 12px;">
            <a href="register_slot.php" class="btn btn-primary">+ Book New Appointment</a>
            <a href="comments.php" class="btn btn-outline">Community Forum</a>
        </div>
    </div>

    <?php if (isset($_GET['signup']) && $_GET['signup'] === 'success'): ?>
        <div class="alert alert-success">🎉 Welcome to CovidCare! Your account has been registered successfully.</div>
    <?php endif; ?>

    <!-- User Profile Overview Card -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-title">👤 Account Details</div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 12px;">
            <div>
                <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">Full Name</span>
                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
            </div>
            <div>
                <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">Registered Email</span>
                <strong><?php echo htmlspecialchars($user['email']); ?></strong>
            </div>
            <div>
                <span style="font-size: 0.85rem; color: var(--text-muted); display: block;">Role / Access Level</span>
                <span class="badge <?php echo ($user['role'] === 'admin') ? 'badge-admin' : 'badge-confirmed'; ?>">
                    <?php echo strtoupper($user['role']); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- User Appointments Section -->
    <div class="card" style="margin-bottom: 30px;">
        <div class="card-title" style="justify-content: space-between;">
            <span>🗓️ My Booked Vaccination & Test Slots</span>
            <span class="badge badge-confirmed"><?php echo count($my_registrations); ?> Total</span>
        </div>

        <?php if (!empty($my_registrations)): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ref ID</th>
                            <th>Patient Name</th>
                            <th>Age / Sex</th>
                            <th>Service / Dose</th>
                            <th>Schedule</th>
                            <th>Pincode</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($my_registrations as $reg): ?>
                            <tr>
                                <td><strong>COV-<?php echo str_pad($reg['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($reg['patient_name']); ?></td>
                                <td><?php echo htmlspecialchars($reg['age']); ?> / <?php echo htmlspecialchars($reg['gender']); ?></td>
                                <td><?php echo htmlspecialchars($reg['dose_type']); ?></td>
                                <td><?php echo htmlspecialchars($reg['preferred_date']); ?><br><small style="color: var(--text-muted);"><?php echo htmlspecialchars($reg['time_slot']); ?></small></td>
                                <td><code><?php echo htmlspecialchars($reg['pincode']); ?></code></td>
                                <td><span class="badge badge-pass"><?php echo htmlspecialchars($reg['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted); padding: 20px 0;">
                No appointment slots booked yet. Click <a href="register_slot.php"><strong>Book Slot</strong></a> to reserve a COVID-19 vaccine or RT-PCR test appointment.
            </p>
        <?php endif; ?>
    </div>

    <!-- User Feedback / Comments Section -->
    <div class="card">
        <div class="card-title">💬 My Community Feedback & Inquiries</div>
        <?php if (!empty($my_comments)): ?>
            <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 14px;">
                <?php foreach ($my_comments as $c): ?>
                    <div style="background: var(--light-bg); padding: 14px 18px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-weight: 600; color: var(--dark);"><?php echo htmlspecialchars($c['author_name']); ?></span>
                            <span style="font-size: 0.8rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($c['created_at'])); ?></span>
                        </div>
                        <p style="font-size: 0.95rem; color: var(--text-main);"><?php echo htmlspecialchars($c['message']); ?></p>
                        <div style="margin-top: 6px;">
                            <span class="badge <?php echo ($c['status'] === 'approved') ? 'badge-pass' : 'badge-pending'; ?>">
                                Status: <?php echo ucfirst($c['status']); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted); padding: 20px 0;">
                You haven't posted any feedback yet. Share your experience or questions on the <a href="comments.php"><strong>Feedback Forum</strong></a>.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
