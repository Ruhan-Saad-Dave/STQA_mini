# STQA Test Case Specifications & Requirements Traceability Matrix (RTM)

---

## 1. Requirements Traceability Matrix (RTM)

| Requirement ID | Requirement Description | Mapped Test Case IDs | Verification Method |
| :--- | :--- | :--- | :--- |
| **REQ-01** | System shall validate patient full name (letters only, length 3-50 chars). | `TC_NAME_01` to `TC_NAME_09` | Regex Match + BVA Length Check |
| **REQ-02** | System shall accept only standard RFC-compliant email addresses. | `TC_EMAIL_01` to `TC_EMAIL_07` | RFC 5322 Regex Validation |
| **REQ-03** | System shall validate 10-digit mobile numbers starting with digits 6-9. | `TC_PHONE_01` to `TC_PHONE_07` | Telecom Prefix & Length Regex |
| **REQ-04** | System shall enforce strong passwords (min 8 chars, Upper, Lower, Number, Symbol). | `TC_PWD_01` to `TC_PWD_08` | Complex Lookahead Regex |
| **REQ-05** | System shall validate 6-digit postal pincodes with non-zero leading digit. | `TC_PIN_01` to `TC_PIN_06` | BVA + Postal Boundary Regex |
| **REQ-06** | System shall validate 12-digit UIDAI Aadhaar and alphanumeric Passport formats. | `TC_UID_01` to `TC_UID_04` | Identity Format Regex |
| **REQ-07** | System shall enforce vaccination age eligibility between 18 and 110 years. | `TC_AGE_01` to `TC_AGE_07` | BVA Integer Boundary Evaluation |
| **REQ-08** | System shall validate appointment dates in YYYY-MM-DD format & prevent past bookings. | `TC_DATE_01` to `TC_DATE_05` | Date Format Regex + Chronology Check |

---

## 2. Test Case Specification Table

### 2.1 Full Name Field (`TC_NAME_XX`)
*Target Regex:* `/^[A-Za-z]+([ '-][A-Za-z]+)*$/` (Min 3, Max 50 characters)

| Test ID | Technique | Test Data (Input) | Expected Outcome | Actual Outcome | Verdict |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `TC_NAME_01` | ECP (Valid) | `"John Doe"` | Accepted (Valid Name) | Accepted | **PASS** |
| `TC_NAME_02` | ECP (Valid) | `"Mary-Jane Watson"` | Accepted (Hyphen allowed) | Accepted | **PASS** |
| `TC_NAME_03` | BVA (Min = 3) | `"Ali"` | Accepted (Exact minimum) | Accepted | **PASS** |
| `TC_NAME_04` | BVA (Min - 1 = 2) | `"Al"` | Rejected (Under minimum) | Rejected | **PASS** |
| `TC_NAME_05` | ECP (Invalid) | `"John Doe 2nd"` | Rejected (Digits forbidden) | Rejected | **PASS** |
| `TC_NAME_06` | ECP (Invalid) | `"John_Doe@Hospital"` | Rejected (Illegal symbols) | Rejected | **PASS** |
| `TC_NAME_07` | ECP (Null) | `""` | Rejected (Field required) | Rejected | **PASS** |
| `TC_NAME_08` | BVA (Max = 50) | `"Alexander Montgomery Christopher Wellington Hastings"` | Accepted (Within 50 chars) | Accepted | **PASS** |
| `TC_NAME_09` | BVA (Max + 1) | `"Alexander Montgomery Christopher Wellington Hastings III"` | Rejected (Exceeds 50 chars) | Rejected | **PASS** |

---

### 2.2 Email Address Field (`TC_EMAIL_XX`)
*Target Regex:* `/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/`

| Test ID | Technique | Test Data (Input) | Expected Outcome | Actual Outcome | Verdict |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `TC_EMAIL_01` | ECP (Valid) | `"patient@hospital.org"` | Accepted (Standard email) | Accepted | **PASS** |
| `TC_EMAIL_02` | ECP (Valid) | `"john.doe+covid@sub.gov.in"` | Accepted (Plus & Subdomain) | Accepted | **PASS** |
| `TC_EMAIL_03` | ECP (Invalid) | `"patienthospital.org"` | Rejected (Missing `@`) | Rejected | **PASS** |
| `TC_EMAIL_04` | ECP (Invalid) | `"user@"` | Rejected (Missing domain) | Rejected | **PASS** |
| `TC_EMAIL_05` | ECP (Invalid) | `"user@domain"` | Rejected (Missing TLD) | Rejected | **PASS** |
| `TC_EMAIL_06` | ECP (Invalid) | `"john doe@domain.com"` | Rejected (Embedded whitespace) | Rejected | **PASS** |
| `TC_EMAIL_07` | Security | `"admin' OR '1'='1@domain.com"`| Rejected (SQL syntax injection)| Rejected | **PASS** |

---

### 2.3 Mobile Number Field (`TC_PHONE_XX`)
*Target Regex:* `/^(?:\+91[\-\s]?)?[6-9]\d{9}$/`

| Test ID | Technique | Test Data (Input) | Expected Outcome | Actual Outcome | Verdict |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `TC_PHONE_01` | ECP (Valid) | `"9820123456"` | Accepted (Starts with 9) | Accepted | **PASS** |
| `TC_PHONE_02` | ECP (Valid) | `"6300123456"` | Accepted (Starts with 6) | Accepted | **PASS** |
| `TC_PHONE_03` | ECP (Valid) | `"+91-9820123456"` | Accepted (With +91 country prefix)| Accepted | **PASS** |
| `TC_PHONE_04` | ECP (Invalid) | `"5820123456"` | Rejected (Illegal prefix 5) | Rejected | **PASS** |
| `TC_PHONE_05` | BVA (9 Digits) | `"982012345"` | Rejected (Length < 10) | Rejected | **PASS** |
| `TC_PHONE_06` | BVA (11 Digits)| `"98201234567"` | Rejected (Length > 10) | Rejected | **PASS** |
| `TC_PHONE_07` | ECP (Invalid) | `"98201ABCD6"` | Rejected (Alphabetic characters)| Rejected | **PASS** |

---

### 2.4 Password Strength Policy (`TC_PWD_XX`)
*Target Regex:* `/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,32}$/`

| Test ID | Technique | Test Data (Input) | Expected Outcome | Actual Outcome | Verdict |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `TC_PWD_01` | ECP (Valid) | `"Pass@1234"` | Accepted (All 4 conditions met) | Accepted | **PASS** |
| `TC_PWD_02` | BVA (Min = 8) | `"Ab1@cdef"` | Accepted (8-character minimum) | Accepted | **PASS** |
| `TC_PWD_03` | BVA (Min - 1 = 7)| `"Ab1@cde"` | Rejected (Below minimum 8) | Rejected | **PASS** |
| `TC_PWD_04` | ECP (Invalid) | `"Password123"` | Rejected (Missing special symbol)| Rejected | **PASS** |
| `TC_PWD_05` | ECP (Invalid) | `"password@123"` | Rejected (Missing uppercase) | Rejected | **PASS** |
| `TC_PWD_06` | ECP (Invalid) | `"Password@XYZ"` | Rejected (Missing digit) | Rejected | **PASS** |
| `TC_PWD_07` | BVA (Max = 32)| `"Aa1@bb2$cc3%dd4&ee5?ff6*gg7!hh8Q"` | Accepted (Exact 32 chars) | Accepted | **PASS** |

---

### 2.5 Postal Pincode Field (`TC_PIN_XX`)
*Target Regex:* `/^[1-9][0-9]{5}$/`

| Test ID | Technique | Test Data (Input) | Expected Outcome | Actual Outcome | Verdict |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `TC_PIN_01` | ECP (Valid) | `"400001"` | Accepted (Standard Mumbai PIN) | Accepted | **PASS** |
| `TC_PIN_02` | ECP (Valid) | `"560001"` | Accepted (Standard Bangalore PIN)| Accepted | **PASS** |
| `TC_PIN_03` | ECP (Invalid) | `"012345"` | Rejected (Leading zero disallowed)| Rejected | **PASS** |
| `TC_PIN_04` | BVA (5 Digits) | `"40001"` | Rejected (Length < 6) | Rejected | **PASS** |
| `TC_PIN_05` | BVA (7 Digits) | `"4000001"` | Rejected (Length > 6) | Rejected | **PASS** |
| `TC_PIN_06` | ECP (Invalid) | `"40001A"` | Rejected (Letters disallowed) | Rejected | **PASS** |

---

### 2.6 Age Eligibility Validation (`TC_AGE_XX`)
*Target Constraint:* Integer $18 \le \text{Age} \le 110$

| Test ID | Technique | Test Data (Input) | Expected Outcome | Actual Outcome | Verdict |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `TC_AGE_01` | BVA (Min = 18) | `"18"` | Accepted (Eligible adult boundary)| Accepted | **PASS** |
| `TC_AGE_02` | BVA (Min - 1 = 17)| `"17"` | Rejected (Underage applicant) | Rejected | **PASS** |
| `TC_AGE_03` | BVA (Nominal = 45)| `"45"` | Accepted (Typical adult range) | Accepted | **PASS** |
| `TC_AGE_04` | BVA (Max = 110) | `"110"` | Accepted (Senior boundary) | Accepted | **PASS** |
| `TC_AGE_05` | BVA (Max + 1 = 111)| `"111"` | Rejected (Beyond realistic age)| Rejected | **PASS** |
| `TC_AGE_06` | ECP (Negative) | `"-25"` | Rejected (Negative number) | Rejected | **PASS** |
| `TC_AGE_07` | ECP (String) | `"twenty"` | Rejected (Non-numeric text) | Rejected | **PASS** |

---

### 2.7 Appointment Date Validation (`TC_DATE_XX`)
*Target Regex:* `/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/` & $\text{Date} \ge \text{Today}$

| Test ID | Technique | Test Data (Input) | Expected Outcome | Actual Outcome | Verdict |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `TC_DATE_01` | ECP (Valid) | `"2026-11-15"` | Accepted (Future valid date) | Accepted | **PASS** |
| `TC_DATE_02` | ECP (Invalid Format)| `"15/11/2026"` | Rejected (Slash notation) | Rejected | **PASS** |
| `TC_DATE_03` | ECP (Invalid Month)| `"2026-13-15"` | Rejected (Month 13 out of range)| Rejected | **PASS** |
| `TC_DATE_04` | ECP (Invalid Day)| `"2026-10-32"` | Rejected (Day 32 out of range) | Rejected | **PASS** |
| `TC_DATE_05` | Chronology Check | `"2020-05-10"` | Rejected (Historic/Past date) | Rejected | **PASS** |
