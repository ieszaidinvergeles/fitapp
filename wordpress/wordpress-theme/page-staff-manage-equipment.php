<?php
/*
Template Name: Staff Manage Equipment
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'deleted') {
    $flash_success = 'Equipment deleted successfully.';
} elseif ($notice === 'created') {
    $flash_success = 'Equipment created successfully.';
} elseif ($notice === 'updated') {
    $flash_success = 'Equipment updated successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $equipment_id = (int)($_POST['equipment_id'] ?? 0);

    if ($equipment_id > 0) {
        $delete_response = api_delete('/equipment/' . $equipment_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_safe_redirect(home_url('/?pagename=staff-manage-equipment&notice=deleted'));
            exit;
        }

        $flash_error = api_message($delete_response) ?: 'Could not delete the equipment.';
    }
}

if (!function_exists('equipment_extract_list')) {
    function equipment_extract_list(array $response): array
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

if (!function_exists('equipment_value')) {
    function equipment_value(array $item, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (!isset($item[$key]) || $item[$key] === null) {
                continue;
            }

            $clean_value = trim((string)$item[$key]);

            if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€”' && strtoupper($clean_value) !== 'NULL') {
                return $item[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('equipment_page_url')) {
    function equipment_page_url(int $page): string
    {
        return home_url('/?pagename=staff-manage-equipment&page_num=' . $page);
    }
}

if (!function_exists('equipment_home_access_label')) {
    function equipment_home_access_label(array $item): string
    {
        return !empty($item['is_home_accessible']) ? 'Available for home workouts' : 'Gym only';
    }
}

/*
|--------------------------------------------------------------------------
| Cargar todo el equipamiento
|--------------------------------------------------------------------------
| If the API paginates in batches of 10, we walk pages and then paginate here.
*/
$paged = fitapp_api_get_page('/equipment', $page, $per_page, true);
$all_equipment = [];
$seen_ids = [];
$listResp = ['result' => []];

for ($api_page = 1; $api_page <= 0; $api_page++) {
    $response = api_get('/equipment?page=' . $api_page, auth: true);

    if (($response['result'] ?? null) === false) {
        $listResp = $response;
        break;
    }

    $items = equipment_extract_list($response);

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

        $all_equipment[] = $item;
        $added_this_page++;
    }

    $listResp = $response;

    if ($added_this_page === 0 || count($items) < 10) {
        break;
    }
}

$total = count($all_equipment);
$last_page = max(1, (int)ceil($total / $per_page));

if ($page > $last_page) {
    $page = $last_page;
}

$current_page = $page;
$offset = ($current_page - 1) * $per_page;
$equipment_items = array_slice($all_equipment, $offset, $per_page);

$from = $total > 0 ? $offset + 1 : 0;
$to = $total > 0 ? min($total, $offset + count($equipment_items)) : 0;

$listResp = $paged['response'];
$equipment_items = $paged['items'];
$pagination = $paged['meta'];
$current_page = $pagination['current_page'];
$last_page = $pagination['last_page'];
$total = $pagination['total'];
$from = $pagination['from'];
$to = $pagination['to'];

wp_app_page_start('Manage Equipment', true);
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
            <h2 class="text-lg font-bold">Equipment List</h2>
            <p class="text-sm text-on-surface-variant">
                Manage machines, sports gear, and gym equipment.
            </p>

            <?php if ($total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> EQUIPMENT ITEMS REGISTERED · PAGE <?= h((string)$current_page) ?> OF <?= h((string)$last_page) ?>
                </p>
            <?php endif; ?>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-equipment')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
        >
            <span class="text-base leading-none">+</span>
            <span>Create equipment</span>
        </a>
    </section>

    <section class="space-y-3">
        <?php foreach ($equipment_items as $index => $item): ?>
            <?php
            $equipment_id = (int)($item['id'] ?? 0);

            $name = equipment_value($item, ['name', 'title', 'equipment_name'], 'Equipment');
            $description = h((string)equipment_value($item, ['description', 'notes', 'details'], ''));
            $has_home_access = !empty($item['is_home_accessible']);
            $home_access_label = equipment_home_access_label($item);

            $image = fitapp_public_asset_url($item['image_url']
                ?? $item['cover_image_url']
                ?? $item['image']
                ?? $item['photo_url']
                ?? '');
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <?php fitapp_render_image_or_placeholder($image, (string)$name, 'h-full w-full object-cover', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'construction', 'No image'); ?>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-lg font-bold break-words">
                                    <?= h($name) ?>
                                </p>

                                <span class="rounded-full bg-primary-container/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-primary-container">
                                    #<?= h((string)$equipment_id) ?>
                                </span>
                            </div>

                            <p class="mt-1">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wide <?= $has_home_access ? 'border-primary-container/30 text-on-surface-variant bg-surface-container-high' : 'border-outline-variant/30 text-on-surface-variant bg-surface-container-high' ?>">
                                    <?= h($home_access_label) ?>
                                </span>
                            </p>

                            <?php if ($description): ?>
                                <p class="mt-1 line-clamp-2 text-sm text-on-surface-variant break-words">
                                    <?= h((string)$description) ?>
                                </p>
                            <?php else: ?>
                                <p class="mt-1 text-sm italic text-on-surface-variant">
                                    No description available.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-equipment&id=' . $equipment_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-equipment&id=' . $equipment_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this equipment?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="equipment_id" value="<?= $equipment_id ?>">
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

        <?php if (!$equipment_items): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No equipment found.</p>
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
                <span class="font-bold text-on-surface"><?= h((string)$total) ?></span>
                equipment items
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">
                <?php if ($current_page > 1): ?>
                    <a
                        href="<?= esc_url(equipment_page_url($current_page - 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        ← Previous
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        ← Previous
                    </span>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end = min($last_page, $current_page + 2);
                ?>

                <?php if ($start > 1): ?>
                    <a
                        href="<?= esc_url(equipment_page_url(1)) ?>"
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
                        href="<?= esc_url(equipment_page_url($i)) ?>"
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
                        href="<?= esc_url(equipment_page_url($last_page)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        <?= h((string)$last_page) ?>
                    </a>
                <?php endif; ?>

                <?php if ($current_page < $last_page): ?>
                    <a
                        href="<?= esc_url(equipment_page_url($current_page + 1)) ?>"
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
