<?php
/**
 * sidebar.php — Sidebar Template
 * Displays the sidebar widget area
 */
?>
<aside class="bg-surface-container rounded-xl p-6 border border-outline-variant/10">
    <h3 class="font-headline font-bold text-lg mb-4 uppercase tracking-wider">Volt Gym Sidebar</h3>
    <?php if (is_active_sidebar('sidebar-1')) : ?>
        <?php dynamic_sidebar('sidebar-1'); ?>
    <?php else : ?>
        <p class="text-on-surface-variant text-sm">Agrega widgets desde WordPress para completar esta barra lateral.</p>
    <?php endif; ?>
</aside>