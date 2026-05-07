<?php
/*
Template Name: Staff Attendance
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$flash_success = '';
$flash_error = '';

$staff_user = $_SESSION['user'] ?? [];
$staff_id = (int)($staff_user['id'] ?? 0);
$staff_name = $staff_user['full_name'] ?? $staff_user['username'] ?? 'Staff member';

function attendance_extract_list(array $response): array
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

function attendance_value(array $row, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
            return $row[$key];
        }
    }

    return $default;
}

function attendance_is_empty_time($value): bool
{
    return $value === null
        || $value === ''
        || $value === '-'
        || $value === '—'
        || $value === 'â€”'
        || strtoupper((string)$value) === 'NULL';
}

function attendance_datetime_label($value): string
{
    if (attendance_is_empty_time($value)) {
        return '';
    }

    $ts = strtotime((string)$value);

    if (!$ts) {
        return (string)$value;
    }

    return date('d/m/Y H:i', $ts);
}

function attendance_time_label($value): string
{
    if (attendance_is_empty_time($value)) {
        return '';
    }

    $ts = strtotime((string)$value);

    if (!$ts) {
        return (string)$value;
    }

    return date('H:i', $ts);
}

function attendance_load_rows(int $page): array
{
    $paged = fitapp_api_get_page('/attendance', $page, 10, true, ['mine' => 1]);

    return [$paged['response'], $paged['items']];
}

/**
 * Procesar Clock In / Clock Out
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action_type'] ?? '';

    if ($action === 'clock_in') {
        $clock_response = api_post('/attendance/clock-in', [], auth: true);

        if (($clock_response['result'] ?? false) !== false) {
            $flash_success = 'Clock in registrado correctamente.';
        } else {
            $flash_error = api_message($clock_response) ?: 'No se pudo registrar el clock in.';
        }
    }

    if ($action === 'clock_out') {
        $attendance_id = (int)($_POST['attendance_id'] ?? 0);

        if ($attendance_id > 0) {
            $clock_response = api_post('/attendance/' . $attendance_id . '/clock-out', [], auth: true);

            if (($clock_response['result'] ?? false) !== false) {
                $flash_success = 'Clock out registrado correctamente.';
            } else {
                $flash_error = api_message($clock_response) ?: 'No se pudo registrar el clock out.';
            }
        } else {
            $flash_error = 'No se encontró un fichaje abierto para cerrar.';
        }
    }
}

/**
 * Cargar asistencia después de procesar acciones
 */
[$response, $rows] = attendance_load_rows($page);

/**
 * Buscar fichaje abierto de hoy
 */
$today = date('Y-m-d');
$open_attendance = null;
$today_rows = [];

foreach ($rows as $row) {
    $row_staff_id = (int)(
        $row['staff_id']
        ?? $row['staff']['id']
        ?? $row['user_id']
        ?? 0
    );

    $row_date = (string)attendance_value($row, ['date'], '');
    $clock_in = attendance_value($row, ['clock_in', 'clock_in_time', 'clock_in_at'], '');
    $clock_out = attendance_value($row, ['clock_out', 'clock_out_time', 'clock_out_at'], null);

    $is_today = $row_date === $today;

    if (!$is_today && !attendance_is_empty_time($clock_in)) {
        $ts = strtotime((string)$clock_in);
        $is_today = $ts ? date('Y-m-d', $ts) === $today : false;
    }

    $belongs_to_user = $staff_id <= 0 || $row_staff_id <= 0 || $row_staff_id === $staff_id;
    $is_open = attendance_is_empty_time($clock_out);

    if ($is_today && $belongs_to_user) {
        $today_rows[] = $row;

        if ($is_open && !$open_attendance) {
            $open_attendance = $row;
        }
    }
}

$open_attendance_id = (int)($open_attendance['id'] ?? 0);
$open_clock_in = $open_attendance
    ? attendance_value($open_attendance, ['clock_in', 'clock_in_time', 'clock_in_at'], '')
    : '';

$history_rows = array_slice($rows, 0, 10);

wp_app_page_start('Staff Attendance', true);
?>

<?php if (($response['result'] ?? null) === false): ?>
    <?php show_error(api_message($response)); ?>
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
            <h2 class="text-lg font-bold">Clock In / Out</h2>
            <p class="text-sm text-on-surface-variant">
                Registra tu entrada y salida del turno de trabajo.
            </p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-dashboard')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to dashboard
        </a>
    </section>

    <section class="grid grid-cols-1 gap-4 lg:grid-cols-12">

        <article class="rounded-3xl border border-outline-variant/20 bg-surface-container p-6 shadow-lg lg:col-span-5">
            <div class="flex flex-col items-center justify-center text-center">

                <div class="mb-5 flex h-24 w-24 items-center justify-center rounded-full <?= $open_attendance ? 'bg-primary-container text-on-primary-container' : 'bg-surface-container-high text-primary-container' ?>">
                    <span class="material-symbols-outlined text-5xl">
                        <?= $open_attendance ? 'timer' : 'fingerprint' ?>
                    </span>
                </div>

                <p class="text-xs font-black uppercase tracking-[0.25em] text-primary-container">
                    Current status
                </p>

                <?php if ($open_attendance): ?>
                    <h3 class="mt-2 font-headline text-3xl font-black uppercase tracking-tight">
                        Clocked In
                    </h3>

                    <p class="mt-2 text-sm text-on-surface-variant">
                        Entrada registrada a las
                        <span class="font-bold text-on-surface">
                            <?= h(attendance_time_label($open_clock_in)) ?>
                        </span>
                    </p>

                    <form method="post" class="mt-6">
                        <input type="hidden" name="action_type" value="clock_out">
                        <input type="hidden" name="attendance_id" value="<?= $open_attendance_id ?>">

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-error/40 px-6 py-3 text-sm font-black uppercase tracking-wide text-error transition hover:bg-error/10"
                        >
                            Clock Out
                            <span class="material-symbols-outlined text-base">logout</span>
                        </button>
                    </form>
                <?php else: ?>
                    <h3 class="mt-2 font-headline text-3xl font-black uppercase tracking-tight">
                        Not Clocked In
                    </h3>

                    <p class="mt-2 text-sm text-on-surface-variant">
                        Todavía no has iniciado tu turno hoy.
                    </p>

                    <form method="post" class="mt-6">
                        <input type="hidden" name="action_type" value="clock_in">

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary-container px-6 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition hover:scale-[1.01] hover:brightness-105"
                        >
                            Clock In
                            <span class="material-symbols-outlined text-base">login</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </article>

        <article class="rounded-3xl border border-outline-variant/20 bg-surface-container p-6 shadow-lg lg:col-span-7">
            <div class="mb-5">
                <p class="text-xs font-black uppercase tracking-[0.25em] text-primary-container">
                    Today
                </p>
                <h3 class="mt-1 text-xl font-bold">Today's attendance</h3>
            </div>

            <?php if ($today_rows): ?>
                <div class="space-y-3">
                    <?php foreach ($today_rows as $row): ?>
                        <?php
                        $clock_in = attendance_value($row, ['clock_in', 'clock_in_time', 'clock_in_at'], '');
                        $clock_out = attendance_value($row, ['clock_out', 'clock_out_time', 'clock_out_at'], null);
                        $gym_name = '';

                        if (!empty($row['gym']) && is_array($row['gym'])) {
                            $gym_name = $row['gym']['name'] ?? '';
                        } elseif (!empty($row['gym_name'])) {
                            $gym_name = $row['gym_name'];
                        } elseif (!empty($row['gym_id'])) {
                            $gym_name = 'Gym #' . (int)$row['gym_id'];
                        }
                        ?>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="font-bold">
                                        <?= h($staff_name) ?>
                                    </p>
                                    <?php if (h($gym_name) !== ''): ?>
                                        <p class="text-xs text-on-surface-variant">
                                            <?= h($gym_name) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-primary-container/10 px-3 py-1 font-black uppercase tracking-wide text-primary-container">
                                        In: <?= h(attendance_time_label($clock_in)) ?>
                                    </span>

                                    <span class="rounded-full border border-outline-variant/30 px-3 py-1 font-black uppercase tracking-wide <?= attendance_is_empty_time($clock_out) ? 'text-primary-container' : 'text-on-surface-variant' ?>">
                                        Out: <?= attendance_is_empty_time($clock_out) ? 'Pending' : h(attendance_time_label($clock_out)) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                    <p class="text-sm text-on-surface-variant">
                        No attendance records for today yet.
                    </p>
                </div>
            <?php endif; ?>
        </article>

    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-6 shadow-lg">
        <div class="mb-5">
            <p class="text-xs font-black uppercase tracking-[0.25em] text-primary-container">
                History
            </p>
            <h3 class="mt-1 text-xl font-bold">Recent attendance records</h3>
        </div>

        <div class="space-y-3">
            <?php foreach ($history_rows as $row): ?>
                <?php
                $clock_in = attendance_value($row, ['clock_in', 'clock_in_time', 'clock_in_at'], '');
                $clock_out = attendance_value($row, ['clock_out', 'clock_out_time', 'clock_out_at'], null);
                $row_date = attendance_value($row, ['date'], '');
                ?>

                <article class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-bold">
                                <?= h($row_date ?: attendance_datetime_label($clock_in)) ?>
                            </p>
                            <p class="text-xs text-on-surface-variant">
                                Attendance record #<?= h((string)($row['id'] ?? '')) ?>
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="rounded-full bg-primary-container/10 px-3 py-1 font-black uppercase tracking-wide text-primary-container">
                                In: <?= h(attendance_time_label($clock_in)) ?>
                            </span>

                            <span class="rounded-full border border-outline-variant/30 px-3 py-1 font-black uppercase tracking-wide <?= attendance_is_empty_time($clock_out) ? 'text-primary-container' : 'text-on-surface-variant' ?>">
                                Out: <?= attendance_is_empty_time($clock_out) ? 'Pending' : h(attendance_time_label($clock_out)) ?>
                            </span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if (!$history_rows): ?>
                <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                    <p class="text-sm text-on-surface-variant">
                        No attendance records yet.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>

</div>

<?php
wp_app_page_end(true);
?>
