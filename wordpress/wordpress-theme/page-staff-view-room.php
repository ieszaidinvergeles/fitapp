<?php
/*
Template Name: Staff View Room
*/
require_once 'functions.php';
require_advanced();

$room_id = (int)($_GET['id'] ?? 0);

$manage_rooms_url = home_url('/?pagename=staff-rooms');
$edit_room_url = home_url('/?pagename=staff-edit-room&id=' . $room_id);

$flash_error = '';

if ($room_id <= 0) {
    wp_safe_redirect($manage_rooms_url);
    exit;
}

function room_view_value(array $room, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (!isset($room[$key]) || $room[$key] === null) {
            continue;
        }

        $clean_value = trim((string)$room[$key]);

        if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€”' && strtoupper($clean_value) !== 'NULL') {
            return $room[$key];
        }
    }

    return $default;
}

function room_extract_list(array $response): array
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

$room_response = api_get('/rooms/' . $room_id, auth: true);
$room = [];

if (($room_response['result'] ?? false) !== false && is_array($room_response['result'] ?? null)) {
    $room = $room_response['result'];
} else {
    $flash_error = api_message($room_response) ?: 'No se pudo cargar la sala.';
}

$gyms_response = api_get('/gyms', auth: true);
$gyms = room_extract_list($gyms_response);

$gyms_by_id = [];
foreach ($gyms as $gym_item) {
    if (isset($gym_item['id'])) {
        $gyms_by_id[(int)$gym_item['id']] = $gym_item;
    }
}

$name       = room_view_value($room, ['name', 'title', 'room_name'], 'Room');
$capacity   = h((string)room_view_value($room, ['capacity', 'capacity_limit', 'max_capacity'], ''));
$image_url  = fitapp_public_asset_url(room_view_value($room, ['image_url', 'cover_image_url', 'image', 'photo_url'], ''));

$gym_id   = (int)($room['gym_id'] ?? $room['gym']['id'] ?? 0);
$gym      = $room['gym'] ?? ($gyms_by_id[$gym_id] ?? []);
$gym_name = h($gym['name'] ?? room_view_value($room, ['gym_name'], ''));

$room_stat_cards = [];

if ($gym_name !== '') {
    $room_stat_cards[] = ['label' => 'Gym', 'value' => $gym_name];
}

if ($capacity !== '') {
    $room_stat_cards[] = ['label' => 'Capacity', 'value' => $capacity];
}

wp_app_page_start('View Room', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-primary-container">
                Room #<?= (int)$room_id ?>
            </p>

            <h2 class="mt-2 text-3xl font-black uppercase tracking-tight sm:text-4xl">
                <?= h($name) ?>
            </h2>

            <p class="mt-2 text-sm text-on-surface-variant">
                Vista completa de la sala seleccionada.
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <a
                href="<?= esc_url($edit_room_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
            >
                Edit room
            </a>

            <a
                href="<?= esc_url($manage_rooms_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
            >
                ← Back to rooms
            </a>
        </div>
    </section>

    <?php if ($room): ?>
        <section class="overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container shadow-lg">
            <div class="grid grid-cols-1 lg:grid-cols-[320px_minmax(0,1fr)]">

                <div class="relative min-h-[260px] overflow-hidden border-b border-outline-variant/20 bg-surface-container-high lg:border-b-0 lg:border-r">
                    <?php fitapp_render_image_or_placeholder((string)$image_url, (string)$name, 'absolute inset-0 h-full w-full object-cover', 'absolute inset-0 min-h-[260px] flex-col items-center justify-center p-8 text-center text-on-surface-variant', 'meeting_room', 'No image'); ?>
                    <?php if ($image_url): ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-background/80 via-background/15 to-transparent"></div>
                    <?php endif; ?>
                </div>

                <div class="p-5 sm:p-8">
                    <?php if ($room_stat_cards): ?>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <?php foreach ($room_stat_cards as $card): ?>
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
