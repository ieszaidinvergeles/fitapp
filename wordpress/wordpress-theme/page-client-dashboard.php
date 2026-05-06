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
$GLOBALS['unread_notifications_count'] = $notifCount;
$nextClass  = $data['next_class']                 ?? null;

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cancel_booking_id'])) {
    $cancel = api_post('/bookings/' . (int)$_POST['cancel_booking_id'] . '/cancel', [], auth: true);
    if (!empty($cancel['result']) && $cancel['result'] !== false) {
        $success = api_message($cancel) ?? 'Booking cancelled successfully.';
        // Refresh data after cancellation
        $response = api_get('/dashboard', auth: true);
        $data = $response['result'] ?? [];
        $bookings = $data['upcoming_bookings'] ?? [];
        $nextClass = $data['next_class'] ?? null;
    } else {
        $error = api_message($cancel) ?? 'Could not cancel booking.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['book_class_id'])) {
    $book = api_post('/bookings', ['class_id' => (int)$_POST['book_class_id']], auth: true);
    if (!empty($book['result']) && $book['result'] !== false) {
        $success = api_message($book) ?? 'Class booked successfully.';
        $response = api_get('/dashboard', auth: true);
        $data = $response['result'] ?? [];
        $bookings = $data['upcoming_bookings'] ?? [];
        $nextClass = $data['next_class'] ?? null;
    } else {
        $error = api_message($book) ?? 'Could not book class.';
    }
}

$bookedClassMap = [];
foreach ($bookings as $b) {
    if (($b['status'] ?? '') === 'active' && !empty($b['class_id'])) {
        $bookedClassMap[$b['class_id']] = $b['id'];
    }
}

wp_app_page_start('Member Dashboard');
?>
<div class="mb-6">
    <?php 
        show_error($error); 
        show_success($success); 
        if (isset($_GET['purchase'])) {
            if ($_GET['purchase'] === 'pending_verification') {
                show_success('Payment successful! To complete your registration and pick up your access card, please visit any of our gyms in person.');
            } elseif ($_GET['purchase'] === 'plan_changed') {
                show_success('Plan updated! Your new benefits are now active.');
            } else {
                show_success('Welcome back! Your membership has been reactivated successfully.');
            }
        }
    ?>
</div>
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
                    <span class="inline-block px-3 py-1 bg-primary-container text-on-primary-container text-[10px] font-black uppercase tracking-widest rounded-full mb-6">Suggested Routine</span>
                    
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

            <!-- Composition & Alerts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Card 1: Full Composition -->
                <div class="bg-surface-container rounded-2xl p-6 border border-outline-variant/10 relative overflow-hidden group">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="font-headline font-bold uppercase tracking-widest text-zinc-500 text-[10px]">Your Composition</h4>
                        <span class="material-symbols-outlined text-primary-container opacity-20">monitoring</span>
                    </div>
                    <?php if ($metric): ?>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-6">
                            <div>
                                <p class="text-[10px] font-bold text-zinc-600 uppercase tracking-widest mb-1">Weight</p>
                                <p class="text-3xl font-headline font-black text-on-surface"><?= h($metric['weight_kg']) ?><span class="text-[10px] text-primary-container italic ml-1">KG</span></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-zinc-600 uppercase tracking-widest mb-1">Height</p>
                                <p class="text-3xl font-headline font-black text-on-surface"><?= h($metric['height_cm']) ?><span class="text-[10px] text-primary-container italic ml-1">CM</span></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-zinc-600 uppercase tracking-widest mb-1">Fat %</p>
                                <p class="text-2xl font-headline font-black text-on-surface"><?= $metric['body_fat_pct'] ? h($metric['body_fat_pct']) : '--' ?><span class="text-[10px] text-primary-container italic ml-1">%</span></p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-zinc-600 uppercase tracking-widest mb-1">Muscle %</p>
                                <p class="text-2xl font-headline font-black text-on-surface"><?= $metric['muscle_mass_pct'] ? h($metric['muscle_mass_pct']) : '--' ?><span class="text-[10px] text-primary-container italic ml-1">%</span></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="py-4">
                            <p class="text-xs text-zinc-500 font-medium italic">No metrics recorded yet.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Card 2: Interactive Alerts -->
                <a href="<?= esc_url(home_url('/?pagename=client-notifications')) ?>" class="bg-surface-container rounded-2xl p-6 border border-outline-variant/10 relative overflow-hidden group hover:bg-[#1e2117] transition-all flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-headline font-bold uppercase tracking-widest text-zinc-500 text-[10px]">Inbox</h4>
                        <div class="relative">
                            <span class="material-symbols-outlined text-3xl text-on-surface-variant group-hover:text-primary-container transition-colors">notifications</span>
                            <?php if ($notifCount > 0): ?>
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-error text-white text-[10px] font-black flex items-center justify-center rounded-full border-2 border-surface-container animate-bounce">
                                    <?= $notifCount ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-3xl font-headline font-black uppercase tracking-tight text-on-surface mb-2">System Alerts</h3>
                        <p class="text-xs text-zinc-500 font-medium leading-relaxed max-w-[200px]">
                            <?= $notifCount > 0 ? "You have $notifCount unread messages waiting for you." : "Your inbox is clear. Stay focused on your goals." ?>
                        </p>
                    </div>
                    <div class="mt-6 flex items-center gap-2 text-primary-container text-[10px] font-black uppercase tracking-widest opacity-0 group-hover:opacity-100 transition-all">
                        View All <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </div>
                </a>
            </div>
            
        </div>

        <!-- Right Column -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Membership Status -->
            <?php 
            $hasNoPlan = empty($user['membership_plan_id']);
            $isExpired = ($user['membership_status'] ?? '') === 'expired';
            $isPending = !empty($user['membership_plan_id']) && empty($user['current_gym_id']);
            $isActive  = ($user['membership_status'] ?? '') === 'active';

            if ($isActive && !$isPending): ?>
                <!-- Status Card: Active Member -->
                <div class="bg-surface-container-highest rounded-3xl p-6 relative overflow-hidden group transition-all border border-outline-variant/10">
                    <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:opacity-10 transition-opacity">
                        <span class="material-symbols-outlined text-[80px] text-primary" style="font-variation-settings: 'FILL' 1;">verified</span>
                    </div>
                    <div class="relative z-10 flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-primary flex items-center justify-center shrink-0 shadow-lg shadow-primary/20">
                            <span class="material-symbols-outlined text-on-primary text-3xl">workspace_premium</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] text-primary font-black uppercase tracking-widest mb-1">Membership Plan</p>
                            <h4 class="font-headline font-black uppercase tracking-tight text-2xl text-on-surface leading-none mb-2"><?= h($membership['name'] ?? 'Free Access') ?></h4>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Active Plan</span>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($isPending): ?>
                <!-- Status Card: Pending Activation -->
                <div class="bg-surface-container-highest/50 rounded-3xl p-6 border border-primary/20 relative overflow-hidden group">
                    <div class="relative z-10 flex items-start gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-zinc-800 flex items-center justify-center shrink-0 border border-white/5">
                            <span class="material-symbols-outlined text-primary text-3xl animate-pulse">pending_actions</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] text-primary font-black uppercase tracking-widest mb-1">Activation Pending</p>
                            <h4 class="font-headline font-black uppercase tracking-tight text-xl text-white leading-tight mb-2">Visit Your Gym</h4>
                            <p class="text-[11px] font-medium text-zinc-400 leading-relaxed mb-4">
                                You've secured your plan. Stop by your center to finalize setup and pick up your key.
                            </p>
                            <a href="<?= esc_url(home_url('/?pagename=gyms')); ?>" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-primary hover:gap-3 transition-all">
                                Find My Gym <span class="material-symbols-outlined text-sm">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>

            <?php elseif ($hasNoPlan || $isExpired): ?>
                <!-- Status Card: Promo/Buy -->
                <a href="<?= esc_url(home_url('/?pagename=client-memberships')); ?>" class="block relative group">
                    <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent rounded-[2rem] blur-xl opacity-50 group-hover:opacity-80 transition-opacity"></div>
                    <div class="relative bg-surface-container-highest/80 backdrop-blur-xl rounded-[2rem] p-6 border border-outline-variant/10 overflow-hidden flex flex-col">
                        <div class="mb-6">
                            <span class="text-[10px] font-headline font-bold uppercase tracking-[0.2em] text-primary mb-2 block">Level Up</span>
                            <h3 class="text-2xl font-headline font-black uppercase italic tracking-tighter leading-none">
                                Elevate Your <span class="text-primary">Game.</span>
                            </h3>
                        </div>
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-black rounded-full text-[10px] font-black uppercase tracking-widest self-start group-hover:gap-4 transition-all">
                            Choose Plan <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </div>
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
                            $capacity = $nextClass['capacity_limit'] ?? 0;
                            $bookedCount = $nextClass['bookings_count'] ?? 0;
                            $isFull = $nextClass['is_full'] ?? false;
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
                                <span class="<?= $isFull ? 'text-error font-bold' : '' ?>">
                                    <?= (int)$bookedCount ?> / <?= (int)$capacity ?> spots
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-zinc-800 flex items-center justify-between">
                            <?php if (isset($bookedClassMap[$nextClass['id']])): ?>
                                <span class="text-[10px] font-black uppercase tracking-widest text-primary-container bg-primary-container/10 px-2 py-1 rounded">Booked</span>
                                <form method="POST" onsubmit="event.preventDefault(); showConfirmModal(this);">
                                    <input type="hidden" name="cancel_booking_id" value="<?= (int)$bookedClassMap[$nextClass['id']] ?>"/>
                                    <button class="text-[10px] font-black uppercase tracking-widest text-error hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">cancel</span> Cancel
                                    </button>
                                </form>
                            <?php elseif ($isFull): ?>
                                <span class="text-[10px] font-black uppercase tracking-widest text-error bg-error/10 px-2 py-1 rounded">Class Full</span>
                                <button disabled class="opacity-30 cursor-not-allowed bg-zinc-800 text-zinc-500 font-black px-4 py-1.5 rounded-full text-[10px] uppercase tracking-wider">
                                    FULL
                                </button>
                            <?php else: ?>
                                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Available</span>
                                <form method="POST">
                                    <input type="hidden" name="book_class_id" value="<?= (int)$nextClass['id'] ?>"/>
                                    <button class="bg-primary-container text-on-primary-container font-black px-4 py-1.5 rounded-full text-[10px] uppercase tracking-wider hover:scale-105 transition-transform">
                                        Book Now
                                    </button>
                                </form>
                            <?php endif; ?>
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
                                <div class="flex items-center gap-3">
                                    <span class="bg-surface-container-highest px-2 py-1 rounded text-[10px] font-bold text-zinc-400 capitalize"><?= h($b['status'] ?? '') ?></span>
                                    <?php if (($b['status'] ?? '') === 'active'): ?>
                                        <form method="POST" onsubmit="event.preventDefault(); showConfirmModal(this);">
                                            <input type="hidden" name="cancel_booking_id" value="<?= (int)$b['id'] ?>"/>
                                            <button type="submit" class="text-error/60 hover:text-error transition-colors flex items-center" title="Cancel Booking">
                                                <span class="material-symbols-outlined text-[18px]">cancel</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
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

<!-- Custom Confirmation Modal -->
<div id="confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4">
    <div class="bg-surface-container rounded-3xl p-8 max-w-sm w-full border border-outline-variant/30 shadow-2xl scale-95 transition-all duration-300 opacity-0" id="modal-box">
        <div class="w-16 h-16 bg-error/10 text-error rounded-full flex items-center justify-center mb-6 mx-auto">
            <span class="material-symbols-outlined text-3xl">warning</span>
        </div>
        <h3 class="text-xl font-black uppercase tracking-tight text-center mb-2">Cancel Booking?</h3>
        <p class="text-zinc-400 text-sm text-center mb-8">Are you sure you want to cancel this booking? This action cannot be undone.</p>
        <div class="flex gap-3">
            <button id="modal-cancel-btn" class="flex-1 px-6 py-3 rounded-full bg-surface-container-high text-xs font-black uppercase tracking-wider hover:bg-surface-container-highest transition-all">No, Keep it</button>
            <button id="modal-confirm-btn" class="flex-1 px-6 py-3 rounded-full bg-error text-on-error text-xs font-black uppercase tracking-wider hover:scale-105 transition-all shadow-lg shadow-error/20">Yes, Cancel</button>
        </div>
    </div>
</div>

<script>
    let pendingForm = null;
    const modal = document.getElementById('confirm-modal');
    const modalBox = document.getElementById('modal-box');

    function showConfirmModal(form) {
        pendingForm = form;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modalBox.classList.remove('scale-95', 'opacity-0');
        }, 10);
    }

    function hideModal() {
        modalBox.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            pendingForm = null;
        }, 300);
    }

    document.getElementById('modal-cancel-btn').addEventListener('click', hideModal);
    document.getElementById('modal-confirm-btn').addEventListener('click', () => {
        if (pendingForm) pendingForm.submit();
    });

    // Close on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) hideModal();
    });
</script>

<?php
$GLOBALS['active'] = 'dashboard';
wp_app_page_end(false);
?>

