<?php
require_once 'functions.php';
require_advanced();
$page = max(1, (int)($_GET['page'] ?? 1));
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'clockin') {
    api_post('/attendance/clock-in', [], auth: true);
}
$response = api_get('/attendance?page=' . $page, auth: true);
$rows = $response['result']['data'] ?? [];
wp_app_page_start('Staff Attendance', true);
?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <form method="POST" class="mb-6">
        <input type="hidden" name="action" value="clockin"/>
        <button class="kinetic-gradient text-on-primary-container px-6 py-3 rounded-full font-black uppercase tracking-widest text-xs">Clock In</button>
    </form>
    <div class="space-y-3">
        <?php foreach ($rows as $a): ?>
            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20">
                <p class="font-bold"><?= h($a['clock_in_time'] ?? 'N/A') ?></p>
                <p class="text-xs text-on-surface-variant">Clock out: <?= h($a['clock_out_time'] ?? 'Pending') ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <p class="text-on-surface-variant">No attendance records yet.</p>
        <?php endif; ?>
    </div>
<?php
wp_app_page_end(true);

