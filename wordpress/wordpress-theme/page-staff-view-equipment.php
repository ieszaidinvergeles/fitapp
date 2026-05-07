<?php
/*
Template Name: Staff View Equipment
*/
require_once 'functions.php';
require_advanced();

$equipment_id = (int)($_GET['id'] ?? 0);

$manage_equipment_url = home_url('/?pagename=staff-manage-equipment');
$edit_equipment_url = home_url('/?pagename=staff-edit-equipment&id=' . $equipment_id);

$flash_error = '';

if ($equipment_id <= 0) {
    wp_safe_redirect($manage_equipment_url);
    exit;
}

function equipment_view_value(array $equipment, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (!isset($equipment[$key]) || $equipment[$key] === null) {
            continue;
        }

        $clean_value = trim((string)$equipment[$key]);

        if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€”' && strtoupper($clean_value) !== 'NULL') {
            return $equipment[$key];
        }
    }

    return $default;
}

$equipment_response = api_get('/equipment/' . $equipment_id, auth: true);
$equipment = [];

if (($equipment_response['result'] ?? false) !== false && is_array($equipment_response['result'] ?? null)) {
    $equipment = $equipment_response['result'];
} else {
    $flash_error = api_message($equipment_response) ?: 'Could not load the equipment.';
}

$name = equipment_view_value($equipment, ['name', 'title', 'equipment_name'], 'Equipment');
$description = equipment_view_value($equipment, ['description', 'notes', 'details'], 'No description available.');
$image_url = fitapp_public_asset_url(equipment_view_value($equipment, ['image_url', 'cover_image_url', 'image', 'photo_url'], ''));
$is_home_accessible = !empty($equipment['is_home_accessible']);

wp_app_page_start('View Equipment', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-primary-container">
                Equipment #<?= (int)$equipment_id ?>
            </p>

            <h2 class="mt-2 text-3xl font-black uppercase tracking-tight sm:text-4xl">
                <?= h($name) ?>
            </h2>

            <p class="mt-2 text-sm text-on-surface-variant">
                Full view of the selected equipment.
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <a
                href="<?= esc_url($edit_equipment_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
            >
                Edit equipment
            </a>

            <a
                href="<?= esc_url($manage_equipment_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
            >
                ← Back to equipment
            </a>
        </div>
    </section>

    <?php if ($equipment): ?>
        <section class="overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container shadow-lg">

            <div class="grid grid-cols-1 lg:grid-cols-[360px_minmax(0,1fr)]">

                <div class="relative min-h-[280px] border-b border-outline-variant/20 bg-surface-container-high lg:border-b-0 lg:border-r">
                    <?php fitapp_render_image_or_placeholder($image_url, (string)$name, 'absolute inset-0 h-full w-full object-cover', 'absolute inset-0 min-h-[280px] flex-col items-center justify-center p-8 text-center text-on-surface-variant', 'construction', 'No image'); ?>
                    <?php if ($image_url): ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-background/80 via-background/20 to-transparent"></div>
                    <?php endif; ?>
                </div>

                <div class="p-5 sm:p-8">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                Home access
                            </p>

                            <p class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide <?= $is_home_accessible ? 'bg-primary-container text-on-primary-container' : 'border border-outline-variant/30 text-on-surface-variant' ?>">
                                <?= $is_home_accessible ? 'Available' : 'Gym only' ?>
                            </p>
                        </div>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                Identifier
                            </p>

                            <p class="mt-2 text-lg font-bold">
                                #<?= (int)$equipment_id ?>
                            </p>
                        </div>

                    </div>

                    <div class="mt-5 rounded-2xl border border-outline-variant/20 bg-surface-container-high p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                            Description
                        </p>

                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-on-surface-variant">
                            <?= h($description) ?>
                        </p>
                    </div>

                    <?php if ($image_url): ?>
                        <div class="mt-5 rounded-2xl border border-outline-variant/20 bg-surface-container-high p-5">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                Image URL
                            </p>

                            <a
                                href="<?= esc_url($image_url) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-primary-container hover:underline"
                            >
                                Open image
                                <span class="material-symbols-outlined text-base">open_in_new</span>
                            </a>
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
