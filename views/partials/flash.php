<?php
/**
 * Flash Message Renderer
 * Reads all flash messages from session and displays them.
 */
defined('CAMPUSLINK') or die();

$flashes = Session::getAllFlash();

if (empty($flashes)) return;

if (!function_exists('lucide_icon')) {
function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;flex-shrink:0;'.$extra_style.'">'.$path.'</svg>';
}
}


// Lucide SVG paths per alert type
$icons = [
    // âœ… success â†’ CheckCircle
    'success' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
    // âŒ error â†’ XCircle
    'error'   => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
    // âš ï¸ warning â†’ AlertTriangle
    'warning' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    // â„¹ï¸ info â†’ Info
    'info'    => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
];

// Fallback icon path (Info) for unknown types
$fallback = $icons['info'];

foreach ($flashes as $type => $messages):
    if (!is_array($messages)) $messages = [$messages];
    foreach ($messages as $message):
        if (empty($message)) continue;
        $iconPath = $icons[$type] ?? $fallback;
?>
<div class="alert alert-<?= e($type) ?>" data-auto-dismiss="6000" role="alert">
    <span class="alert-icon">
        <?= lucide_icon($iconPath, 18, 'currentColor') ?>
    </span>
    <span><?= e($message) ?></span>
    <!-- Ã— close â†’ X -->
    <button class="alert-close" aria-label="Close"
            style="margin-left:auto;background:none;border:none;cursor:pointer;
                   color:inherit;opacity:0.6;padding:0 0 0 0.5rem;
                   display:flex;align-items:center;">
        <?= lucide_icon('<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>', 16, 'currentColor') ?>
    </button>
</div>
<?php
    endforeach;
endforeach;
?>
