/* ============================================================
   CampusLink - Browse & Vendor Profile JavaScript
   Search, filter, save, review submission, photo gallery
   ============================================================ */

'use strict';

// ============================================================
// BROWSE SEARCH — debounced live search
// ============================================================
function initBrowseSearch() {
    const searchInput  = document.querySelector('.browse-search-input');
    const searchForm   = document.querySelector('.browse-search-form');
    const categoryEl   = document.querySelector('.browse-search-select');

    if (!searchInput || !searchForm) return;

    // Submit on Enter
    searchInput.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchForm.submit();
        }
    });

    // Auto-submit when category changes
    if (categoryEl) {
        categoryEl.addEventListener('change', () => searchForm.submit());
    }

    // Highlight search term in results
    const params  = new URLSearchParams(window.location.search);
    const query   = params.get('q');
    if (!query) return;

    const resultCards = document.querySelectorAll('.browse-card-name, .browse-card-desc');
    resultCards.forEach(el => {
        el.innerHTML = el.innerHTML.replace(
            new RegExp(`(${escapeRegex(query)})`, 'gi'),
            '<mark style="background:rgba(245,158,11,0.25);border-radius:2px;padding:0 2px;">$1</mark>'
        );
    });
}

function escapeRegex(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}


// ============================================================
// FILTER SIDEBAR — mobile toggle
// ============================================================
function initFilterSidebar() {
    const filterBtn  = document.querySelector('.mobile-filter-btn');
    const sidebar    = document.querySelector('.filter-sidebar');

    if (!filterBtn || !sidebar) return;

    filterBtn.addEventListener('click', () => {
        sidebar.classList.toggle('open');

        const isOpen = sidebar.classList.contains('open');
        filterBtn.setAttribute('aria-expanded', String(isOpen));
        document.body.style.overflow = isOpen ? 'hidden' : '';
    });

    // Close when clicking outside
    document.addEventListener('click', e => {
        if (
            sidebar.classList.contains('open') &&
            !sidebar.contains(e.target) &&
            !filterBtn.contains(e.target)
        ) {
            sidebar.classList.remove('open');
            document.body.style.overflow = '';
        }
    });

    // Update filter badge count
    const params     = new URLSearchParams(window.location.search);
    const filterCount = ['q', 'category', 'sort']
        .filter(key => params.get(key) && params.get(key) !== 'plan_priority')
        .length;

    const badge = filterBtn.querySelector('.mobile-filter-badge');
    if (badge && filterCount > 0) {
        badge.textContent = filterCount;
        badge.style.display = 'flex';
    }
}


// ============================================================
// SORT PILLS — update URL
// ============================================================
function initSortPills() {
    document.querySelectorAll('.sort-pill[data-sort]').forEach(pill => {
        pill.addEventListener('click', e => {
            e.preventDefault();

            const sort   = pill.dataset.sort;
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sort);
            params.delete('page'); // Reset to page 1

            window.location.search = params.toString();
        });
    });
}


// ============================================================
// VENDOR PROFILE — PHOTO GALLERY / LIGHTBOX
// ============================================================
function initPhotoGallery() {
    const photos    = document.querySelectorAll('.service-photo-thumb');
    const lightbox  = document.querySelector('.photo-lightbox');
    const lightImg  = lightbox?.querySelector('.lightbox-img');
    const closeBtn  = lightbox?.querySelector('.lightbox-close');
    const prevBtn   = lightbox?.querySelector('.lightbox-prev');
    const nextBtn   = lightbox?.querySelector('.lightbox-next');
    const counter   = lightbox?.querySelector('.lightbox-counter');

    if (!photos.length || !lightbox) return;

    const srcs   = [...photos].map(img => img.src);
    let current  = 0;

    function openLightbox(index) {
        current = index;
        lightImg.src = srcs[index];
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
        updateCounter();
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }

    function showNext() {
        current = (current + 1) % srcs.length;
        lightImg.src = srcs[current];
        updateCounter();
    }

    function showPrev() {
        current = (current - 1 + srcs.length) % srcs.length;
        lightImg.src = srcs[current];
        updateCounter();
    }

    function updateCounter() {
        if (counter) counter.textContent = `${current + 1} / ${srcs.length}`;
    }

    photos.forEach((img, index) => {
        img.addEventListener('click', () => openLightbox(index));
    });

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (nextBtn)  nextBtn.addEventListener('click', showNext);
    if (prevBtn)  prevBtn.addEventListener('click', showPrev);

    // Click outside to close
    lightbox.addEventListener('click', e => {
        if (e.target === lightbox) closeLightbox();
    });

    // Keyboard navigation
    document.addEventListener('keydown', e => {
        if (!lightbox.classList.contains('active')) return;
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft')  showPrev();
        if (e.key === 'Escape')     closeLightbox();
    });

    // Touch swipe
    let touchStartX = 0;
    lightbox.addEventListener('touchstart', e => {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });

    lightbox.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            diff > 0 ? showNext() : showPrev();
        }
    }, { passive: true });
}


// ============================================================
// REVIEW SUBMISSION
// ============================================================
function initReviewForm() {
    const form = document.querySelector('.review-submit-form form');
    if (!form) return;

    // Star rating display
    const starInputs = form.querySelectorAll('.review-stars-input input[type="radio"]');
    const ratingText = form.querySelector('.selected-rating-label');

    const ratingLabels = {
        1: '⭐ Poor',
        2: '⭐⭐ Fair',
        3: '⭐⭐⭐ Good',
        4: '⭐⭐⭐⭐ Very Good',
        5: '⭐⭐⭐⭐⭐ Excellent',
    };

    starInputs.forEach(input => {
        input.addEventListener('change', () => {
            if (ratingText) {
                ratingText.textContent = ratingLabels[input.value] || '';
            }
        });
    });

    // Char counter
    const textarea = form.querySelector('textarea[name="review"]');
    const counter  = form.querySelector('.review-char-counter');
    const maxChars = parseInt(textarea?.dataset.maxChars) || 500;

    if (textarea && counter) {
        textarea.addEventListener('input', () => {
            const len = textarea.value.length;
            counter.textContent = `${len} / ${maxChars}`;
            counter.className   = 'review-char-counter';
            if (len > maxChars - 50) counter.classList.add('warning');
            if (len > maxChars - 20) counter.classList.add('danger');
        });
    }

    // AJAX submit
    form.addEventListener('submit', async e => {
        e.preventDefault();

        const rating   = form.querySelector('input[name="rating"]:checked')?.value;
        const review   = textarea?.value.trim();
        const vendorId = form.querySelector('input[name="vendor_id"]')?.value;
        const submitBtn = form.querySelector('[type="submit"]');

        if (!rating) {
            CampusLink.toast('Please select a star rating.', 'error');
            return;
        }

        if (!review || review.length < 10) {
            CampusLink.toast('Review must be at least 10 characters.', 'error');
            return;
        }

        if (review.length > maxChars) {
            CampusLink.toast(`Review must not exceed ${maxChars} characters.`, 'error');
            return;
        }

        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;

        try {
            const data = await CampusLink.ajax('/reviews/submit', 'POST', {
                csrf_token: CampusLink.getCsrf(),
                vendor_id:  vendorId,
                rating:     parseInt(rating),
                review,
            });

            if (data.success) {
                CampusLink.toast(data.message, 'success');

                // Replace form with success message
                const formWrapper = form.closest('.review-submit-form');
                if (formWrapper) {
                    formWrapper.innerHTML = `
                        <div class="alert alert-success" style="margin:0;">
                            <span class="alert-icon">✅</span>
                            ${data.message}
                        </div>
                    `;
                }
            } else {
                CampusLink.toast(data.message || 'Could not submit review.', 'error');
                submitBtn.classList.remove('btn-loading');
                submitBtn.disabled = false;
            }
        } catch {
            CampusLink.toast('Network error. Please try again.', 'error');
            submitBtn.classList.remove('btn-loading');
            submitBtn.disabled = false;
        }
    });
}


// ============================================================
// COMPLAINT MODAL
// ============================================================
function initComplaintModal() {
    const openBtns  = document.querySelectorAll('[data-open-modal="complaint"]');
    const modal     = document.querySelector('#complaintModal');
    const closeBtn  = modal?.querySelector('.modal-close');
    const overlay   = modal;

    if (!modal) return;

    const open = () => {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    };

    openBtns.forEach(btn => btn.addEventListener('click', open));
    if (closeBtn) closeBtn.addEventListener('click', close);

    overlay.addEventListener('click', e => {
        if (e.target === overlay) close();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.classList.contains('active')) close();
    });

    // Complaint form submission
    const form = modal.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', async e => {
        e.preventDefault();

        const formData   = new FormData(form);
        const submitBtn  = form.querySelector('[type="submit"]');
        submitBtn.classList.add('btn-loading');
        submitBtn.disabled = true;

        try {
            const response = await fetch('/complaints/submit', {
                method:  'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body:    formData,
            });

            const data = await response.json();

            if (data.success) {
                close();
                CampusLink.toast('Complaint submitted. Ticket: ' + (data.ticket_id || ''), 'success', 6000);
                form.reset();
            } else {
                CampusLink.toast(data.message || 'Submission failed.', 'error');
            }
        } catch {
            CampusLink.toast('Network error. Please try again.', 'error');
        } finally {
            submitBtn.classList.remove('btn-loading');
            submitBtn.disabled = false;
        }
    });
}


// ============================================================
// VENDOR PROFILE — SHARE BUTTON
// ============================================================
function initShareButton() {
    const shareBtn = document.querySelector('.share-vendor-btn');
    if (!shareBtn) return;

    shareBtn.addEventListener('click', async () => {
        const url   = window.location.href;
        const title = shareBtn.dataset.title || document.title;

        if (navigator.share) {
            try {
                await navigator.share({ title, url });
            } catch {
                // User cancelled
            }
        } else {
            CampusLink.copyToClipboard(url);
        }
    });
}


// ============================================================
// INFINITE SCROLL (optional, for browse page)
// ============================================================
function initInfiniteScroll() {
    const sentinel = document.querySelector('.browse-infinite-sentinel');
    const grid     = document.querySelector('.browse-vendors-grid');

    if (!sentinel || !grid) return;

    let loading   = false;
    let nextPage  = parseInt(sentinel.dataset.nextPage);
    const maxPage = parseInt(sentinel.dataset.maxPage);

    if (nextPage > maxPage) return;

    const observer = new IntersectionObserver(async entries => {
        if (!entries[0].isIntersecting || loading) return;
        if (nextPage > maxPage) {
            observer.disconnect();
            sentinel.remove();
            return;
        }

        loading = true;
        sentinel.innerHTML = '<div class="spinner spinner-sm" style="margin:1rem auto;"></div>';

        const params = new URLSearchParams(window.location.search);
        params.set('page', nextPage);

        try {
            const response = await fetch('/browse?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const html = await response.text();

            const parser  = new DOMParser();
            const doc     = parser.parseFromString(html, 'text/html');
            const cards   = doc.querySelectorAll('.browse-vendor-card');

            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                grid.appendChild(card);
                requestAnimationFrame(() => {
                    card.style.transition = 'all 0.4s ease';
                    card.style.opacity    = '1';
                    card.style.transform  = 'translateY(0)';
                });
            });

            nextPage++;
            sentinel.innerHTML = '';

        } catch {
            sentinel.innerHTML = '<p style="text-align:center;color:var(--text-muted);">Could not load more. <a href="?page=' + nextPage + '">Refresh</a></p>';
        } finally {
            loading = false;
        }
    }, { rootMargin: '400px' });

    observer.observe(sentinel);
}


// ============================================================
// CATEGORY FILTER SIDEBAR
// ============================================================
function initCategoryFilter() {
    const params     = new URLSearchParams(window.location.search);
    const activeSlug = params.get('category') || '';

    document.querySelectorAll('.filter-category-btn').forEach(btn => {
        const slug = btn.dataset.slug;

        // Mark active
        if (slug === activeSlug || (!slug && !activeSlug)) {
            btn.classList.add('active');
        }

        btn.addEventListener('click', () => {
            const newParams = new URLSearchParams(window.location.search);

            if (slug) {
                newParams.set('category', slug);
            } else {
                newParams.delete('category');
            }

            newParams.delete('page');
            window.location.search = newParams.toString();
        });
    });
}


// ============================================================
// DOM READY
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    initBrowseSearch();
    initFilterSidebar();
    initSortPills();
    initPhotoGallery();
    initReviewForm();
    initComplaintModal();
    initShareButton();
    initInfiniteScroll();
    initCategoryFilter();
});