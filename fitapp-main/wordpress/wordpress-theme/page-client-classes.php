<?php
require_once 'functions.php';
require_login();
$page = max(1, (int)($_GET['page'] ?? 1));
$date = $_GET['date'] ?? '';
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['book_class_id'])) {
    $book = api_post('/bookings', ['class_id' => (int)$_POST['book_class_id']], auth: true);
    if (!empty($book['result']) && $book['result'] !== false) {
        $success = api_message($book) ?? 'Class booked successfully.';
    } else {
        $error = api_message($book) ?? 'Could not book class.';
    }
}

$endpoint = '/classes?page=' . $page . ($date !== '' ? '&date=' . urlencode($date) : '');
$response = api_get($endpoint, auth: true);
$result = $response['result'] ?? [];
$classes = $result['data'] ?? [];
wp_app_page_start('Classes');
?>
    <?php show_error($error); show_success($success); ?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <form method="GET" class="mb-6 flex items-center gap-3">
        <input type="date" name="date" value="<?= h($date, '') ?>" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3"/>
        <button class="px-4 py-3 rounded-full bg-surface-container-high text-xs font-black uppercase tracking-wider">Filter</button>
        <a href="page-client-classes.php" class="text-xs font-black uppercase tracking-wider text-on-surface-variant hover:text-primary-container">Clear</a>
    </form>
    <div class="space-y-4">
        <?php foreach ($classes as $c): ?>
            <article class="bg-surface-container rounded-2xl p-5 border border-outline-variant/20">
                <h2 class="font-headline text-2xl font-bold"><?= h($c['activity']['name'] ?? 'Class') ?></h2>
                <p class="text-sm text-on-surface-variant mt-2"><?= h($c['start_time'] ?? '') ?> - <?= h($c['end_time'] ?? '') ?></p>
                <p class="text-xs text-on-surface-variant mt-1">Room: <?= h($c['room']['name'] ?? '-') ?> • Capacity: <?= h($c['capacity_limit'] ?? $c['capacity'] ?? '-') ?></p>
                <p class="text-xs mt-1 <?= !empty($c['is_cancelled']) ? 'text-error' : 'text-primary-container' ?>">
                    <?= !empty($c['is_cancelled']) ? 'Cancelled' : 'Available' ?>
                </p>
                <?php if (empty($c['is_cancelled'])): ?>
                    <form method="POST" class="mt-3">
                        <input type="hidden" name="book_class_id" value="<?= (int)($c['id'] ?? 0) ?>"/>
                        <button class="kinetic-gradient text-on-primary-container px-5 py-2 rounded-full text-xs font-black uppercase tracking-wider">Book Class</button>
                    </form>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
        <?php if (!$classes): ?><p class="text-on-surface-variant">No classes available.</p><?php endif; ?>
    </div>
<?php
wp_app_page_end(false);

