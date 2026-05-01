<?php
require_once 'functions.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['activate_id'])) {
    $activate = api_post('/routines/' . (int)$_POST['activate_id'] . '/activate', [], auth: true);
    if (!empty($activate['result']) && $activate['result'] !== false) {
        $success = api_message($activate) ?? 'Routine activated.';
    } else {
        $error = api_message($activate) ?? 'Could not activate routine.';
    }
}

$response = $id > 0 ? api_get('/routines/' . $id, auth: true) : ['result' => false, 'message' => ['general' => 'Routine not found.']];
$routine = $response['result'] ?? null;

wp_app_page_start('Routine Detail');
?>
    <?php show_error($error); show_success($success); ?>
    <?php if (($response['result'] ?? null) === false || !$routine): ?>
        <?php show_error(api_message($response)); ?>
    <?php else: ?>
        <section class="bg-surface-container rounded-2xl p-6 border border-outline-variant/20">
            <h2 class="font-headline text-3xl font-bold"><?= h($routine['name'] ?? 'Routine') ?></h2>
            <p class="text-sm text-on-surface-variant mt-2">Difficulty: <?= h($routine['difficulty_level'] ?? '-') ?> • Duration: <?= h($routine['estimated_duration_min'] ?? '-') ?> min</p>
            <p class="mt-3 text-sm text-on-surface-variant"><?= h($routine['description'] ?? 'No description') ?></p>
            <form method="POST" class="mt-6">
                <input type="hidden" name="activate_id" value="<?= (int)($routine['id'] ?? 0) ?>"/>
                <button class="kinetic-gradient text-on-primary-container px-6 py-3 rounded-full font-black uppercase tracking-widest text-xs">Activate Routine</button>
            </form>
        </section>
    <?php endif; ?>
<?php
wp_app_page_end(false);

