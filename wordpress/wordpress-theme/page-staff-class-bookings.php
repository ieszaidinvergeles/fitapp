<?php
/*
Template Name: Staff Class Bookings
*/
require_once 'functions.php';
require_advanced();

$class_id = (int)($_GET['id'] ?? 0);

if ($class_id <= 0) {
    wp_redirect(home_url('/?pagename=staff-manage-classes'));
    exit;
}

function booking_extract_list(array $response): array
{
    if (!empty($response['result']['data']) && is_array($response['result']['data'])) {
        return $response['result']['data'];
    }

    if (!empty($response['result']) && is_array($response['result'])) {
        return $response['result'];
    }

    return [];
}

function booking_lookup_by_id(array $items, int $id): array
{
    foreach ($items as $item) {
        if (is_array($item) && (int)($item['id'] ?? 0) === $id) {
            return $item;
        }
    }

    return [];
}

function booking_format_date($raw): string
{
    if (!$raw || !is_string($raw)) {
        return '';
    }

    $ts = strtotime($raw);
    if (!$ts) {
        return $raw;
    }

    return date('d/m/Y H:i', $ts);
}

function booking_status_label(string $status): string
{
    $labels = [
        'active' => 'Activa',
        'attended' => 'Asistió',
        'no_show' => 'No asistió',
        'cancelled' => 'Cancelada',
        'booked' => 'Reservada',
    ];

    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

/**
 * Cargar clase
 */
$class_response = api_get('/classes/' . $class_id, auth: true);
$class_data = (($class_response['result'] ?? false) !== false) ? $class_response['result'] : [];

/**
 * Cargar todas las reservas y filtrar por class_id
 */
$bookings_response = api_get('/bookings', auth: true);
$all_bookings = booking_extract_list($bookings_response);

$bookings = array_values(array_filter($all_bookings, function ($booking) use ($class_id) {
    return (int)($booking['class_id'] ?? 0) === $class_id;
}));

/**
 * Cargar usuarios
 */
$users_response = api_get('/users', auth: true);
$users = booking_extract_list($users_response);

/**
 * Cargar actividades
 */
$activities_response = api_get('/activities', auth: true);
$activities = booking_extract_list($activities_response);

$activity_id = (int)(
    $class_data['activity_id']
    ?? $class_data['activity']['id']
    ?? 0
);

$activity = $class_data['activity'] ?? booking_lookup_by_id($activities, $activity_id);

$class_name = $activity['name']
    ?? $class_data['activity_name']
    ?? $class_data['name']
    ?? $class_data['title']
    ?? $class_data['class_name']
    ?? 'Class';

$total_bookings = count($bookings);

wp_app_page_start('Class Bookings', true);
?>

<?php if (($class_response['result'] ?? null) === false): ?>
    <?php show_error(api_message($class_response)); ?>
<?php endif; ?>

<?php if (($bookings_response['result'] ?? null) === false): ?>
    <?php show_error(api_message($bookings_response)); ?>
<?php endif; ?>

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Class Bookings</h2>
            <p class="text-sm text-on-surface-variant">
                Reservas asociadas a:
                <span class="font-semibold text-on-surface"><?= h($class_name) ?></span>
            </p>
            <p class="mt-1 text-sm text-primary-container font-semibold">
                <?= $total_bookings ?> <?= $total_bookings === 1 ? 'reserva encontrada' : 'reservas encontradas' ?>
            </p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-manage-classes')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to classes
        </a>
    </div>

    <section class="space-y-3">
        <?php foreach ($bookings as $booking): ?>
            <?php
            $user_id = (int)($booking['user_id'] ?? 0);
            $user_data = booking_lookup_by_id($users, $user_id);

            $user_name = $user_data['full_name']
                ?? $user_data['username']
                ?? 'User #' . $user_id;

            $user_email = $user_data['email'] ?? '';

            $status = strtolower((string)($booking['status'] ?? 'booked'));
            $status_label = booking_status_label($status);

            $booked_at = booking_format_date($booking['booked_at'] ?? '');
            $cancelled_at = booking_format_date($booking['cancelled_at'] ?? '');
            $booking_meta_bits = [];

            if ($booked_at !== '') {
                $booking_meta_bits[] = 'Reserva realizada: ' . h($booked_at);
            }

            if ($cancelled_at !== '') {
                $booking_meta_bits[] = 'Cancelada: ' . h($cancelled_at);
            }
            ?>

            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-bold text-lg"><?= h($user_name) ?></p>
                        <?php if ($user_email !== ''): ?>
                            <p class="text-sm text-on-surface-variant"><?= h($user_email) ?></p>
                        <?php endif; ?>

                        <?php if ($booking_meta_bits): ?>
                            <p class="text-xs text-on-surface-variant mt-1">
                                <?= implode(' | ', $booking_meta_bits) ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide <?= $status === 'cancelled' ? 'border border-error/40 text-error' : 'bg-primary-container text-on-primary-container' ?>">
                        <?= h($status_label) ?>
                    </span>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$bookings): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No hay reservas para esta clase.</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
wp_app_page_end(true);
?>
