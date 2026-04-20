<?php
require_once 'functions.php';
require_login();
$error = null;
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = [
        'date' => $_POST['date'] ?? '',
        'weight_kg' => (float)($_POST['weight_kg'] ?? 0),
    ];
    if (!empty($_POST['height_cm'])) $body['height_cm'] = (float)$_POST['height_cm'];
    if (!empty($_POST['body_fat_pct'])) $body['body_fat_pct'] = (float)$_POST['body_fat_pct'];
    if (!empty($_POST['muscle_mass_pct'])) $body['muscle_mass_pct'] = (float)$_POST['muscle_mass_pct'];
    $save = api_post('/body-metrics', $body, auth: true);
    if (!empty($save['result']) && $save['result'] !== false) {
        $success = api_message($save) ?? 'Body metric saved.';
    } else {
        $error = api_message($save) ?? 'Failed to save metric.';
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/body-metrics?page=' . $page, auth: true);
$rows = $response['result']['data'] ?? [];
wp_app_page_start('Body Metrics');
?>
    <?php show_error($error); show_success($success); ?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <form method="POST" class="bg-surface-container rounded-2xl p-6 border border-outline-variant/20 grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3"/>
        <input type="number" step="0.1" name="weight_kg" placeholder="Weight kg" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3"/>
        <input type="number" step="0.1" name="height_cm" placeholder="Height cm" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3"/>
        <input type="number" step="0.1" name="body_fat_pct" placeholder="Body fat %" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3"/>
        <button class="kinetic-gradient text-on-primary-container px-6 py-3 rounded-full font-black uppercase tracking-widest text-xs w-max">Save Metric</button>
    </form>
    <div class="space-y-3">
        <?php foreach ($rows as $m): ?>
            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20">
                <p class="font-bold"><?= h($m['date'] ?? '') ?></p>
                <p class="text-xs text-on-surface-variant">Weight: <?= h($m['weight_kg'] ?? '-') ?> kg • Height: <?= h($m['height_cm'] ?? '-') ?> cm</p>
                <p class="text-xs text-on-surface-variant">Body Fat: <?= h($m['body_fat_pct'] ?? '-') ?>% • Muscle: <?= h($m['muscle_mass_pct'] ?? '-') ?>% • BMI: <?= h($m['bmi'] ?? '-') ?></p>
            </article>
        <?php endforeach; ?>
    </div>
<?php
wp_app_page_end(false);

