# STQA Defect Tracking & Bug Log

---

### Defect Metrics Summary
- **Total Defects Logged:** 4
- **Critical / High:** 2
- **Medium / Low:** 2
- **Resolved / Closed:** 4 (100% Closure Rate)

---

### Defect Reports

#### 1. DEF-001: Server-Side Validation Bypass via Direct POST Request
- **Severity:** Critical | **Priority:** P1
- **Module:** `register_slot.php`
- **Reported By:** QA Tester
- **Description:** When disabling JavaScript in the client browser, a user could submit empty or improperly formatted names (e.g., `"John123"`), and the data was initially committed to the database.
- **Root Cause Analysis (RCA):** The system originally relied purely on HTML5/JavaScript client-side regex without an equivalent server-side filter.
- **Resolution:** Implemented Tier-2 server-side `preg_match("/^[A-Za-z]+([ '-][A-Za-z]+)*$/", ...)` validation in PHP. Now, any malformed POST payload is immediately halted with HTTP-level field error flags.
- **Status:** **CLOSED / VERIFIED**

---

#### 2. DEF-002: Pincode Boundary Allowed Leading Zeros
- **Severity:** Medium | **Priority:** P2
- **Module:** `assets/js/validation.js` & `register_slot.php`
- **Reported By:** QA Tester
- **Description:** Entering `"012345"` passed the regex test because the initial expression used `^\d{6}$`.
- **Root Cause Analysis (RCA):** The quantifier did not constrain the first digit domain ($1-9$), allowing fictional postal codes.
- **Resolution:** Updated regular expression across client and server to `^[1-9][0-9]{5}$`. Boundary Value tests `TC_PIN_01` through `TC_PIN_06` now pass.
- **Status:** **CLOSED / VERIFIED**

---

#### 3. DEF-003: Past Date Appointment Slots Were Accepted
- **Severity:** High | **Priority:** P1
- **Module:** `register_slot.php`
- **Reported By:** QA Tester
- **Description:** A patient could select an appointment date in the year 2020 (`2020-05-10`), because the regex only checked formatting syntax (`YYYY-MM-DD`) but omitted chronological feasibility.
- **Root Cause Analysis (RCA):** Regular expressions alone cannot verify temporal constraints relative to current system time.
- **Resolution:** Added chronological timestamp comparison (`$selectedDate >= $today`) in both JavaScript validator and PHP business logic, alongside the `min` HTML attribute.
- **Status:** **CLOSED / VERIFIED**

---

#### 4. DEF-004: Potential Stored XSS in Public Comments Form
- **Severity:** High | **Priority:** P2
- **Module:** `comments.php`
- **Reported By:** Security Tester
- **Description:** Submitting `<script>alert('XSS')</script>` in the comment textarea could result in code execution when displayed to other citizens.
- **Root Cause Analysis (RCA):** Direct database output without entity encoding.
- **Resolution:** Wrapped all comment outputs in `htmlspecialchars($message, ENT_QUOTES, 'UTF-8')` and added length boundaries (10 to 600 characters).
- **Status:** **CLOSED / VERIFIED**
