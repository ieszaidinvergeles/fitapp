<?php
/*
Template Name: Staff View Notification
*/
require_once 'functions.php';
require_advanced();

$notification_id = (int)($_GET['id'] ?? 0);

$manage_notifications_url = home_url('/?pagename=staff-notifications');
$flash_error = '';

if ($notification_id <= 0) {
    wp_safe_redirect($manage_notifications_url);
    exit;
}

function view_notification_value(array $notification, array $keys, $default = '-')
{
    foreach ($keys as $key) {
        if (isset($notification[$key]) && $notification[$key] !== null && $notification[$key] !== '') {
            return $notification[$key];
        }
    }

    return $default;
}

function view_notification_label($value): string
{
    $value = trim((string)$value);

    if ($value === '' || $value === '-' || $value === '—') {
        return '-';
    }

    return ucwords(str_replace('_', ' ', $value));
}

function view_notification_date($raw): string
{
    if (!$raw || $raw === '-' || $raw === '—') {
        return '-';
    }

    $ts = strtotime((string)$raw);

    if (!$ts) {
        return (string)$raw;
    }

    return date('d/m/Y H:i', $ts);
}

$response = api_get('/notifications/' . $notification_id, auth: true);
$notification = [];

if (($response['result'] ?? false) !== false && is_array($response['result'] ?? null)) {
    $notification = $response['result'];
} else {
    $flash_error = api_message($response) ?: 'No se pudo cargar la notificación.';
}

$title = view_notification_value($notification, ['title', 'subject', 'name'], 'Notification');

$message = view_notification_value($notification, [
    'message',
    'content',
    'body',
    'description',
    'text'
], 'No message available.');

$audience = view_notification_label(view_notification_value($notification, [
    'target_audience',
    'audience',
    'role'
], '-'));

$type = view_notification_label(view_notification_value($notification, [
    'type',
    'category',
    'notification_type'
], 'System'));

$status = view_notification_label(view_notification_value($notification, [
    'status',
    'state'
], 'Pending'));

$created_at = view_notification_date(view_notification_value($notification, [
    'created_at',
    'date'
], '-'));

$related_gym = '-';

if (!empty($notification['gym']) && is_array($notification['gym'])) {
    $related_gym = $notification['gym']['name'] ?? '-';
} elseif (!empty($notification['related_gym']) && is_array($notification['related_gym'])) {
    $related_gym = $notification['related_gym']['name'] ?? '-';
} else {
    $related_gym = view_notification_value($notification, ['related_gym_id', 'gym_id'], '-');
}

wp_app_page_start('View Notification', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-primary-container">
                Notification #<?= (int)$notification_id ?>
            </p>

            <h2 class="mt-2 text-3xl font-black uppercase tracking-tight sm:text-4xl">
                <?= h($title) ?>
            </h2>

            <p class="mt-2 text-sm text-on-surface-variant">
                Vista completa de la notificación seleccionada.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_notifications_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to notifications
        </a>
    </section>

    <?php if ($notification): ?>
        <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-5 sm:p-7 shadow-lg">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start">

                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl border border-outline-variant/20 bg-surface-container-high">
                    <span class="material-symbols-outlined text-5xl text-primary-container">
                        notifications_active
                    </span>
                </div>

                <div class="min-w-0 flex-1 space-y-4">

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                Type
                            </p>
                            <p class="mt-2 text-sm font-bold">
                                <?= h($type) ?>
                            </p>
                        </div>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                Audience
                            </p>
                            <p class="mt-2 text-sm font-bold">
                                <?= h($audience) ?>
                            </p>
                        </div>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                Status
                            </p>
                            <p class="mt-2 inline-flex rounded-full bg-primary-container/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-primary-container">
                                <?= h($status) ?>
                            </p>
                        </div>

                    </div>

                    <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                            Message
                        </p>

                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-on-surface-variant">
                            <?= h($message) ?>
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                Related gym
                            </p>
                            <p class="mt-2 text-sm font-bold">
                                <?= h((string)$related_gym) ?>
                            </p>
                        </div>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                Created
                            </p>
                            <p class="mt-2 text-sm font-bold">
                                <?= h($created_at) ?>
                            </p>
                        </div>

                    </div>

                </div>
            </div>

        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>