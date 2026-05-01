<?php
/**
 * template-parts/nav-staff.php — Bottom Navigation (Staff Role)
 *
 * WordPress-style template part for the staff-facing bottom navigation bar.
 * Included via get_template_part('template-parts/nav', 'staff') in staff pages.
 *
 * SRP: Solely responsible for rendering staff navigation links.
 * OCP: New nav items can be added without modifying existing entries.
 *
 * @param string $active  The current page identifier for active state highlighting.
 */

$active = $active ?? '';
?>
<nav class="fixed bottom-0 left-0 w-full flex justify-around items-center px-4 py-3 bg-[#0d0f08]/90 backdrop-blur-xl z-50 border-t-0 rounded-t-3xl shadow-[0_-10px_30px_rgba(215,255,0,0.05)]">
    <?php
    $items = [
        ['page-staff-dashboard.php', 'grid_view',          'Home',     'staff'],
        ['page-staff-manage-classes.php', 'event_note',    'Schedule', 'classes'],
        ['page-staff-attendance.php','how_to_reg',         'Check-in', 'attendance'],
    ];
    if (can_manage_members()) {
        $items[] = ['page-staff-admin-users.php', 'admin_panel_settings', 'Users', 'admin'];
    }
    foreach ($items as [$href, $icon, $label, $key]):
        $isActive = ($active === $key);
        $cls = $isActive
            ? 'flex flex-col items-center justify-center text-[#d4fb00] shadow-[0_0_15px_rgba(215,255,0,0.3)] bg-[#1e2117] rounded-xl px-4 py-1 active:scale-90 transition-transform'
            : 'flex flex-col items-center justify-center text-zinc-600 hover:text-[#d4fb00] transition-all active:scale-90 transition-transform';
    ?>
    <a class="<?= $cls ?>" href="<?= $href ?>">
        <span class="material-symbols-outlined text-2xl"><?= $icon ?></span>
        <span class="font-['Manrope'] text-[10px] font-bold uppercase tracking-widest mt-1"><?= $label ?></span>
    </a>
    <?php endforeach; ?>
</nav>
