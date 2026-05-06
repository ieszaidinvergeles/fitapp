<?php
/*
Template Name: Staff Create Gym
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$create_gym_url = home_url('/?pagename=staff-create-gym');
$manage_gyms_url = home_url('/?pagename=staff-manage-gyms');

function gym_create_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_gym_submit'])) {
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

    $create_response = fitapp_api_multipart_post('/gyms', $payload, $_FILES['logo'] ?? null, 'logo', true);

    if (($create_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_gyms_url . '&notice=created');
        exit;
    }

    $flash_error = api_message($create_response) ?: 'No se pudo crear el gimnasio. Revisa los campos obligatorios.';
}

wp_app_page_start('Create Gym', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Gym</h2>
            <p class="text-sm text-on-surface-variant">
                Crea un nuevo centro con ubicación, contacto e imagen.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_gyms_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to gyms
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" action="<?= esc_url($create_gym_url) ?>" enctype="multipart/form-data" class="space-y-6">

            <input type="hidden" name="create_gym_submit" value="1">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Gym name
                </label>

                <input
                    type="text"
                    name="gym_name"
                    value="<?= h(gym_create_value('gym_name')) ?>"
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
                    value="<?= h(gym_create_value('address')) ?>"
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
                        value="<?= h(gym_create_value('city')) ?>"
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
                        value="<?= h(gym_create_value('phone')) ?>"
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
                    value="<?= h(gym_create_value('location_coords')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: 40.4168,-3.7038"
                >
            </div>

            <?php fitapp_render_image_dropzone('Gym logo', 'Upload gym logo', 'gymLogoInput', 'gymLogoDropzone', 'logo', '', 'Gym logo preview', 'location_city'); ?>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Gym
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
