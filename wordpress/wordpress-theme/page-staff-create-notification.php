<?php
/*
Template Name: Staff Create Notification
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$manage_notifications_url = home_url('/?pagename=staff-notifications');
$create_notification_url = home_url('/?pagename=staff-create-notification');

function notification_create_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
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

$gyms_response = api_get('/gyms', auth: true);
$gyms = notification_extract_list($gyms_response);

$audiences = [
    'global' => 'Global',
    'specific_gym' => 'Specific Gym',
    'staff_only' => 'Staff Only',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_notification_submit'])) {
    $payload = [
        'title' => trim((string)($_POST['notification_title'] ?? '')),
        'body' => trim((string)($_POST['message_text'] ?? '')),
        'target_audience' => trim((string)($_POST['target_audience'] ?? '')),
        'related_gym_id' => !empty($_POST['related_gym_id']) ? (int)$_POST['related_gym_id'] : null,
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== null && $value !== '-' && $value !== '—';
    });

    $create_response = api_post('/notifications', $payload, auth: true);

    if (($create_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_notifications_url . '&notice=created');
        exit;
    }

    $flash_error = api_message($create_response) ?: 'Could not create the notification. Check the required fields.';
}

wp_app_page_start('Create Notification', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Notification</h2>
            <p class="text-sm text-on-surface-variant">
                Create an announcement for users, staff, or a specific gym.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_notifications_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to notifications
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" action="<?= esc_url($create_notification_url) ?>" class="space-y-6">

            <input type="hidden" name="create_notification_submit" value="1">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Notification title
                </label>

                <input
                    type="text"
                    name="notification_title"
                    value="<?= h(notification_create_value('notification_title')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: Maintenance Notice"
                    maxlength="120"
                    required
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Message
                </label>

                <textarea
                    name="message_text"
                    rows="6"
                    maxlength="500"
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Write the notification message..."
                    required
                ><?= h(notification_create_value('message_text')) ?></textarea>

                <p class="mt-1 text-xs text-on-surface-variant">
                    Maximum 500 characters.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Audience
                    </label>

                    <?php $selected_audience = notification_create_value('target_audience', 'global'); ?>

                    <select
                        name="target_audience"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <?php foreach ($audiences as $value => $label): ?>
                            <option value="<?= h($value) ?>" <?= $selected_audience === $value ? 'selected' : '' ?>>
                                <?= h($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Related gym
                    </label>

                    <?php $selected_gym = (int)notification_create_value('related_gym_id', 0); ?>

                    <select
                        name="related_gym_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    >
                        <option value="">No specific gym</option>

                        <?php foreach ($gyms as $gym): ?>
                            <?php $gym_id = (int)($gym['id'] ?? 0); ?>
                            <option value="<?= $gym_id ?>" <?= $selected_gym === $gym_id ? 'selected' : '' ?>>
                                <?= h($gym['name'] ?? 'Gym') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary-container">info</span>
                    <p class="text-xs leading-relaxed text-on-surface-variant">
                        If you choose a global audience, the announcement will be shown to all users. If you choose a specific gym, it will be linked to that location.
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Notification
                </button>

                <a
                    href="<?= esc_url($manage_notifications_url) ?>"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
                >
                    Cancel
                </a>
            </div>

        </form>
    </section>

</div>

<?php
wp_app_page_end(true);
?>
