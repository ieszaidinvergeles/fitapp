<?php
/*
Template Name: Staff Manage Classes
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

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
        }

        $flash_error = api_message($delete_response) ?: 'No se pudo eliminar la clase.';
    }
}

/**
 * Helpers API
 */
if (!function_exists('extract_classes_from_response')) {
    function extract_classes_from_response(array $response): array
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
}

if (!function_exists('extract_list_from_response')) {
    function extract_list_from_response(array $response): array
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
}

if (!function_exists('build_lookup_by_id')) {
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
}

if (!function_exists('response_last_page')) {
    function response_last_page(array $response): ?int
    {
        $last_page = $response['result']['meta']['last_page']
            ?? $response['result']['last_page']
            ?? null;

        return $last_page !== null ? max(1, (int)$last_page) : null;
    }
}

if (!function_exists('class_page_url')) {
    function class_page_url(int $page): string
    {
        return home_url('/?pagename=staff-manage-classes&page_num=' . $page);
    }
}

if (!function_exists('class_clean_value')) {
    function class_clean_value($value, string $default = ''): string
    {
        $value = trim((string)$value);

        if ($value === '' || $value === '-' || $value === '—' || $value === 'â€”' || strtoupper($value) === 'NULL') {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('class_datetime_label')) {
    function class_datetime_label(array $class_data = null, string $field): string
    {
        $raw = class_clean_value($class_data[$field] ?? '');

        if ($raw === '') {
            return '';
        }

        $ts = strtotime($raw);

        if (!$ts) {
            return $raw;
        }

        return date('d/m/Y H:i', $ts);
    }
}

/**
 * Cargar TODAS las clases.
 */
$all_classes = [];
$seen_ids = [];
$listResp = ['result' => []];

for ($api_page = 1; $api_page <= 100; $api_page++) {
    $response = api_get('/classes?include_past=1&page=' . $api_page, auth: true);

    if (($response['result'] ?? null) === false) {
        $listResp = $response;
        break;
    }

    $items = extract_classes_from_response($response);

    if (empty($items)) {
        break;
    }

    $added_this_page = 0;

    foreach ($items as $item) {
        $id = (int)($item['id'] ?? 0);

        if ($id > 0 && isset($seen_ids[$id])) {
            continue;
        }

        if ($id > 0) {
            $seen_ids[$id] = true;
        }

        $all_classes[] = $item;
        $added_this_page++;
    }

    $listResp = $response;

    $last_api_page = response_last_page($response);

    if ($added_this_page === 0 || ($last_api_page !== null && $api_page >= $last_api_page)) {
        break;
    }
}

/**
 * Fallback por si el endpoint real es otro.
 */
if (!$all_classes && (($listResp['result'] ?? null) !== false)) {
    for ($api_page = 1; $api_page <= 100; $api_page++) {
        $response = api_get('/gym-classes?include_past=1&page=' . $api_page, auth: true);

        if (($response['result'] ?? null) === false) {
            $listResp = $response;
            break;
        }

        $items = extract_classes_from_response($response);

        if (empty($items)) {
            break;
        }

        $added_this_page = 0;

        foreach ($items as $item) {
            $id = (int)($item['id'] ?? 0);

            if ($id > 0 && isset($seen_ids[$id])) {
                continue;
            }

            if ($id > 0) {
                $seen_ids[$id] = true;
            }

            $all_classes[] = $item;
            $added_this_page++;
        }

        $listResp = $response;

        $last_api_page = response_last_page($response);

        if ($added_this_page === 0 || ($last_api_page !== null && $api_page >= $last_api_page)) {
            break;
        }
    }
}

/**
 * Ordenar clases:
 * 1. Primero próximas clases, de más cercana a más lejana.
 * 2. Después clases pasadas, de más reciente a más antigua.
 */
usort($all_classes, function ($a, $b) {
    $now = time();

    $timeA = strtotime((string)($a['start_time'] ?? '')) ?: 0;
    $timeB = strtotime((string)($b['start_time'] ?? '')) ?: 0;

    $aIsFuture = $timeA >= $now;
    $bIsFuture = $timeB >= $now;

    if ($aIsFuture && !$bIsFuture) {
        return -1;
    }

    if (!$aIsFuture && $bIsFuture) {
        return 1;
    }

    if ($aIsFuture && $bIsFuture) {
        return $timeA <=> $timeB;
    }

    return $timeB <=> $timeA;
});

/**
 * Paginación local.
 */
$total_classes = count($all_classes);
$last_page = max(1, (int)ceil($total_classes / $per_page));

if ($page > $last_page) {
    $page = $last_page;
}

$current_page = $page;
$offset = ($current_page - 1) * $per_page;
$classes = array_slice($all_classes, $offset, $per_page);

$from = $total_classes > 0 ? $offset + 1 : 0;
$to = $total_classes > 0 ? min($total_classes, $offset + count($classes)) : 0;
$upcoming_total = 0;
$past_total = 0;
$now_ts = time();

foreach ($all_classes as $class_item) {
    $class_ts = strtotime((string)($class_item['start_time'] ?? '')) ?: 0;

    if ($class_ts >= $now_ts) {
        $upcoming_total++;
    } else {
        $past_total++;
    }
}

/**
 * Cargar relaciones.
 */
$activities_response = api_get('/activities', auth: true);
$rooms_response = api_get('/rooms', auth: true);
$gyms_response = api_get('/gyms', auth: true);
$users_response = api_get('/users', auth: true);

$activities = extract_list_from_response($activities_response);
$rooms = extract_list_from_response($rooms_response);
$gyms = extract_list_from_response($gyms_response);
$users_lookup = extract_list_from_response($users_response);

$activities_by_id = build_lookup_by_id($activities);
$rooms_by_id = build_lookup_by_id($rooms);
$gyms_by_id = build_lookup_by_id($gyms);
$users_by_id = build_lookup_by_id($users_lookup);

/**
 * Cargar bookings.
 */
$all_bookings = [];
$seen_booking_ids = [];

for ($api_page = 1; $api_page <= 50; $api_page++) {
    $bookings_response = api_get('/bookings?page=' . $api_page, auth: true);

    if (($bookings_response['result'] ?? null) === false) {
        break;
    }

    $booking_items = extract_list_from_response($bookings_response);

    if (empty($booking_items)) {
        break;
    }

    $added_this_page = 0;

    foreach ($booking_items as $booking) {
        $booking_id = (int)($booking['id'] ?? 0);

        if ($booking_id > 0 && isset($seen_booking_ids[$booking_id])) {
            continue;
        }

        if ($booking_id > 0) {
            $seen_booking_ids[$booking_id] = true;
        }

        $all_bookings[] = $booking;
        $added_this_page++;
    }

    if ($added_this_page === 0 || count($booking_items) < 10) {
        break;
    }
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

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Class List</h2>
            <p class="text-sm text-on-surface-variant">
                Gestiona clases, revisa su estado o cancela sesiones.
            </p>

            <?php if ($total_classes > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total_classes) ?> CLASSES REGISTERED | PAGE <?= h((string)$current_page) ?> OF <?= h((string)$last_page) ?>
                </p>
            <?php endif; ?>
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
        <?php $visible_class_group = null; ?>
        <?php foreach ($classes as $c): ?>
            <?php
            $class_id = (int)($c['id'] ?? 0);
            $class_timestamp = strtotime((string)($c['start_time'] ?? '')) ?: 0;
            $class_group = $class_timestamp >= time() ? 'upcoming' : 'past';

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

            $class_name = class_clean_value(
                $activity['name']
                ?? $c['activity_name']
                ?? $c['name']
                ?? $c['title']
                ?? $c['class_name']
                ?? 'Class',
                'Class'
            );

            $start_label = class_datetime_label($c, 'start_time');
            $end_label = class_datetime_label($c, 'end_time');

            $room_name = class_clean_value($room['name'] ?? $c['room_name'] ?? '');
            $capacity = class_clean_value($c['capacity_limit'] ?? $c['capacity'] ?? $c['max_capacity'] ?? '');
            $instructor = class_clean_value($instructorData['full_name'] ?? $instructorData['username'] ?? $instructorData['email'] ?? '');
            $gym_name = class_clean_value($gym['name'] ?? $c['gym_name'] ?? '');

            $time_text = '';
            if ($start_label && $end_label) {
                $time_text = $start_label . ' - ' . $end_label;
            } elseif ($start_label) {
                $time_text = $start_label;
            }

            $location_bits = [];
            if ($room_name !== '') {
                $location_bits[] = 'Room: ' . $room_name;
            }
            if ($capacity !== '') {
                $location_bits[] = 'Capacity: ' . $capacity;
            }

            $staff_bits = [];
            if ($instructor !== '') {
                $staff_bits[] = 'Instructor: ' . $instructor;
            }
            if ($gym_name !== '') {
                $staff_bits[] = 'Gym: ' . $gym_name;
            }

            $bookings_count = 0;
            foreach ($all_bookings as $booking) {
                if ((int)($booking['class_id'] ?? 0) === $class_id) {
                    $bookings_count++;
                }
            }

            $is_cancelled = !empty($c['is_cancelled']);
            ?>

            <?php if ($visible_class_group !== $class_group): ?>
                <?php $visible_class_group = $class_group; ?>
                <div class="pt-2">
                    <div class="flex items-center gap-3">
                        <span class="h-px flex-1 bg-outline-variant/20"></span>
                        <span class="rounded-full border border-outline-variant/30 bg-surface-container-high px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.22em] text-primary-container">
                            <?= $class_group === 'upcoming'
                                ? 'Upcoming classes (' . h((string)$upcoming_total) . ')'
                                : 'Past classes (' . h((string)$past_total) . ')' ?>
                        </span>
                        <span class="h-px flex-1 bg-outline-variant/20"></span>
                    </div>
                </div>
            <?php endif; ?>

            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-bold text-lg break-words">
                                <?= h($class_name) ?>
                            </p>

                            <span class="rounded-full bg-primary-container/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-primary-container">
                                #<?= h((string)$class_id) ?>
                            </span>
                        </div>

                        <?php if ($time_text): ?>
                            <p class="mt-1 text-sm text-on-surface-variant break-words">
                                <?= h($time_text) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($location_bits): ?>
                            <p class="mt-1 text-sm text-on-surface-variant break-words">
                                <?= h(implode(' | ', $location_bits)) ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($staff_bits): ?>
                            <p class="mt-1 text-sm text-on-surface-variant break-words">
                                <?= h(implode(' | ', $staff_bits)) ?>
                            </p>
                        <?php endif; ?>

                        <p class="mt-1 text-sm text-on-surface-variant break-words">
                            Bookings:
                            <span class="font-semibold text-on-surface"><?= (string)$bookings_count ?></span>
                        </p>

                        <p class="mt-1 text-sm <?= $is_cancelled ? 'text-error' : 'text-primary-container' ?>">
                            <?= $is_cancelled ? 'Cancelled' : 'Active' ?>
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[340px] lg:justify-end">
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
        <section class="flex flex-col gap-4 rounded-xl border border-outline-variant/20 bg-surface-container p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-on-surface-variant">
                Showing
                <span class="font-bold text-on-surface"><?= h((string)$from) ?></span>
                -
                <span class="font-bold text-on-surface"><?= h((string)$to) ?></span>
                of
                <span class="font-bold text-on-surface"><?= h((string)$total_classes) ?></span>
                classes
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">

                <?php if ($current_page > 1): ?>
                    <a
                        href="<?= esc_url(class_page_url($current_page - 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        &larr; Previous
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        &larr; Previous
                    </span>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end = min($last_page, $current_page + 2);
                ?>

                <?php if ($start > 1): ?>
                    <a
                        href="<?= esc_url(class_page_url(1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        1
                    </a>

                    <?php if ($start > 2): ?>
                        <span class="px-1 text-sm text-on-surface-variant">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a
                        href="<?= esc_url(class_page_url($i)) ?>"
                        class="rounded-full border px-4 py-2 text-sm font-bold transition <?= $i === $current_page
                            ? 'border-primary-container bg-primary-container text-on-primary-container shadow-[0_0_18px_rgba(212,251,0,0.22)]'
                            : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                    >
                        <?= h((string)$i) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($end < $last_page): ?>
                    <?php if ($end < $last_page - 1): ?>
                        <span class="px-1 text-sm text-on-surface-variant">...</span>
                    <?php endif; ?>

                    <a
                        href="<?= esc_url(class_page_url($last_page)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        <?= h((string)$last_page) ?>
                    </a>
                <?php endif; ?>

                <?php if ($current_page < $last_page): ?>
                    <a
                        href="<?= esc_url(class_page_url($current_page + 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        Next &rarr;
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        Next &rarr;
                    </span>
                <?php endif; ?>

            </div>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
