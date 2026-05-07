<?php
/*
Template Name: Staff Create Gym Inventory
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';
$manage_inventory_url = home_url('/?pagename=staff-manage-gym-inventory');

$statuses = [
    'operational' => 'Operational',
    'maintenance' => 'Maintenance',
    'retired' => 'Retired',
];

function staff_inventory_create_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === 'â€”' || strtoupper((string)$value) === 'NULL') {
        return '';
    }

    return $value;
}

$gyms = fitapp_api_get_page('/gyms', 1, 50, true)['items'];
$equipment_items = fitapp_api_get_page('/equipment', 1, 50, true)['items'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_inventory_submit'])) {
    $payload = [
        'gym_id' => (int)($_POST['gym_id'] ?? 0),
        'equipment_id' => (int)($_POST['equipment_id'] ?? 0),
        'quantity' => (int)($_POST['quantity'] ?? 0),
        'status' => trim((string)($_POST['status'] ?? 'operational')),
    ];

    $create_response = api_post('/gym-inventory', $payload, auth: true);

    if (($create_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_inventory_url . '&notice=created');
        exit;
    }

    $flash_error = api_message($create_response) ?: 'Could not create the inventory item.';
}

wp_app_page_start('Create Gym Inventory', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">
    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Inventory Item</h2>
            <p class="text-sm text-on-surface-variant">
                Assign equipment to a gym and define quantity and status.
            </p>
        </div>

        <a href="<?= esc_url($manage_inventory_url) ?>" class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high">
            ← Back to inventory
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 shadow-lg sm:p-6">
        <form method="post" action="" class="space-y-6">
            <input type="hidden" name="create_inventory_submit" value="1">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Gym</label>
                    <select name="gym_id" class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20" required>
                        <option value="">Select gym</option>
                        <?php foreach ($gyms as $gym): ?>
                            <?php $gym_id = (int)($gym['id'] ?? 0); ?>
                            <?php if ($gym_id > 0): ?>
                                <option value="<?= $gym_id ?>" <?= (int)staff_inventory_create_value('gym_id') === $gym_id ? 'selected' : '' ?>>
                                    <?= h($gym['name'] ?? ('Gym #' . $gym_id)) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Equipment</label>
                    <select name="equipment_id" class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20" required>
                        <option value="">Select equipment</option>
                        <?php foreach ($equipment_items as $equipment): ?>
                            <?php $equipment_id = (int)($equipment['id'] ?? 0); ?>
                            <?php if ($equipment_id > 0): ?>
                                <option value="<?= $equipment_id ?>" <?= (int)staff_inventory_create_value('equipment_id') === $equipment_id ? 'selected' : '' ?>>
                                    <?= h($equipment['name'] ?? ('Equipment #' . $equipment_id)) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Quantity</label>
                    <input type="number" name="quantity" min="0" value="<?= h(staff_inventory_create_value('quantity', '1')) ?>" class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20" required>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Status</label>
                    <?php $selected_status = staff_inventory_create_value('status', 'operational'); ?>
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
                    Create Inventory Item
                </button>

                <a href="<?= esc_url($manage_inventory_url) ?>" class="inline-flex w-full items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high sm:w-auto">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</div>

<?php
wp_app_page_end(true);
?>
