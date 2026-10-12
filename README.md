# COVID-19 Dynamic Information & Slot Booking Portal
### Software Testing & Quality Assurance (STQA) College Mini Project

---

## 📌 Project Overview
This repository contains a full-stack dynamic web portal designed and developed for the **Software Testing & Quality Assurance (STQA)** curriculum. While providing a practical, healthcare-oriented service—including dynamic COVID-19 pandemic statistics, symptoms & safety guidelines, user account management, vaccination & RT-PCR slot reservations, and public community feedback—the system places central emphasis on **rigorous input verification and software quality metrics**:

1. **Dual-Tier Validation Strategy:**
   - **Tier 1 (Client-Side):** Real-time DOM verification and instant feedback via JavaScript Regular Expressions.
   - **Tier 2 (Server-Side):** Gatekeeping via PHP `preg_match()` and PDO prepared statements (SQL injection & XSS prevention).
2. **Black-Box Testing Disciplines:**
   - Equivalence Class Partitioning (ECP) across valid and invalid input classes.
   - Boundary Value Analysis (BVA) on field lengths, numerical ages, and postal pincodes.
3. **Interactive In-Browser STQA Test Runner:**
   - A dedicated evaluation dashboard (`test_runner.php`) that executes 50+ pre-configured test cases live, calculates pass rates and execution times, and exports formal CSV reports for submission.
4. **Formal STQA Documentation Package:**
   - IEEE 829 Software Test Plan (`testing/test_plan.md`).
   - Test Case Specifications & Requirements Traceability Matrix (`testing/test_cases.md`).
   - Defect Tracking & Bug Log with Root Cause Analysis (`testing/defect_log.md`).

---

## 🛠️ Technology Stack
- **Frontend:** HTML5, CSS3 (Custom Responsive Healthcare Theme), JavaScript (ES6+ Vanilla).
- **Backend:** PHP 7.4 / 8.x with PDO (PHP Data Objects).
- **Database:** MySQL 5.7+ / MariaDB.
- **Environment:** Compatible with XAMPP, WAMP, LAMP, or PHP Built-in Web Server.

---

## 📁 Repository Structure
```
STQA_mini/
│
├── config/
│   └── db.php                  # Database connection (PDO) with session & XSS helpers
│
├── database/
│   └── schema.sql              # MySQL database schema with initial seed records
│
├── includes/
│   ├── header.php              # Navigation header with role and auth awareness
│   └── footer.php              # Footer with links and JS script inclusions
│
├── assets/
│   ├── css/
│   │   └── style.css           # Responsive styles, regex states (.is-valid/.is-invalid)
│   └── js/
│       ├── validation.js       # Core client regex validation engine & password meter
│       ├── main.js             # UI dynamics (stats animation, mobile drawer)
│       └── test_runner.js      # Interactive automated STQA test executor & CSV exporter
│
├── index.php                   # Home page: live pandemic statistics & advisories
├── signup.php                  # Citizen registration with password strength meter
├── login.php                   # User login with credential verification
├── logout.php                  # Clean session destruction
├── dashboard.php               # Citizen dashboard: appointment slips & personal records
├── register_slot.php           # Vaccine / RT-PCR booking with dual-layer regex checks
├── comments.php                # Public community forum with input sanitization
├── admin.php                   # Admin moderation: manage bookings and delete comments
├── test_runner.php             # STQA automated test suite & live regex sandbox
│
├── docs/
│   └── PROJECT_DOCUMENTATION.md # Comprehensive technical report, schema dictionary, APIs, & performance
│
├── testing/
│   ├── test_plan.md            # Formal IEEE 829 Software Test Plan document
│   ├── test_cases.md           # Full Test Case Specifications & Traceability Matrix (RTM)
│   ├── test_cases.json         # Raw dataset containing 50+ test cases
│   └── defect_log.md           # Defect / Bug report log with RCA & resolution details
│
└── README.md                   # Setup guide and STQA project documentation
```

---

## 🚀 Setup & Installation Guide

### Step 1: Database Setup (MySQL)
1. Open your MySQL client (phpMyAdmin or MySQL CLI via XAMPP/WAMP).
2. Create or import the database using `database/schema.sql`:
   ```sql
   SOURCE C:/Users/ruhan/STQA_mini/database/schema.sql;
   ```
   *(Or in phpMyAdmin: click "Import" &rarr; Choose `database/schema.sql` &rarr; Click "Go").*
3. The schema creates the `covid_stqa_db` database and seeds sample users, appointments, and comments.

### Step 2: Database Configuration
Open `config/db.php` if your local MySQL credentials differ from the defaults:
```php
$db_host = 'localhost';
$db_name = 'covid_stqa_db';
$db_user = 'root';
$db_pass = ''; // Leave empty for standard XAMPP, or specify your password
```

### Step 3: Run the Application
You can run this project using either **XAMPP/WAMP** or the **PHP Built-in Server**:

#### Option A: Using PHP Built-in Server (Recommended for quick testing)
Open PowerShell or Command Prompt inside the `STQA_mini` directory and run:
```bash
php -S localhost:8000
```
Then visit in your web browser:
👉 **`http://localhost:8000`**

#### Option B: Using XAMPP / WAMP
1. Copy or move this `STQA_mini` folder into `C:\xampp\htdocs\`.
2. Start the **Apache** and **MySQL** modules from the XAMPP Control Panel.
3. Visit in your web browser:
   👉 **`http://localhost/STQA_mini/`**

---

## 🧪 Pre-Configured Test & Demo Accounts

For college evaluation and viva demonstrations, pre-seeded accounts are provided:

| Role | Email Address | Password | Privileges |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin@covidportal.gov.in` | `Admin@123` | View all bookings, change statuses, moderate comments (`admin.php`) |
| **Citizen / Patient** | `aarav.sharma@example.com` | `User@1234` | View appointment slips, book slots, post feedback (`dashboard.php`) |

---

## 🔍 Regular Expression Specification Matrix

| Field | Production Regular Expression | Quality Assurance Rule |
| :--- | :--- | :--- |
| **Full Name** | `^[A-Za-z]+([ '-][A-Za-z]+)*$` | Only letters, hyphens, and spaces allowed. Length bounded between 3 and 50. |
| **Email** | `^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$` | Standard RFC 5322 syntax. Mandates `@` and valid domain with 2+ letter TLD. |
| **Mobile** | `^(?:\+91[\-\s]?)?[6-9]\d{9}$` | 10 digits starting with 6, 7, 8, or 9 (optional `+91-` country prefix). |
| **Password** | `^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,32}$` | 8-32 chars, requires uppercase, lowercase, number, and special symbol (`@$!%*?&`). |
| **Pincode** | `^[1-9][0-9]{5}$` | Exactly 6 numeric digits; first digit cannot be zero. |
| **Aadhaar** | `^[2-9]{1}[0-9]{3}\s?[0-9]{4}\s?[0-9]{4}$` | 12-digit Indian national identity format (first digit cannot be 0 or 1). |
| **Passport** | `^[A-Za-z][0-9]{7}$` | 1 alphabetical character followed by 7 numeric digits. |
| **Age** | `^(1[89]\|[2-9][0-9]\|10[0-9]\|110)$` | Integer between 18 and 110 (vaccination eligibility domain). |
| **Date** | `^\d{4}-(0[1-9]\|1[0-2])-(0[1-9]\|[12]\d\|3[01])$` | ISO standard YYYY-MM-DD + Chronology check (cannot book in past). |

---

## 🧪 How to Demonstrate the STQA Test Suite (For Evaluators & Viva)

1. Navigate to the **🧪 STQA Test Suite** tab in the top navigation bar or go directly to `test_runner.php`.
2. Click **"▶ Run Complete STQA Test Suite"**:
   - The test engine will automatically run all 50+ test cases sequentially.
   - Watch the live progress, execution timer, and the metrics banner update in real time.
3. Demonstrate **Filter by Field** (e.g., filter exclusively for *Password Strength* or *Pincode* tests).
4. Use the **🔬 Interactive Live Regex Sandbox**:
   - Type any arbitrary test string (e.g. `user@`, `John123`, `400001`) into the test box to show your teacher instant pattern matching behavior.
5. Click **"⬇ Export CSV Test Report"** to generate a downloadable spreadsheet containing the entire test execution log with execution times and verdicts.

---

## 📚 Complete Project Documentation Index

For in-depth academic review, submission reports, and viva evaluation:
- 📖 **[Comprehensive Technical Specification & Quality Report](docs/PROJECT_DOCUMENTATION.md)**: Feature-by-feature implementation breakdown, complete MySQL data dictionary, endpoint specifications, ReDoS algorithmic complexity and performance analysis, and quality sign-off.
- 📋 **[IEEE 829 Software Test Plan](testing/test_plan.md)**: Scope, V-model strategy, suspension/resumption criteria, item pass/fail metrics.
- 📊 **[Test Case Specification & Traceability Matrix (RTM)](testing/test_cases.md)**: Detailed test matrix covering all 50+ BVA, ECP, and Regex tests mapped to requirements.
- 🐞 **[Defect Tracking & Bug Log](testing/defect_log.md)**: Real defect lifecycles, root-cause analyses, security mitigations, and 100% closure verification.

