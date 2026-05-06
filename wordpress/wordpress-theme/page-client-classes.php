<?php
require_once 'functions.php';
require_login();

$date = $_GET['date'] ?? date('Y-m-d');
$activityId = $_GET['activity_id'] ?? '';
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['book_class_id'])) {
    $book = api_post('/bookings', ['class_id' => (int)$_POST['book_class_id']], auth: true);
    if (!empty($book['result']) && $book['result'] !== false) {
        $success = api_message($book) ?? 'Class booked successfully.';
    } else {
        $error = api_message($book) ?? 'Could not book class.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cancel_booking_id'])) {
    $cancel = api_post('/bookings/' . (int)$_POST['cancel_booking_id'] . '/cancel', [], auth: true);
    if (!empty($cancel['result']) && $cancel['result'] !== false) {
        $success = api_message($cancel) ?? 'Booking cancelled successfully.';
    } else {
        $error = api_message($cancel) ?? 'Could not cancel booking.';
    }
}

$endpoint = '/classes?per_page=100';
if ($date !== '') {
    $endpoint .= '&date=' . urlencode($date);
}
if ($activityId !== '') {
    $endpoint .= '&activity_id=' . urlencode($activityId);
}

$response = api_get($endpoint, auth: true);
$result = $response['result'] ?? [];
$classes = isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);

$activitiesResponse = api_get('/activities');
$actResult = $activitiesResponse['result'] ?? [];
$activities = isset($actResult['data']) ? $actResult['data'] : (is_array($actResult) ? $actResult : []);

$bookingsResponse = api_get('/bookings', auth: true);
$userBookingsRaw = isset($bookingsResponse['result']['data']) ? $bookingsResponse['result']['data'] : (is_array($bookingsResponse['result'] ?? null) ? $bookingsResponse['result'] : []);
$bookedClassIds = [];
foreach ($userBookingsRaw as $b) {
    if (($b['status'] ?? '') === 'active' && !empty($b['class_id'])) {
        $bookedClassIds[$b['class_id']] = $b['id'];
    }
}

wp_app_page_start('Classes');
?>
    <?php show_error($error); show_success($success); ?>
    <!-- Header / Hero Section -->
    <section class="mb-12">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="animate-in fade-in slide-in-from-right duration-700 delay-200">
                <p class="text-on-surface-variant font-body text-sm leading-relaxed">Push beyond the threshold. Elite performance begins with the right schedule.</p>
            </div>
        </div>
    </section>

    <?php
    // Calendar Logic
    $selectedDate = $date; // already defined above as today or from GET
    $viewDate = !empty($_GET['view_date']) ? $_GET['view_date'] : $selectedDate;
    $time = strtotime($viewDate);
    $currMonth = (int)date('m', $time);
    $currYear  = (int)date('Y', $time);
    
    $firstDayOfMonth = strtotime("$currYear-$currMonth-01");
    $daysInMonth = (int)date('t', $firstDayOfMonth);
    $startDayOfWeek = (int)date('N', $firstDayOfMonth); // 1 (Mon) to 7 (Sun)
    
    $prevMonthDate = date('Y-m-d', strtotime('-1 month', $firstDayOfMonth));
    $nextMonthDate = date('Y-m-d', strtotime('+1 month', $firstDayOfMonth));
    
    // Days from previous month to fill the first row
    $prevMonthDays = [];
    $prevMonthLastDay = (int)date('t', strtotime('-1 month', $firstDayOfMonth));
    for ($i = $startDayOfWeek - 1; $i > 0; $i--) {
        $prevMonthDays[] = $prevMonthLastDay - $i + 1;
    }
    ?>

    <!-- Interactive Calendar & Controls Section -->
    <section class="mb-12 bg-surface-container rounded-3xl p-6 border border-outline-variant/10 animate-in fade-in zoom-in-95 duration-700 delay-300 shadow-2xl overflow-hidden">
        <!-- Header: Month + Controls -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8 pb-6 border-b border-outline-variant/5">
            <div class="flex items-center gap-6">
                <h2 class="text-2xl font-headline font-black uppercase tracking-tighter text-on-surface">
                    <?= date('F', $firstDayOfMonth) ?> <span class="text-primary-container"><?= $currYear ?></span>
                </h2>
                <div class="flex gap-2">
                    <a href="<?= esc_url(add_query_arg('view_date', $prevMonthDate)) ?>" class="bg-surface-container-highest text-on-surface w-8 h-8 rounded-full flex items-center justify-center hover:bg-primary-container hover:text-black transition-all">
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    </a>
                    <a href="<?= esc_url(add_query_arg('view_date', $nextMonthDate)) ?>" class="bg-surface-container-highest text-on-surface w-8 h-8 rounded-full flex items-center justify-center hover:bg-primary-container hover:text-black transition-all">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </a>
                </div>
            </div>

            <!-- Integrated Filters -->
            <form method="GET" action="<?= esc_url(home_url('/')) ?>" class="flex items-center gap-3">
                <input type="hidden" name="pagename" value="client-classes" />
                <input type="hidden" name="date" value="<?= h($date) ?>" />
                
                <div class="relative min-w-[180px]">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-zinc-500 text-sm">category</span>
                    <select name="activity_id" onchange="this.form.submit()" 
                            class="bg-surface-container-highest/40 rounded-xl border border-outline-variant/10 pl-10 pr-4 py-2 w-full text-[10px] font-bold uppercase tracking-widest text-on-surface focus:border-primary-container/30 focus:ring-0 appearance-none">
                        <option value="">All Activities</option>
                        <?php foreach ($activities as $act): ?>
                            <option value="<?= h($act['id']) ?>" <?= ((string)$act['id'] === (string)$activityId) ? 'selected' : '' ?>>
                                <?= h($act['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <a href="<?= esc_url(home_url('/?pagename=client-classes')) ?>" 
                   class="p-2 rounded-xl bg-surface-container-highest text-zinc-500 hover:text-primary-container transition-all"
                   title="Reset All Filters">
                    <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                </a>
            </form>
        </div>

        <div class="grid grid-cols-7 gap-y-3 text-center items-center">
            <!-- Day Labels -->
            <?php foreach (['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'] as $day): ?>
                <div class="text-[9px] font-label font-black text-zinc-600 uppercase tracking-[0.2em] mb-1"><?= $day ?></div>
            <?php endforeach; ?>

            <!-- Prev Month Days -->
            <?php foreach ($prevMonthDays as $d): ?>
                <div class="opacity-10 py-2 text-xs font-headline font-bold text-on-surface"><?= $d ?></div>
            <?php endforeach; ?>

            <!-- Current Month Days -->
            <?php for ($d = 1; $d <= $daysInMonth; $d++): 
                $loopDate = sprintf('%04d-%02d-%02d', $currYear, $currMonth, $d);
                $isSelected = ($loopDate === $selectedDate);
                $isToday = ($loopDate === date('Y-m-d'));
            ?>
                <div class="py-0.5 flex items-center justify-center">
                    <a href="<?= esc_url(add_query_arg(['date' => $loopDate, 'pagename' => 'client-classes'])) ?>" 
                       class="relative w-8 h-8 rounded-full flex items-center justify-center text-xs font-headline font-bold transition-all
                              <?= $isSelected ? 'bg-primary-container text-black shadow-[0_0_15px_rgba(212,251,0,0.3)] scale-110 z-10' : 'text-on-surface hover:bg-surface-container-highest' ?>
                              <?= $isToday && !$isSelected ? 'border border-primary-container/30 text-primary-container' : '' ?>">
                        <?= $d ?>
                    </a>
                </div>
            <?php endfor; ?>

            <!-- Next Month Days -->
            <?php 
            $remainingSlots = 42 - (count($prevMonthDays) + $daysInMonth);
            for ($d = 1; $d <= $remainingSlots; $d++): ?>
                <div class="opacity-10 py-2 text-xs font-headline font-bold text-on-surface"><?= $d ?></div>
            <?php endfor; ?>
        </div>
    </section>

    <!-- Classes Timeline -->
    <div class="mb-8">
        <h2 class="text-2xl font-headline font-black italic uppercase tracking-tight text-on-surface">
            <?= $date ? 'Classes for ' . date('F j, Y', strtotime($date)) : 'Available Classes' ?>
        </h2>
    </div>

    <div class="relative ml-4 pl-10 border-l border-primary-container/20 space-y-8 pb-12 animate-in fade-in slide-in-from-bottom-8 duration-1000 delay-500">
        <?php if (empty($classes)): ?>
            <div class="relative">
                <div class="absolute -left-[45px] top-4 w-2 h-2 rounded-full bg-zinc-800 border border-zinc-700"></div>
                <div class="bg-surface-container/50 rounded-3xl p-12 text-center border border-outline-variant/10">
                    <span class="material-symbols-outlined text-zinc-700 text-6xl mb-4">event_busy</span>
                    <p class="text-zinc-500 font-bold uppercase tracking-widest text-xs">No classes available for this selection.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($classes as $c): 
                $startTime = !empty($c['start_time']) ? date('H:i', strtotime($c['start_time'])) : '--:--';
                $duration = '--';
                if (!empty($c['start_time']) && !empty($c['end_time'])) {
                    $diff = strtotime($c['end_time']) - strtotime($c['start_time']);
                    $duration = round($diff / 60);
                }
                $instructorName = $c['instructor']['full_name'] ?? $c['instructor']['username'] ?? 'TBA';
                $capacity = (int)($c['capacity_limit'] ?? 0);
                $bookedCount = (int)($c['bookings_count'] ?? 0);
                $isFull = (bool)($c['is_full'] ?? false);
                $isCancelled = !empty($c['is_cancelled']);
                $isPast = strtotime($c['start_time'] ?? '') < time();
                $spotsLeft = max(0, $capacity - $bookedCount);
            ?>
                <div class="relative group">
                    <!-- Timeline Dot -->
                    <div class="absolute -left-[45px] top-1/2 -translate-y-1/2 w-[10px] h-[10px] rounded-full <?= $isCancelled ? 'bg-error' : ($isPast ? 'bg-zinc-800' : 'bg-primary-container shadow-[0_0_10px_rgba(212,251,0,0.6)]') ?> transition-all group-hover:scale-150"></div>
                    
                    <div class="bg-surface-container rounded-3xl p-6 border border-outline-variant/10 hover:border-primary-container/30 transition-all duration-500 relative overflow-hidden">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-2xl font-headline font-black text-primary-container"><?= h($startTime) ?></span>
                                    <span class="text-[10px] font-label font-black uppercase tracking-[0.2em] text-on-surface-variant bg-surface-container-highest px-2 py-0.5 rounded">/ <?= $duration ?> MIN</span>
                                    <?php if ($isCancelled): ?>
                                        <span class="bg-error/20 text-error text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded">Cancelled</span>
                                    <?php endif; ?>
                                </div>
                                
                                <h3 class="text-3xl font-headline font-bold uppercase tracking-tight text-on-surface mb-4"><?= h($c['activity']['name'] ?? 'Class') ?></h3>
                                
                                <div class="flex flex-wrap items-center gap-6">
                                    <div class="flex items-center gap-2 text-on-surface-variant group/info">
                                        <span class="material-symbols-outlined text-lg text-primary-container group-hover/info:scale-110 transition-transform">person</span>
                                        <span class="text-xs font-bold tracking-wide"><?= h($instructorName) ?></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-on-surface-variant group/info">
                                        <span class="material-symbols-outlined text-lg text-primary-container group-hover/info:scale-110 transition-transform">meeting_room</span>
                                        <span class="text-xs font-bold tracking-wide"><?= h($c['room']['name'] ?? 'Studio') ?></span>
                                    </div>
                                    <div class="flex items-center gap-2 text-on-surface-variant group/info">
                                        <span class="material-symbols-outlined text-lg text-primary-container group-hover/info:scale-110 transition-transform">chair</span>
                                        <span class="text-xs font-bold tracking-wide uppercase <?= ($spotsLeft <= 5 && $spotsLeft > 0) ? 'text-secondary' : '' ?>">
                                            <?= $isFull ? 'FULLY BOOKED' : "$spotsLeft SPOTS LEFT" ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-3 min-w-[140px]">
                                <?php if ($isPast): ?>
                                    <span class="text-center px-6 py-3 rounded-2xl bg-zinc-800/50 text-zinc-500 text-[10px] font-black uppercase tracking-widest border border-zinc-700/30">
                                        Finished
                                    </span>
                                <?php elseif (isset($bookedClassIds[$c['id']])): ?>
                                    <form method="POST" onsubmit="event.preventDefault(); showConfirmModal(this);">
                                        <input type="hidden" name="cancel_booking_id" value="<?= (int)($bookedClassIds[$c['id']]) ?>"/>
                                        <button class="w-full px-6 py-3 rounded-2xl bg-error/10 text-error border border-error/20 text-[10px] font-black uppercase tracking-widest hover:bg-error hover:text-on-error transition-all active:scale-95">
                                            Cancel Booking
                                        </button>
                                    </form>
                                <?php elseif ($isFull): ?>
                                    <button disabled class="px-6 py-3 rounded-2xl bg-zinc-800 text-zinc-600 text-[10px] font-black uppercase tracking-widest opacity-50 cursor-not-allowed border border-zinc-700/20">
                                        Class Full
                                    </button>
                                <?php elseif (!$isCancelled): ?>
                                    <form method="POST">
                                        <input type="hidden" name="book_class_id" value="<?= (int)($c['id'] ?? 0) ?>"/>
                                        <button class="w-full px-6 py-3 rounded-2xl bg-primary-container text-on-primary-container text-[10px] font-black uppercase tracking-widest hover:shadow-[0_0_15px_rgba(212,251,0,0.4)] hover:scale-105 active:scale-95 transition-all">
                                            Book Now
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>


    </div>
</div>

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

    modal.addEventListener('click', (e) => {
        if (e.target === modal) hideModal();
    });
</script>

<?php
$GLOBALS['active'] = 'classes';
wp_app_page_end(false);
?>
