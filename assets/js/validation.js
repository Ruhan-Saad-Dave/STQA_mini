/**
 * Software Testing & Quality Assurance (STQA)
 * Core Regular Expression Validation Engine
 * 
 * Client-Side Validation Layer (Layer 1)
 * Used across Registration, Signup, Login, and STQA Test Runner
 */

const CovidValidator = {
    // 1. Regular Expression Patterns
    patterns: {
        // Full Name: Only alphabets, spaces, hyphens, min 3, max 50 characters
        name: /^[A-Za-z]+([ '-][A-Za-z]+)*$/,

        // Email: RFC 5322 compliant email format with valid TLD
        email: /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/,

        // Mobile: 10 digit Indian mobile numbers (starts with 6,7,8,9), optional +91 prefix
        phone: /^(?:\+91[\-\s]?)?[6-9]\d{9}$/,

        // Password: 8-32 chars, at least 1 uppercase, 1 lowercase, 1 digit, 1 special character
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,32}$/,

        // Pincode: 6-digit Indian Postal PIN, first digit 1-9
        pincode: /^[1-9][0-9]{5}$/,

        // Aadhaar: 12-digit UIDAI format (cannot begin with 0 or 1, optional spacing)
        aadhaar: /^[2-9]{1}[0-9]{3}\s?[0-9]{4}\s?[0-9]{4}$/,

        // Passport: Standard format (1 letter followed by 7 digits)
        passport: /^[A-Z][0-9]{7}$/i,

        // Age: Numeric between 18 and 110
        age: /^(1[89]|[2-9][0-9]|10[0-9]|110)$/,

        // Date: ISO standard YYYY-MM-DD
        date: /^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/
    },

    // 2. Descriptive Error Messages for Quality Assurance
    messages: {
        name: "Name must be 3-50 letters only (no numbers or special symbols except hyphen/space).",
        email: "Enter a valid email address (e.g., patient@example.com).",
        phone: "Mobile number must be a valid 10-digit number starting with 6-9.",
        password: "Password must be 8-32 characters with uppercase, lowercase, number & symbol (@$!%*?&).",
        pincode: "Pincode must be exactly 6 digits and cannot start with 0.",
        aadhaar: "Aadhaar must be a 12-digit number (e.g., 2345 6789 0123).",
        passport: "Passport number must be 1 letter followed by 7 digits (e.g., A1234567).",
        age: "Age must be an integer between 18 and 110.",
        date: "Date must be in valid YYYY-MM-DD format."
    },

    // 3. Validation Logic per Field
    validateField: function(type, value) {
        const trimmed = (value || '').trim();

        if (!trimmed) {
            return {
                isValid: false,
                message: "This field is required and cannot be empty."
            };
        }

        // Additional boundary check for Name length
        if (type === 'name') {
            if (trimmed.length < 3) {
                return { isValid: false, message: "Name is too short (minimum 3 characters)." };
            }
            if (trimmed.length > 50) {
                return { isValid: false, message: "Name is too long (maximum 50 characters)." };
            }
        }

        // Additional boundary check for Age
        if (type === 'age') {
            const num = parseInt(trimmed, 10);
            if (isNaN(num) || num < 18 || num > 110) {
                return { isValid: false, message: "Age must be strictly between 18 and 110 years." };
            }
        }

        // Date range check (cannot book in the past)
        if (type === 'date') {
            const isPatternValid = this.patterns.date.test(trimmed);
            if (!isPatternValid) {
                return { isValid: false, message: this.messages.date };
            }
            const selectedDate = new Date(trimmed);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                return { isValid: false, message: "Appointment date cannot be in the past." };
            }
            return { isValid: true, message: "Valid date selected." };
        }

        const pattern = this.patterns[type];
        if (!pattern) {
            return { isValid: true, message: "" };
        }

        const passes = pattern.test(trimmed);
        return {
            isValid: passes,
            message: passes ? "Looks good!" : this.messages[type]
        };
    },

    // 4. Password Strength Evaluator (STQA Metric)
    evaluatePasswordStrength: function(password) {
        let score = 0;
        if (!password) return { score: 0, text: "None", percent: 0, color: "#ccc" };

        if (password.length >= 8) score += 1;
        if (/[a-z]/.test(password)) score += 1;
        if (/[A-Z]/.test(password)) score += 1;
        if (/\d/.test(password)) score += 1;
        if (/[@$!%*?&]/.test(password)) score += 1;

        if (score <= 2) {
            return { score, text: "Weak", percent: 30, color: "#e63946" };
        } else if (score <= 4) {
            return { score, text: "Moderate", percent: 70, color: "#f4a261" };
        } else {
            return { score, text: "Strong", percent: 100, color: "#2a9d8f" };
        }
    },

    // 5. Automatic DOM Event Binding for Forms
    attachFormValidation: function(formElement) {
        if (!formElement) return;

        const inputs = formElement.querySelectorAll('[data-validate]');

        inputs.forEach(input => {
            const validationType = input.getAttribute('data-validate');

            const handleValidation = () => {
                let typeToValidate = validationType;

                // Dynamic identity type check for registrations
                if (validationType === 'identity') {
                    const idTypeSelect = formElement.querySelector('#id_type');
                    if (idTypeSelect && idTypeSelect.value === 'Passport') {
                        typeToValidate = 'passport';
                    } else {
                        typeToValidate = 'aadhaar';
                    }
                }

                const result = this.validateField(typeToValidate, input.value);
                const feedbackEl = input.parentElement.querySelector('.invalid-feedback') || 
                                   input.parentElement.querySelector('.validation-msg');

                if (result.isValid) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                    if (feedbackEl) {
                        feedbackEl.textContent = '';
                        feedbackEl.style.display = 'none';
                    }
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                    if (feedbackEl) {
                        feedbackEl.textContent = result.message;
                        feedbackEl.style.display = 'block';
                    }
                }

                // Password strength bar handling if present
                if (typeToValidate === 'password') {
                    const strengthBar = document.getElementById('password-strength-meter');
                    const strengthLabel = document.getElementById('password-strength-text');
                    if (strengthBar && strengthLabel) {
                        const strength = this.evaluatePasswordStrength(input.value);
                        strengthBar.style.width = strength.percent + '%';
                        strengthBar.style.backgroundColor = strength.color;
                        strengthLabel.textContent = `Strength: ${strength.text}`;
                        strengthLabel.style.color = strength.color;
                    }
                }

                return result.isValid;
            };

            input.addEventListener('input', handleValidation);
            input.addEventListener('blur', handleValidation);
        });

        // Form submit interception
        formElement.addEventListener('submit', function(e) {
            let formHasErrors = false;
            let firstInvalidInput = null;

            inputs.forEach(input => {
                let typeToValidate = input.getAttribute('data-validate');
                if (typeToValidate === 'identity') {
                    const idTypeSelect = formElement.querySelector('#id_type');
                    typeToValidate = (idTypeSelect && idTypeSelect.value === 'Passport') ? 'passport' : 'aadhaar';
                }

                const result = CovidValidator.validateField(typeToValidate, input.value);
                const feedbackEl = input.parentElement.querySelector('.invalid-feedback') || 
                                   input.parentElement.querySelector('.validation-msg');

                if (!result.isValid) {
                    formHasErrors = true;
                    input.classList.add('is-invalid');
                    input.classList.remove('is-valid');
                    if (feedbackEl) {
                        feedbackEl.textContent = result.message;
                        feedbackEl.style.display = 'block';
                    }
                    if (!firstInvalidInput) {
                        firstInvalidInput = input;
                    }
                }
            });

            if (formHasErrors) {
                e.preventDefault();
                if (firstInvalidInput) {
                    firstInvalidInput.focus();
                }
            }
        });
    }
};

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-stqa-validate]').forEach(form => {
        CovidValidator.attachFormValidation(form);
    });
});
