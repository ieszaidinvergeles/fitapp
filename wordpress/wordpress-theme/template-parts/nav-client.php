<?php
/**
 * template-parts/nav-client.php — Bottom Navigation (Client Role)
 *
 * WordPress-style template part for the client-facing bottom navigation bar.
 * Included via get_template_part('template-parts/nav', 'client') in client pages.
 *
 * SRP: Solely responsible for rendering client navigation links.
 * OCP: New nav items can be added without modifying existing entries.
 *
 * @param string $active  The current page identifier for active state highlighting.
 */

$active = $active ?? '';
?>
<nav class="md:hidden fixed bottom-0 left-0 w-full bg-[#0d0f08]/90 backdrop-blur-xl flex justify-around items-center px-4 py-3 z-50 rounded-t-3xl shadow-[0_-10px_30px_rgba(215,255,0,0.05)]">
    <?php
    /**
     * Nav items definition.
     * Each entry: [href, icon, label, key]
     */
    $items = [
        ['?pagename=client-dashboard',  'grid_view',           'Home',       'dashboard'],
        ['?pagename=client-bookings',   'confirmation_number', 'Booking',    'classes'],
        ['?pagename=client-routines',   'event_note',          'Routines',   'routines'],
        ['?pagename=client-diet-plans', 'restaurant',          'Diets',      'diet-plans'],
        ['?pagename=client-settings',   'settings',            'Settings',   'settings'],
    ];
    foreach ($items as [$href, $icon, $label, $key]):
        $isActive = ($active === $key);
        $cls = $isActive
            ? 'flex flex-col items-center justify-center text-[#d4fb00] shadow-[0_0_15px_rgba(215,255,0,0.3)] bg-[#1e2117] rounded-xl px-4 py-1 active:scale-90 transition-transform'
            : 'flex flex-col items-center justify-center text-zinc-600 hover:text-[#d4fb00] transition-all active:scale-90 transition-transform';
    ?>
    <a class="<?= $cls ?>" href="<?= $href ?>">
        <span class="material-symbols-outlined"><?= $icon ?></span>
        <span class="font-['Manrope'] text-[10px] font-bold uppercase tracking-widest mt-1"><?= $label ?></span>
    </a>
    <?php endforeach; ?>
</nav>
