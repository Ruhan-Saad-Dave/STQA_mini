<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * Home Page: COVID-19 Portal & Dynamic Statistics Hub
 */
$page_title = 'COVID-19 Live Portal & Awareness';
require_once __DIR__ . '/includes/header.php';

// Fetch dynamic portal counts if DB is connected
$db = getDB();
$db_registrations_count = 0;
$db_feedback_count = 0;

if ($db) {
    try {
        $stmt1 = $db->query("SELECT COUNT(*) AS total FROM registrations");
        $db_registrations_count = (int)$stmt1->fetch()['total'];

        $stmt2 = $db->query("SELECT COUNT(*) AS total FROM comments WHERE status = 'approved'");
        $db_feedback_count = (int)$stmt2->fetch()['total'];
    } catch (Exception $e) {
        // Fallback silently if table does not exist yet
    }
}
?>

<!-- Hero & Live Statistics Section -->
<section class="hero">
    <div class="container">
        <span class="badge" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; margin-bottom: 12px;">Official Public Health Initiative</span>
        <h1 class="hero-title">COVID-19 Information & Booking Portal</h1>
        <p class="hero-subtitle">
            Empowering citizens with accurate health advisories, symptom assessment, RT-PCR testing, and verified vaccination appointments.
        </p>

        <?php if ($db_connection_error): ?>
            <div class="alert alert-info" style="max-width: 700px; margin: 0 auto 20px auto; text-align: left;">
                ℹ️ <strong>Database Notice:</strong> MySQL is currently offline or not configured (<code><?php echo htmlspecialchars($db_connection_error); ?></code>). 
                Import <code>database/schema.sql</code> into your MySQL/phpMyAdmin to enable live database storage. The front-end validation and STQA test suites are fully functional!
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; margin-bottom: 30px;">
            <a href="register_slot.php" class="btn btn-primary">Book Vaccination / Test Slot &rarr;</a>
            <a href="test_runner.php" class="btn btn-outline" style="color: white; border-color: rgba(255,255,255,0.3);">Run STQA Regex Test Suite 🧪</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number" data-target="44998532">0</div>
                <div class="stat-label">Confirmed Cases (Total)</div>
            </div>
            <div class="stat-card active">
                <div class="stat-number" data-target="1420">0</div>
                <div class="stat-label">Active Cases</div>
            </div>
            <div class="stat-card recovered">
                <div class="stat-number" data-target="44465112">0</div>
                <div class="stat-label">Recovered Patients</div>
            </div>
            <div class="stat-card vaccinated">
                <div class="stat-number" data-target="<?php echo 2206750000 + $db_registrations_count; ?>">0</div>
                <div class="stat-label">Doses Administered</div>
            </div>
        </div>
    </div>
</section>

<!-- Section: Symptoms & Self-Assessment -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Common Symptoms of COVID-19</h2>
            <p class="section-desc">Symptoms may appear 2-14 days after exposure to the virus. Monitor your condition carefully.</p>
        </div>

        <div class="grid-3">
            <div class="card">
                <div class="card-title">🌡️ High Fever & Chills</div>
                <p>Persistent body temperature exceeding 100.4°F (38°C), accompanied by severe fatigue, muscle aches, and shivering.</p>
            </div>
            <div class="card">
                <div class="card-title">🫁 Dry Cough & Chest Discomfort</div>
                <p>Continuous coughing without phlegm, shortness of breath, or mild pressure in the chest area.</p>
            </div>
            <div class="card">
                <div class="card-title">👃 Loss of Taste or Smell</div>
                <p>Anosmia or ageusia (sudden loss of olfactory and gustatory sensations), a hallmark clinical indicator of viral infection.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Preventive Safety Guidelines -->
<section class="section" style="background-color: #f1f5f9;">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">WHO Recommended Safety Guidelines</h2>
            <p class="section-desc">Protect yourself and vulnerable communities by adhering to these preventive measures.</p>
        </div>

        <div class="grid-3">
            <div class="card" style="border-top: 4px solid var(--primary);">
                <div class="card-title">😷 Wear Certified Face Masks</div>
                <p>Wear well-fitted N95 or 3-ply surgical masks in crowded, enclosed, or poorly ventilated public spaces.</p>
            </div>
            <div class="card" style="border-top: 4px solid var(--secondary);">
                <div class="card-title">🧼 Frequent Hand Sanitization</div>
                <p>Wash hands thoroughly with soap and water for at least 20 seconds or use 70%+ alcohol-based sanitizer.</p>
            </div>
            <div class="card" style="border-top: 4px solid var(--warning);">
                <div class="card-title">💉 Timely Immunization</div>
                <p>Complete both primary doses and receive recommended booster doses to maintain high antibody titers.</p>
            </div>
        </div>
    </div>
</section>

<!-- Section: Emergency Helplines & Assistance -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">24x7 Emergency Helpline Numbers</h2>
            <p class="section-desc">Official government and health department toll-free assistance channels.</p>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-title">📞 National COVID-19 Helpline</div>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin: 8px 0;">Toll-Free: 1075</p>
                <p class="form-help">Operated by Ministry of Health and Family Welfare (MoHFW) for general guidance and center allocations.</p>
            </div>
            <div class="card">
                <div class="card-title">🧠 Tele-MANAS Mental Health Support</div>
                <p style="font-size: 1.5rem; font-weight: 700; color: var(--secondary); margin: 8px 0;">Toll-Free: 14416 / 08046110007</p>
                <p class="form-help">Counseling and emotional support for pandemic anxiety, isolation, and post-COVID recovery.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
