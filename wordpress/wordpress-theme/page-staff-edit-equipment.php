<?php
/*
Template Name: Staff Edit Equipment
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$equipment_id = (int)($_GET['id'] ?? $_POST['equipment_id'] ?? 0);

$manage_equipment_url = home_url('/?pagename=staff-manage-equipment');
$edit_equipment_url = home_url('/?pagename=staff-edit-equipment&id=' . $equipment_id);

if ($equipment_id <= 0) {
    wp_safe_redirect($manage_equipment_url);
    exit;
}

function equipment_edit_value(array $equipment, string $key, $default = '')
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];

        if ($value === '-' || $value === '—') {
            return '';
        }

        return $value;
    }

    return $equipment[$key] ?? $default;
}

$equipment_response = api_get('/equipment/' . $equipment_id, auth: true);
$equipment = [];

if (($equipment_response['result'] ?? false) !== false && is_array($equipment_response['result'] ?? null)) {
    $equipment = $equipment_response['result'];
} else {
    $flash_error = api_message($equipment_response) ?: 'No se pudo cargar el equipamiento.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_equipment_submit'])) {
    $payload = [
        'name' => trim((string)($_POST['equipment_name'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'image_url' => trim((string)($_POST['image_url'] ?? '')),
        'is_home_accessible' => !empty($_POST['is_home_accessible']),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== '-' && $value !== '—';
    });

    $update_response = api_put('/equipment/' . $equipment_id, $payload, auth: true);

    if (($update_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_equipment_url . '&notice=updated');
        exit;
    }

    $flash_error = api_message($update_response) ?: 'No se pudo actualizar el equipamiento.';
}

$current_image = equipment_edit_value($equipment, 'image_url');
$is_home_accessible = !empty($_POST)
    ? !empty($_POST['is_home_accessible'])
    : !empty($equipment['is_home_accessible']);

wp_app_page_start('Edit Equipment', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Equipment</h2>
            <p class="text-sm text-on-surface-variant">
                Modifica el nombre, descripción, imagen y disponibilidad del equipamiento.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_equipment_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to equipment
        </a>
    </section>

    <?php if ($equipment): ?>
        <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
            <form method="post" action="<?= esc_url($edit_equipment_url) ?>" class="space-y-6">

                <input type="hidden" name="edit_equipment_submit" value="1">
                <input type="hidden" name="equipment_id" value="<?= (int)$equipment_id ?>">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Equipment name
                    </label>

                    <input
                        type="text"
                        name="equipment_name"
                        value="<?= h($_POST['equipment_name'] ?? ($equipment['name'] ?? '')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: Olympic Barbell"
                        maxlength="80"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Description
                    </label>

                    <textarea
                        name="description"
                        rows="6"
                        maxlength="280"
                        class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Describe el equipamiento, uso, ubicación o características..."
                        required
                    ><?= h($_POST['description'] ?? ($equipment['description'] ?? '')) ?></textarea>

                    <p class="mt-1 text-xs text-on-surface-variant">
                        Máximo 280 caracteres. Este campo es obligatorio.
                    </p>
                </div>

                <label class="flex items-center gap-3 rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3">
                    <input
                        type="checkbox"
                        name="is_home_accessible"
                        value="1"
                        class="h-4 w-4 rounded border-outline-variant/30 bg-surface text-primary focus:ring-primary-container"
                        <?= $is_home_accessible ? 'checked' : '' ?>
                    >
                    <span class="text-sm font-medium text-on-surface">
                        Available for home workouts
                    </span>
                </label>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Equipment image URL
                    </label>

                    <input
                        id="equipmentImageUrl"
                        type="url"
                        name="image_url"
                        value="<?= h($_POST['image_url'] ?? ($equipment['image_url'] ?? '')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="https://example.com/equipment.jpg"
                    >

                    <div
                        id="imagePreviewWrap"
                        class="mt-4 flex h-[190px] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high transition hover:border-primary-container"
                    >
                        <div id="imagePreviewPlaceholder" class="<?= $current_image ? 'hidden' : 'flex' ?> flex-col items-center justify-center text-center text-on-surface-variant">
                            <span class="material-symbols-outlined mb-2 text-4xl text-on-surface-variant/60">image</span>
                            <span class="text-xs font-bold">Image preview</span>
                            <span class="mt-1 text-[11px] text-on-surface-variant/70">Optional</span>
                        </div>

                        <img
                            id="imagePreview"
                            src="<?= esc_url($current_image) ?>"
                            alt="Equipment image preview"
                            class="<?= $current_image ? '' : 'hidden' ?> h-full w-full object-cover"
                        >
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button
                        type="submit"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                    >
                        Save Changes
                    </button>

                    <a
                        href="<?= esc_url($manage_equipment_url) ?>"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
                    >
                        Cancel
                    </a>
                </div>

            </form>
        </section>
    <?php endif; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('equipmentImageUrl');
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePreviewPlaceholder');

    if (!imageInput || !preview || !placeholder) return;

    function showPlaceholder() {
        preview.removeAttribute('src');
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }

    function updatePreview() {
        const url = imageInput.value.trim();

        if (!url || url === '-' || url === '—') {
            showPlaceholder();
            return;
        }

        preview.onload = function () {
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };

        preview.onerror = function () {
            showPlaceholder();
        };

        preview.src = url;
    }

    imageInput.addEventListener('input', updatePreview);
    updatePreview();
});
</script>

<?php
wp_app_page_end(true);
?>