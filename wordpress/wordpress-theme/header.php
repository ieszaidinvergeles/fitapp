<!DOCTYPE html>
<html class="dark" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php bloginfo('name'); ?><?php if (!empty($page_title)): ?> | <?= h($page_title) ?><?php endif; ?></title>
<?php $themeBase = get_stylesheet_directory_uri(); ?>
<link rel="stylesheet" href="<?= esc_url($themeBase . '/style.css') ?>"/>
<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
if (typeof window.tailwind === 'undefined') {
  document.write('<script src="https://unpkg.com/@tailwindcss/browser@4"><\/script>');
}
</script>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700;800;900&family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script id="tailwind-config">
tailwind.config = {
  darkMode: "class",
  theme: {
    extend: {
      colors: {
        "primary-dim": "#c9ef00", "surface-bright": "#2a2d22", "on-secondary": "#585400",
        "on-primary-fixed-variant": "#566800", "on-secondary-fixed-variant": "#625e00",
        "error-container": "#b92902", "surface-container-low": "#12140c",
        "primary-container": "#d4fb00", "primary": "#f5ffc5", "on-primary-fixed": "#3d4a00",
        "tertiary-dim": "#eecd34", "primary-fixed-dim": "#c7ec00",
        "surface-container-lowest": "#000000", "secondary": "#f0e753",
        "error-dim": "#d53d18", "secondary-fixed-dim": "#e2d946",
        "tertiary-fixed-dim": "#eecd34", "on-tertiary": "#665600",
        "surface-variant": "#24271d", "on-secondary-fixed": "#444100",
        "on-surface-variant": "#abaca1", "surface-container-highest": "#24271d",
        "on-surface": "#f4f4e8", "secondary-fixed": "#f0e753",
        "on-background": "#f4f4e8", "surface-dim": "#0d0f08",
        "tertiary-fixed": "#fddb42", "on-primary-container": "#4d5d00",
        "tertiary-container": "#fddb42", "surface-tint": "#f5ffc5",
        "on-error": "#450900", "on-secondary-container": "#fff9ca",
        "outline": "#75766c", "secondary-dim": "#e2d946",
        "on-tertiary-fixed-variant": "#685700", "surface-container": "#181b12",
        "surface": "#0d0f08", "inverse-primary": "#556600",
        "inverse-on-surface": "#54564c", "primary-fixed": "#d4fb00",
        "background": "#0d0f08", "surface-container-high": "#1e2117",
        "tertiary": "#ffeba0", "on-primary": "#556600",
        "on-tertiary-container": "#5c4d00", "on-error-container": "#ffd2c8",
        "outline-variant": "#474940", "on-tertiary-fixed": "#473b00",
        "secondary-container": "#666000", "inverse-surface": "#fafaed",
        "error": "#ff7351"
      },
      borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
      fontFamily: { "headline": ["Space Grotesk"], "body": ["Manrope"], "label": ["Manrope"] }
    }
  }
}
</script>
</head>
<body class="bg-surface-dim text-on-surface font-body selection:bg-primary-container selection:text-on-primary-container">
<?php if (empty($GLOBALS['hide_global_header'])): ?>
<header class="sticky top-0 z-50 border-b border-outline-variant/20 bg-surface/90 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <a href="index.php" class="font-headline font-black tracking-tight text-2xl uppercase text-primary-container">Volt Gym</a>
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-on-surface-variant">Despierta el trueno</p>
        </div>
        <nav class="flex items-center gap-5 text-xs font-bold uppercase tracking-wider text-on-surface-variant">
            <a href="front-page.php" class="hover:text-primary-container transition-colors">Inicio</a>
            <a href="page-register.php" class="hover:text-primary-container transition-colors">Registro</a>
            <a href="page-client-dashboard.php" class="hover:text-primary-container transition-colors">Panel</a>
        </nav>
        <form action="front-page.php" method="get" class="flex items-center gap-2">
            <input
                type="search"
                name="s"
                value="<?= h($_GET['s'] ?? '', '') ?>"
                placeholder="Buscar (proximamente)"
                class="bg-surface-container-highest border border-outline-variant/30 rounded-full px-4 py-2 text-xs focus:ring-2 focus:ring-primary-container"
            />
            <button type="submit" class="kinetic-gradient text-on-primary-container text-xs font-black uppercase tracking-widest rounded-full px-4 py-2">
                Ir
            </button>
        </form>
    </div>
</header>
<?php endif; ?>
