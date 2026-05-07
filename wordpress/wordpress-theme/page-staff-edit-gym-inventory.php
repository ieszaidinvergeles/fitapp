<?php
/*
Template Name: Staff Edit Gym Inventory
*/
require_once 'functions.php';
require_advanced();

$gym_id = (int)($_GET['gym_id'] ?? $_POST['old_gym_id'] ?? 0);
$equipment_id = (int)($_GET['equipment_id'] ?? $_POST['old_equipment_id'] ?? 0);
$flash_error = '';

$manage_inventory_url = home_url('/?pagename=staff-manage-gym-inventory');
$edit_inventory_url = home_url('/?pagename=staff-edit-gym-inventory&gym_id=' . $gym_id . '&equipment_id=' . $equipment_id);

if ($gym_id <= 0 || $equipment_id <= 0) {
    wp_safe_redirect($manage_inventory_url);
    exit;
}

$statuses = [
    'operational' => 'Operational',
    'maintenance' => 'Maintenance',
    'retired' => 'Retired',
];

function staff_inventory_edit_value(array $item, string $key, $default = '')
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];
        return ($value === '-' || $value === 'â€”' || strtoupper((string)$value) === 'NULL') ? '' : $value;
    }

    return $item[$key] ?? $default;
}

$inventory_response = api_get('/gym-inventory/' . $gym_id . '/' . $equipment_id, auth: true);
$inventory = [];

if (($inventory_response['result'] ?? false) !== false && is_array($inventory_response['result'] ?? null)) {
    $inventory = $inventory_response['result'];
} else {
    $flash_error = api_message($inventory_response) ?: 'Could not load the inventory item.';
}

$gyms = fitapp_api_get_page('/gyms', 1, 50, true)['items'];
$equipment_items = fitapp_api_get_page('/equipment', 1, 50, true)['items'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_inventory_submit'])) {
    $payload = [
        'gym_id' => (int)($_POST['gym_id'] ?? 0),
        'equipment_id' => (int)($_POST['equipment_id'] ?? 0),
        'quantity' => (int)($_POST['quantity'] ?? 0),
        'status' => trim((string)($_POST['status'] ?? 'operational')),
    ];

    $update_response = api_put('/gym-inventory/' . $gym_id . '/' . $equipment_id, $payload, auth: true);

    if (($update_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_inventory_url . '&notice=updated');
        exit;
    }

    $flash_error = api_message($update_response) ?: 'Could not update the inventory item.';
}

wp_app_page_start('Edit Gym Inventory', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">
    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Inventory Item</h2>
            <p class="text-sm text-on-surface-variant">
                Update gym, equipment, quantity, and status.
            </p>
        </div>

        <a href="<?= esc_url($manage_inventory_url) ?>" class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high">
            ← Back to inventory
        </a>
    </section>

    <?php if ($inventory): ?>
        <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 shadow-lg sm:p-6">
            <form method="post" action="<?= esc_url($edit_inventory_url) ?>" class="space-y-6">
                <input type="hidden" name="edit_inventory_submit" value="1">
                <input type="hidden" name="old_gym_id" value="<?= $gym_id ?>">
                <input type="hidden" name="old_equipment_id" value="<?= $equipment_id ?>">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Gym</label>
                        <?php $selected_gym = (int)staff_inventory_edit_value($inventory, 'gym_id'); ?>
                        <select name="gym_id" class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20" required>
                            <option value="">Select gym</option>
                            <?php foreach ($gyms as $gym): ?>
                                <?php $option_gym_id = (int)($gym['id'] ?? 0); ?>
                                <?php if ($option_gym_id > 0): ?>
                                    <option value="<?= $option_gym_id ?>" <?= $selected_gym === $option_gym_id ? 'selected' : '' ?>>
                                        <?= h($gym['name'] ?? ('Gym #' . $option_gym_id)) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Equipment</label>
                        <?php $selected_equipment = (int)staff_inventory_edit_value($inventory, 'equipment_id'); ?>
                        <select name="equipment_id" class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20" required>
                            <option value="">Select equipment</option>
                            <?php foreach ($equipment_items as $equipment): ?>
                                <?php $option_equipment_id = (int)($equipment['id'] ?? 0); ?>
                                <?php if ($option_equipment_id > 0): ?>
                                    <option value="<?= $option_equipment_id ?>" <?= $selected_equipment === $option_equipment_id ? 'selected' : '' ?>>
                                        <?= h($equipment['name'] ?? ('Equipment #' . $option_equipment_id)) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Quantity</label>
                        <input type="number" name="quantity" min="0" value="<?= h(staff_inventory_edit_value($inventory, 'quantity', '0')) ?>" class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20" required>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Status</label>
                        <?php $selected_status = staff_inventory_edit_value($inventory, 'status', 'operational'); ?>
                        <select name="status" class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20" required>
                            <?php foreach ($statuses as $value => $label): ?>
                                <option value="<?= h($value) ?>" <?= $selected_status === $value ? 'selected' : '' ?>>
                                    <?= h($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 sm:w-auto">
                        Save Changes
                    </button>

                    <a href="<?= esc_url($manage_inventory_url) ?>" class="inline-flex w-full items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high sm:w-auto">
                        Cancel
                    </a>
                </div>
            </form>
        </section>
    <?php endif; ?>
</div>

<?php
wp_app_page_end(true);
?>
