$viewFiles = Get-ChildItem -Recurse -Include *.php -Path views,admin,index.php | Select-Object -ExpandProperty FullName | Sort-Object -Unique
$allFiles = Get-ChildItem -Recurse -Include *.php | Where-Object { $_.FullName -notmatch 'vendor-composer' } | Select-Object -ExpandProperty FullName | Sort-Object -Unique
$codeFiles = $allFiles | Where-Object { $viewFiles -notcontains $_ }
$pairs = @(
    @{k='👤'; v='<i data-lucide="user"></i>'}
    @{k='🚪'; v='<i data-lucide="log-out"></i>'}
    @{k='🏠'; v='<i data-lucide="home"></i>'}
    @{k='🔍'; v='<i data-lucide="search"></i>'}
    @{k='📞'; v='<i data-lucide="phone"></i>'}
    @{k='⚠️'; v='<i data-lucide="alert-triangle"></i>'}
    @{k='✅'; v='<i data-lucide="check-circle"></i>'}
    @{k='❌'; v='<i data-lucide="x-circle"></i>'}
    @{k='🔒'; v='<i data-lucide="lock"></i>'}
    @{k='📋'; v='<i data-lucide="clipboard"></i>'}
    @{k='💳'; v='<i data-lucide="credit-card"></i>'}
    @{k='🧾'; v='<i data-lucide="file-text"></i>'}
    @{k='⭐'; v='<i data-lucide="star"></i>'}
    @{k='✨'; v='<i data-lucide="sparkles"></i>'}
    @{k='📍'; v='<i data-lucide="map-pin"></i>'}
    @{k='💡'; v='<i data-lucide="lightbulb"></i>'}
    @{k='📱'; v='<i data-lucide="smartphone"></i>'}
    @{k='🏪'; v='<i data-lucide="store"></i>'}
    @{k='🔔'; v='<i data-lucide="bell"></i>'}
    @{k='♥'; v='<i data-lucide="heart"></i>'}
    @{k='❤️'; v='<i data-lucide="heart"></i>'}
    @{k='🚨'; v='<i data-lucide="alert-circle"></i>'}
    @{k='⏰'; v='<i data-lucide="clock"></i>'}
    @{k='←'; v='<i data-lucide="arrow-left"></i>'}
    @{k='☰'; v='<i data-lucide="menu"></i>'}
    @{k='ℹ️'; v='<i data-lucide="info"></i>'}
    @{k='📌'; v='<i data-lucide="map-pin"></i>'}
    @{k='🌐'; v='<i data-lucide="globe"></i>'}
    @{k='⏳'; v='<i data-lucide="clock"></i>'}
    @{k='🎓'; v='<i data-lucide="graduation-cap"></i>'}
    @{k='✉️'; v='<i data-lucide="mail"></i>'}
    @{k='📧'; v='<i data-lucide="mail"></i>'}
)
$count = 0
foreach ($file in $viewFiles) {
    $content = Get-Content -Raw -LiteralPath $file
    $new = $content
    foreach ($pair in $pairs) {
        $new = $new -replace [regex]::Escape($pair.k), $pair.v
    }
    $new = $new -replace 'data-feather=', 'data-lucide='
    if ($new -ne $content) {
        Set-Content -LiteralPath $file -Value $new -Encoding UTF8
        $count++
    }
}
foreach ($file in $codeFiles) {
    $content = Get-Content -Raw -LiteralPath $file
    $new = $content
    foreach ($pair in $pairs) {
        $new = $new -replace [regex]::Escape($pair.k), ''
    }
    if ($new -ne $content) {
        Set-Content -LiteralPath $file -Value $new -Encoding UTF8
        $count++
    }
}
Write-Output "Replaced emojis in $count files."

