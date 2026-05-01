<?php
/*
Template Name: Staff Edit Class
*/
require_once 'functions.php';
require_advanced();

$class_id = (int)($_GET['id'] ?? 0);
$flash_error = '';

if ($class_id <= 0) {
    wp_redirect(home_url('/?pagename=staff-manage-classes'));
    exit;
}

function edit_class_post_value(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function edit_class_field(array $class = null, string $key, $default = '')
{
    if (!$class) {
        return $default;
    }
    return $class[$key] ?? $default;
}

function edit_class_datetime_value(array $class = null, string $key): string
{
    $raw = $class[$key] ?? '';
    if (!$raw || !is_string($raw)) {
        return '';
    }

    $ts = strtotime($raw);
    if (!$ts) {
        return '';
    }

    return date('Y-m-d\TH:i', $ts);
}

/**
 * Cargar clase
 */
$class_response = api_get('/classes/' . $class_id, auth: true);
$editing_class = (($class_response['result'] ?? false) !== false) ? $class_response['result'] : null;

if (!$editing_class) {
    wp_redirect(home_url('/?pagename=staff-manage-classes'));
    exit;
}

/**
 * Cargar combos
 */
$activities_response = api_get('/activities', auth: true);
$activities = $activities_response['result']['data'] ?? ($activities_response['result'] ?? []);

$rooms_response = api_get('/rooms', auth: true);
$rooms = $rooms_response['result']['data'] ?? ($rooms_response['result'] ?? []);

$gyms_response = api_get('/gyms', auth: true);
$gyms = $gyms_response['result']['data'] ?? ($gyms_response['result'] ?? []);

$instructors_response = api_get('/users', auth: true);
$all_users = $instructors_response['result']['data'] ?? ($instructors_response['result'] ?? []);
$instructors = array_values(array_filter($all_users, function ($user) {
    $role = $user['role'] ?? '';
    return in_array($role, ['staff', 'assistant', 'manager', 'admin'], true);
}));

/**
 * Procesar edición
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'activity_id' => !empty($_POST['activity_id']) ? (int)$_POST['activity_id'] : null,
        'room_id' => !empty($_POST['room_id']) ? (int)$_POST['room_id'] : null,
        'gym_id' => !empty($_POST['gym_id']) ? (int)$_POST['gym_id'] : null,
        'instructor_id' => !empty($_POST['instructor_id']) ? (int)$_POST['instructor_id'] : null,
        'start_time' => trim((string)($_POST['start_time'] ?? '')),
        'end_time' => trim((string)($_POST['end_time'] ?? '')),
        'capacity_limit' => isset($_POST['capacity_limit']) ? (int)$_POST['capacity_limit'] : null,
        'is_cancelled' => !empty($_POST['is_cancelled']),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== null;
    });

    $update_response = api_put('/classes/' . $class_id, $payload, auth: true);

    if (($update_response['result'] ?? false) !== false) {
        wp_redirect(home_url('/?pagename=staff-manage-classes&notice=updated'));
        exit;
    } else {
        $flash_error = api_message($update_response) ?: 'No se pudo actualizar la clase.';
    }
}

$is_postback = $_SERVER['REQUEST_METHOD'] === 'POST';

$form_activity_id = $is_postback ? (int)edit_class_post_value('activity_id', 0) : (int)(
    $editing_class['activity_id']
    ?? $editing_class['activity']['id']
    ?? 0
);

$form_room_id = $is_postback ? (int)edit_class_post_value('room_id', 0) : (int)(
    $editing_class['room_id']
    ?? $editing_class['room']['id']
    ?? 0
);

$form_gym_id = $is_postback ? (int)edit_class_post_value('gym_id', 0) : (int)(
    $editing_class['gym_id']
    ?? $editing_class['gym']['id']
    ?? 0
);

$form_instructor_id = $is_postback ? (int)edit_class_post_value('instructor_id', 0) : (int)(
    $editing_class['instructor_id']
    ?? $editing_class['instructor']['id']
    ?? 0
);

$form_start_time = $is_postback
    ? edit_class_post_value('start_time', '')
    : edit_class_datetime_value($editing_class, 'start_time');

$form_end_time = $is_postback
    ? edit_class_post_value('end_time', '')
    : edit_class_datetime_value($editing_class, 'end_time');

$form_capacity_limit = $is_postback
    ? (string)edit_class_post_value('capacity_limit', '20')
    : (string)edit_class_field($editing_class, 'capacity_limit', 20);

$form_is_cancelled = $is_postback
    ? !empty($_POST['is_cancelled'])
    : !empty($editing_class['is_cancelled']);

wp_app_page_start('Edit Class', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Class</h2>
            <p class="text-sm text-on-surface-variant">Modifica los datos de la clase y guarda los cambios.</p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-manage-classes')) ?>"
            class="inline-flex items-center rounded-lg border border-outline-variant/30 px-4 py-2"
        >
            ← Back to classes
        </a>
    </div>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" class="space-y-5">

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm mb-1">Activity</label>
                    <select
                        name="activity_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select activity</option>
                        <?php foreach ($activities as $activity): ?>
                            <?php $activity_id = (int)($activity['id'] ?? 0); ?>
                            <option value="<?= $activity_id ?>" <?= $form_activity_id === $activity_id ? 'selected' : '' ?>>
                                <?= h($activity['name'] ?? 'Activity') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Room</label>
                    <select
                        name="room_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select room</option>
                        <?php foreach ($rooms as $room): ?>
                            <?php $room_id = (int)($room['id'] ?? 0); ?>
                            <option value="<?= $room_id ?>" <?= $form_room_id === $room_id ? 'selected' : '' ?>>
                                <?= h($room['name'] ?? 'Room') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm mb-1">Gym</label>
                    <select
                        name="gym_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select gym</option>
                        <?php foreach ($gyms as $gym): ?>
                            <?php $gym_id = (int)($gym['id'] ?? 0); ?>
                            <option value="<?= $gym_id ?>" <?= $form_gym_id === $gym_id ? 'selected' : '' ?>>
                                <?= h($gym['name'] ?? 'Gym') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm mb-1">Instructor</label>
                    <select
                        name="instructor_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select instructor</option>
                        <?php foreach ($instructors as $instructor): ?>
                            <?php $instructor_id = (int)($instructor['id'] ?? 0); ?>
                            <option value="<?= $instructor_id ?>" <?= $form_instructor_id === $instructor_id ? 'selected' : '' ?>>
                                <?= h($instructor['full_name'] ?? $instructor['email'] ?? 'Instructor') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm mb-1">Start time</label>
                    <input
                        type="datetime-local"
                        name="start_time"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h($form_start_time) ?>"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm mb-1">End time</label>
                    <input
                        type="datetime-local"
                        name="end_time"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h($form_end_time) ?>"
                        required
                    >
                </div>
            </div>

            <div>
                <label class="block text-sm mb-1">Capacity limit</label>
                <input
                    type="number"
                    min="1"
                    name="capacity_limit"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    value="<?= h($form_capacity_limit) ?>"
                    required
                >
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3">
                <input
                    type="checkbox"
                    name="is_cancelled"
                    value="1"
                    class="h-4 w-4 rounded border-outline-variant/30 bg-surface text-primary focus:ring-primary-container"
                    <?= $form_is_cancelled ? 'checked' : '' ?>
                >
                <span>Cancelled</span>
            </label>

            <div class="flex flex-wrap gap-2">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Save changes
                </button>

                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-classes')) ?>"
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