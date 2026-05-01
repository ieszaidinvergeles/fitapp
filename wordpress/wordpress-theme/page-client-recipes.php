<?php
require_once 'functions.php';
require_login();
$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/recipes?page=' . $page, auth: true);
$items = $response['result']['data'] ?? [];
wp_app_page_start('Recipes');
?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($items as $r): ?>
            <article class="bg-surface-container rounded-xl p-5 border border-outline-variant/20">
                <h2 class="font-headline text-2xl font-bold"><?= h($r['name'] ?? 'Recipe') ?></h2>
                <p class="text-xs text-on-surface-variant mt-2"><?= h($r['type'] ?? '-') ?> • <?= h($r['calories'] ?? '-') ?> cal</p>
                <p class="text-xs text-on-surface-variant mt-1">Protein <?= h($r['macros']['protein'] ?? '-') ?>g • Carbs <?= h($r['macros']['carbs'] ?? '-') ?>g • Fat <?= h($r['macros']['fat'] ?? '-') ?>g</p>
                <p class="text-xs text-on-surface-variant mt-2"><?= h($r['description'] ?? '') ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (!$items): ?>
            <p class="text-on-surface-variant">No recipes available right now.</p>
        <?php endif; ?>
    </div>
<?php
wp_app_page_end(false);

