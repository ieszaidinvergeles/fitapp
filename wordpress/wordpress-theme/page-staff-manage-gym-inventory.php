<?php
/*
Template Name: Staff Manage Gym Inventory
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'deleted') {
    $flash_success = 'Inventory item deleted successfully.';
} elseif ($notice === 'created') {
    $flash_success = 'Inventory item created successfully.';
} elseif ($notice === 'updated') {
    $flash_success = 'Inventory item updated successfully.';
}

function staff_inventory_value(array $item, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (!isset($item[$key]) || $item[$key] === null) {
            continue;
        }

        $text = trim((string)$item[$key]);

        if ($text !== '' && $text !== '-' && $text !== 'â€”' && strtoupper($text) !== 'NULL') {
            return $item[$key];
        }
    }

    return $default;
}

function staff_inventory_page_url(int $page): string
{
    return home_url('/?pagename=staff-manage-gym-inventory&page_num=' . max(1, $page));
}

function staff_inventory_lookup(array $items): array
{
    $map = [];

    foreach ($items as $item) {
        $id = (int)($item['id'] ?? 0);

        if ($id > 0) {
            $map[$id] = $item;
        }
    }

    return $map;
}

function staff_inventory_status_label(string $status): string
{
    return [
        'operational' => 'Operational',
        'maintenance' => 'Maintenance',
        'retired' => 'Retired',
    ][$status] ?? ucwords(str_replace('_', ' ', $status));
}

function staff_inventory_status_class(string $status): string
{
    return match ($status) {
        'operational' => 'border-primary-container/30 bg-primary-container/10 text-primary-container',
        'maintenance' => 'border-yellow-400/30 bg-yellow-400/10 text-yellow-300',
        'retired' => 'border-error/40 bg-error/10 text-error',
        default => 'border-outline-variant/30 bg-surface-container-high text-on-surface-variant',
    };
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $gym_id = (int)($_POST['gym_id'] ?? 0);
    $equipment_id = (int)($_POST['equipment_id'] ?? 0);

    if ($gym_id > 0 && $equipment_id > 0) {
        $delete_response = api_delete('/gym-inventory/' . $gym_id . '/' . $equipment_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_safe_redirect(home_url('/?pagename=staff-manage-gym-inventory&notice=deleted'));
            exit;
        }

        $flash_error = api_message($delete_response) ?: 'Could not delete this inventory item.';
    }
}

$paged = fitapp_api_get_page('/gym-inventory', $page, $per_page, true);
$inventory_response = $paged['response'];
$inventory_items = $paged['items'];
$pagination = $paged['meta'];

$gyms = fitapp_api_get_page('/gyms', 1, 50, true)['items'];
$equipment_items = fitapp_api_get_page('/equipment', 1, 50, true)['items'];
$gyms_by_id = staff_inventory_lookup($gyms);
$equipment_by_id = staff_inventory_lookup($equipment_items);

$current_page = $pagination['current_page'];
$last_page = $pagination['last_page'];
$total = $pagination['total'];
$from = $pagination['from'];
$to = $pagination['to'];

wp_app_page_start('Manage Gym Inventory', true);
?>

<?php if (($inventory_response['result'] ?? null) === false): ?>
    <?php show_error(api_message($inventory_response)); ?>
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
            <h2 class="text-lg font-bold">Gym Equipment Inventory</h2>
            <p class="text-sm text-on-surface-variant">
                Manage equipment quantity and status by gym.
            </p>

            <?php if ($total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> ITEMS REGISTERED · PAGE <?= h((string)$current_page) ?> OF <?= h((string)$last_page) ?>
                </p>
            <?php endif; ?>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-gym-inventory')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
        >
            <span class="text-base leading-none">+</span>
            <span>Create inventory item</span>
        </a>
    </section>

    <section class="space-y-3">
        <?php foreach ($inventory_items as $item): ?>
            <?php
            $gym_id = (int)($item['gym_id'] ?? 0);
            $equipment_id = (int)($item['equipment_id'] ?? 0);
            $gym = is_array($item['gym'] ?? null) ? $item['gym'] : ($gyms_by_id[$gym_id] ?? []);
            $equipment = is_array($item['equipment'] ?? null) ? $item['equipment'] : ($equipment_by_id[$equipment_id] ?? []);

            $gym_name = staff_inventory_value($gym, ['name'], 'Gym #' . $gym_id);
            $equipment_name = staff_inventory_value($equipment, ['name'], 'Equipment #' . $equipment_id);
            $equipment_description = staff_inventory_value($equipment, ['description'], '');
            $quantity = (int)($item['quantity'] ?? 0);
            $status = h(staff_inventory_value($item, ['status'], 'operational'));
            $image = fitapp_public_asset_url(staff_inventory_value($equipment, ['image_url', 'image', 'photo_url'], ''));
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <?php fitapp_render_image_or_placeholder($image, (string)$equipment_name, 'h-full w-full object-cover', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'inventory_2', 'No image'); ?>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-lg font-bold break-words">
                                    <?= h($equipment_name) ?>
                                </p>

                                <span class="rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wide <?= staff_inventory_status_class($status) ?>">
                                    <?= h(staff_inventory_status_label($status)) ?>
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-on-surface-variant">
                                Gym:
                                <span class="font-bold text-on-surface"><?= h($gym_name) ?></span>
                                · Quantity:
                                <span class="font-bold text-on-surface"><?= h((string)$quantity) ?></span>
                            </p>

                            <?php if (h((string)$equipment_description) !== ''): ?>
                                <p class="mt-1 line-clamp-2 text-sm text-on-surface-variant break-words">
                                    <?= h($equipment_description) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-gym-inventory&gym_id=' . $gym_id . '&equipment_id=' . $equipment_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-gym-inventory&gym_id=' . $gym_id . '&equipment_id=' . $equipment_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this inventory item?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="gym_id" value="<?= $gym_id ?>">
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

        <?php if (!$inventory_items): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No inventory items found.</p>
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
                inventory items
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">
                <?php if ($current_page > 1): ?>
                    <a href="<?= esc_url(staff_inventory_page_url($current_page - 1)) ?>" class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high">← Previous</a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">← Previous</span>
                <?php endif; ?>

                <?php for ($i = max(1, $current_page - 2); $i <= min($last_page, $current_page + 2); $i++): ?>
                    <a
                        href="<?= esc_url(staff_inventory_page_url($i)) ?>"
                        class="rounded-full border px-4 py-2 text-sm font-bold transition <?= $i === $current_page ? 'border-primary-container bg-primary-container text-on-primary-container shadow-[0_0_18px_rgba(212,251,0,0.22)]' : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                    >
                        <?= h((string)$i) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $last_page): ?>
                    <a href="<?= esc_url(staff_inventory_page_url($current_page + 1)) ?>" class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high">Next →</a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">Next →</span>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php
wp_app_page_end(true);
?>
