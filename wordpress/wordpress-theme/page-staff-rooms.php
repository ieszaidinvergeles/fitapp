<?php
/*
Template Name: Staff Rooms
*/
require_once 'functions.php';
require_advanced();

$page     = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error   = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'deleted') {
    $flash_success = 'Sala eliminada correctamente.';
} elseif ($notice === 'created') {
    $flash_success = 'Sala creada correctamente.';
} elseif ($notice === 'updated') {
    $flash_success = 'Sala actualizada correctamente.';
}

/**
 * Eliminar sala
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $room_id = (int)($_POST['room_id'] ?? 0);

    if ($room_id > 0) {
        $delete_response = api_delete('/rooms/' . $room_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_safe_redirect(home_url('/?pagename=staff-rooms&notice=deleted'));
            exit;
        }

        $flash_error = api_message($delete_response) ?: 'No se pudo eliminar la sala.';
    }
}

if (!function_exists('room_extract_list')) {
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
}

if (!function_exists('room_value')) {
    function room_value(array $room, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (!isset($room[$key]) || $room[$key] === null) {
                continue;
            }

            $clean_value = trim((string)$room[$key]);

            if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€"' && strtoupper($clean_value) !== 'NULL') {
                return $room[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('room_page_url')) {
    function room_page_url(int $page): string
    {
        return home_url('/?pagename=staff-rooms&page_num=' . $page);
    }
}

/**
 * Cargar gimnasios para cruzar nombre de gym
 */
$gyms_response = api_get('/gyms', auth: true);
$gyms          = room_extract_list($gyms_response);

$gyms_by_id = [];
foreach ($gyms as $gym) {
    if (isset($gym['id'])) {
        $gyms_by_id[(int)$gym['id']] = $gym;
    }
}

/**
 * Cargar salas paginadas — una sola llamada al backend.
 */
$paged        = fitapp_api_get_page('/rooms', $page, $per_page, true);
$listResp     = $paged['response'];
$rooms        = $paged['items'];
$pagination   = $paged['meta'];
$current_page = $pagination['current_page'];
$last_page    = $pagination['last_page'];
$total        = $pagination['total'];
$from         = $pagination['from'];
$to           = $pagination['to'];

$has_next_unknown = ($last_page <= $current_page && count($rooms) >= $per_page);

wp_app_page_start('Rooms', true);
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
            <h2 class="text-lg font-bold">Room List</h2>
            <p class="text-sm text-on-surface-variant">
                Gestiona salas, aforo y centro asociado.
            </p>

            <?php if ($total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> ROOMS REGISTERED · PAGE <?= h((string)$current_page) ?> OF <?= h((string)$last_page) ?>
                </p>
            <?php elseif ($rooms): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    Room list · Page <?= h((string)$current_page) ?>
                </p>
            <?php endif; ?>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-room')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
        >
            <span class="text-base leading-none">+</span>
            <span>Create room</span>
        </a>
    </section>

    <section class="space-y-3">
        <?php foreach ($rooms as $room): ?>
            <?php
            $room_id   = (int)($room['id'] ?? 0);
            $name      = room_value($room, ['name', 'title', 'room_name'], 'Room');
            $capacity  = room_value($room, ['capacity', 'capacity_limit', 'max_capacity'], '');
            $image_url = fitapp_public_asset_url(room_value($room, ['image_url', 'cover_image_url', 'image', 'photo_url'], ''));

            $gym_id   = (int)($room['gym_id'] ?? $room['gym']['id'] ?? 0);
            $gym      = $room['gym'] ?? ($gyms_by_id[$gym_id] ?? []);
            $gym_name = h($gym['name'] ?? room_value($room, ['gym_name'], ''));

            $room_meta_bits = [];

            if ($gym_name !== '') {
                $room_meta_bits[] = 'Gym: ' . $gym_name;
            }

            if ((string)$capacity !== '') {
                $room_meta_bits[] = 'Capacity: ' . h((string)$capacity);
            }
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <?php fitapp_render_image_or_placeholder((string)$image_url, (string)$name, 'h-full w-full object-cover', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'meeting_room', 'No image'); ?>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-lg font-bold break-words">
                                    <?= h($name) ?>
                                </p>

                                <span class="rounded-full bg-primary-container/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-primary-container">
                                    #<?= h((string)$room_id) ?>
                                </span>
                            </div>

                            <?php if ($room_meta_bits): ?>
                                <p class="mt-1 text-sm text-on-surface-variant break-words">
                                    <?= implode(' | ', $room_meta_bits) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-room&id=' . $room_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-room&id=' . $room_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar esta sala?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="room_id" value="<?= $room_id ?>">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg border border-error/40 px-3 py-2 text-sm text-error transition hover:bg-error/10"
                            >
                                Delete
                            </button>
                        </form>
                    </div>

                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$rooms): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No rooms found.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($current_page > 1 || $current_page < $last_page || $has_next_unknown): ?>
        <section class="flex flex-col gap-4 rounded-xl border border-outline-variant/20 bg-surface-container p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-on-surface-variant">
                Showing
                <span class="font-bold text-on-surface"><?= h((string)$from) ?></span>
                –
                <span class="font-bold text-on-surface"><?= h((string)$to) ?></span>
                <?php if ($total > 0): ?>
                of <span class="font-bold text-on-surface"><?= h((string)$total) ?></span>
                <?php endif; ?>
                rooms
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">
                <?php if ($current_page > 1): ?>
                    <a
                        href="<?= esc_url(room_page_url($current_page - 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        ← Previous
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        ← Previous
                    </span>
                <?php endif; ?>

                <?php if ($last_page > 1): ?>
                    <?php
                    $start = max(1, $current_page - 2);
                    $end   = min($last_page, $current_page + 2);
                    ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <a
                            href="<?= esc_url(room_page_url($i)) ?>"
                            class="rounded-full border px-4 py-2 text-sm font-bold transition <?= $i === $current_page
                                ? 'border-primary-container bg-primary-container text-on-primary-container shadow-[0_0_18px_rgba(212,251,0,0.22)]'
                                : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                        >
                            <?= h((string)$i) ?>
                        </a>
                    <?php endfor; ?>
                <?php else: ?>
                    <span class="rounded-full border border-primary-container bg-primary-container px-4 py-2 text-sm font-bold text-on-primary-container">
                        <?= $current_page ?>
                    </span>
                <?php endif; ?>

                <?php if ($current_page < $last_page || $has_next_unknown): ?>
                    <a
                        href="<?= esc_url(room_page_url($current_page + 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        Next →
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        Next →
                    </span>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
