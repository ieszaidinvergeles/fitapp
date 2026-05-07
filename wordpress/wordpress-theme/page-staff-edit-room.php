<?php
/*
Template Name: Staff Edit Room
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$room_id = (int)($_GET['id'] ?? $_POST['room_id'] ?? 0);

$manage_rooms_url = home_url('/?pagename=staff-rooms');
$edit_room_url = home_url('/?pagename=staff-edit-room&id=' . $room_id);

if ($room_id <= 0) {
    wp_safe_redirect($manage_rooms_url);
    exit;
}

function room_edit_value(array $room, string $key, $default = '')
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];

        if ($value === '-' || $value === '—') {
            return '';
        }

        return $value;
    }

    return $room[$key] ?? $default;
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

/**
 * Cargar sala
 */
$room_response = api_get('/rooms/' . $room_id, auth: true);
$room = [];

if (($room_response['result'] ?? false) !== false && is_array($room_response['result'] ?? null)) {
    $room = $room_response['result'];
} else {
    $flash_error = api_message($room_response) ?: 'No se pudo cargar la sala.';
}

/**
 * Cargar gimnasios
 */
$gyms_response = api_get('/gyms', auth: true);
$gyms = room_extract_list($gyms_response);

/**
 * Procesar edición
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_room_submit'])) {
    $payload = [
        'name'     => trim((string)($_POST['room_name'] ?? '')),
        'gym_id'   => !empty($_POST['gym_id']) ? (int)$_POST['gym_id'] : null,
        'capacity' => !empty($_POST['capacity']) ? (int)$_POST['capacity'] : null,
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== null;
    });

    $update_response = fitapp_api_multipart_update('/rooms/' . $room_id, $payload, $_FILES['image'] ?? null, 'image', true);

    if (($update_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_rooms_url . '&notice=updated');
        exit;
    }

    $flash_error = api_message($update_response) ?: 'No se pudo actualizar la sala.';
}

wp_app_page_start('Edit Room', true);
$current_image = fitapp_public_asset_url($room['image_url'] ?? '');
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Room</h2>
            <p class="text-sm text-on-surface-variant">
                Modifica la sala, su aforo y el gimnasio asociado.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_rooms_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to rooms
        </a>
    </section>

    <?php if ($room): ?>
        <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
            <form method="post" action="<?= esc_url($edit_room_url) ?>" enctype="multipart/form-data" class="space-y-6">

                <input type="hidden" name="edit_room_submit" value="1">
                <input type="hidden" name="room_id" value="<?= (int)$room_id ?>">

                <?php fitapp_render_image_dropzone('Room image', 'Change room image', 'roomImageInput', 'roomDropzone', 'image', $current_image, 'Room image preview', 'meeting_room'); ?>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Room name
                    </label>

                    <input
                        type="text"
                        name="room_name"
                        value="<?= h($_POST['room_name'] ?? ($room['name'] ?? '')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: Main Floor"
                        maxlength="80"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Gym
                    </label>

                    <?php
                    $current_gym_id = (int)(
                        $_POST['gym_id']
                        ?? $room['gym_id']
                        ?? $room['gym']['id']
                        ?? 0
                    );
                    ?>

                    <select
                        name="gym_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select gym</option>

                        <?php foreach ($gyms as $gym): ?>
                            <?php $gym_id = (int)($gym['id'] ?? 0); ?>
                            <option value="<?= $gym_id ?>" <?= $current_gym_id === $gym_id ? 'selected' : '' ?>>
                                <?= h($gym['name'] ?? 'Gym') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Capacity
                    </label>

                    <input
                        type="number"
                        name="capacity"
                        min="1"
                        value="<?= h($_POST['capacity'] ?? ($room['capacity'] ?? '')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: 25"
                        required
                    >
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button
                        type="submit"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                    >
                        Save Changes
                    </button>

                    <a
                        href="<?= esc_url($manage_rooms_url) ?>"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
                    >
                        Cancel
                    </a>
                </div>

            </form>
        </section>
    <?php endif; ?>

</div>

<?php fitapp_render_image_dropzone_script('roomImageInput', 'roomDropzone'); ?>

<?php
wp_app_page_end(true);
?>
