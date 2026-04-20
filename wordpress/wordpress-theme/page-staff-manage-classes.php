<?php
require_once 'functions.php';
require_advanced();
$page = max(1, (int)($_GET['page'] ?? 1));
$listResp = api_get('/classes?page=' . $page, auth: true);
$classes = $listResp['result']['data'] ?? [];
wp_app_page_start('Manage Classes', true);
?>
    <?php if (($listResp['result'] ?? null) === false) { show_error(api_message($listResp)); } ?>
    <div class="space-y-3">
        <?php foreach ($classes as $c): ?>
            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20">
                <p class="font-bold"><?= h($c['activity']['name'] ?? 'Class') ?></p>
                <p class="text-xs text-on-surface-variant"><?= h($c['start_time'] ?? '') ?> - <?= h($c['end_time'] ?? '') ?></p>
                <p class="text-xs text-on-surface-variant">Room: <?= h($c['room']['name'] ?? '-') ?> • Capacity: <?= h($c['capacity_limit'] ?? '-') ?></p>
                <p class="text-xs text-on-surface-variant">Instructor: <?= h($c['instructor']['full_name'] ?? '-') ?> • Gym: <?= h($c['gym']['name'] ?? '-') ?></p>
                <p class="text-xs mt-1 <?= !empty($c['is_cancelled']) ? 'text-error' : 'text-primary-container' ?>"><?= !empty($c['is_cancelled']) ? 'Cancelled' : 'Active' ?></p>
            </article>
        <?php endforeach; ?>
        <?php if (!$classes): ?>
            <p class="text-on-surface-variant">No classes found.</p>
        <?php endif; ?>
    </div>
<?php
wp_app_page_end(true);

