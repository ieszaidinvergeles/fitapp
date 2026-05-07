<?php
/*
Template Name: Staff View Gym Inventory
*/
require_once 'functions.php';
require_advanced();

$gym_id = (int)($_GET['gym_id'] ?? 0);
$equipment_id = (int)($_GET['equipment_id'] ?? 0);
$flash_error = '';

$manage_inventory_url = home_url('/?pagename=staff-manage-gym-inventory');
$edit_inventory_url = home_url('/?pagename=staff-edit-gym-inventory&gym_id=' . $gym_id . '&equipment_id=' . $equipment_id);

if ($gym_id <= 0 || $equipment_id <= 0) {
    wp_safe_redirect($manage_inventory_url);
    exit;
}

function staff_inventory_view_value(array $item, array $keys, $default = '')
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

function staff_inventory_view_status_label(string $status): string
{
    return [
        'operational' => 'Operational',
        'maintenance' => 'Maintenance',
        'retired' => 'Retired',
    ][$status] ?? ucwords(str_replace('_', ' ', $status));
}

function staff_inventory_view_status_class(string $status): string
{
    return match ($status) {
        'operational' => 'border-primary-container/30 bg-primary-container/10 text-primary-container',
        'maintenance' => 'border-yellow-400/30 bg-yellow-400/10 text-yellow-300',
        'retired' => 'border-error/40 bg-error/10 text-error',
        default => 'border-outline-variant/30 bg-surface-container-high text-on-surface-variant',
    };
}

$inventory_response = api_get('/gym-inventory/' . $gym_id . '/' . $equipment_id, auth: true);
$inventory = [];

if (($inventory_response['result'] ?? false) !== false && is_array($inventory_response['result'] ?? null)) {
    $inventory = $inventory_response['result'];
} else {
    $flash_error = api_message($inventory_response) ?: 'Could not load the inventory item.';
}

$gym = is_array($inventory['gym'] ?? null) ? $inventory['gym'] : [];
$equipment = is_array($inventory['equipment'] ?? null) ? $inventory['equipment'] : [];

$gym_name = staff_inventory_view_value($gym, ['name'], 'Gym #' . $gym_id);
$equipment_name = staff_inventory_view_value($equipment, ['name'], 'Equipment #' . $equipment_id);
$equipment_description = staff_inventory_view_value($equipment, ['description'], '');
$quantity = (int)($inventory['quantity'] ?? 0);
$status = h(staff_inventory_view_value($inventory, ['status'], 'operational'));
$image_url = fitapp_public_asset_url(staff_inventory_view_value($equipment, ['image_url', 'image', 'photo_url'], ''));

wp_app_page_start('View Gym Inventory', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">
    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-primary-container">
                Gym #<?= (int)$gym_id ?> · Equipment #<?= (int)$equipment_id ?>
            </p>

            <h2 class="mt-2 text-3xl font-black uppercase tracking-tight sm:text-4xl">
                <?= h($equipment_name) ?>
            </h2>

            <p class="mt-2 text-sm text-on-surface-variant">
                Full view of the selected inventory item.
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <a href="<?= esc_url($edit_inventory_url) ?>" class="inline-flex w-full items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 sm:w-auto">
                Edit inventory
            </a>

            <a href="<?= esc_url($manage_inventory_url) ?>" class="inline-flex w-full items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high sm:w-auto">
                ← Back to inventory
            </a>
        </div>
    </section>

    <?php if ($inventory): ?>
        <section class="overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container shadow-lg">
            <div class="grid grid-cols-1 lg:grid-cols-[360px_minmax(0,1fr)]">
                <div class="relative min-h-[280px] border-b border-outline-variant/20 bg-surface-container-high lg:border-b-0 lg:border-r">
                    <?php fitapp_render_image_or_placeholder($image_url, (string)$equipment_name, 'absolute inset-0 h-full w-full object-cover', 'absolute inset-0 min-h-[280px] flex-col items-center justify-center p-8 text-center text-on-surface-variant', 'inventory_2', 'No image'); ?>
                    <?php if ($image_url): ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-background/80 via-background/20 to-transparent"></div>
                    <?php endif; ?>
                </div>

                <div class="p-5 sm:p-8">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Gym</p>
                            <p class="mt-2 text-lg font-bold"><?= h($gym_name) ?></p>
                        </div>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Quantity</p>
                            <p class="mt-2 text-lg font-bold"><?= h((string)$quantity) ?></p>
                        </div>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Status</p>
                            <p class="mt-2 inline-flex rounded-full border px-3 py-1 text-xs font-black uppercase tracking-wide <?= staff_inventory_view_status_class($status) ?>">
                                <?= h(staff_inventory_view_status_label($status)) ?>
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-outline-variant/20 bg-surface-container-high p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Equipment Description</p>
                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-on-surface-variant">
                            <?= h($equipment_description, 'No description available.') ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>

<?php
wp_app_page_end(true);
?>
