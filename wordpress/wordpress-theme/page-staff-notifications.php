<?php
/*
Template Name: Staff Notifications
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'deleted') {
    $flash_success = 'Notification deleted successfully.';
} elseif ($notice === 'created') {
    $flash_success = 'Notification created successfully.';
} elseif ($notice === 'updated') {
    $flash_success = 'Notification updated successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $notification_id = (int)($_POST['notification_id'] ?? 0);

    if ($notification_id > 0) {
        $delete_response = api_delete('/notifications/' . $notification_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_safe_redirect(home_url('/?pagename=staff-notifications&notice=deleted'));
            exit;
        }

        $flash_error = api_message($delete_response) ?: 'Could not delete the notification.';
    }
}

function notification_extract_list(array $response): array
{
    if (($response['result'] ?? false) === false) {
        return [];
    }

    if (!empty($response['result']['data']) && is_array($response['result']['data'])) {
        return $response['result']['data'];
    }

    if (!empty($response['result']) && is_array($response['result'])) {
        return $response['result'];
    }

    return [];
}

function notification_value(array $notification, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (!isset($notification[$key]) || $notification[$key] === null) {
            continue;
        }

        $clean_value = trim((string)$notification[$key]);

        if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€”' && strtoupper($clean_value) !== 'NULL') {
            return $notification[$key];
        }
    }

    return $default;
}

function notification_page_url(int $page): string
{
    return home_url('/?pagename=staff-notifications&page_num=' . $page);
}

function notification_format_date($raw): string
{
    if (!$raw || $raw === '-' || $raw === '—' || $raw === 'â€”' || strtoupper((string)$raw) === 'NULL') {
        return '';
    }

    $ts = strtotime((string)$raw);

    if (!$ts) {
        return (string)$raw;
    }

    return date('d/m/Y H:i', $ts);
}

function notification_label($value): string
{
    $value = trim((string)$value);

    if ($value === '' || $value === '-' || $value === '—' || $value === 'â€”' || strtoupper($value) === 'NULL') {
        return '';
    }

    return ucwords(str_replace('_', ' ', $value));
}

/*
|--------------------------------------------------------------------------
| Load paginated notifications
|--------------------------------------------------------------------------
| Walk pages in case the API returns paginated results.
*/
$paged = fitapp_api_get_page('/notifications', $page, $per_page, true);
$listResp = $paged['response'];
$notifications = $paged['items'];
$pagination = $paged['meta'];
$current_page = $pagination['current_page'];
$last_page = $pagination['last_page'];
$total = $pagination['total'];
$from = $pagination['from'];
$to = $pagination['to'];

wp_app_page_start('Notifications', true);
?>

<?php if (($listResp['result'] ?? null) === false): ?>
    <?php show_error(api_message($listResp)); ?>
<?php endif; ?>

<?php if ($flash_success): ?>
    <?php show_success($flash_success); ?>
<?php endif; ?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Notification List</h2>
            <p class="text-sm text-on-surface-variant">
                Manage system announcements, global messages, and gym notifications.
            </p>

            <?php if ($total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> NOTIFICATIONS REGISTERED · PAGE <?= h((string)$current_page) ?> OF <?= h((string)$last_page) ?>
                </p>
            <?php endif; ?>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-notification')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
        >
            <span class="text-base leading-none">+</span>
            <span>Create notification</span>
        </a>
    </section>

    <section class="space-y-3">
        <?php foreach ($notifications as $notification): ?>
            <?php
            $notification_id = (int)($notification['id'] ?? 0);

            $title = notification_value($notification, ['title', 'subject', 'name'], 'Notification');
            $message = notification_value($notification, ['body', 'message', 'content', 'description', 'text'], '');
            $audience = notification_label(notification_value($notification, ['target_audience', 'audience', 'role'], ''));
            $type = notification_label(notification_value($notification, ['type', 'category', 'notification_type'], 'System'));
            $status = notification_label(notification_value($notification, ['status', 'state'], 'Pending'));
            $created_at = notification_format_date(notification_value($notification, ['created_at', 'date'], ''));

            $related_gym = '';
            if (!empty($notification['gym']) && is_array($notification['gym'])) {
                $related_gym = $notification['gym']['name'] ?? '';
            } elseif (!empty($notification['related_gym']) && is_array($notification['related_gym'])) {
                $related_gym = $notification['related_gym']['name'] ?? '';
            } else {
                $related_gym_id = (int)notification_value($notification, ['related_gym_id', 'gym_id'], 0);
                $related_gym = $related_gym_id > 0 ? 'Gym #' . $related_gym_id : '';
            }

            $notification_meta_bits = [];
            if (h($audience) !== '') {
                $notification_meta_bits[] = 'Audience: ' . h($audience);
            }
            if (h((string)$related_gym) !== '') {
                $notification_meta_bits[] = 'Gym: ' . h((string)$related_gym);
            }
            if (h($status) !== '') {
                $notification_meta_bits[] = 'Status: ' . h($status);
            }
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <span class="material-symbols-outlined text-4xl text-primary-container">notifications_active</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-lg font-bold break-words">
                                    <?= h($title) ?>
                                </p>

                                <span class="rounded-full bg-primary-container/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-primary-container">
                                    #<?= h((string)$notification_id) ?>
                                </span>

                                <span class="rounded-full border border-outline-variant/30 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-on-surface-variant">
                                    <?= h($type) ?>
                                </span>
                            </div>

                            <?php if ($notification_meta_bits): ?>
                                <p class="mt-1 text-sm text-on-surface-variant break-words">
                                    <?= implode(' | ', $notification_meta_bits) ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($message): ?>
                                <p class="mt-1 line-clamp-2 text-sm text-on-surface-variant break-words">
                                    <?= h((string)$message) ?>
                                </p>
                            <?php else: ?>
                                <p class="mt-1 text-sm italic text-on-surface-variant">
                                    No message available.
                                </p>
                            <?php endif; ?>

                            <?php if ($created_at !== ''): ?>
                                <p class="mt-1 text-xs text-on-surface-variant">
                                    Created: <?= h($created_at) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-notification&id=' . $notification_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this notification?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="notification_id" value="<?= $notification_id ?>">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg border border-error/40 px-3 py-2 text-sm text-error transition hover:bg-error/10"
                            >
                                Delete
                            </button>
                        </form>
                    </div>

                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$notifications): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No notifications found.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($last_page > 1): ?>
        <section class="flex flex-col gap-4 rounded-xl border border-outline-variant/20 bg-surface-container p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-on-surface-variant">
                Showing
                <span class="font-bold text-on-surface"><?= h((string)$from) ?></span>
                -
                <span class="font-bold text-on-surface"><?= h((string)$to) ?></span>
                of
                <span class="font-bold text-on-surface"><?= h((string)$total) ?></span>
                notifications
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">
                <?php if ($current_page > 1): ?>
                    <a
                        href="<?= esc_url(notification_page_url($current_page - 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        ← Previous
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        ← Previous
                    </span>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end = min($last_page, $current_page + 2);
                ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a
                        href="<?= esc_url(notification_page_url($i)) ?>"
                        class="rounded-full border px-4 py-2 text-sm font-bold transition <?= $i === $current_page
                            ? 'border-primary-container bg-primary-container text-on-primary-container shadow-[0_0_18px_rgba(212,251,0,0.22)]'
                            : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                    >
                        <?= h((string)$i) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $last_page): ?>
                    <a
                        href="<?= esc_url(notification_page_url($current_page + 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        Next →
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        Next →
                    </span>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
