<?php
/*
Template Name: Staff Cancel Class
*/
require_once 'functions.php';
require_user_management();

$class_id = (int)($_GET['id'] ?? 0);
$flash_error = '';

if ($class_id <= 0) {
    wp_redirect(home_url('/?pagename=staff-manage-classes'));
    exit;
}

/**
 * Helpers
 */
function cancel_class_value(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

function cancel_class_field(array $data = null, array $paths = [], $default = '-')
{
    if (!$data) {
        return $default;
    }

    foreach ($paths as $path) {
        $value = $data;
        $ok = true;

        foreach ($path as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                $ok = false;
                break;
            }
        }

        if ($ok && $value !== null && $value !== '') {
            return $value;
        }
    }

    return $default;
}

function cancel_class_datetime_label(array $class_data = null): string
{
    $raw = cancel_class_field($class_data, [
        ['starts_at'],
        ['start_time'],
        ['date_time'],
        ['scheduled_at'],
        ['date'],
    ], '');

    if (!$raw || !is_string($raw)) {
        return 'No date available';
    }

    $ts = strtotime($raw);
    if (!$ts) {
        return $raw;
    }

    return date('d/m/Y H:i', $ts);
}

function cancel_lookup_by_id(array $items, int $id): array
{
    foreach ($items as $item) {
        if (is_array($item) && (int)($item['id'] ?? 0) === $id) {
            return $item;
        }
    }

    return [];
}

function cancel_extract_list(array $response): array
{
    if (!empty($response['result']['data']) && is_array($response['result']['data'])) {
        return $response['result']['data'];
    }

    if (!empty($response['result']) && is_array($response['result'])) {
        return $response['result'];
    }

    return [];
}

/**
 * Cargar clase
 */
$class_response = api_get('/classes/' . $class_id, auth: true);
$class_data = (($class_response['result'] ?? false) !== false) ? $class_response['result'] : null;

if (!$class_data) {
    wp_redirect(home_url('/?pagename=staff-manage-classes'));
    exit;
}

/**
 * Cargar relaciones para mostrar nombres reales
 */
$activities_response = api_get('/activities', auth: true);
$rooms_response = api_get('/rooms', auth: true);
$gyms_response = api_get('/gyms', auth: true);
$users_response = api_get('/users', auth: true);

$activities = cancel_extract_list($activities_response);
$rooms = cancel_extract_list($rooms_response);
$gyms = cancel_extract_list($gyms_response);
$users = cancel_extract_list($users_response);

$activity_id = (int)($class_data['activity_id'] ?? $class_data['activity']['id'] ?? 0);
$room_id = (int)($class_data['room_id'] ?? $class_data['room']['id'] ?? 0);
$gym_id = (int)($class_data['gym_id'] ?? $class_data['gym']['id'] ?? 0);
$instructor_id = (int)($class_data['instructor_id'] ?? $class_data['instructor']['id'] ?? 0);

$activity = $class_data['activity'] ?? cancel_lookup_by_id($activities, $activity_id);
$room = $class_data['room'] ?? cancel_lookup_by_id($rooms, $room_id);
$gym = $class_data['gym'] ?? cancel_lookup_by_id($gyms, $gym_id);
$instructor_data = $class_data['instructor'] ?? cancel_lookup_by_id($users, $instructor_id);

$class_name = $activity['name']
    ?? $class_data['activity_name']
    ?? $class_data['name']
    ?? $class_data['title']
    ?? $class_data['class_name']
    ?? 'Class';

$class_time = cancel_class_datetime_label($class_data);

$class_location = $room['name']
    ?? $class_data['room_name']
    ?? $class_data['location']
    ?? 'No location';

$class_attendance = $class_data['bookings_count']
    ?? $class_data['students_count']
    ?? $class_data['attendees_count']
    ?? $class_data['capacity_used']
    ?? '0';

$class_instructor = $instructor_data['full_name']
    ?? $instructor_data['username']
    ?? $instructor_data['email']
    ?? 'Unassigned';

$class_gym = $gym['name']
    ?? $class_data['gym_name']
    ?? '';

/**
 * Procesar cancelación
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim((string)($_POST['reason'] ?? ''));

    if ($reason === '') {
        $flash_error = 'Debes indicar un motivo de cancelación.';
    } else {
        $payload = [
            'reason' => $reason,
            'status' => 'cancelled',
            'is_cancelled' => true,
        ];

        $cancel_response = api_post('/classes/' . $class_id . '/cancel', $payload, auth: true);

        if (($cancel_response['result'] ?? false) === false) {
            $cancel_response = api_put('/classes/' . $class_id, $payload, auth: true);
        }

        if (($cancel_response['result'] ?? false) !== false) {
            wp_redirect(home_url('/?pagename=staff-manage-classes&notice=cancelled'));
            exit;
        }

        $flash_error = api_message($cancel_response) ?: 'No se pudo cancelar la clase.';
    }
}

wp_app_page_start('Cancel Class', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="mx-auto max-w-2xl space-y-8">
    <section class="text-center">
        <div class="mx-auto mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-error-container text-on-error-container shadow-[0_0_20px_rgba(213,61,24,0.2)]">
            <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">warning</span>
        </div>

        <h2 class="font-headline text-3xl font-bold uppercase tracking-tighter">Confirm Cancellation</h2>
        <p class="mt-2 text-on-surface-variant font-medium">
            Confirmar esta acción cancelará la sesión inmediatamente.
        </p>
    </section>

    <section class="rounded-xl border border-outline-variant/30 border-l-4 border-l-error bg-surface-container/30 p-6">
        <div class="space-y-6">
            <div class="border-b border-outline-variant/20 pb-4">
                <span class="mb-2 block font-label text-[10px] font-extrabold uppercase tracking-[0.2em] text-error">
                    Selected Session
                </span>
                <h3 class="font-headline text-2xl font-bold uppercase tracking-tight leading-tight">
                    <?= h($class_name) ?>
                </h3>
            </div>

            <div class="flex flex-col gap-5">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-error text-xl">schedule</span>
                        <span class="text-on-surface-variant text-[11px] uppercase font-bold tracking-widest">Time</span>
                    </div>
                    <p class="font-headline text-base font-semibold text-right"><?= h($class_time) ?></p>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-error text-xl">location_on</span>
                        <span class="text-on-surface-variant text-[11px] uppercase font-bold tracking-widest">Location</span>
                    </div>
                    <p class="font-headline text-base font-semibold text-right">
                        <?= h($class_location) ?><?= $class_gym ? ' · ' . h($class_gym) : '' ?>
                    </p>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-error text-xl">groups</span>
                        <span class="text-on-surface-variant text-[11px] uppercase font-bold tracking-widest">Attendance</span>
                    </div>
                    <p class="font-headline text-base font-semibold text-right"><?= h((string)$class_attendance) ?> Students</p>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-error text-xl">person</span>
                        <span class="text-on-surface-variant text-[11px] uppercase font-bold tracking-widest">Instructor</span>
                    </div>
                    <p class="font-headline text-base font-semibold text-right"><?= h($class_instructor) ?></p>
                </div>
            </div>
        </div>
    </section>

    <section>
        <form method="post" class="space-y-6">
            <div class="space-y-4">
                <label for="reason" class="block font-label text-xs font-bold uppercase tracking-widest text-on-surface">
                    Cancellation Reason
                </label>

                <textarea
                    id="reason"
                    name="reason"
                    rows="4"
                    class="w-full resize-none rounded-xl border border-error/20 bg-surface-container-highest p-5 text-on-surface placeholder:text-on-surface-variant/40 transition-all focus:border-error focus:ring-1 focus:ring-error"
                    placeholder="e.g., Facility maintenance, Coach illness..."
                ><?= h(cancel_class_value('reason', '')) ?></textarea>

                <div class="flex items-start gap-3 rounded-lg border border-error/20 bg-error-container/10 p-3">
                    <span class="material-symbols-outlined mt-0.5 text-error text-lg">info</span>
                    <p class="text-[10px] font-medium uppercase tracking-tight leading-relaxed text-error/80">
                        Este mensaje se enviará inmediatamente a los alumnos afectados si tu backend tiene notificaciones activas.
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <button
                    type="submit"
                    class="flex h-16 w-full items-center justify-center gap-3 rounded-full bg-primary-container font-headline text-lg font-black uppercase tracking-tight text-on-primary-container shadow-[0_4px_30px_rgba(215,255,0,0.5)] transition-all hover:brightness-110 active:scale-[0.98]"
                >
                    Cancel Entire Session
                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1; color: #20230f;">cancel</span>
                </button>

                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-classes')) ?>"
                    class="flex h-16 w-full items-center justify-center rounded-full border-2 border-primary-container bg-transparent font-headline text-sm font-black uppercase tracking-[0.2em] text-primary-container transition-all hover:bg-primary-container/5 active:scale-[0.98]"
                >
                    Keep class &amp; go back
                </a>
            </div>
        </form>
    </section>
</div>

<?php
wp_app_page_end(true);
?>