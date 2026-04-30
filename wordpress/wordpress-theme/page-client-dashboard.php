<?php
/*
Template Name: Staff Dashboard
*/
require_once 'functions.php';
require_login();

$response = api_get('/dashboard', auth: true);
$data = $response['result'] ?? [];

$user       = $data['user']                       ?? [];
$membership = $data['membership']                 ?? null;
$gym        = $data['gym']                        ?? null;
$routine    = $data['active_routine']             ?? null;
$bookings   = $data['upcoming_bookings']          ?? [];
$metric     = $data['latest_metric']              ?? null;
$notifCount = $data['unread_notifications_count'] ?? 0;
$nextClass  = $data['next_class']                 ?? null;

$page_title = 'Member Dashboard';
$active     = 'dashboard';
$GLOBALS['hide_global_header'] = true;
$GLOBALS['hide_global_footer'] = true;

voltgym_get_header();
?>
<header class="fixed top-0 z-40 w-full bg-[#0d0f08] flex justify-between items-center px-6 py-4 border-b border-surface-container-high border-opacity-50">
    <div class="flex items-center gap-4">
        <button id="open-client-menu" type="button" class="text-[#d4fb00] hover:bg-zinc-800 transition-colors p-2 rounded-lg active:scale-95 duration-150">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>
        <h1 class="text-3xl font-black italic text-[#d4fb00] tracking-tighter font-headline uppercase">VOLT</h1>
    </div>
    <div class="flex items-center gap-6">
        <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 border-primary-container bg-surface-container-high text-primary-container font-headline font-bold">
            <?= strtoupper(substr(h($user['full_name'] ?? $user['username'] ?? 'U'), 0, 1)) ?>
        </div>
        <a href="<?php echo esc_url(home_url('/?pagename=logout')); ?>" class="text-zinc-500 hover:text-error transition-colors">
            <span class="material-symbols-outlined">logout</span>
        </a>
    </div>
</header>

<div id="client-menu-overlay" class="hidden fixed inset-0 z-[70] bg-black/70 backdrop-blur-sm"></div>
<aside id="client-menu-drawer" class="fixed top-0 left-0 h-full w-80 max-w-[85vw] z-[80] -translate-x-full transition-transform duration-300 bg-surface-container p-6 border-r border-outline-variant/30">
    <div class="flex items-center justify-between mb-8">
        <h3 class="font-headline font-black uppercase text-primary-container tracking-tight">Volt Gym</h3>
        <button id="close-client-menu" type="button" class="text-on-surface-variant hover:text-primary-container">
            <span class="material-symbols-outlined">close</span>
        </button>
    </div>
    <nav class="space-y-2">
        <a href="<?php echo esc_url(home_url('/?pagename=client-dashboard')); ?>" class="block px-4 py-3 rounded-xl bg-surface-container-high text-on-surface font-bold uppercase tracking-wider text-xs">Dashboard</a>
        <a href="<?php echo esc_url(home_url('/?pagename=client-classes')); ?>" class="block px-4 py-3 rounded-xl hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface transition-colors font-bold uppercase tracking-wider text-xs">Classes</a>
        <a href="<?php echo esc_url(home_url('/?pagename=client-bookings')); ?>" class="block px-4 py-3 rounded-xl hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface transition-colors font-bold uppercase tracking-wider text-xs">Bookings</a>
        <a href="<?php echo esc_url(home_url('/?pagename=client-routines')); ?>" class="block px-4 py-3 rounded-xl hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface transition-colors font-bold uppercase tracking-wider text-xs">Routines</a>
        <a href="<?php echo esc_url(home_url('/?pagename=client-settings')); ?>" class="block px-4 py-3 rounded-xl hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface transition-colors font-bold uppercase tracking-wider text-xs">Settings</a>
        <a href="<?php echo esc_url(home_url('/?pagename=logout')); ?>" class="block px-4 py-3 rounded-xl hover:bg-error-container/20 text-error hover:text-error transition-colors font-bold uppercase tracking-wider text-xs">Logout</a>
    </nav>
</aside>

<main class="pt-24 pb-32 px-6 max-w-7xl mx-auto">
    <section class="mb-12">
        <h2 class="font-headline font-extrabold text-5xl md:text-7xl tracking-tighter mb-2 italic">Welcome, <?= h($user['full_name'] ?? $user['username'] ?? 'Athlete') ?></h2>
        <div class="flex items-center gap-4">
            <div class="h-[2px] w-12 bg-primary-container"></div>
            <p class="text-on-surface-variant font-bold uppercase tracking-[0.2em] text-xs">
                <?= h($membership['name'] ?? 'Free Tier') ?> Access
                <?php if ($gym): ?> - <?= h($gym['name']) ?><?php endif; ?>
            </p>
        </div>
    </section>

    <?php
    if (($response['result'] ?? null) === false) {
        show_error(api_message($response));
    }
    ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Active Routine -->
            <div class="relative overflow-hidden rounded-xl bg-surface-container-high p-8 group border border-outline-variant/20">
                <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                    <span class="material-symbols-outlined text-[120px] text-primary-container" style="font-variation-settings: 'FILL' 1;">fitness_center</span>
                </div>
                <div class="relative z-10">
                    <span class="inline-block px-3 py-1 bg-primary-container text-on-primary-container text-[10px] font-black uppercase tracking-widest rounded-full mb-6">Active Plan</span>
                    
                    <?php if ($routine): ?>
                        <h3 class="font-headline font-black text-4xl mb-2 tracking-tight uppercase"><?= h($routine['name']) ?></h3>
                        <p class="text-on-surface-variant mb-8 max-w-md">Level: <?= h($routine['difficulty_level'] ?? 'N/A') ?></p>
                        <a href="<?php echo esc_url(home_url('/?pagename=client-routine&id=' . urlencode($routine['id']))); ?>" class="bg-gradient-to-r from-primary to-primary-container text-on-primary font-black px-10 py-4 rounded-full flex items-center gap-3 w-max hover:scale-105 transition-transform shadow-[0_0_20px_rgba(215,255,0,0.2)]">
                            VIEW ROUTINE
                            <span class="material-symbols-outlined">play_arrow</span>
                        </a>
                    <?php else: ?>
                        <h3 class="font-headline font-black text-3xl mb-2 tracking-tight uppercase">No Active Routine</h3>
                        <p class="text-on-surface-variant mb-8 max-w-md">Assign a routine to push your limits.</p>
                        <a href="<?php echo esc_url(home_url('/?pagename=client-routines')); ?>" class="bg-gradient-to-r from-primary to-primary-container text-on-primary font-black px-10 py-4 rounded-full flex items-center w-max gap-3 hover:scale-105 transition-transform shadow-[0_0_20px_rgba(215,255,0,0.2)]">
                            BROWSE ROUTINES
                            <span class="material-symbols-outlined">search</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Body Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-surface-container rounded-xl p-6 border-l-4 border-primary-container">
                    <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-4">Body Weight</p>
                    <div class="flex items-end gap-2">
                        <span class="text-4xl font-headline font-bold tracking-tighter"><?= $metric ? h($metric['weight_kg']) : '--' ?></span>
                        <span class="text-primary-container font-black mb-1 italic">KG</span>
                    </div>
                </div>
                <div class="bg-surface-container rounded-xl p-6 border-l-4 border-secondary">
                    <p class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest mb-4">Last Update</p>
                    <div class="flex items-end gap-2">
                        <span class="text-2xl font-headline font-bold tracking-tighter"><?= $metric ? h($metric['date']) : 'Never' ?></span>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Notifications -->
            <?php if ($notifCount > 0): ?>
            <a href="<?php echo esc_url(home_url('/?pagename=client-settings')); ?>" class="block bg-surface-container-highest rounded-3xl p-6 relative overflow-hidden group hover:bg-[#1e2117] transition-all border border-outline-variant/10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-container flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-on-primary-container text-2xl">notifications_active</span>
                    </div>
                    <div>
                        <h4 class="font-headline font-bold uppercase tracking-tight text-xl text-primary-container leading-none"><?= (int)$notifCount ?></h4>
                        <p class="text-[10px] text-zinc-400 font-bold uppercase tracking-widest mt-1">Unread Alerts</p>
                    </div>
                    <span class="material-symbols-outlined ml-auto text-zinc-600 group-hover:text-primary-container">chevron_right</span>
                </div>
            </a>
            <?php endif; ?>

            <!-- Upcoming Class -->
            <div class="bg-surface-container rounded-xl p-6 border border-outline-variant/10">
                <h4 class="font-headline font-bold uppercase tracking-tight text-xl mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-container">event</span> Next Class
                </h4>
                <?php if ($nextClass): ?>
                    <div class="bg-surface-container-low p-4 rounded-xl">
                        <?php
                            $startTime = !empty($nextClass['start_time']) ? date('M j, Y - g:i A', strtotime($nextClass['start_time'])) : '';
                            $endTime = !empty($nextClass['end_time']) ? date('g:i A', strtotime($nextClass['end_time'])) : '';
                            $timeDisplay = $startTime . ($endTime ? ' to ' . $endTime : '');
                            $instructorName = $nextClass['instructor']['full_name'] ?? $nextClass['instructor']['username'] ?? 'TBA';
                            $capacity = $nextClass['capacity_limit'] ?? 'Unlimited';
                            $available = $nextClass['available_spots'] ?? 0;
                        ?>
                        <p class="text-[10px] text-primary-container font-black uppercase tracking-widest"><?= h($timeDisplay) ?></p>
                        <p class="font-headline font-bold text-lg leading-tight uppercase tracking-tighter mt-1"><?= h($nextClass['activity']['name'] ?? 'Class') ?></p>
                        
                        <div class="mt-3 flex items-center justify-between text-xs text-zinc-400">
                            <div class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">person</span>
                                <span><?= h($instructorName) ?></span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">group</span>
                                <span><?= (int)$available ?> / <?= h($capacity) ?> spots</span>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-6 text-zinc-500">
                        <span class="material-symbols-outlined text-4xl mb-2 opacity-50">event_busy</span>
                        <p class="text-sm font-medium">No upcoming classes.</p>
                        <a href="<?php echo esc_url(home_url('/?pagename=client-classes')); ?>" class="inline-block mt-4 text-xs font-bold text-primary hover:underline uppercase tracking-wider">Book Now</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Upcoming Bookings -->
            <div class="bg-surface-container rounded-xl p-6 border border-outline-variant/10">
                <h4 class="font-headline font-bold uppercase tracking-tight text-xl mb-4 text-zinc-400 text-sm">My Bookings</h4>
                <?php if ($bookings): ?>
                    <ul class="space-y-3">
                        <?php foreach (array_slice($bookings, 0, 3) as $b): ?>
                            <li class="flex items-center justify-between text-sm pb-3 border-b border-zinc-800 last:border-0 last:pb-0">
                                <div>
                                    <p class="font-bold text-on-surface"><?= h($b['gym_class']['activity']['name'] ?? 'Class') ?></p>
                                    <p class="text-[10px] text-zinc-500 font-mono mt-0.5"><?= h(!empty($b['gym_class']['start_time']) ? date('M j, Y - g:i A', strtotime($b['gym_class']['start_time'])) : '') ?></p>
                                </div>
                                <span class="bg-surface-container-highest px-2 py-1 rounded text-[10px] font-bold text-zinc-400 capitalize"><?= h($b['status'] ?? '') ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if (count($bookings) > 3): ?>
                        <a href="<?php echo esc_url(home_url('/?pagename=client-bookings')); ?>" class="block text-center mt-4 text-[10px] font-bold text-primary-fixed-dim uppercase tracking-wider hover:underline">View All</a>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-xs text-zinc-500">None scheduled.</p>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</main>

<?php
voltgym_get_template_part('template-parts/nav', 'client');
voltgym_get_footer();
unset($GLOBALS['hide_global_header']);
unset($GLOBALS['hide_global_footer']);
?>
<script>
(() => {
    const overlay = document.getElementById('client-menu-overlay');
    const drawer = document.getElementById('client-menu-drawer');
    const openBtn = document.getElementById('open-client-menu');
    const closeBtn = document.getElementById('close-client-menu');
    if (!overlay || !drawer || !openBtn || !closeBtn) return;

    const open = () => {
        overlay.classList.remove('hidden');
        drawer.classList.remove('-translate-x-full');
    };
    const close = () => {
        overlay.classList.add('hidden');
        drawer.classList.add('-translate-x-full');
    };
    openBtn.addEventListener('click', open);
    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', close);
})();
</script>

