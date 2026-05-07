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

function view_notification_value(array $notification, array $keys, $default = '')
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

function view_notification_label($value): string
{
    $value = trim((string)$value);

    if ($value === '' || $value === '-' || $value === '—' || $value === 'â€”' || strtoupper($value) === 'NULL') {
        return '';
    }

    return ucwords(str_replace('_', ' ', $value));
}

function view_notification_date($raw): string
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
], ''));

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
], ''));

$related_gym = '';

if (!empty($notification['gym']) && is_array($notification['gym'])) {
    $related_gym = $notification['gym']['name'] ?? '';
} elseif (!empty($notification['related_gym']) && is_array($notification['related_gym'])) {
    $related_gym = $notification['related_gym']['name'] ?? '';
} else {
    $related_gym = view_notification_value($notification, ['related_gym_id', 'gym_id'], '');
}

$notification_stat_cards = [];
$notification_extra_cards = [];

if ($type !== '') {
    $notification_stat_cards[] = ['label' => 'Type', 'value' => h($type), 'pill' => false];
}

if ($audience !== '') {
    $notification_stat_cards[] = ['label' => 'Audience', 'value' => h($audience), 'pill' => false];
}

if ($status !== '') {
    $notification_stat_cards[] = ['label' => 'Status', 'value' => h($status), 'pill' => true];
}

if (h((string)$related_gym) !== '') {
    $notification_extra_cards[] = ['label' => 'Related gym', 'value' => h((string)$related_gym)];
}

if ($created_at !== '') {
    $notification_extra_cards[] = ['label' => 'Created', 'value' => h($created_at)];
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

                    <?php if ($notification_stat_cards): ?>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <?php foreach ($notification_stat_cards as $card): ?>
                                <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                        <?= h($card['label']) ?>
                                    </p>

                                    <?php if (!empty($card['pill'])): ?>
                                        <p class="mt-2 inline-flex rounded-full bg-primary-container/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-primary-container">
                                            <?= $card['value'] ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="mt-2 text-sm font-bold">
                                            <?= $card['value'] ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                            Message
                        </p>

                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-on-surface-variant">
                            <?= h($message) ?>
                        </p>
                    </div>

                    <?php if ($notification_extra_cards): ?>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <?php foreach ($notification_extra_cards as $card): ?>
                                <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                        <?= h($card['label']) ?>
                                    </p>
                                    <p class="mt-2 text-sm font-bold">
                                        <?= $card['value'] ?>
                                    </p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
