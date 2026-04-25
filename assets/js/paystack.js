/* ============================================================
   CampusLink - Paystack Payment JavaScript
   Handles Paystack popup initialization and verification.
   ============================================================ */

'use strict';

// ============================================================
// PAYSTACK PAYMENT HANDLER
// ============================================================
const PaystackHandler = {

    init() {
        this.bindPayButton();
        this.bindPlanCards();
    },

    // ============================================================
    // BIND PAY BUTTON
    // ============================================================
    bindPayButton() {
        const payBtn = document.querySelector('.paystack-pay-btn');
        if (!payBtn) return;

        payBtn.addEventListener('click', async e => {
            e.preventDefault();
            await this.initiatePayment(payBtn);
        });
    },

    // ============================================================
    // BIND PLAN CARD SELECTION
    // ============================================================
    bindPlanCards() {
        const planCards = document.querySelectorAll('.plan-select-card');
        planCards.forEach(card => {
            card.addEventListener('click', () => {
                planCards.forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                this.updatePaymentSummary(card);
            });
        });

        // Auto-select first plan
        if (planCards.length > 0 && !document.querySelector('.plan-select-card.selected')) {
            planCards[0].click();
        }
    },

    // ============================================================
    // UPDATE PAYMENT SUMMARY
    // ============================================================
    updatePaymentSummary(card) {
        const planName   = card.querySelector('.plan-select-name')?.textContent;
        const planAmount = card.dataset.amount;
        const planLabel  = card.querySelector('.plan-select-name')?.textContent;

        const summaryPlan    = document.querySelector('.payment-summary-plan');
        const summaryAmount  = document.querySelector('.payment-summary-amount');
        const payBtn         = document.querySelector('.paystack-pay-btn');

        if (summaryPlan && planLabel) summaryPlan.textContent    = planLabel;
        if (summaryAmount && planAmount) summaryAmount.textContent = CampusLink.formatCurrency(parseInt(planAmount));

        if (payBtn && planAmount) {
            payBtn.dataset.amount = planAmount;
            payBtn.textContent = `Pay ${CampusLink.formatCurrency(parseInt(planAmount))} →`;
        }
    },

    // ============================================================
    // INITIATE PAYMENT (calls backend first)
    // ============================================================
    async initiatePayment(btn) {
        const vendorId    = btn.dataset.vendorId   || document.querySelector('input[name="vendor_id"]')?.value;
        const vendorType  = btn.dataset.vendorType || document.querySelector('input[name="vendor_type"]')?.value;
        const planType    = document.querySelector('.plan-select-card.selected')?.dataset.plan
                         || document.querySelector('input[name="plan_type"]')?.value;
        const paystackKey = btn.dataset.paystackKey || window.PAYSTACK_PUBLIC_KEY;

        if (!vendorId || !planType || !paystackKey) {
            CampusLink.toast('Please select a plan before proceeding.', 'error');
            return;
        }

        btn.classList.add('btn-loading');
        btn.disabled = true;

        try {
            const data = await CampusLink.ajax('/vendor/payment/initiate', 'POST', {
                csrf_token:  CampusLink.getCsrf(),
                vendor_id:   vendorId,
                vendor_type: vendorType,
                plan_type:   planType,
            });

            if (!data.success) {
                CampusLink.toast(data.message || 'Could not initialize payment.', 'error');
                return;
            }

            // Launch Paystack popup using authorization_url from server
            // This preserves all server-side configuration including channels
            this.openPopup({
                authUrl:   data.authorization_url,
                reference: data.reference,
                onSuccess: (response) => this.handleSuccess(response),
                onClose:   ()         => this.handleClose(),
            });

        } catch (err) {
            CampusLink.toast('Network error. Please try again.', 'error');
        } finally {
            btn.classList.remove('btn-loading');
            btn.disabled = false;
        }
    },

    // ============================================================
    // OPEN PAYSTACK POPUP
    // ============================================================
    openPopup({ authUrl, reference, onSuccess, onClose }) {
        if (!authUrl) {
            CampusLink.toast('Payment initialization failed. Please try again.', 'error');
            return;
        }

        // Open authorization_url in a popup window
        // Paystack handles channels and all config from server-side initialization
        const width = 600;
        const height = 700;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;

        const popup = window.open(
            authUrl,
            'Paystack',
            `width=${width},height=${height},left=${left},top=${top},scrollbars=yes`
        );

        if (!popup) {
            CampusLink.toast('Popup blocked. Please enable popups for this site.', 'error');
            return;
        }

        // Check if popup is closed
        const pollClose = setInterval(() => {
            if (popup.closed) {
                clearInterval(pollClose);
                onClose();
                CampusLink.toast('Payment window closed. Your payment was not completed.', 'warning');
            }
        }, 500);
    },

    // ============================================================
    // HANDLE PAYMENT SUCCESS
    // ============================================================
    async handleSuccess(response) {
        const { reference } = response;

        // Show verifying state
        const payBtn = document.querySelector('.paystack-pay-btn');
        if (payBtn) {
            payBtn.textContent = '🔄 Verifying payment...';
            payBtn.disabled    = true;
        }

        const loadingOverlay = this.showLoadingOverlay('Verifying payment, please wait...');

        try {
            // SERVER-SIDE verification — never trust client-side
            const verifyUrl = `/vendor/payment/verify?reference=${encodeURIComponent(reference)}`;
            window.location.href = verifyUrl;

        } catch (err) {
            loadingOverlay?.remove();
            CampusLink.toast(
                'Payment received but verification failed. Please contact support with reference: ' + reference,
                'error',
                8000
            );

            if (payBtn) {
                payBtn.textContent = 'Contact Support';
                payBtn.disabled    = false;
            }
        }
    },

    // ============================================================
    // HANDLE POPUP CLOSE (user closed without paying)
    // ============================================================
    handleClose() {
        const payBtn = document.querySelector('.paystack-pay-btn');
        if (payBtn) {
            payBtn.disabled = false;
        }
    },

    // ============================================================
    // LOADING OVERLAY
    // ============================================================
    showLoadingOverlay(message = 'Processing...') {
        const existing = document.querySelector('.payment-loading-overlay');
        if (existing) existing.remove();

        const overlay = document.createElement('div');
        overlay.className = 'payment-loading-overlay';
        overlay.style.cssText = `
            position: fixed;
            inset: 0;
            background: rgba(255,255,255,0.92);
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            backdrop-filter: blur(4px);
        `;

        overlay.innerHTML = `
            <div class="spinner" style="width:48px;height:48px;border-width:4px;margin:0;"></div>
            <p style="font-size:1rem;font-weight:600;color:var(--text-primary);">${CampusLink.escapeHtml(message)}</p>
            <p style="font-size:0.8rem;color:var(--text-muted);">Please do not close this page.</p>
        `;

        document.body.appendChild(overlay);
        return overlay;
    },
};


// ============================================================
// PAYMENT SUCCESS PAGE CONFETTI
// ============================================================
function initPaymentSuccessPage() {
    const successPage = document.querySelector('.payment-success-page');
    if (!successPage) return;

    // Simple confetti burst
    const colors = ['#0b3d91', '#1ea952', '#f59e0b', '#ffffff'];
    const canvas  = document.createElement('canvas');
    canvas.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:1000;';
    document.body.appendChild(canvas);

    const ctx    = canvas.getContext('2d');
    canvas.width  = window.innerWidth;
    canvas.height = window.innerHeight;

    const particles = Array.from({ length: 100 }, () => ({
        x:     Math.random() * canvas.width,
        y:     Math.random() * canvas.height - canvas.height,
        r:     Math.random() * 8 + 4,
        d:     Math.random() * 100,
        color: colors[Math.floor(Math.random() * colors.length)],
        tilt:  Math.floor(Math.random() * 10) - 10,
        tiltAngle:          0,
        tiltAngleIncrement: Math.random() * 0.07 + 0.05,
    }));

    let angle    = 0;
    let frameId  = null;
    let elapsed  = 0;

    function drawConfetti() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        angle += 0.01;
        elapsed++;

        particles.forEach(p => {
            ctx.beginPath();
            ctx.lineWidth   = p.r / 2;
            ctx.strokeStyle = p.color;
            ctx.moveTo(p.x + p.tilt + p.r / 4, p.y);
            ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r / 4);
            ctx.stroke();

            p.tiltAngle += p.tiltAngleIncrement;
            p.y         += (Math.cos(angle + p.d) + 2 + p.r / 2) * 0.8;
            p.tilt       = Math.sin(p.tiltAngle - elapsed / 3) * 15;
        });

        if (elapsed < 250) {
            frameId = requestAnimationFrame(drawConfetti);
        } else {
            canvas.remove();
        }
    }

    drawConfetti();

    window.addEventListener('resize', () => {
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
    });
}


// ============================================================
// DOM READY
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    PaystackHandler.init();
    initPaymentSuccessPage();
});

// Expose globally
window.PaystackHandler = PaystackHandler;