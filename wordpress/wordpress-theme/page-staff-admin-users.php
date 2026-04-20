<?php
require_once 'functions.php';
require_user_management();
$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/users?page=' . $page, auth: true);
$users = $response['result']['data'] ?? [];
wp_app_page_start('Manage Users', true);
?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <div class="space-y-3">
        <?php foreach ($users as $u): ?>
            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20">
                <p class="font-bold"><?= h($u['full_name'] ?? $u['email'] ?? 'User') ?></p>
                <p class="text-xs text-on-surface-variant"><?= h($u['email'] ?? '') ?> • <?= h($u['role'] ?? '') ?></p>
                <p class="text-xs text-on-surface-variant">Gym: <?= h($u['current_gym']['name'] ?? '-') ?> • Plan: <?= h($u['membership_plan']['name'] ?? $u['membership_status'] ?? '-') ?></p>
                <p class="text-xs <?= !empty($u['is_blocked_from_booking']) ? 'text-error' : 'text-primary-container' ?>">
                    <?= !empty($u['is_blocked_from_booking']) ? 'Blocked from booking' : 'Booking enabled' ?> • Strikes: <?= (int)($u['cancellation_strikes'] ?? 0) ?>
                </p>
            </article>
        <?php endforeach; ?>
        <?php if (!$users): ?>
            <p class="text-on-surface-variant">No users found.</p>
        <?php endif; ?>
    </div>
<?php
wp_app_page_end(true);

