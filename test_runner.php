<?php
/**
 * Software Testing & Quality Assurance (STQA) Project
 * Module: Interactive Regular Expression Test Execution Runner
 * 
 * Primary Academic Deliverable for College Lab Evaluation & Viva
 */
$page_title = 'STQA Automated Test Suite & Regex Runner';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container section">
    <div class="section-header">
        <span class="badge" style="background: #e0f2fe; color: #0284c7; margin-bottom: 8px;">Software Quality Assurance Lab</span>
        <h1 class="section-title">🧪 STQA Automated Test Suite & Regex Matrix</h1>
        <p class="section-desc">
            Automated verification of form validation rules using Boundary Value Analysis (BVA), Equivalence Class Partitioning (ECP), and Regular Expression matching.
        </p>
    </div>

    <!-- Execution Metrics Panel -->
    <div class="test-suite-header">
        <div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: white;">Test Execution Dashboard</h2>
            <p style="font-size: 0.85rem; color: #94a3b8;">Real-time Black-Box Test Case Execution Engine</p>
        </div>
        <div class="test-metric-box">
            <div class="test-metric">
                <div class="test-metric-num" id="metric-total" style="color: #38bdf8;">0</div>
                <div class="test-metric-label">Total Tests</div>
            </div>
            <div class="test-metric">
                <div class="test-metric-num" id="metric-passed" style="color: #4ade80;">0</div>
                <div class="test-metric-label">Passed</div>
            </div>
            <div class="test-metric">
                <div class="test-metric-num" id="metric-failed" style="color: #f87171;">0</div>
                <div class="test-metric-label">Failed</div>
            </div>
            <div class="test-metric">
                <div class="test-metric-num" id="metric-rate" style="color: #fbbf24;">0%</div>
                <div class="test-metric-label">Pass Rate</div>
            </div>
            <div class="test-metric">
                <div class="test-metric-num" id="metric-time" style="color: #c084fc;">-</div>
                <div class="test-metric-label">Exec Time</div>
            </div>
        </div>
    </div>

    <!-- Toolbar Controls & Filters -->
    <div style="background: white; padding: 18px 24px; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <button id="btn-run-all" class="btn btn-primary">
                ▶ Run Complete STQA Test Suite
            </button>
            <button id="btn-export-csv" class="btn btn-outline">
                ⬇ Export CSV Test Report
            </button>
        </div>

        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <div>
                <label for="filter-category" style="font-size: 0.85rem; font-weight: 600; color: var(--dark-muted); margin-right: 6px;">Field:</label>
                <select id="filter-category" class="form-control" style="padding: 6px 12px; font-size: 0.85rem; width: auto; display: inline-block;">
                    <option value="all">All Fields (Combined)</option>
                    <option value="name">Full Name</option>
                    <option value="email">Email Address</option>
                    <option value="phone">Mobile Number</option>
                    <option value="password">Password Strength</option>
                    <option value="pincode">Pincode / Postal</option>
                    <option value="aadhaar">Aadhaar Card</option>
                    <option value="age">Age (Eligibility)</option>
                    <option value="date">Appointment Date</option>
                </select>
            </div>

            <div>
                <label for="filter-status" style="font-size: 0.85rem; font-weight: 600; color: var(--dark-muted); margin-right: 6px;">Verdict:</label>
                <select id="filter-status" class="form-control" style="padding: 6px 12px; font-size: 0.85rem; width: auto; display: inline-block;">
                    <option value="all">All Statuses</option>
                    <option value="pass">Only Passed</option>
                    <option value="fail">Only Failed</option>
                    <option value="ready">Ready (Not Run)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Live Regex Testing Sandbox (Viva & Professor Demo Highlight) -->
    <div class="card" style="margin-bottom: 30px; border-left: 4px solid var(--secondary);">
        <h2 class="card-title">🔬 Interactive Live Regex Sandbox (Evaluator Demo Tool)</h2>
        <p class="form-help" style="margin-bottom: 16px;">
            Test any custom string in real time against our production regular expression patterns to observe boundary behavior instantly.
        </p>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label class="form-label" for="sandbox-preset">Validation Rule Pattern:</label>
                <select id="sandbox-preset" class="form-control">
                    <option value="name">Full Name Regex (3-50 letters)</option>
                    <option value="email">RFC Email Address Regex</option>
                    <option value="phone">10-Digit Mobile Regex</option>
                    <option value="password">Password Complexity Regex</option>
                    <option value="pincode">6-Digit Pincode Regex</option>
                    <option value="aadhaar">12-Digit Aadhaar Regex</option>
                    <option value="age">Age (18 - 110 Integer) Check</option>
                    <option value="date">Date (YYYY-MM-DD) Regex</option>
                </select>
            </div>

            <div>
                <label class="form-label" for="sandbox-input">Live Input String under Test:</label>
                <input type="text" id="sandbox-input" class="form-control" placeholder="Type test string here (e.g. John Doe, patient@hospital.org)...">
            </div>
        </div>

        <div style="margin-bottom: 12px; font-size: 0.85rem; color: var(--text-muted);">
            Active Pattern: <code id="sandbox-regex-display" style="background: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-weight: 600; color: #0f172a;">/^[A-Za-z]+([ '-][A-Za-z]+)*$/</code>
        </div>

        <div id="sandbox-result" class="alert alert-info" style="margin-bottom: 0;">
            Type a test input above to see instant evaluation feedback.
        </div>
    </div>

    <!-- Test Cases Execution Table -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 class="card-title">📊 Test Cases Execution Matrix</h2>
            <div style="font-size: 0.85rem; color: var(--text-muted);">
                Documentation: <a href="docs/PROJECT_DOCUMENTATION.md" target="_blank" style="text-decoration: underline; font-weight: 600; color: var(--primary);">Full Project Report</a> | 
                <a href="testing/test_plan.md" target="_blank" style="text-decoration: underline;">Test Plan (IEEE 829)</a> | 
                <a href="testing/test_cases.md" target="_blank" style="text-decoration: underline;">Test Spec & RTM</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table" id="test-cases-table">
                <thead>
                    <tr>
                        <th style="width: 120px;">Test ID</th>
                        <th style="width: 140px;">Field / Rule</th>
                        <th>Technique & Scenario</th>
                        <th>Input Data</th>
                        <th style="width: 130px;">Expected</th>
                        <th style="width: 130px;">Actual</th>
                        <th style="width: 90px;">Verdict</th>
                        <th style="width: 70px;">Action</th>
                    </tr>
                </thead>
                <tbody id="test-results-body">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            Loading test cases dataset...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Test Runner Logic Script -->
<script src="assets/js/test_runner.js"></script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
