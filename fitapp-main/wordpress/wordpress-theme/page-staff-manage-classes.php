<?php
/*
Template Name: Staff Manage Classes
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page'] ?? 1));
$flash_success = '';
$flash_error = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'cancelled') {
    $flash_success = 'Clase cancelada correctamente.';
} elseif ($notice === 'deleted') {
    $flash_success = 'Clase eliminada correctamente.';
} elseif ($notice === 'created') {
    $flash_success = 'Clase creada correctamente.';
} elseif ($notice === 'updated') {
    $flash_success = 'Clase actualizada correctamente.';
}

/**
 * Eliminar clase
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $class_id = (int)($_POST['class_id'] ?? 0);

    if ($class_id > 0) {
        $delete_response = api_delete('/classes/' . $class_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_redirect(home_url('/?pagename=staff-manage-classes&notice=deleted'));
            exit;
        } else {
            $flash_error = api_message($delete_response) ?: 'No se pudo eliminar la clase.';
        }
    }
}

/**
 * Extraer listas de respuestas API.
 */
function extract_classes_from_response(array $response): array
{
    if (!empty($response['result']['data']) && is_array($response['result']['data'])) {
        return $response['result']['data'];
    }

    if (!empty($response['result']) && is_array($response['result'])) {
        return $response['result'];
    }

    return [];
}

function extract_list_from_response(array $response): array
{
    if (!empty($response['result']['data']) && is_array($response['result']['data'])) {
        return $response['result']['data'];
    }

    if (!empty($response['result']) && is_array($response['result'])) {
        return $response['result'];
    }

    return [];
}

function build_lookup_by_id(array $items): array
{
    $map = [];

    foreach ($items as $item) {
        if (is_array($item) && isset($item['id'])) {
            $map[(int)$item['id']] = $item;
        }
    }

    return $map;
}

/**
 * Cargar clases
 */
$listResp = api_get('/classes?page=' . $page, auth: true);
$classes = [];
$pagination = [];

if (($listResp['result'] ?? null) !== false) {
    $classes = extract_classes_from_response($listResp);

    if (!empty($listResp['result']['meta']) && is_array($listResp['result']['meta'])) {
        $pagination = $listResp['result']['meta'];
    }
}

/**
 * Fallback por si el endpoint real es otro
 */
if (!$classes && (($listResp['result'] ?? null) !== false)) {
    $altResp = api_get('/gym-classes?page=' . $page, auth: true);

    if (($altResp['result'] ?? null) !== false) {
        $classes = extract_classes_from_response($altResp);

        if (!empty($altResp['result']['meta']) && is_array($altResp['result']['meta'])) {
            $pagination = $altResp['result']['meta'];
        }

        $listResp = $altResp;
    }
}

$current_page = max(1, (int)($pagination['current_page'] ?? $page));
$last_page = max(1, (int)($pagination['last_page'] ?? 1));

/**
 * Cargar relaciones para pintar nombres.
 */
$activities_response = api_get('/activities', auth: true);
$rooms_response = api_get('/rooms', auth: true);
$gyms_response = api_get('/gyms', auth: true);
$users_response = api_get('/users', auth: true);
$bookings_response = api_get('/bookings', auth: true);

$activities = extract_list_from_response($activities_response);
$rooms = extract_list_from_response($rooms_response);
$gyms = extract_list_from_response($gyms_response);
$users_lookup = extract_list_from_response($users_response);
$all_bookings = extract_list_from_response($bookings_response);

$activities_by_id = build_lookup_by_id($activities);
$rooms_by_id = build_lookup_by_id($rooms);
$gyms_by_id = build_lookup_by_id($gyms);
$users_by_id = build_lookup_by_id($users_lookup);

/**
 * Helpers visuales
 */
function class_field(array $data = null, array $paths = [], $default = '-')
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

function class_datetime_label(array $class_data = null, string $field): string
{
    $raw = class_field($class_data, [[$field]], '');

    if (!$raw || !is_string($raw)) {
        return '-';
    }

    $ts = strtotime($raw);
    if (!$ts) {
        return $raw;
    }

    return date('d/m/Y H:i', $ts);
}

wp_app_page_start('Manage Classes', true);
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

<div class="space-y-6">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Class List</h2>
            <p class="text-sm text-on-surface-variant">
                Gestiona clases, revisa su estado o cancela sesiones.
            </p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-class')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
        >
            <span class="text-base leading-none">+</span>
            <span>Create class</span>
        </a>
    </section>

    <section class="space-y-3">
        <?php foreach ($classes as $c): ?>
            <?php
            $class_id = (int)($c['id'] ?? 0);

            $detail_response = $class_id > 0
                ? api_get('/classes/' . $class_id, auth: true)
                : [];

            $detail = $detail_response['result'] ?? [];

            if (is_array($detail) && !empty($detail)) {
                $c = array_replace_recursive($c, $detail);
            }

            $activity_id = (int)($c['activity_id'] ?? $c['activity']['id'] ?? 0);
            $room_id = (int)($c['room_id'] ?? $c['room']['id'] ?? 0);
            $gym_id = (int)($c['gym_id'] ?? $c['gym']['id'] ?? 0);
            $instructor_id = (int)($c['instructor_id'] ?? $c['instructor']['id'] ?? 0);

            $activity = $c['activity'] ?? ($activities_by_id[$activity_id] ?? []);
            $room = $c['room'] ?? ($rooms_by_id[$room_id] ?? []);
            $gym = $c['gym'] ?? ($gyms_by_id[$gym_id] ?? []);
            $instructorData = $c['instructor'] ?? ($users_by_id[$instructor_id] ?? []);

            $class_name = $activity['name']
                ?? $c['activity_name']
                ?? $c['name']
                ?? $c['title']
                ?? $c['class_name']
                ?? 'Class';

            $start_label = class_datetime_label($c, 'start_time');
            $end_label = class_datetime_label($c, 'end_time');

            $room_name = $room['name'] ?? $c['room_name'] ?? '-';
            $capacity = $c['capacity_limit'] ?? $c['capacity'] ?? $c['max_capacity'] ?? '-';

            $instructor = $instructorData['full_name']
                ?? $instructorData['username']
                ?? $instructorData['email']
                ?? '-';

            $gym_name = $gym['name'] ?? $c['gym_name'] ?? '-';

            $bookings_count = 0;

            /**
             * 🔥 FIX REAL (NO tocar diseño)
             * contamos desde $all_bookings (ya cargado arriba)
             */
            foreach ($all_bookings as $booking) {
                if ((int)($booking['class_id'] ?? 0) === $class_id) {
                    $bookings_count++;
                }
            }
            ?>

            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-lg break-words">
                            <?= h($class_name) ?>
                        </p>

                        <p class="text-sm text-on-surface-variant break-words">
                            <?= h($start_label) ?> - <?= h($end_label) ?>
                        </p>

                        <p class="text-sm text-on-surface-variant break-words">
                            Room: <?= h($room_name) ?>
                            • Capacity: <?= h((string)$capacity) ?>
                        </p>

                        <p class="text-sm text-on-surface-variant break-words">
                            Instructor: <?= h($instructor) ?>
                            • Gym: <?= h($gym_name) ?>
                        </p>

                        <p class="text-sm text-on-surface-variant break-words">
                            Bookings: <?= (string)$bookings_count ?>
                        </p>

                        <p class="text-sm mt-1 <?= !empty($c['is_cancelled']) ? 'text-error' : 'text-primary-container' ?>">
                            <?= !empty($c['is_cancelled']) ? 'Cancelled' : 'Active' ?>
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-class&id=' . $class_id)) ?>"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-outline-variant/30 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-cancel-class&id=' . $class_id)) ?>"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-error/40 text-sm text-error transition hover:bg-error/10"
                        >
                            Cancel
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-class-bookings&id=' . $class_id)) ?>"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-outline-variant/30 text-sm transition hover:bg-surface-container-high"
                        >
                            View bookings
                        </a>

                        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar esta clase?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="class_id" value="<?= $class_id ?>">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-error/40 text-sm text-error transition hover:bg-error/10"
                            >
                                Delete
                            </button>
                        </form>
                    </div>

                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$classes): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No classes found.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($last_page > 1): ?>
        <section class="flex flex-wrap items-center justify-center gap-2 pt-2">
            <?php if ($current_page > 1): ?>
                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-classes&page=' . ($current_page - 1))) ?>"
                    class="px-3 py-2 rounded-lg border border-outline-variant/30 text-sm transition hover:bg-surface-container-high"
                >
                    Previous
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $current_page - 2);
            $end = min($last_page, $current_page + 2);
            ?>

            <?php if ($start > 1): ?>
                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-classes&page=1')) ?>"
                    class="px-3 py-2 rounded-lg border border-outline-variant/30 text-sm transition hover:bg-surface-container-high"
                >
                    1
                </a>
                <?php if ($start > 2): ?>
                    <span class="px-2 text-on-surface-variant">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-classes&page=' . $i)) ?>"
                    class="px-3 py-2 rounded-lg border text-sm transition <?= $i === $current_page
                        ? 'border-primary-container bg-primary-container text-on-primary-container font-bold'
                        : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                >
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($end < $last_page): ?>
                <?php if ($end < $last_page - 1): ?>
                    <span class="px-2 text-on-surface-variant">...</span>
                <?php endif; ?>
                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-classes&page=' . $last_page)) ?>"
                    class="px-3 py-2 rounded-lg border border-outline-variant/30 text-sm transition hover:bg-surface-container-high"
                >
                    <?= $last_page ?>
                </a>
            <?php endif; ?>

            <?php if ($current_page < $last_page): ?>
                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-classes&page=' . ($current_page + 1))) ?>"
                    class="px-3 py-2 rounded-lg border border-outline-variant/30 text-sm transition hover:bg-surface-container-high"
                >
                    Next
                </a>
            <?php endif; ?>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>