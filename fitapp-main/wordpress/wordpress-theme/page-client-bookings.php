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

$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/bookings?page=' . $page, auth: true);
$result = $response['result'] ?? [];
$bookings = $result['data'] ?? [];
wp_app_page_start('My Bookings');
?>
    <?php show_error($error); show_success($success); ?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <div class="space-y-4">
        <?php foreach ($bookings as $b): ?>
            <article class="bg-surface-container rounded-2xl p-5 border border-outline-variant/20">
                <p class="text-xs font-bold uppercase tracking-widest text-primary-container"><?= h($b['status'] ?? 'booked') ?></p>
                <h2 class="font-headline text-2xl font-bold mt-1"><?= h($b['gym_class']['activity']['name'] ?? 'Class') ?></h2>
                <p class="text-sm text-on-surface-variant mt-2"><?= h($b['gym_class']['start_time'] ?? '') ?> - <?= h($b['gym_class']['end_time'] ?? '') ?></p>
                <p class="text-xs text-on-surface-variant mt-1">Room: <?= h($b['gym_class']['room']['name'] ?? '-') ?> • Gym: <?= h($b['gym_class']['gym']['name'] ?? '-') ?></p>
                <?php if (($b['status'] ?? '') === 'active'): ?>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="cancel_booking_id" value="<?= (int)($b['id'] ?? 0) ?>"/>
                        <button class="px-5 py-2 rounded-full bg-error/20 text-error text-xs font-black uppercase tracking-wider">Cancel Booking</button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        <?php if (!$bookings): ?>
            <p class="text-on-surface-variant">No bookings yet.</p>
        <?php endif; ?>
    </div>
<?php
wp_app_page_end(false);

