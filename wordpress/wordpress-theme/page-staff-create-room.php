<?php
/*
Template Name: Staff Create Room
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$create_room_url = home_url('/?pagename=staff-create-room');
$manage_rooms_url = home_url('/?pagename=staff-rooms');

function room_create_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
}

function extract_room_list(array $response): array
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
$gyms = extract_room_list($gyms_response);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_room_submit'])) {
    $payload = [
        'name' => trim((string)($_POST['room_name'] ?? '')),
        'gym_id' => !empty($_POST['gym_id']) ? (int)$_POST['gym_id'] : null,
        'capacity' => !empty($_POST['capacity']) ? (int)$_POST['capacity'] : null,
        'floor' => trim((string)($_POST['floor'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== null && $value !== '-' && $value !== '—';
    });

    $create_response = fitapp_api_multipart_post('/rooms', $payload, $_FILES['image'] ?? null, 'image', true);

    if (($create_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_rooms_url . '&notice=created');
        exit;
    }

    $flash_error = api_message($create_response) ?: 'No se pudo crear la sala. Revisa los campos obligatorios.';
}

wp_app_page_start('Create Room', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Room</h2>
            <p class="text-sm text-on-surface-variant">
                Crea una nueva sala asociada a un gimnasio.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_rooms_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to rooms
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" action="<?= esc_url($create_room_url) ?>" enctype="multipart/form-data" class="space-y-6">

            <input type="hidden" name="create_room_submit" value="1">

            <?php fitapp_render_image_dropzone('Room image', 'Upload room image', 'roomImageInput', 'roomDropzone', 'image', '', 'Room image preview', 'meeting_room'); ?>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Room name
                </label>

                <input
                    type="text"
                    name="room_name"
                    value="<?= h(room_create_value('room_name')) ?>"
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

                <?php $selected_gym = (int)room_create_value('gym_id', 0); ?>

                <select
                    name="gym_id"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    required
                >
                    <option value="">Select gym</option>

                    <?php foreach ($gyms as $gym): ?>
                        <?php $gym_id = (int)($gym['id'] ?? 0); ?>
                        <option value="<?= $gym_id ?>" <?= $selected_gym === $gym_id ? 'selected' : '' ?>>
                            <?= h($gym['name'] ?? 'Gym') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Capacity
                    </label>

                    <input
                        type="number"
                        name="capacity"
                        min="1"
                        value="<?= h(room_create_value('capacity')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: 25"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Floor
                    </label>

                    <input
                        type="text"
                        name="floor"
                        value="<?= h(room_create_value('floor')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: Main floor"
                    >
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Description
                </label>

                <textarea
                    name="description"
                    rows="5"
                    maxlength="280"
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Describe la sala, su uso principal o características..."
                ><?= h(room_create_value('description')) ?></textarea>

                <p class="mt-1 text-xs text-on-surface-variant">
                    Opcional. Máximo 280 caracteres.
                </p>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Room
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

</div>

<?php fitapp_render_image_dropzone_script('roomImageInput', 'roomDropzone'); ?>

<?php
wp_app_page_end(true);
?>
