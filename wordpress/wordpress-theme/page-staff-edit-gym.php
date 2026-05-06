<?php
/*
Template Name: Staff Edit Gym
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$gym_id = (int)($_GET['id'] ?? $_POST['gym_id'] ?? 0);

$manage_gyms_url = home_url('/?pagename=staff-manage-gyms');
$edit_gym_url = home_url('/?pagename=staff-edit-gym&id=' . $gym_id);

if ($gym_id <= 0) {
    wp_safe_redirect($manage_gyms_url);
    exit;
}

function gym_edit_value(array $gym, string $key, $default = '')
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];

        if ($value === '-' || $value === '—') {
            return '';
        }

        return $value;
    }

    return $gym[$key] ?? $default;
}

$gym_response = api_get('/gyms/' . $gym_id, auth: true);
$gym = [];

if (($gym_response['result'] ?? false) !== false && is_array($gym_response['result'] ?? null)) {
    $gym = $gym_response['result'];
} else {
    $flash_error = api_message($gym_response) ?: 'No se pudo cargar el gimnasio.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_gym_submit'])) {
    $payload = [
        'name' => trim((string)($_POST['gym_name'] ?? '')),
        'address' => trim((string)($_POST['address'] ?? '')),
        'city' => trim((string)($_POST['city'] ?? '')),
        'phone' => trim((string)($_POST['phone'] ?? '')),
        'location_coords' => trim((string)($_POST['location_coords'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== '-' && $value !== '—';
    });

    $update_response = fitapp_api_multipart_update('/gyms/' . $gym_id, $payload, $_FILES['logo'] ?? null, 'logo', true);

    if (($update_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_gyms_url . '&notice=updated');
        exit;
    }

    $flash_error = api_message($update_response) ?: 'No se pudo actualizar el gimnasio.';
}

$current_image = fitapp_public_asset_url(gym_edit_value($gym, 'logo_url'));

wp_app_page_start('Edit Gym', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Gym</h2>
            <p class="text-sm text-on-surface-variant">
                Modifica los datos del centro, ubicación, contacto e imagen.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_gyms_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to gyms
        </a>
    </section>

    <?php if ($gym): ?>
        <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
            <form method="post" action="<?= esc_url($edit_gym_url) ?>" enctype="multipart/form-data" class="space-y-6">

                <input type="hidden" name="edit_gym_submit" value="1">
                <input type="hidden" name="gym_id" value="<?= (int)$gym_id ?>">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Gym name
                    </label>

                    <input
                        type="text"
                        name="gym_name"
                        value="<?= h($_POST['gym_name'] ?? ($gym['name'] ?? '')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: FitApp Madrid Centro"
                        maxlength="100"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Address
                    </label>

                    <input
                        type="text"
                        name="address"
                        value="<?= h($_POST['address'] ?? ($gym['address'] ?? '')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: Calle Gran Vía 45"
                        required
                    >
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                            City
                        </label>

                        <input
                            type="text"
                            name="city"
                            value="<?= h($_POST['city'] ?? ($gym['city'] ?? '')) ?>"
                            class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                            placeholder="Example: Madrid"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="<?= h($_POST['phone'] ?? ($gym['phone'] ?? '')) ?>"
                            class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                            placeholder="+34 910 000 001"
                        >
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Location coordinates
                    </label>

                    <input
                        type="text"
                        name="location_coords"
                        value="<?= h($_POST['location_coords'] ?? ($gym['location_coords'] ?? '')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: 40.4168,-3.7038"
                    >
                </div>

                <?php fitapp_render_image_dropzone('Gym logo', 'Change gym logo', 'gymLogoInput', 'gymLogoDropzone', 'logo', $current_image, 'Gym logo preview', 'location_city'); ?>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button
                        type="submit"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                    >
                        Save Changes
                    </button>

                    <a
                        href="<?= esc_url($manage_gyms_url) ?>"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
                    >
                        Cancel
                    </a>
                </div>

            </form>
        </section>
    <?php endif; ?>

</div>

<?php fitapp_render_image_dropzone_script('gymLogoInput', 'gymLogoDropzone'); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('gymLogoUrl');
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
