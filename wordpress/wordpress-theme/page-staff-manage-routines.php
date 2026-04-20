<?php
require_once 'functions.php';
require_advanced();
$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/routines?page=' . $page, auth: true);
$rows = $response['result']['data'] ?? [];
wp_app_page_start('Manage Routines', true);
?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <div class="space-y-3">
        <?php foreach ($rows as $r): ?>
            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20">
                <p class="font-bold"><?= h($r['name'] ?? 'Routine') ?></p>
                <p class="text-xs text-on-surface-variant mt-1"><?= h($r['difficulty_level'] ?? '-') ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <p class="text-on-surface-variant">No routines found.</p>
        <?php endif; ?>
    </div>
<?php
wp_app_page_end(true);

