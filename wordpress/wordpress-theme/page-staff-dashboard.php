<?php
/*
Template Name: Client Dashboard
*/
require_once 'functions.php';
require_advanced();

$response = api_get('/staff/dashboard', auth: true);
$data = $response['result'] ?? [];

$user = $data['user'] ?? [];
$todayClasses = $data['today_classes'] ?? [];
$notif = isset($data['pending_notifications']) && is_array($data['pending_notifications'])
    ? count($data['pending_notifications'])
    : (int)($data['unread_notifications_count'] ?? 0);

// Docs-aligned aggregate stats with fallback for older payloads.
$totalUsers = (int)($data['active_members_count'] ?? $data['stats']['total_users'] ?? 0);
$activeMemberships = (int)($data['active_members_count'] ?? $data['stats']['active_memberships'] ?? 0);
$totalGyms = isset($data['gyms']) && is_array($data['gyms'])
    ? count($data['gyms'])
    : (int)($data['stats']['total_gyms'] ?? 0);

$page_title = 'Staff Portal';
$active = 'staff';
$GLOBALS['hide_global_header'] = true;
$GLOBALS['hide_global_footer'] = true;
voltgym_get_header();
?>
<header class="bg-[#0d0f08] flex justify-between items-center w-full px-6 py-4 fixed top-0 z-50 border-b border-surface-container-high border-opacity-50">
    <div class="flex items-center gap-4">
        <button class="text-[#d4fb00] hover:bg-zinc-800 transition-colors p-2 rounded-lg active:scale-95 duration-150">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <h1 class="font-headline font-bold tracking-tighter uppercase text-3xl italic text-[#d4fb00]">VOLT</h1>
    </div>
    <div class="flex items-center gap-4">
        <span class="hidden md:block font-headline font-bold text-xs tracking-widest text-on-surface-variant uppercase">Staff Portal</span>
        <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 border-primary-container bg-surface-container-high text-primary-container font-headline font-bold">
            <?= strtoupper(substr(h($user['full_name'] ?? $user['username'] ?? 'S'), 0, 1)) ?>
        </div>
        <a href="<?= esc_url(home_url('/?pagename=logout')) ?>" class="text-zinc-500 hover:text-error transition-colors ml-2">
            <span class="material-symbols-outlined">logout</span>
        </a>
    </div>
</header>

<main class="pt-24 pb-32 px-6 max-w-7xl mx-auto min-h-screen">
    <section class="mb-10">
        <h2 class="font-headline text-5xl md:text-7xl font-bold tracking-tighter uppercase leading-none">
            Staff <span class="text-primary-container italic">Portal</span>
        </h2>
        <p class="text-on-surface-variant font-medium mt-2 tracking-wide">Welcome back, <?= h($user['full_name'] ?? 'Team Member') ?> (<?= h($user['role'] ?? '') ?>)</p>
    </section>

    <?php
    if (($response['result'] ?? null) === false) {
        show_error(api_message($response));
    }
    ?>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <!-- Stats -->
        <div class="md:col-span-4 bg-surface-container rounded-3xl p-8 flex flex-col justify-between border-l-4 border-primary-container overflow-hidden relative">
            <div class="absolute -right-4 -top-4 opacity-10">
                <span class="material-symbols-outlined text-[120px]">groups</span>
            </div>
            <div>
                <h3 class="font-headline font-bold text-xs uppercase tracking-[0.2em] text-on-surface-variant mb-4">Active Members</h3>
                <div class="flex items-baseline gap-2">
                    <span class="font-headline text-8xl font-black text-primary tracking-tighter text-shadow-[0_0_15px_rgba(212,251,0,0.4)]"><?= $totalUsers ?></span>
                </div>
            </div>
            <div class="mt-8 pt-4 border-t border-outline-variant/20 flex gap-4">
                <div>
                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest">Active Plans</p>
                    <p class="font-headline font-bold text-lg"><?= $activeMemberships ?></p>
                </div>
                <div>
                    <p class="text-[10px] text-zinc-500 font-bold uppercase tracking-widest">Total Gyms</p>
                    <p class="font-headline font-bold text-lg"><?= $totalGyms ?></p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <a href="page-staff-attendance.php" class="bg-primary-container text-on-primary-container p-6 rounded-3xl flex flex-col items-center justify-center gap-4 group hover:bg-primary transition-all active:scale-95 shadow-[0_0_15px_rgba(212,251,0,0.2)]">
                <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">fingerprint</span>
                <span class="font-headline font-black uppercase text-sm tracking-widest text-center">Clock In/Out</span>
            </a>
            <a href="page-staff-manage-classes.php" class="bg-surface-container-high text-on-surface p-6 rounded-3xl flex flex-col items-center justify-center gap-4 hover:bg-surface-bright transition-all active:scale-95 border border-outline-variant/10">
                <span class="material-symbols-outlined text-4xl text-secondary">event</span>
                <span class="font-headline font-black uppercase text-sm tracking-widest text-center">Manage Classes</span>
            </a>
            <a href="page-staff-admin-users.php" class="bg-surface-container-high text-on-surface p-6 rounded-3xl flex flex-col items-center justify-center gap-4 hover:bg-surface-bright transition-all active:scale-95 border border-outline-variant/10">
                <span class="material-symbols-outlined text-4xl text-tertiary">how_to_reg</span>
                <span class="font-headline font-black uppercase text-sm tracking-widest text-center">Manage Users</span>
            </a>
        </div>

        <!-- Today's Schedule -->
        <div class="md:col-span-8 bg-surface-container rounded-3xl p-8 border border-outline-variant/10">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h3 class="font-headline font-bold text-xs uppercase tracking-[0.2em] text-on-surface-variant mb-1">Today's Briefing</h3>
                    <h4 class="font-headline text-3xl font-bold uppercase tracking-tight">Shift Schedule</h4>
                </div>
                <a href="page-staff-manage-classes.php" class="text-primary-container font-headline font-bold text-xs uppercase tracking-widest flex items-center gap-2 hover:underline">
                    View Full Schedule <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
            
            <?php if ($todayClasses): ?>
                <div class="space-y-2">
                    <?php foreach ($todayClasses as $c): ?>
                    <div class="bg-surface-container-high p-4 rounded-xl flex items-center justify-between hover:bg-surface-bright transition-colors border border-outline-variant/5">
                        <div class="flex items-center gap-6">
                            <span class="font-headline font-bold text-on-surface-variant text-sm w-16"><?= h(substr($c['start_time'] ?? '', 0, 5)) ?></span>
                            <div class="h-10 w-1 bg-primary-container rounded-full"></div>
                            <div>
                                <p class="font-headline font-black uppercase text-sm tracking-wider"><?= h($c['activity']['name'] ?? '') ?></p>
                                <p class="text-xs text-on-surface-variant font-bold"><?= h($c['room']['name'] ?? '') ?> - <?= (int)($c['capacity'] ?? 0) ?> Cap</p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-on-surface-variant italic">No classes scheduled for today.</p>
            <?php endif; ?>
        </div>

        <!-- System Notifications -->
        <div class="md:col-span-4 bg-surface-container-high rounded-3xl p-8 flex flex-col border border-outline-variant/10">
            <div class="flex items-center justify-between mb-6">
                <div class="flex flex-row items-center gap-2">
                    <span class="material-symbols-outlined text-primary" style="font-variation-settings: 'FILL' 1;">notifications_active</span>
                    <h3 class="font-headline font-bold text-xs uppercase tracking-[0.2em] text-on-surface">System Alerts</h3>
                </div>
                <?php if ($notif > 0): ?>
                    <span class="bg-primary-container text-on-primary-container text-[10px] font-bold px-2 py-0.5 rounded uppercase"><?= $notif ?> New</span>
                <?php endif; ?>
            </div>
            
            <div class="space-y-4 flex-grow">
                <p class="text-sm text-on-surface-variant">Check the full management portal for real-time issues and system messages.</p>
            </div>
            
            <a href="page-staff-notifications.php" class="block w-full text-center mt-6 py-3 bg-surface-bright rounded-xl font-headline font-bold text-[10px] uppercase tracking-widest hover:text-primary transition-colors border border-outline-variant/10">
                View All Notifications
            </a>
        </div>
    </div>
</main>

<?php
voltgym_get_template_part('template-parts/nav', 'staff');
voltgym_get_footer();
unset($GLOBALS['hide_global_header']);
unset($GLOBALS['hide_global_footer']);
?>

