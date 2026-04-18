/* ============================================================
   CampusLink - Auth JavaScript
   Login, Register, OTP, Forgot/Reset Password
   ============================================================ */

'use strict';

// ============================================================
// PASSWORD VISIBILITY TOGGLE
// ============================================================
function initPasswordToggles() {
    document.querySelectorAll('.password-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            if (!input) return;

            const isPassword = input.type === 'password';
            input.type       = isPassword ? 'text' : 'password';
            btn.textContent  = isPassword ? '🙈' : '👁️';
            btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    });
}


// ============================================================
// PASSWORD STRENGTH METER
// ============================================================
function initPasswordStrength() {
    const passwordInput = document.querySelector('#password, input[name="password"]');
    const strengthBar   = document.querySelector('.password-strength');
    const strengthText  = document.querySelector('.password-strength-text');

    if (!passwordInput || !strengthBar) return;

    passwordInput.addEventListener('input', () => {
        const val      = passwordInput.value;
        const strength = getPasswordStrength(val);

        strengthBar.className = `password-strength strength-${strength.level}`;

        if (strengthText) {
            strengthText.textContent = val.length === 0 ? '' : strength.label;
            strengthText.style.color = strength.color;
        }
    });
}

function getPasswordStrength(password) {
    if (!password) return { level: '', label: '', color: '' };

    let score = 0;
    if (password.length >= 8)                       score++;
    if (password.length >= 12)                      score++;
    if (/[A-Z]/.test(password))                     score++;
    if (/[a-z]/.test(password))                     score++;
    if (/\d/.test(password))                        score++;
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) score++;

    if (score <= 2) return { level: 'weak',   label: 'Weak — add uppercase & numbers',   color: '#e53e3e' };
    if (score <= 3) return { level: 'fair',   label: 'Fair — try adding special chars',  color: '#f59e0b' };
    if (score <= 4) return { level: 'good',   label: 'Good password',                   color: '#0b3d91' };
    return           { level: 'strong', label: 'Strong password ✓',              color: '#1ea952' };
}


// ============================================================
// REAL-TIME FORM VALIDATION
// ============================================================
function initFormValidation() {
    const form = document.querySelector('.auth-form, .registration-form');
    if (!form) return;

    // Validate on blur
    form.querySelectorAll('.form-control').forEach(input => {
        input.addEventListener('blur', () => validateField(input));
        input.addEventListener('input', () => {
            if (input.classList.contains('is-invalid')) {
                validateField(input);
            }
        });
    });

    // Prevent default submit and validate all
    form.addEventListener('submit', e => {
        let valid = true;
        form.querySelectorAll('.form-control[required]').forEach(input => {
            if (!validateField(input)) valid = false;
        });

        if (!valid) {
            e.preventDefault();
            // Scroll to first error
            const firstError = form.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstError.focus();
            }
        } else {
            // Show loading on submit button
            const submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('btn-loading');
                submitBtn.disabled = true;
            }
        }
    });
}

function validateField(input) {
    const value   = input.value.trim();
    const name    = input.name;
    let error     = '';

    // Required
    if (input.hasAttribute('required') && !value) {
        const label = input.closest('.form-group')?.querySelector('.form-label')?.textContent?.trim() || 'This field';
        error = `${label.replace('*', '').trim()} is required.`;
    }
    // Email
    else if (input.type === 'email' && value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        error = 'Please enter a valid email address.';
    }
    // School email domain
    else if (input.dataset.schoolEmail && value) {
        const domain = input.dataset.schoolEmail;
        if (!value.endsWith('@' + domain)) {
            error = `Must be a @${domain} school email.`;
        }
    }
    // Phone (Nigerian)
    else if (input.dataset.phone && value) {
        if (!/^(\+?234|0)[789][01]\d{8}$/.test(value.replace(/\s/g, ''))) {
            error = 'Enter a valid Nigerian phone number.';
        }
    }
    // Min length
    else if (input.dataset.min && value.length < parseInt(input.dataset.min)) {
        error = `Must be at least ${input.dataset.min} characters.`;
    }
    // Max length
    else if (input.dataset.max && value.length > parseInt(input.dataset.max)) {
        error = `Must not exceed ${input.dataset.max} characters.`;
    }
    // Password confirmation
    else if (name === 'password_confirmation') {
        const passInput = document.querySelector('input[name="password"]');
        if (passInput && value !== passInput.value) {
            error = 'Passwords do not match.';
        }
    }
    // Matric number pattern
    else if (input.dataset.matric && value) {
        if (!/^[A-Za-z]{2,4}\/\d{4,6}\/\d{4,6}$/.test(value)) {
            error = 'Enter a valid matric number (e.g. CSC/2021/001).';
        }
    }

    // Apply or clear error
    showFieldError(input, error);
    return !error;
}

function showFieldError(input, message) {
    input.classList.toggle('is-invalid', !!message);
    input.classList.toggle('is-valid', !message && input.value.trim().length > 0);

    let errorEl = input.closest('.form-group')?.querySelector('.form-error');

    if (message) {
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'form-error';
            input.parentNode.appendChild(errorEl);
        }
        errorEl.textContent = message;
    } else if (errorEl) {
        errorEl.textContent = '';
    }
}


// ============================================================
// OTP INPUT — auto advance & paste handling
// ============================================================
function initOtpInputs() {
    const otpInputs = document.querySelectorAll('.otp-input');
    if (!otpInputs.length) return;

    otpInputs.forEach((input, index) => {
        // Only allow digits
        input.addEventListener('keydown', e => {
            if (!/^\d$/.test(e.key) && !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                e.preventDefault();
            }
        });

        input.addEventListener('input', () => {
            const val = input.value.replace(/\D/g, '');
            input.value = val.slice(0, 1);

            input.classList.toggle('filled', input.value.length > 0);

            // Auto advance
            if (input.value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }

            // Combine OTP into hidden field
            syncOtpValue(otpInputs);
        });

        input.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                otpInputs[index - 1].focus();
                otpInputs[index - 1].value = '';
                otpInputs[index - 1].classList.remove('filled');
                syncOtpValue(otpInputs);
            }
        });

        // Handle paste
        input.addEventListener('paste', e => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            const digits = pasted.replace(/\D/g, '').slice(0, otpInputs.length);

            digits.split('').forEach((digit, i) => {
                if (otpInputs[index + i]) {
                    otpInputs[index + i].value = digit;
                    otpInputs[index + i].classList.add('filled');
                }
            });

            syncOtpValue(otpInputs);

            // Focus next empty or last
            const nextEmpty = [...otpInputs].find(el => !el.value);
            (nextEmpty || otpInputs[otpInputs.length - 1]).focus();
        });
    });
}

function syncOtpValue(inputs) {
    const combined = [...inputs].map(i => i.value).join('');
    const hidden   = document.querySelector('input[name="otp"]');
    if (hidden) hidden.value = combined;
}


// ============================================================
// OTP RESEND TIMER
// ============================================================
function initOtpTimer() {
    const timerEl   = document.querySelector('.otp-timer');
    const resendBtn = document.querySelector('.otp-resend-btn');

    if (!timerEl || !resendBtn) return;

    let remaining = parseInt(timerEl.dataset.seconds) || 60;

    resendBtn.disabled = true;

    const interval = setInterval(() => {
        remaining--;
        timerEl.textContent = `${remaining}s`;

        if (remaining <= 0) {
            clearInterval(interval);
            timerEl.textContent   = '';
            resendBtn.disabled    = false;
            resendBtn.textContent = 'Resend Code';
        }
    }, 1000);

    // Handle resend
    resendBtn.addEventListener('click', async () => {
        resendBtn.disabled    = true;
        resendBtn.textContent = 'Sending...';

        try {
            const data = await CampusLink.ajax('/auth/resend-otp', 'POST', {
                csrf_token: CampusLink.getCsrf(),
            });

            CampusLink.toast(data.message, data.success ? 'success' : 'error');

            if (data.success) {
                // Restart timer
                remaining           = 60;
                timerEl.textContent = `${remaining}s`;
                const newInterval = setInterval(() => {
                    remaining--;
                    timerEl.textContent = `${remaining}s`;
                    if (remaining <= 0) {
                        clearInterval(newInterval);
                        timerEl.textContent   = '';
                        resendBtn.disabled    = false;
                        resendBtn.textContent = 'Resend Code';
                    }
                }, 1000);
            } else {
                resendBtn.disabled    = false;
                resendBtn.textContent = 'Resend Code';
            }
        } catch {
            CampusLink.toast('Network error. Try again.', 'error');
            resendBtn.disabled    = false;
            resendBtn.textContent = 'Resend Code';
        }
    });
}


// ============================================================
// MULTI-STEP REGISTRATION FORM
// ============================================================
function initMultiStepForm() {
    const form  = document.querySelector('.multi-step-form');
    if (!form)  return;

    const steps       = form.querySelectorAll('.form-step');
    const stepButtons = document.querySelectorAll('.reg-step');
    let currentStep   = 0;

    function showStep(index) {
        steps.forEach((step, i) => {
            step.classList.toggle('active', i === index);
        });

        stepButtons.forEach((btn, i) => {
            btn.classList.remove('active', 'completed');
            if (i < index)  btn.classList.add('completed');
            if (i === index) btn.classList.add('active');
        });

        // Scroll to top of form
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        currentStep = index;
    }

    // Next buttons
    form.querySelectorAll('[data-next-step]').forEach(btn => {
        btn.addEventListener('click', () => {
            const currentStepEl = steps[currentStep];
            const inputs = currentStepEl.querySelectorAll('.form-control[required]');
            let valid = true;

            inputs.forEach(input => {
                if (!validateField(input)) valid = false;
            });

            if (valid && currentStep < steps.length - 1) {
                showStep(currentStep + 1);
            }
        });
    });

    // Prev buttons
    form.querySelectorAll('[data-prev-step]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep > 0) showStep(currentStep - 1);
        });
    });

    // Initialize
    showStep(0);
}


// ============================================================
// EMAIL AVAILABILITY CHECK
// ============================================================
function initEmailCheck() {
    const emailInputs = document.querySelectorAll('input[data-check-email]');

    emailInputs.forEach(input => {
        const debouncedCheck = CampusLink.debounce(async () => {
            const value = input.value.trim();
            if (!value || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return;

            const endpoint = input.dataset.checkEmail;

            try {
                const data = await CampusLink.ajax(
                    `${endpoint}?email=${encodeURIComponent(value)}`,
                    'GET'
                );

                if (data.available === false) {
                    showFieldError(input, 'This email is already registered.');
                } else if (data.available === true) {
                    showFieldError(input, '');
                }
            } catch {
                // Silently fail
            }
        }, 600);

        input.addEventListener('input', debouncedCheck);
    });
}


// ============================================================
// PHONE AVAILABILITY CHECK
// ============================================================
function initPhoneCheck() {
    const phoneInputs = document.querySelectorAll('input[data-check-phone]');

    phoneInputs.forEach(input => {
        const debouncedCheck = CampusLink.debounce(async () => {
            const value = input.value.trim().replace(/\s/g, '');
            if (!value || !/^(\+?234|0)[789][01]\d{8}$/.test(value)) return;

            const endpoint = input.dataset.checkPhone;

            try {
                const data = await CampusLink.ajax(
                    `${endpoint}?phone=${encodeURIComponent(value)}`,
                    'GET'
                );

                if (data.available === false) {
                    showFieldError(input, 'This phone number is already registered.');
                } else if (data.available === true) {
                    showFieldError(input, '');
                }
            } catch {
                // Silently fail
            }
        }, 600);

        input.addEventListener('input', debouncedCheck);
    });
}


// ============================================================
// FILE UPLOAD PREVIEW
// ============================================================
function initFileUploadPreviews() {
    document.querySelectorAll('.file-upload-area').forEach(area => {
        const input   = area.querySelector('input[type="file"]');
        const preview = area.querySelector('.file-preview');

        if (!input) return;

        input.addEventListener('change', () => {
            const file = input.files[0];
            if (!file) return;

            // Validate size (client-side)
            const maxMB = parseInt(input.dataset.maxMb) || 2;
            if (file.size > maxMB * 1024 * 1024) {
                CampusLink.toast(`File too large. Maximum is ${maxMB}MB.`, 'error');
                input.value = '';
                return;
            }

            // Show preview for images
            if (file.type.startsWith('image/') && preview) {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = preview.querySelector('img') || document.createElement('img');
                    img.src   = e.target.result;
                    img.alt   = 'Preview';
                    preview.appendChild(img);
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            // Show filename for PDFs
            const nameEl = area.querySelector('.file-upload-text');
            if (nameEl) {
                nameEl.innerHTML = `<strong>✓ ${file.name}</strong> (${(file.size / 1024).toFixed(0)} KB)`;
            }
        });

        // Drag and drop
        ['dragover', 'dragleave', 'drop'].forEach(event => {
            area.addEventListener(event, e => {
                e.preventDefault();
                area.classList.toggle('drag-over', event === 'dragover');

                if (event === 'drop') {
                    input.files = e.dataTransfer.files;
                    input.dispatchEvent(new Event('change'));
                }
            });
        });
    });
}


// ============================================================
// PLAN SELECTION (on registration/payment page)
// ============================================================
function initPlanSelection() {
    const planCards = document.querySelectorAll('.plan-select-card');
    const hiddenInput = document.querySelector('input[name="plan_type"]');

    planCards.forEach(card => {
        card.addEventListener('click', () => {
            planCards.forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');

            const plan = card.dataset.plan;
            if (hiddenInput) hiddenInput.value = plan;

            // Update total display
            const totalEl  = document.querySelector('.payment-total-amount');
            const amount   = card.dataset.amount;
            const label    = card.querySelector('.plan-select-name')?.textContent;
            if (totalEl && amount) {
                totalEl.textContent = CampusLink.formatCurrency(parseInt(amount));
            }

            const planLabelEl = document.querySelector('.selected-plan-label');
            if (planLabelEl && label) {
                planLabelEl.textContent = label;
            }
        });
    });
}


// ============================================================
// RESEND VERIFICATION EMAIL
// ============================================================
function initResendVerification() {
    const btn = document.querySelector('.resend-verification-btn');
    if (!btn) return;

    btn.addEventListener('click', async () => {
        btn.disabled    = true;
        btn.textContent = 'Sending...';

        try {
            const data = await CampusLink.ajax('/auth/resend-verification', 'POST', {
                csrf_token: CampusLink.getCsrf(),
            });

            CampusLink.toast(data.message, data.success ? 'success' : 'error');

            if (data.success) {
                let seconds = 60;
                btn.textContent = `Resend in ${seconds}s`;

                const timer = setInterval(() => {
                    seconds--;
                    btn.textContent = `Resend in ${seconds}s`;
                    if (seconds <= 0) {
                        clearInterval(timer);
                        btn.disabled    = false;
                        btn.textContent = 'Resend Verification Email';
                    }
                }, 1000);
            } else {
                btn.disabled    = false;
                btn.textContent = 'Resend Verification Email';
            }
        } catch {
            CampusLink.toast('Network error. Try again.', 'error');
            btn.disabled    = false;
            btn.textContent = 'Resend Verification Email';
        }
    });
}


// ============================================================
// DOM READY
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    initPasswordToggles();
    initPasswordStrength();
    initFormValidation();
    initOtpInputs();
    initOtpTimer();
    initMultiStepForm();
    initEmailCheck();
    initPhoneCheck();
    initFileUploadPreviews();
    initPlanSelection();
    initResendVerification();
});