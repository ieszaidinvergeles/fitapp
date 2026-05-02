<?php
require_once 'functions.php';
require_login();
$page = max(1, (int)($_GET['paged_classes'] ?? $_GET['page'] ?? 1));
$date = $_GET['date'] ?? '';
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

$endpoint = '/classes?page=' . $page;
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
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <form method="GET" action="<?= esc_url(home_url('/')) ?>" class="mb-6 flex flex-wrap md:flex-nowrap items-center gap-3">
        <input type="hidden" name="pagename" value="client-classes" />
        <input type="date" name="date" value="<?= h($date, '') ?>" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3 w-full md:w-auto"/>
        <select name="activity_id" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-8 py-3 w-full md:w-auto text-on-surface">
            <option value="">All Activities</option>
            <?php foreach ($activities as $act): ?>
                <option value="<?= h($act['id']) ?>" <?= ((string)$act['id'] === (string)$activityId) ? 'selected' : '' ?>>
                    <?= h($act['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="flex items-center gap-3 w-full md:w-auto mt-2 md:mt-0">
            <button type="submit" class="flex-1 md:flex-none px-6 py-3 rounded-full bg-surface-container-high text-xs font-black uppercase tracking-wider text-on-surface hover:bg-surface-container-highest transition-colors text-center border border-outline-variant/20">Filter</button>
            <a href="<?= esc_url(home_url('/?pagename=client-classes')) ?>" class="flex-1 md:flex-none px-6 py-3 rounded-full text-xs font-black uppercase tracking-wider text-on-surface-variant hover:text-primary-container hover:bg-surface-container transition-colors text-center">Clear</a>
        </div>
    </form>
    <div class="space-y-4">
        <?php foreach ($classes as $c): ?>
            <article class="bg-surface-container rounded-2xl p-5 border border-outline-variant/20 relative overflow-hidden group hover:bg-[#1e2117] transition-all">
                <?php
                    $startTime = !empty($c['start_time']) ? date('M j, Y - g:i A', strtotime($c['start_time'])) : '';
                    $endTime = !empty($c['end_time']) ? date('g:i A', strtotime($c['end_time'])) : '';
                    $timeDisplay = $startTime . ($endTime ? ' to ' . $endTime : '');
                    $instructorName = $c['instructor']['full_name'] ?? $c['instructor']['username'] ?? 'Not assigned';
                    $capacity = $c['capacity_limit'] ?? 'Unlimited';
                    $available = $c['available_spots'] ?? 0;
                ?>
                <h2 class="font-headline text-3xl font-black uppercase tracking-tight text-primary-container mb-1"><?= h($c['activity']['name'] ?? 'Class') ?></h2>
                <p class="text-xs text-zinc-400 font-bold tracking-widest uppercase mb-4"><?= h($timeDisplay) ?></p>
                
                <div class="flex items-center gap-6 mb-4">
                    <div class="flex items-center gap-2 text-xs text-zinc-300">
                        <span class="material-symbols-outlined text-[16px] text-zinc-500">person</span>
                        <span class="font-medium"><?= h($instructorName) ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-zinc-300">
                        <span class="material-symbols-outlined text-[16px] text-zinc-500">meeting_room</span>
                        <span class="font-medium"><?= h($c['room']['name'] ?? 'Not assigned') ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-zinc-300">
                        <span class="material-symbols-outlined text-[16px] text-zinc-500">group</span>
                        <span class="font-medium"><?= (int)$available ?> / <?= h($capacity) ?> spots</span>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <span class="px-3 py-1 rounded text-[10px] font-black uppercase tracking-widest <?= !empty($c['is_cancelled']) ? 'bg-error-container/20 text-error' : 'bg-primary-container/20 text-primary-container' ?>">
                        <?= !empty($c['is_cancelled']) ? 'Cancelled' : 'Available' ?>
                    </span>
                    
                    <?php 
                        $isPast = strtotime($c['start_time']) < time();
                    ?>
                    <?php if ($isPast): ?>
                        <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 bg-zinc-800/50 px-4 py-2 rounded-full border border-zinc-700/30">
                            Class Finished
                        </span>
                    <?php elseif (isset($bookedClassIds[$c['id']])): ?>
                        <form method="POST" onsubmit="event.preventDefault(); showConfirmModal(this);">
                            <input type="hidden" name="cancel_booking_id" value="<?= (int)($bookedClassIds[$c['id']]) ?>"/>
                            <button class="bg-surface-container-high border border-error/30 text-error font-black px-6 py-2 rounded-full flex items-center gap-2 text-xs uppercase tracking-wider hover:bg-error/10 hover:border-error transition-all shadow-sm">
                                Cancel Booking
                            </button>
                        </form>
                    <?php elseif (empty($c['is_cancelled'])): ?>
                        <form method="POST">
                            <input type="hidden" name="book_class_id" value="<?= (int)($c['id'] ?? 0) ?>"/>
                            <button class="bg-gradient-to-r from-primary to-primary-container text-on-primary font-black px-6 py-2 rounded-full flex items-center gap-2 text-xs uppercase tracking-wider hover:scale-105 transition-transform shadow-[0_0_15px_rgba(215,255,0,0.15)]">
                                Book Now
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$classes): ?><p class="text-on-surface-variant">No classes available.</p><?php endif; ?>
        
        <?php
        $meta = $result['meta'] ?? null;
        if ($meta && ($meta['last_page'] ?? 1) > 1):
            $currentPage = $meta['current_page'];
            $lastPage = $meta['last_page'];
            
            $buildUrl = function($p) use ($date, $activityId) {
                $url = '/?pagename=client-classes&paged_classes=' . $p;
                if ($date !== '') $url .= '&date=' . urlencode($date);
                if ($activityId !== '') $url .= '&activity_id=' . urlencode($activityId);
                return esc_url(home_url($url));
            };
        ?>
        <div class="mt-8 flex items-center justify-center gap-2">
            <?php if ($currentPage > 1): ?>
                <a href="<?= $buildUrl($currentPage - 1) ?>" class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface hover:text-primary-container hover:bg-surface-container-high transition-colors">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
            <?php endif; ?>
            
            <span class="text-xs font-bold text-zinc-500 uppercase tracking-widest px-4">Page <?= $currentPage ?> of <?= $lastPage ?></span>
            
            <?php if ($currentPage < $lastPage): ?>
                <a href="<?= $buildUrl($currentPage + 1) ?>" class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface hover:text-primary-container hover:bg-surface-container-high transition-colors">
                    <span class="material-symbols-outlined">chevron_right</span>
                </a>
            <?php endif; ?>
        </div>
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
