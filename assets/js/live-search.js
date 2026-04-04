'use strict';
// ============================================================
// CAMPUSLINK — AJAX LIVE VENDOR SEARCH
// Debounced, cached, accessible
// ============================================================

(function () {
  const searchInput  = document.getElementById('heroSearchInput');
  const searchCat    = document.getElementById('heroSearchCat');
  const liveResults  = document.getElementById('liveResults');
  const lrInner      = document.getElementById('lrInner');
  if (!searchInput || !liveResults || !lrInner) return;

  let debounceTimer = null;
  let lastQuery     = '';
  const cache       = new Map();

  function debounce(fn, delay) {
    return (...args) => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => fn(...args), delay);
    };
  }

  async function search(query, cat) {
    if (query.length < 2) { hideSuggestions(); return; }
    if (query === lastQuery && !cat) return;
    lastQuery = query;

    const cacheKey = `${query}:${cat}`;
    if (cache.has(cacheKey)) {
      renderResults(cache.get(cacheKey), query);
      return;
    }

    // Show loading skeleton
    lrInner.innerHTML = `
      <div style="padding:16px 18px;display:flex;flex-direction:column;gap:10px">
        ${[1,2,3].map(() => `
          <div style="display:flex;align-items:center;gap:12px">
            <div class="skeleton" style="width:44px;height:44px;border-radius:10px;flex-shrink:0"></div>
            <div style="flex:1">
              <div class="skeleton" style="height:14px;width:60%;border-radius:4px;margin-bottom:6px"></div>
              <div class="skeleton" style="height:12px;width:40%;border-radius:4px"></div>
            </div>
          </div>
        `).join('')}
      </div>`;
    showSuggestions();

    try {
      const params = new URLSearchParams({ q: query, limit: '6' });
      if (cat) params.set('cat', cat);
      const res  = await fetch(`/api/live-search?${params}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await res.json();
      cache.set(cacheKey, data.results || []);
      renderResults(data.results || [], query);
    } catch {
      lrInner.innerHTML = `<div class="lr-empty"><i data-lucide="wifi-off" style="width:20px;height:20px;margin-bottom:8px;display:block;margin-left:auto;margin-right:auto"></i>Connection error. Please try again.</div>`;
      if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [lrInner] });
    }
  }

  function renderResults(results, query) {
    if (!results.length) {
      lrInner.innerHTML = `
        <div class="lr-empty">
          <i data-lucide="search-x" style="width:24px;height:24px;display:block;margin:0 auto 8px"></i>
          No vendors found for <strong>"${escapeHtml(query)}"</strong>.<br/>
          <a href="/browse?q=${encodeURIComponent(query)}" style="color:#86efac;font-weight:700;text-decoration:none;margin-top:6px;display:inline-block">Browse all vendors →</a>
        </div>`;
      if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [lrInner] });
      showSuggestions();
      return;
    }

    const html = results.map(v => `
      <a class="lr-item" href="${v.url}" onclick="hideSuggestions()">
        <div class="lr-thumb">
          ${v.logo
            ? `<img src="${v.logo}" alt="${v.name}" loading="lazy" />`
            : `<div style="width:100%;height:100%;background:linear-gradient(135deg,#0b3d91,#1e5bb8);display:flex;align-items:center;justify-content:center;font-family:'Sora',sans-serif;font-weight:800;color:white;font-size:1rem">${v.name.charAt(0)}</div>`
          }
        </div>
        <div class="lr-info">
          <strong>${highlightMatch(v.name, query)}</strong>
          <span>${v.category}${v.rating > 0 ? ` · ★${v.rating}` : ''}</span>
        </div>
        ${v.is_featured ? `<div class="lr-badge"><span style="background:rgba(245,158,11,0.15);color:#f59e0b;font-size:0.65rem;font-weight:800;padding:2px 8px;border-radius:99px;font-family:'Sora',sans-serif">Featured</span></div>` : ''}
      </a>
    `).join('') + `
      <a href="/browse?q=${encodeURIComponent(query)}" style="display:flex;align-items:center;justify-content:center;gap:8px;padding:14px;font-family:'Sora',sans-serif;font-size:0.8rem;font-weight:700;color:rgba(255,255,255,0.5);text-decoration:none;border-top:1px solid rgba(255,255,255,0.05);transition:color 0.2s" onmouseover="this.style.color='#86efac'" onmouseout="this.style.color='rgba(255,255,255,0.5)'">
        <i data-lucide="arrow-right" style="width:14px;height:14px"></i>
        See all results for "${escapeHtml(query)}"
      </a>`;
    lrInner.innerHTML = html;
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [lrInner] });
    showSuggestions();
  }

  function highlightMatch(text, query) {
    const rx = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return escapeHtml(text).replace(rx, '<mark style="background:rgba(30,169,82,0.25);color:#86efac;border-radius:2px;padding:0 2px">$1</mark>');
  }

  function escapeHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function showSuggestions() { liveResults.style.display = 'block'; }
  function hideSuggestions() { liveResults.style.display = 'none'; }

  window.hideSuggestions = hideSuggestions;

  const debouncedSearch = debounce((q, c) => search(q, c), 280);

  searchInput.addEventListener('input', () => {
    const q = searchInput.value.trim();
    const c = searchCat?.value || '';
    if (q.length < 2) { hideSuggestions(); return; }
    debouncedSearch(q, c);
  });

  searchCat?.addEventListener('change', () => {
    const q = searchInput.value.trim();
    if (q.length >= 2) debouncedSearch(q, searchCat.value);
  });

  searchInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      hideSuggestions();
      window.doSearch?.();
    }
    if (e.key === 'Escape') hideSuggestions();
  });

  document.addEventListener('click', (e) => {
    if (!liveResults.contains(e.target) && e.target !== searchInput) {
      hideSuggestions();
    }
  });
})();