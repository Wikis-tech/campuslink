<?php
/**
 * Pagination Component
 * $pagination  array  — total, current_page, total_pages, has_prev, has_next
 * $baseUrl     string — URL without page param
 */
defined('CAMPUSLINK') or die();

if (!isset($pagination) || ($pagination['total_pages'] ?? 1) <= 1) return;

$current    = (int)$pagination['current_page'];
$totalPages = (int)$pagination['total_pages'];

// Build URL with page param
function buildPageUrl(string $baseUrl, int $page): string {
    $parts = parse_url($baseUrl);
    parse_str($parts['query'] ?? '', $params);
    $params['page'] = $page;
    $query = http_build_query($params);
    return ($parts['path'] ?? '') . ($query ? '?' . $query : '');
}

$baseUrl = $baseUrl ?? strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query(
    array_filter($_GET, fn($k) => $k !== 'page', ARRAY_FILTER_USE_KEY)
);
?>
<nav class="pagination" aria-label="Page navigation" role="navigation">

    <!-- Previous -->
    <?php if ($pagination['has_prev']): ?>
    <a href="<?= e(buildPageUrl($baseUrl, $current - 1)) ?>"
       class="page-btn" aria-label="Previous page">‹</a>
    <?php else: ?>
    <span class="page-btn disabled" aria-disabled="true">‹</span>
    <?php endif; ?>

    <!-- Page Numbers -->
    <?php
    $range  = 2;
    $start  = max(1, $current - $range);
    $end    = min($totalPages, $current + $range);

    if ($start > 1):
    ?>
        <a href="<?= e(buildPageUrl($baseUrl, 1)) ?>" class="page-btn">1</a>
        <?php if ($start > 2): ?>
        <span class="page-btn disabled" aria-hidden="true">…</span>
        <?php endif;
    endif;

    for ($i = $start; $i <= $end; $i++):
    ?>
    <?php if ($i === $current): ?>
        <span class="page-btn active" aria-current="page"><?= $i ?></span>
    <?php else: ?>
        <a href="<?= e(buildPageUrl($baseUrl, $i)) ?>" class="page-btn"><?= $i ?></a>
    <?php endif;
    endfor;

    if ($end < $totalPages):
        if ($end < $totalPages - 1): ?>
        <span class="page-btn disabled" aria-hidden="true">…</span>
        <?php endif; ?>
        <a href="<?= e(buildPageUrl($baseUrl, $totalPages)) ?>" class="page-btn"><?= $totalPages ?></a>
    <?php endif; ?>

    <!-- Next -->
    <?php if ($pagination['has_next']): ?>
    <a href="<?= e(buildPageUrl($baseUrl, $current + 1)) ?>"
       class="page-btn" aria-label="Next page">›</a>
    <?php else: ?>
    <span class="page-btn disabled" aria-disabled="true">›</span>
    <?php endif; ?>

</nav>
<p class="text-center text-sm" style="margin-top:0.75rem;color:var(--text-muted);">
    Page <?= $current ?> of <?= $totalPages ?>
    (<?= number_format($pagination['total']) ?> total)
</p>