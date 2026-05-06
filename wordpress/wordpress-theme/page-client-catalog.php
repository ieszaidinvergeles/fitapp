<?php
/**
 * Template Name: Client Catalog
 */

require_login();

wp_app_page_start('Gym Catalog');
?>

<div class="mb-12">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <p class="text-primary-container font-black uppercase tracking-[0.3em] text-[10px] mb-2">Shop & Merchandise</p>
            <h2 class="font-headline text-5xl md:text-7xl font-black uppercase tracking-tighter leading-none italic text-white">
                Official <span class="text-primary-container">Gear</span>
            </h2>
        </div>
    </div>

    <!-- The Catalog Grid (via Plugin Shortcode) -->
    <div class="bg-surface-container-low rounded-[2.5rem] border border-outline-variant/10 p-2 md:p-6 shadow-2xl">
        <?php echo do_shortcode('[gym_catalog]'); ?>
    </div>
</div>

<?php
wp_app_page_end();
