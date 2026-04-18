<?php defined('CAMPUSLINK') or die(); ?>

<?php
function lucide_icon(string $path, int $size = 20, string $color = 'currentColor', string $extra_style = ''): string {
    return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'"
                 viewBox="0 0 24 24" fill="none" stroke="'.$color.'"
                 stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                 style="display:inline-block;vertical-align:middle;'.$extra_style.'">'.$path.'</svg>';
}

// Map your category names/slugs to Lucide SVG paths
// Extend this array to match all your actual category names
$categoryIcons = [
    'food'          => '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/>',
    'fashion'       => '<path d="M20.38 3.46L16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z"/>',
    'tech'          => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
    'beauty'        => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
    'laundry'       => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
    'tutoring'      => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
    'printing'      => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
    'transport'     => '<rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
    'photography'   => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
    'default'       => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
];

function get_category_icon(array $cat, array $iconMap): string {
    $slug = strtolower(trim($cat['slug'] ?? $cat['name'] ?? ''));
    foreach ($iconMap as $key => $path) {
        if (str_contains($slug, $key)) return $path;
    }
    return $iconMap['default'];
}
?>

<style>
.cats-hero {
    background: linear-gradient(135deg,#0b3d91 0%,#1a56db 50%,#0e9f6e 100%);
    padding: 2.5rem 1rem 2rem;
    color: #fff;
    text-align: center;
}
.cats-hero h1 { font-size:clamp(1.4rem,4vw,2rem);font-weight:900;margin:0 0 0.4rem; }
.cats-hero p  { color:rgba(255,255,255,0.8);font-size:0.9rem;margin:0; }

.cats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px,1fr));
    gap: 1rem;
    max-width: 1100px;
    margin: 2rem auto;
    padding: 0 1rem;
}
@media(max-width:480px){
    .cats-grid { grid-template-columns: repeat(2,1fr); }
}

.cat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.5rem 1rem;
    text-align: center;
    text-decoration: none;
    color: #1e293b;
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}
.cat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(26,86,219,0.12);
    border-color: #1a56db;
}
.cat-icon  {
    width: 52px; height: 52px;
    border-radius: 12px;
    background: #eff6ff;
    display: flex; align-items: center; justify-content: center;
}
.cat-name  { font-weight: 800; font-size: 0.9rem; }
.cat-count {
    font-size: 0.75rem;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 600;
}
.cats-empty {
    text-align:center;padding:3rem 1rem;color:#94a3b8;
}
</style>

<div class="cats-hero">
    <h1 style="display:flex;align-items:center;justify-content:center;gap:0.6rem;">
        <!-- FolderOpen icon replacing 📂 -->
        <?= lucide_icon('<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>', 32, '#fff') ?>
        All Categories
    </h1>
    <p>Browse vendors by service category</p>
</div>

<div class="cats-grid">
    <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
        <a href="<?= SITE_URL ?>/browse?category=<?= (int)$cat['id'] ?>"
           class="cat-card">
            <div class="cat-icon">
                <?= lucide_icon(get_category_icon($cat, $categoryIcons), 26, '#1a56db') ?>
            </div>
            <div class="cat-name"><?= e($cat['name']) ?></div>
            <div class="cat-count">
                <?= (int)$cat['vendor_count'] ?>
                vendor<?= $cat['vendor_count'] != 1 ? 's' : '' ?>
            </div>
        </a>
        <?php endforeach; ?>
    <?php else: ?>
    <div class="cats-empty" style="grid-column:1/-1;">
        <!-- FolderOpen icon replacing 📂 -->
        <div style="display:flex;justify-content:center;margin-bottom:1rem;">
            <?= lucide_icon('<path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>', 48, '#94a3b8') ?>
        </div>
        <h3>No categories yet</h3>
        <p>Categories will appear here once vendors are registered.</p>
    </div>
    <?php endif; ?>
</div>