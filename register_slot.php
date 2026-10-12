<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * Module: COVID-19 Vaccination & RT-PCR Slot Registration
 * 
 * Demonstrates Dual-Tier Regular Expression Validation:
 * Tier 1: Client-Side real-time DOM validation via assets/js/validation.js
 * Tier 2: Server-Side validation via PHP preg_match() (QA Defect Prevention)
 */
$page_title = 'Book Vaccine / RT-PCR Slot';
require_once __DIR__ . '/includes/header.php';

$errors = [];
$success_booking = null;
$logged_user = getCurrentUser();

// Pre-fill fields if user is logged in
$form_data = [
    'patient_name'   => $logged_user ? $logged_user['name'] : '',
    'age'            => '',
    'gender'         => 'Male',
    'id_type'        => 'Aadhaar',
    'id_number'      => '',
    'mobile'         => '',
    'email'          => $logged_user ? $logged_user['email'] : '',
    'pincode'        => '',
    'dose_type'      => 'Dose 1 (Covishield/Covaxin)',
    'preferred_date' => '',
    'time_slot'      => '10:00 AM - 11:30 AM'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Populate form data
    foreach ($form_data as $key => $val) {
        if (isset($_POST[$key])) {
            $form_data[$key] = trim($_POST[$key]);
        }
    }

    // =========================================================
    // SERVER-SIDE REGEX VALIDATION (STQA Layer 2)
    // =========================================================

    // 1. Full Name Regex: 3 to 50 alphabetical chars, optional space/hyphen
    $regex_name = "/^[A-Za-z]+([ '-][A-Za-z]+)*$/";
    if (empty($form_data['patient_name'])) {
        $errors['patient_name'] = "Patient name is required.";
    } elseif (strlen($form_data['patient_name']) < 3 || strlen($form_data['patient_name']) > 50) {
        $errors['patient_name'] = "Name length must be between 3 and 50 characters (BVA Check).";
    } elseif (!preg_match($regex_name, $form_data['patient_name'])) {
        $errors['patient_name'] = "Name can only contain alphabets, hyphens, and spaces.";
    }

    // 2. Age Validation: Strictly 18 to 110 (Equivalence Partitioning)
    $age_int = filter_var($form_data['age'], FILTER_VALIDATE_INT);
    if ($age_int === false || $age_int < 18 || $age_int > 110) {
        $errors['age'] = "Age must be an integer between 18 and 110 (ECP Check: Age 18-110).";
    }

    // 3. Email Regex: RFC standard pattern
    $regex_email = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    if (empty($form_data['email'])) {
        $errors['email'] = "Email address is required.";
    } elseif (!preg_match($regex_email, $form_data['email'])) {
        $errors['email'] = "Invalid email format. Must follow RFC syntax (e.g. name@domain.com).";
    }

    // 4. Mobile Number Regex: 10 digits starting with 6,7,8,9
    $regex_phone = "/^(?:\+91[\-\s]?)?[6-9]\d{9}$/";
    if (empty($form_data['mobile'])) {
        $errors['mobile'] = "Mobile number is required.";
    } elseif (!preg_match($regex_phone, $form_data['mobile'])) {
        $errors['mobile'] = "Invalid 10-digit mobile number. Must start with 6, 7, 8, or 9.";
    }

    // 5. Pincode Regex: Exactly 6 digits, first digit 1-9
    $regex_pincode = "/^[1-9][0-9]{5}$/";
    if (empty($form_data['pincode'])) {
        $errors['pincode'] = "Pincode is required.";
    } elseif (!preg_match($regex_pincode, $form_data['pincode'])) {
        $errors['pincode'] = "Invalid 6-digit postal pincode. Cannot begin with zero.";
    }

    // 6. Identity Proof Regex based on selected ID type
    if ($form_data['id_type'] === 'Aadhaar') {
        $regex_aadhaar = "/^[2-9]{1}[0-9]{3}\s?[0-9]{4}\s?[0-9]{4}$/";
        if (!preg_match($regex_aadhaar, $form_data['id_number'])) {
            $errors['id_number'] = "Aadhaar must be a 12-digit number (e.g., 2345 6789 0123).";
        }
    } elseif ($form_data['id_type'] === 'Passport') {
        $regex_passport = "/^[A-Za-z][0-9]{7}$/";
        if (!preg_match($regex_passport, $form_data['id_number'])) {
            $errors['id_number'] = "Passport must have 1 letter followed by 7 numeric digits (e.g., A1234567).";
        }
    } else {
        if (strlen($form_data['id_number']) < 5) {
            $errors['id_number'] = "Enter a valid government identification number.";
        }
    }

    // 7. Preferred Date Regex (YYYY-MM-DD)
    $regex_date = "/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/";
    if (empty($form_data['preferred_date'])) {
        $errors['preferred_date'] = "Appointment date is required.";
    } elseif (!preg_match($regex_date, $form_data['preferred_date'])) {
        $errors['preferred_date'] = "Date format must be YYYY-MM-DD.";
    } else {
        $selected_timestamp = strtotime($form_data['preferred_date']);
        $today_timestamp = strtotime(date('Y-m-d'));
        if ($selected_timestamp < $today_timestamp) {
            $errors['preferred_date'] = "Appointment date cannot be in the past.";
        }
    }

    // If validation passed, persist into MySQL
    if (empty($errors)) {
        $db = getDB();
        $user_id = $logged_user ? $logged_user['id'] : null;

        if ($db) {
            try {
                $sql = "INSERT INTO registrations 
                        (user_id, patient_name, age, gender, id_type, id_number, mobile, email, pincode, dose_type, preferred_date, time_slot, status) 
                        VALUES 
                        (:user_id, :patient_name, :age, :gender, :id_type, :id_number, :mobile, :email, :pincode, :dose_type, :preferred_date, :time_slot, 'Confirmed')";

                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':user_id'        => $user_id,
                    ':patient_name'   => $form_data['patient_name'],
                    ':age'            => $age_int,
                    ':gender'         => $form_data['gender'],
                    ':id_type'        => $form_data['id_type'],
                    ':id_number'      => $form_data['id_number'],
                    ':mobile'         => $form_data['mobile'],
                    ':email'          => $form_data['email'],
                    ':pincode'        => $form_data['pincode'],
                    ':dose_type'      => $form_data['dose_type'],
                    ':preferred_date' => $form_data['preferred_date'],
                    ':time_slot'      => $form_data['time_slot']
                ]);

                $inserted_id = $db->lastInsertId();
                $success_booking = array_merge($form_data, [
                    'booking_id' => 'COV-' . str_pad($inserted_id, 5, '0', STR_PAD_LEFT),
                    'status'     => 'Confirmed'
                ]);
            } catch (Exception $e) {
                $errors['general'] = "Database insertion error: " . $e->getMessage();
            }
        } else {
            // Simulated success if DB is not reachable (for offline demo)
            $success_booking = array_merge($form_data, [
                'booking_id' => 'COV-SIM-' . rand(10000, 99999),
                'status'     => 'Confirmed (Simulation Mode)'
            ]);
        }
    }
}
?>

<div class="container section">
    <div class="section-header">
        <h1 class="section-title">Vaccination & RT-PCR Appointment Booking</h1>
        <p class="section-desc">
            Complete the form below to reserve an appointment slot. Inputs are validated in real-time using regular expression patterns.
        </p>
    </div>

    <?php if ($success_booking): ?>
        <!-- Appointment Confirmation Slip (QA Passed) -->
        <div class="card" style="max-width: 650px; margin: 0 auto; border: 2px solid var(--success); background: #f0fdf4;">
            <div style="text-align: center; margin-bottom: 20px;">
                <span style="font-size: 3rem;">✅</span>
                <h2 style="color: #166534; font-size: 1.5rem; margin-top: 8px;">Registration Confirmed!</h2>
                <p style="color: #15803d; font-size: 0.95rem;">Both Client and Server Regular Expression verifications succeeded.</p>
            </div>

            <div style="background: white; border-radius: var(--radius-sm); padding: 20px; border: 1px solid #bbf7d0;">
                <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 10px; margin-bottom: 10px;">
                    <strong>Booking Reference ID:</strong>
                    <span style="font-family: monospace; font-weight: 700; color: var(--primary);"><?php echo htmlspecialchars($success_booking['booking_id']); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--text-muted);">Patient Name:</span>
                    <strong><?php echo htmlspecialchars($success_booking['patient_name']); ?> (Age: <?php echo htmlspecialchars($success_booking['age']); ?>)</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--text-muted);">Vaccine / Service:</span>
                    <strong><?php echo htmlspecialchars($success_booking['dose_type']); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--text-muted);">Scheduled Date & Slot:</span>
                    <strong><?php echo htmlspecialchars($success_booking['preferred_date']); ?> | <?php echo htmlspecialchars($success_booking['time_slot']); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                    <span style="color: var(--text-muted);">Center Pincode:</span>
                    <strong><?php echo htmlspecialchars($success_booking['pincode']); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Contact Verified:</span>
                    <span><?php echo htmlspecialchars($success_booking['mobile']); ?> | <?php echo htmlspecialchars($success_booking['email']); ?></span>
                </div>
            </div>

            <div style="margin-top: 20px; text-align: center; display: flex; gap: 12px; justify-content: center;">
                <a href="register_slot.php" class="btn btn-outline btn-sm">Book Another Slot</a>
                <a href="test_runner.php" class="btn btn-primary btn-sm">Inspect STQA Test Cases 🧪</a>
            </div>
        </div>

    <?php else: ?>

        <!-- Slot Booking Registration Form -->
        <div class="form-card">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errors['general']); ?></div>
            <?php endif; ?>

            <form action="register_slot.php" method="POST" data-stqa-validate novalidate>
                <!-- 1. Patient Name -->
                <div class="form-group">
                    <label class="form-label" for="patient_name">Full Name (as per Govt ID) <span class="req">*</span></label>
                    <input type="text" id="patient_name" name="patient_name" 
                           class="form-control <?php echo isset($errors['patient_name']) ? 'is-invalid' : ''; ?>" 
                           placeholder="e.g. John Doe" 
                           value="<?php echo htmlspecialchars($form_data['patient_name']); ?>" 
                           data-validate="name" required>
                    <div class="invalid-feedback">
                        <?php echo $errors['patient_name'] ?? 'Name must be 3-50 letters only.'; ?>
                    </div>
                    <small class="form-help">Regex: <code>^[A-Za-z]+([ '-][A-Za-z]+)*$</code></small>
                </div>

                <!-- 2. Age & Gender -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="age">Age (Years) <span class="req">*</span></label>
                        <input type="number" id="age" name="age" 
                               class="form-control <?php echo isset($errors['age']) ? 'is-invalid' : ''; ?>" 
                               placeholder="18 - 110" 
                               value="<?php echo htmlspecialchars($form_data['age']); ?>" 
                               data-validate="age" min="18" max="110" required>
                        <div class="invalid-feedback">
                            <?php echo $errors['age'] ?? 'Age must be between 18 and 110.'; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="gender">Gender <span class="req">*</span></label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="Male" <?php echo ($form_data['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($form_data['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($form_data['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>

                <!-- 3. Govt ID Proof -->
                <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="id_type">Identity Proof Type <span class="req">*</span></label>
                        <select id="id_type" name="id_type" class="form-control">
                            <option value="Aadhaar" <?php echo ($form_data['id_type'] === 'Aadhaar') ? 'selected' : ''; ?>>Aadhaar Card</option>
                            <option value="Passport" <?php echo ($form_data['id_type'] === 'Passport') ? 'selected' : ''; ?>>Passport</option>
                            <option value="Voter ID" <?php echo ($form_data['id_type'] === 'Voter ID') ? 'selected' : ''; ?>>Voter ID</option>
                            <option value="Driving License" <?php echo ($form_data['id_type'] === 'Driving License') ? 'selected' : ''; ?>>Driving License</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="id_number">Identity Proof Number <span class="req">*</span></label>
                        <input type="text" id="id_number" name="id_number" 
                               class="form-control <?php echo isset($errors['id_number']) ? 'is-invalid' : ''; ?>" 
                               placeholder="e.g. 2345 6789 0123" 
                               value="<?php echo htmlspecialchars($form_data['id_number']); ?>" 
                               data-validate="identity" required>
                        <div class="invalid-feedback">
                            <?php echo $errors['id_number'] ?? 'Invalid ID format for selected proof.'; ?>
                        </div>
                    </div>
                </div>

                <!-- 4. Mobile & Email -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="mobile">Mobile Number <span class="req">*</span></label>
                        <input type="tel" id="mobile" name="mobile" 
                               class="form-control <?php echo isset($errors['mobile']) ? 'is-invalid' : ''; ?>" 
                               placeholder="10-digit number" 
                               value="<?php echo htmlspecialchars($form_data['mobile']); ?>" 
                               data-validate="phone" required>
                        <div class="invalid-feedback">
                            <?php echo $errors['mobile'] ?? 'Must be 10 digits starting with 6-9.'; ?>
                        </div>
                        <small class="form-help">Regex: <code>^[6-9]\d{9}$</code></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address <span class="req">*</span></label>
                        <input type="email" id="email" name="email" 
                               class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               placeholder="name@domain.com" 
                               value="<?php echo htmlspecialchars($form_data['email']); ?>" 
                               data-validate="email" required>
                        <div class="invalid-feedback">
                            <?php echo $errors['email'] ?? 'Enter a valid email address.'; ?>
                        </div>
                    </div>
                </div>

                <!-- 5. Pincode & Dose Type -->
                <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="pincode">Area Pincode <span class="req">*</span></label>
                        <input type="text" id="pincode" name="pincode" 
                               class="form-control <?php echo isset($errors['pincode']) ? 'is-invalid' : ''; ?>" 
                               placeholder="6-digit PIN" 
                               value="<?php echo htmlspecialchars($form_data['pincode']); ?>" 
                               data-validate="pincode" maxlength="6" required>
                        <div class="invalid-feedback">
                            <?php echo $errors['pincode'] ?? 'Pincode must be 6 digits (non-zero start).'; ?>
                        </div>
                        <small class="form-help">Regex: <code>^[1-9][0-9]{5}$</code></small>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="dose_type">Dose / Test Service <span class="req">*</span></label>
                        <select id="dose_type" name="dose_type" class="form-control">
                            <option value="Dose 1 (Covishield/Covaxin)" <?php echo ($form_data['dose_type'] === 'Dose 1 (Covishield/Covaxin)') ? 'selected' : ''; ?>>Dose 1 (Primary)</option>
                            <option value="Dose 2 (Covishield/Covaxin)" <?php echo ($form_data['dose_type'] === 'Dose 2 (Covishield/Covaxin)') ? 'selected' : ''; ?>>Dose 2 (Second Dose)</option>
                            <option value="Precaution / Booster Dose" <?php echo ($form_data['dose_type'] === 'Precaution / Booster Dose') ? 'selected' : ''; ?>>Precaution / Booster Dose</option>
                            <option value="RT-PCR Test (Diagnostic)" <?php echo ($form_data['dose_type'] === 'RT-PCR Test (Diagnostic)') ? 'selected' : ''; ?>>RT-PCR Test (Diagnostic)</option>
                        </select>
                    </div>
                </div>

                <!-- 6. Preferred Date & Time Slot -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="preferred_date">Preferred Date <span class="req">*</span></label>
                        <input type="date" id="preferred_date" name="preferred_date" 
                               class="form-control <?php echo isset($errors['preferred_date']) ? 'is-invalid' : ''; ?>" 
                               value="<?php echo htmlspecialchars($form_data['preferred_date']); ?>" 
                               min="<?php echo date('Y-m-d'); ?>"
                               data-validate="date" required>
                        <div class="invalid-feedback">
                            <?php echo $errors['preferred_date'] ?? 'Enter valid future date.'; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="time_slot">Preferred Time Slot <span class="req">*</span></label>
                        <select id="time_slot" name="time_slot" class="form-control">
                            <option value="09:00 AM - 10:30 AM">09:00 AM - 10:30 AM</option>
                            <option value="11:00 AM - 12:30 PM">11:00 AM - 12:30 PM</option>
                            <option value="02:00 PM - 03:30 PM">02:00 PM - 03:30 PM</option>
                            <option value="04:00 PM - 05:30 PM">04:00 PM - 05:30 PM</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px;">
                        Confirm & Reserve Slot &rarr;
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
