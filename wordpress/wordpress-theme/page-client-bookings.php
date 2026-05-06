<?php
require_once 'functions.php';
require_login();
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cancel_booking_id'])) {
    $bookingId = (int)$_POST['cancel_booking_id'];
    $cancel = api_post('/bookings/' . $bookingId . '/cancel', [], auth: true);
    if (!empty($cancel['result']) && $cancel['result'] !== false) {
        $success = api_message($cancel) ?? 'Booking cancelled.';
    } else {
        $error = api_message($cancel) ?? 'Could not cancel booking.';
    }
}

$page = max(1, (int)($_GET['paged_bookings'] ?? 1));
$includePast = isset($_GET['include_past']) && $_GET['include_past'] === '1';
$hideCancelled = isset($_GET['hide_cancelled']) && $_GET['hide_cancelled'] === '1';

$endpoint = '/bookings?page=' . $page;
if ($includePast) {
    $endpoint .= '&include_past=1';
}
if ($hideCancelled) {
    $endpoint .= '&hide_cancelled=1';
}

$response = api_get($endpoint, auth: true);
$result = $response['result'] ?? [];
$bookings = $result['data'] ?? [];

wp_app_page_start('My Bookings');
?>
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-on-surface-variant text-sm font-medium mt-1">Manage your upcoming and past class reservations.</p>
        </div>
        
        <form method="GET" class="flex items-center gap-3 bg-surface-container-high px-4 py-2 rounded-2xl border border-outline-variant/10">
            <input type="hidden" name="pagename" value="client-bookings" />
            <label class="flex items-center gap-3 cursor-pointer group">
                <div class="relative flex items-center">
                    <input type="checkbox" name="include_past" value="1" <?= $includePast ? 'checked' : '' ?> onchange="this.form.submit()" class="peer sr-only"/>
                    <div class="w-10 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-primary-container transition-colors"></div>
                    <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 group-hover:text-on-surface transition-colors">Show Past</span>
            </label>

            <div class="w-[1px] h-4 bg-zinc-800"></div>

            <label class="flex items-center gap-3 cursor-pointer group">
                <div class="relative flex items-center">
                    <input type="checkbox" name="hide_cancelled" value="1" <?= $hideCancelled ? 'checked' : '' ?> onchange="this.form.submit()" class="peer sr-only"/>
                    <div class="w-10 h-5 bg-zinc-700 rounded-full peer peer-checked:bg-primary-container transition-colors"></div>
                    <div class="absolute left-1 top-1 w-3 h-3 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                </div>
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-400 group-hover:text-on-surface transition-colors">Hide Cancelled</span>
            </label>
        </form>
    </div>

    <?php show_error($error); show_success($success); ?>

    <div class="space-y-4">
        <?php foreach ($bookings as $b): ?>
            <?php 
                $status = $b['status'] ?? 'active';
                $isCancelled = $status === 'cancelled';
                $isPast = strtotime($b['gym_class']['start_time'] ?? '') < time();
                $dateDisplay = !empty($b['gym_class']['start_time']) ? date('M j, Y - g:i A', strtotime($b['gym_class']['start_time'])) : '';
            ?>
            <article class="bg-surface-container rounded-2xl p-6 border border-outline-variant/10 group hover:bg-[#1e2117] transition-all relative overflow-hidden">
                <?php if ($isPast && $status === 'active'): ?>
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <span class="material-symbols-outlined text-6xl text-primary-container">history</span>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest 
                                <?= $status === 'active' ? 'bg-primary-container/20 text-primary-container' : ($status === 'cancelled' ? 'bg-error-container/20 text-error' : 'bg-surface-container-highest text-zinc-400') ?>">
                                <?= h($status) ?>
                            </span>
                            <?php if ($isPast): ?>
                                <span class="px-2 py-0.5 rounded bg-zinc-800 text-zinc-500 text-[10px] font-black uppercase tracking-widest">Finished</span>
                            <?php endif; ?>
                        </div>
                        <h2 class="font-headline text-3xl font-black uppercase tracking-tight group-hover:text-primary-container transition-colors"><?= h($b['gym_class']['activity']['name'] ?? 'Class') ?></h2>
                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-3">
                            <div class="flex items-center gap-2 text-xs text-zinc-400">
                                <span class="material-symbols-outlined text-[16px] text-primary-container">calendar_today</span>
                                <span class="font-mono"><?= h($dateDisplay) ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-zinc-400">
                                <span class="material-symbols-outlined text-[16px] text-zinc-500">meeting_room</span>
                                <span><?= h($b['gym_class']['room']['name'] ?? 'Main Floor') ?></span>
                            </div>
                        </div>
                    </div>

                    <?php if ($status === 'active' && !$isPast): ?>
                        <form method="POST" onsubmit="event.preventDefault(); showConfirmModal(this);">
                            <input type="hidden" name="cancel_booking_id" value="<?= (int)($b['id'] ?? 0) ?>"/>
                            <button class="bg-surface-container-high border border-error/30 text-error font-black px-8 py-3 rounded-full flex items-center gap-2 text-xs uppercase tracking-wider hover:bg-error/10 hover:border-error transition-all shadow-sm">
                                <span class="material-symbols-outlined text-sm">cancel</span>
                                Cancel Booking
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$bookings): ?>
            <div class="bg-surface-container rounded-3xl p-12 border border-dashed border-outline-variant/30 text-center">
                <span class="material-symbols-outlined text-6xl text-zinc-700 mb-4">event_busy</span>
                <h3 class="text-xl font-bold text-zinc-500">No bookings found</h3>
                <p class="text-zinc-600 text-sm mt-2">Try changing your filters or book a new class.</p>
                <a href="<?= esc_url(home_url('/?pagename=client-classes')) ?>" class="inline-block mt-6 px-8 py-3 bg-primary-container text-on-primary-container rounded-full text-xs font-black uppercase tracking-wider hover:scale-105 transition-transform">Book Now</a>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php
        $meta = $result['meta'] ?? $response['meta'] ?? null;
        if ($meta && ($meta['last_page'] ?? 1) > 1):
            $currentPage = $meta['current_page'];
            $lastPage = $meta['last_page'];
            
            $baseQuery = '?pagename=client-bookings';
            if ($includePast) $baseQuery .= '&include_past=1';
            if ($hideCancelled) $baseQuery .= '&hide_cancelled=1';
        ?>
        <div class="mt-12 flex items-center justify-center gap-4">
            <?php if ($currentPage > 1): ?>
                <a href="<?= $baseQuery ?>&paged_bookings=<?= $currentPage - 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary-container transition-colors border border-outline-variant/20">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
            <?php endif; ?>
            
            <div class="px-6 py-3 rounded-2xl bg-surface-container border border-outline-variant/20 text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">
                Page <?= $currentPage ?> / <?= $lastPage ?>
            </div>
            
            <?php if ($currentPage < $lastPage): ?>
                <a href="<?= $baseQuery ?>&paged_bookings=<?= $currentPage + 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary-container transition-colors border border-outline-variant/20">
                    <span class="material-symbols-outlined">chevron_right</span>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Custom Confirmation Modal (Reusable) -->
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
$GLOBALS['active'] = 'bookings';
wp_app_page_end(false);
