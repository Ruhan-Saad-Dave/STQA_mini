/**
 * Software Testing & Quality Assurance (STQA)
 * Interactive Automated Test Runner Engine
 */

let testCasesData = [];
let executionResults = [];

// Initialize Runner
document.addEventListener('DOMContentLoaded', () => {
    loadTestCases();
    setupEventHandlers();
    setupSandbox();
});

// Load test cases from JSON or embedded fallback
async function loadTestCases() {
    try {
        const response = await fetch('testing/test_cases.json');
        if (response.ok) {
            testCasesData = await response.json();
        } else {
            throw new Error("Local fetch rejected");
        }
    } catch (err) {
        // Fallback dataset if running via file:// or offline
        console.warn("Using embedded test cases fallback:", err);
        testCasesData = getEmbeddedFallbackTestCases();
    }

    renderTestCasesTable(testCasesData);
    updateMetricsDisplay();
}

function setupEventHandlers() {
    document.getElementById('btn-run-all')?.addEventListener('click', runAllTests);
    document.getElementById('filter-category')?.addEventListener('change', filterTests);
    document.getElementById('filter-status')?.addEventListener('change', filterTests);
    document.getElementById('btn-export-csv')?.addEventListener('click', exportResultsToCSV);
}

// Render test case rows
function renderTestCasesTable(cases) {
    const tbody = document.getElementById('test-results-body');
    if (!tbody) return;

    if (cases.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 24px; color: var(--text-muted);">No test cases match filter.</td></tr>';
        return;
    }

    tbody.innerHTML = cases.map(tc => {
        const res = executionResults.find(r => r.id === tc.id);
        const statusBadge = res 
            ? (res.status === 'PASS' ? '<span class="badge badge-pass">PASS</span>' : '<span class="badge badge-fail">FAIL</span>')
            : '<span class="badge" style="background: #e2e8f0; color: #475569;">Ready</span>';

        const actualDisplay = res ? (res.actual ? 'Accepted (Valid)' : 'Rejected (Invalid)') : '-';
        const expectedDisplay = tc.expected ? 'Accepted (Valid)' : 'Rejected (Invalid)';

        return `
            <tr id="row-${tc.id}">
                <td><code>${tc.id}</code></td>
                <td><strong>${tc.pattern_name}</strong></td>
                <td><small style="color: var(--text-muted);">${tc.technique}</small><br>${tc.description}</td>
                <td><code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">"${escapeHtml(tc.input)}"</code></td>
                <td><span style="font-size: 0.85rem; font-weight: 600; color: ${tc.expected ? '#166534' : '#991b1b'};">${expectedDisplay}</span></td>
                <td id="actual-${tc.id}">${actualDisplay}</td>
                <td id="status-${tc.id}">${statusBadge}</td>
                <td>
                    <button class="btn btn-outline btn-sm" onclick="runSingleTest('${tc.id}')" style="padding: 2px 8px; font-size: 0.75rem;">Run</button>
                </td>
            </tr>
        `;
    }).join('');
}

// Execute single test case
function runSingleTest(testId) {
    const tc = testCasesData.find(c => c.id === testId);
    if (!tc) return;

    const startTime = performance.now();
    const validationResult = CovidValidator.validateField(tc.category, tc.input);
    const duration = (performance.now() - startTime).toFixed(2);

    const actual = validationResult.isValid;
    const isPass = (actual === tc.expected);
    const status = isPass ? 'PASS' : 'FAIL';

    // Store execution
    const existingIndex = executionResults.findIndex(r => r.id === testId);
    const record = {
        id: tc.id,
        category: tc.category,
        pattern_name: tc.pattern_name,
        technique: tc.technique,
        description: tc.description,
        input: tc.input,
        expected: tc.expected,
        actual: actual,
        status: status,
        duration: duration + ' ms',
        feedback: validationResult.message
    };

    if (existingIndex >= 0) {
        executionResults[existingIndex] = record;
    } else {
        executionResults.push(record);
    }

    // Update row DOM
    const actualEl = document.getElementById(`actual-${tc.id}`);
    const statusEl = document.getElementById(`status-${tc.id}`);
    if (actualEl) {
        actualEl.innerHTML = actual ? 'Accepted (Valid)' : 'Rejected (Invalid)';
    }
    if (statusEl) {
        statusEl.innerHTML = isPass ? '<span class="badge badge-pass">PASS</span>' : '<span class="badge badge-fail">FAIL</span>';
    }

    updateMetricsDisplay();
}

// Execute all test cases sequentially
async function runAllTests() {
    const btn = document.getElementById('btn-run-all');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '⏳ Executing STQA Suite...';
    }

    const startTime = performance.now();
    executionResults = [];

    for (const tc of testCasesData) {
        runSingleTest(tc.id);
        // Small visual yield
        await new Promise(r => setTimeout(r, 10));
    }

    const totalTime = (performance.now() - startTime).toFixed(1);

    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '▶ Re-run Test Suite';
    }

    const timeEl = document.getElementById('metric-time');
    if (timeEl) timeEl.innerText = totalTime + ' ms';
}

function updateMetricsDisplay() {
    const totalEl = document.getElementById('metric-total');
    const passedEl = document.getElementById('metric-passed');
    const failedEl = document.getElementById('metric-failed');
    const rateEl = document.getElementById('metric-rate');

    const total = testCasesData.length;
    const executed = executionResults.length;
    const passed = executionResults.filter(r => r.status === 'PASS').length;
    const failed = executionResults.filter(r => r.status === 'FAIL').length;
    const rate = executed > 0 ? Math.round((passed / executed) * 100) : 0;

    if (totalEl) totalEl.innerText = total;
    if (passedEl) passedEl.innerText = passed;
    if (failedEl) failedEl.innerText = failed;
    if (rateEl) rateEl.innerText = executed > 0 ? `${rate}%` : 'N/A';
}

function filterTests() {
    const category = document.getElementById('filter-category')?.value || 'all';
    const status = document.getElementById('filter-status')?.value || 'all';

    let filtered = testCasesData;

    if (category !== 'all') {
        filtered = filtered.filter(tc => tc.category === category);
    }

    if (status !== 'all') {
        filtered = filtered.filter(tc => {
            const res = executionResults.find(r => r.id === tc.id);
            if (!res) return status === 'ready';
            return res.status.toLowerCase() === status.toLowerCase();
        });
    }

    renderTestCasesTable(filtered);
}

// Interactive Sandbox
function setupSandbox() {
    const select = document.getElementById('sandbox-preset');
    const input = document.getElementById('sandbox-input');
    const patternDisplay = document.getElementById('sandbox-regex-display');
    const resultBox = document.getElementById('sandbox-result');

    if (!select || !input) return;

    const runSandboxCheck = () => {
        const type = select.value;
        const val = input.value;
        const regex = CovidValidator.patterns[type];

        if (patternDisplay && regex) {
            patternDisplay.innerText = regex.toString();
        }

        const res = CovidValidator.validateField(type, val);

        if (resultBox) {
            if (!val.trim()) {
                resultBox.className = 'alert alert-info';
                resultBox.innerHTML = 'Enter a value to test against the selected Regular Expression pattern.';
            } else if (res.isValid) {
                resultBox.className = 'alert alert-success';
                resultBox.innerHTML = `✅ <strong>MATCH / ACCEPTED:</strong> The string satisfies the regex format. (${res.message})`;
            } else {
                resultBox.className = 'alert alert-danger';
                resultBox.innerHTML = `❌ <strong>REJECTED / INVALID:</strong> ${res.message}`;
            }
        }
    };

    select.addEventListener('change', runSandboxCheck);
    input.addEventListener('input', runSandboxCheck);
    runSandboxCheck();
}

// Export execution to CSV
function exportResultsToCSV() {
    if (executionResults.length === 0) {
        alert("Please run the test suite first before exporting the report!");
        return;
    }

    const headers = ["Test Case ID", "Category", "Technique", "Scenario Description", "Input Value", "Expected Result", "Actual Result", "Status", "Duration", "Feedback Message"];
    const rows = executionResults.map(r => [
        r.id,
        `"${r.pattern_name}"`,
        `"${r.technique}"`,
        `"${r.description}"`,
        `"${r.input}"`,
        r.expected ? "Accepted" : "Rejected",
        r.actual ? "Accepted" : "Rejected",
        r.status,
        r.duration,
        `"${r.feedback || ''}"`
    ]);

    const csvContent = "data:text/csv;charset=utf-8," + [headers.join(","), ...rows.map(e => e.join(","))].join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `STQA_Regex_Test_Report_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function getEmbeddedFallbackTestCases() {
    return [
        { id: "TC_NAME_01", category: "name", technique: "ECP - Valid", description: "Standard alphabetical name", input: "John Doe", expected: true, pattern_name: "Full Name" },
        { id: "TC_NAME_02", category: "name", technique: "ECP - Invalid (Digits)", description: "Name containing numbers", input: "John123", expected: false, pattern_name: "Full Name" },
        { id: "TC_NAME_03", category: "name", technique: "BVA - Min Length", description: "3-character minimum boundary", input: "Ali", expected: true, pattern_name: "Full Name" },
        { id: "TC_NAME_04", category: "name", technique: "BVA - Min-1 Length", description: "2-character invalid boundary", input: "Al", expected: false, pattern_name: "Full Name" },
        { id: "TC_EMAIL_01", category: "email", technique: "ECP - Valid", description: "Standard valid email", input: "patient@hospital.org", expected: true, pattern_name: "Email Address" },
        { id: "TC_EMAIL_02", category: "email", technique: "ECP - Invalid", description: "Missing @ sign in email", input: "userdomain.com", expected: false, pattern_name: "Email Address" },
        { id: "TC_PHONE_01", category: "phone", technique: "ECP - Valid", description: "Standard 10-digit mobile starting with 9", input: "9820123456", expected: true, pattern_name: "Mobile Number" },
        { id: "TC_PHONE_02", category: "phone", technique: "BVA - Min-1 Length", description: "9 digits (below boundary)", input: "982012345", expected: false, pattern_name: "Mobile Number" },
        { id: "TC_PWD_01", category: "password", technique: "ECP - Valid", description: "Standard strong password", input: "Pass@1234", expected: true, pattern_name: "Password Strength" },
        { id: "TC_PWD_02", category: "password", technique: "ECP - Invalid", description: "Missing symbol in password", input: "Password123", expected: false, pattern_name: "Password Strength" },
        { id: "TC_PIN_01", category: "pincode", technique: "ECP - Valid", description: "Valid 6-digit postal pin", input: "400001", expected: true, pattern_name: "Pincode / Postal" },
        { id: "TC_PIN_02", category: "pincode", technique: "ECP - Invalid", description: "Pincode starting with zero", input: "012345", expected: false, pattern_name: "Pincode / Postal" },
        { id: "TC_UID_01", category: "aadhaar", technique: "ECP - Valid", description: "12-digit Aadhaar number", input: "2345 6789 0123", expected: true, pattern_name: "Aadhaar Card" },
        { id: "TC_AGE_01", category: "age", technique: "BVA - Min Boundary", description: "Eligible boundary age 18", input: "18", expected: true, pattern_name: "Age Validation" },
        { id: "TC_AGE_02", category: "age", technique: "BVA - Min-1 Boundary", description: "Underage boundary 17", input: "17", expected: false, pattern_name: "Age Validation" },
        { id: "TC_DATE_01", category: "date", technique: "ECP - Valid", description: "Future ISO date", input: "2026-11-20", expected: true, pattern_name: "Appointment Date" }
    ];
}
