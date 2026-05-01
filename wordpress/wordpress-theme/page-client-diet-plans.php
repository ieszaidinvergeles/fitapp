<?php
require_once 'functions.php';
require_login();
$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/membership-plans?page=' . $page, auth: true);
$items = $response['result']['data'] ?? [];
wp_app_page_start('Diet Plans');
?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($items as $p): ?>
            <article class="bg-surface-container rounded-xl p-5 border border-outline-variant/20">
                <h2 class="font-headline text-2xl font-bold"><?= h($p['name'] ?? h($p['type'] ?? 'Plan')) ?></h2>
                <p class="text-xs text-on-surface-variant mt-2">Price: <?= h($p['price'] ?? '-') ?> • Duration: <?= h($p['duration_months'] ?? '-') ?> months</p>
                <p class="text-xs text-on-surface-variant mt-1">Type: <?= h($p['type'] ?? '-') ?> • Duo allowed: <?= !empty($p['allow_partner_link']) ? 'Yes' : 'No' ?></p>
                <p class="text-xs text-on-surface-variant mt-2"><?= h($p['description'] ?? '') ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (!$items): ?>
            <p class="text-on-surface-variant">No diet plans available right now.</p>
        <?php endif; ?>
    </div>
<?php
wp_app_page_end(false);

