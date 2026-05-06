<?php
/*
Template Name: Staff Create Class
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

function class_form_value(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
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
 * Procesar creación
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

    $create_response = api_post('/classes', $payload, auth: true);

    if (($create_response['result'] ?? false) !== false) {
        wp_redirect(home_url('/?pagename=staff-manage-classes&notice=created'));
        exit;
    } else {
        $flash_error = api_message($create_response) ?: 'No se pudo crear la clase.';
    }
}

wp_app_page_start('Create Class', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-xl font-bold">Create Class</h2>
            <p class="text-sm text-on-surface-variant">
                Crea una nueva clase desde este formulario.
            </p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-manage-classes')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to classes
        </a>
    </div>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" class="space-y-6">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Activity</label>
                    <select
                        name="activity_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select activity</option>
                        <?php foreach ($activities as $activity): ?>
                            <?php $activity_id = (int)($activity['id'] ?? 0); ?>
                            <option value="<?= $activity_id ?>" <?= (int)class_form_value('activity_id', 0) === $activity_id ? 'selected' : '' ?>>
                                <?= h($activity['name'] ?? 'Activity') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Room</label>
                    <select
                        name="room_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select room</option>
                        <?php foreach ($rooms as $room): ?>
                            <?php $room_id = (int)($room['id'] ?? 0); ?>
                            <option value="<?= $room_id ?>" <?= (int)class_form_value('room_id', 0) === $room_id ? 'selected' : '' ?>>
                                <?= h($room['name'] ?? 'Room') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Gym</label>
                    <select
                        name="gym_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select gym</option>
                        <?php foreach ($gyms as $gym): ?>
                            <?php $gym_id = (int)($gym['id'] ?? 0); ?>
                            <option value="<?= $gym_id ?>" <?= (int)class_form_value('gym_id', 0) === $gym_id ? 'selected' : '' ?>>
                                <?= h($gym['name'] ?? 'Gym') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Instructor</label>
                    <select
                        name="instructor_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select instructor</option>
                        <?php foreach ($instructors as $instructor): ?>
                            <?php $instructor_id = (int)($instructor['id'] ?? 0); ?>
                            <option value="<?= $instructor_id ?>" <?= (int)class_form_value('instructor_id', 0) === $instructor_id ? 'selected' : '' ?>>
                                <?= h($instructor['full_name'] ?? $instructor['email'] ?? 'Instructor') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Start time</label>
                    <input
                        type="datetime-local"
                        name="start_time"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h(class_form_value('start_time', '')) ?>"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">End time</label>
                    <input
                        type="datetime-local"
                        name="end_time"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h(class_form_value('end_time', '')) ?>"
                        required
                    >
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Capacity limit</label>
                    <input
                        type="number"
                        min="1"
                        name="capacity_limit"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h((string)class_form_value('capacity_limit', '20')) ?>"
                        required
                    >
                </div>
            </div>

            <label
                for="isCancelledToggle"
                class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-4 transition hover:border-primary-container/30"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-container/10 text-primary-container">
                        <span class="material-symbols-outlined text-[20px]">event_busy</span>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-on-surface">
                            Create as cancelled
                        </p>
                        <p class="text-xs text-on-surface-variant">
                            Marca esta opción si la clase debe crearse ya cancelada.
                        </p>
                    </div>
                </div>

                <div class="relative shrink-0">
                    <input
                        id="isCancelledToggle"
                        type="checkbox"
                        name="is_cancelled"
                        value="1"
                        class="peer sr-only"
                        <?= !empty($_POST['is_cancelled']) ? 'checked' : '' ?>
                    >

                    <span class="block h-7 w-14 rounded-full bg-surface transition-colors duration-200 peer-checked:bg-primary-container"></span>

                    <span class="pointer-events-none absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-md transition-all duration-200 peer-checked:left-8"></span>
                </div>
            </label>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Class
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