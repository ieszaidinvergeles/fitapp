<?php
require_once 'functions.php';
require_login();
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['activate_routine_id'])) {
    $routineId = (int)$_POST['activate_routine_id'];
    $activate = api_post('/routines/' . $routineId . '/activate', [], auth: true);
    if (!empty($activate['result']) && $activate['result'] !== false) {
        $success = api_message($activate) ?? 'Routine activated.';
    } else {
        $error = api_message($activate) ?? 'Could not activate routine.';
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$listResp = api_get('/routines?page=' . $page, auth: true);
$listData = $listResp['result']['data'] ?? [];
wp_app_page_start('Routines');
?>
    <?php show_error($error); show_success($success); ?>
    <?php if (($listResp['result'] ?? null) === false) { show_error(api_message($listResp)); } ?>
    <div class="space-y-4">
        <?php foreach ($listData as $r): ?>
            <article class="bg-surface-container rounded-2xl p-5 border border-outline-variant/20">
                <h2 class="font-headline text-2xl font-bold"><?= h($r['name'] ?? 'Routine') ?></h2>
                <p class="text-sm text-on-surface-variant mt-2">Difficulty: <?= h($r['difficulty_level'] ?? '-') ?></p>
                <p class="text-xs text-on-surface-variant mt-1">Duration: <?= (int)($r['estimated_duration_min'] ?? 0) ?> min • Exercises: <?= is_array($r['exercises'] ?? null) ? count($r['exercises']) : 0 ?></p>
                <div class="mt-3 flex items-center gap-2">
                    <a href="page-client-routine.php?id=<?= (int)($r['id'] ?? 0) ?>" class="px-4 py-2 rounded-full bg-surface-container-high text-xs font-black uppercase tracking-wider">View</a>
                    <form method="POST">
                        <input type="hidden" name="activate_routine_id" value="<?= (int)($r['id'] ?? 0) ?>"/>
                        <button class="kinetic-gradient text-on-primary-container px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider">Activate</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$listData): ?><p class="text-on-surface-variant">No routines available.</p><?php endif; ?>
    </div>
<?php
$GLOBALS['active'] = 'routines';
wp_app_page_end(false);

