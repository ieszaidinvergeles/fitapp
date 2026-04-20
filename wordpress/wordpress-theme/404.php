<?php
/**
 * 404.php — 404 Error Template
 * Displays when a page is not found
 */
get_header();
?>
<main class="min-h-screen flex items-center justify-center px-6">
    <div class="text-center">
        <div class="text-[200px] font-headline font-black text-primary-container leading-none mb-8">404</div>
        <h1 class="font-headline font-extrabold text-5xl md:text-7xl tracking-tighter mb-4 italic">
            Page Not Found
        </h1>
        <p class="text-on-surface-variant text-xl mb-8 max-w-md mx-auto">
            The page you're looking for doesn't exist or has been moved.
        </p>
        <a href="<?php echo home_url(); ?>" class="bg-gradient-to-r from-primary to-primary-container text-on-primary font-black px-10 py-4 rounded-full inline-flex items-center gap-3 hover:scale-105 transition-transform shadow-[0_0_20px_rgba(215,255,0,0.2)]">
            Go Home
            <span class="material-symbols-outlined">home</span>
        </a>
    </div>
</main>
<?php get_footer(); ?>