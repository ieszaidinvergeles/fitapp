<?php
/*
Template Name: Staff Create Equipment
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$create_equipment_url = home_url('/?pagename=staff-create-equipment');
$manage_equipment_url = home_url('/?pagename=staff-manage-equipment');

function equipment_create_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
}

/**
 * Enviar formulario multipart a Laravel.
 * Esto permite mandar imagen real por $_FILES.
 */
function equipment_api_multipart_post(string $endpoint, array $fields, ?array $file = null, bool $auth = true): array
{
    $url = API_BASE . $endpoint;

    $headers = [
        'Accept: application/json',
    ];

    if ($auth && !empty($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    }

    $post_fields = $fields;

    if (
        $file
        && !empty($file['tmp_name'])
        && is_uploaded_file($file['tmp_name'])
        && (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK
    ) {
        $post_fields['image'] = new CURLFile(
            $file['tmp_name'],
            $file['type'] ?? 'application/octet-stream',
            $file['name'] ?? 'equipment-image'
        );
    }

    $ch = curl_init($url);

    if ($ch === false) {
        return [
            'result' => false,
            'message' => ['general' => 'Could not initialize HTTP client.'],
        ];
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $raw = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);

    curl_close($ch);

    if ($raw === false) {
        return [
            'result' => false,
            'message' => ['general' => 'Could not connect to API. cURL: ' . $curl_error],
        ];
    }

    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        return [
            'result' => false,
            'message' => ['general' => 'API returned non-JSON. HTTP ' . $http_code],
        ];
    }

    return $decoded;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_equipment_submit'])) {
    $payload = [
        'name' => trim((string)($_POST['equipment_name'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'is_home_accessible' => !empty($_POST['is_home_accessible']) ? '1' : '0',
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== '-' && $value !== '—';
    });

    $create_response = equipment_api_multipart_post('/equipment', $payload, $_FILES['image'] ?? null, true);

    if (($create_response['result'] ?? false) !== false) {
        wp_safe_redirect(add_query_arg('notice', 'created', $manage_equipment_url));
        exit;
    }

    $flash_error = api_message($create_response) ?: 'Could not create the equipment. Check the required fields.';
}

wp_app_page_start('Create Equipment', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Equipment</h2>
            <p class="text-sm text-on-surface-variant">
                Create new gym equipment.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_equipment_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to equipment
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" action="<?= esc_url($create_equipment_url) ?>" enctype="multipart/form-data" class="space-y-6">

            <input type="hidden" name="create_equipment_submit" value="1">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Equipment image
                </label>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_180px]">
                    <label
                        id="equipmentDropzone"
                        class="flex min-h-[150px] cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high px-4 py-6 text-center transition hover:border-primary-container hover:bg-surface-container-highest"
                    >
                        <span class="material-symbols-outlined mb-2 text-4xl text-primary-container">upload</span>
                        <span class="text-sm font-bold text-on-surface">Upload equipment image</span>
                        <span class="mt-1 text-xs text-on-surface-variant">JPG, PNG or WEBP</span>
                        <span class="mt-1 text-[11px] text-on-surface-variant/70">Click or drag and drop</span>

                        <input
                            id="equipmentImageInput"
                            type="file"
                            name="image"
                            accept="image/*"
                            class="hidden"
                        >
                    </label>

                    <div
                        id="imagePreviewWrap"
                        class="group relative flex h-[150px] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high transition hover:border-primary-container hover:bg-surface-container-highest"
                    >
                        <div id="imagePreviewPlaceholder" class="flex flex-col items-center justify-center text-center text-on-surface-variant">
                            <span class="material-symbols-outlined mb-2 text-4xl text-on-surface-variant/60">image</span>
                            <span class="text-xs font-bold">No image selected</span>
                        </div>

                        <img
                            id="imagePreview"
                            src=""
                            alt="Equipment image preview"
                            class="hidden h-full w-full object-cover"
                        >

                        <button
                            id="removeEquipmentImage"
                            type="button"
                            class="absolute inset-0 hidden items-center justify-center bg-black/55 opacity-0 transition group-hover:opacity-100"
                        >
                            <span class="material-symbols-outlined rounded-full bg-error p-2 text-white">close</span>
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Equipment name
                </label>

                <input
                    type="text"
                    name="equipment_name"
                    value="<?= h(equipment_create_value('equipment_name'), '') ?>"
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
                    placeholder="Describe the equipment, usage, location, or features..."
                    required
                ><?= h(equipment_create_value('description'), '') ?></textarea>

                <p class="mt-1 text-xs text-on-surface-variant">
                    Maximum 280 characters. This field is required.
                </p>
            </div>

            <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-4 transition hover:border-primary-container/50 hover:bg-surface-container-highest">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary-container">home</span>
                    <div>
                        <span class="block text-sm font-bold text-on-surface">
                            Available for home workouts
                        </span>
                        <span class="block text-xs text-on-surface-variant">
                            Mark this equipment as usable outside the gym.
                        </span>
                    </div>
                </div>

                <input
                    type="checkbox"
                    name="is_home_accessible"
                    value="1"
                    class="peer sr-only"
                    <?= !empty($_POST['is_home_accessible']) ? 'checked' : '' ?>
                >

                <span class="relative block h-7 w-12 shrink-0 rounded-full border border-outline-variant/30 bg-surface-container transition after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-on-surface-variant after:shadow-md after:transition-all after:content-[''] peer-checked:border-primary-container peer-checked:bg-primary-container peer-checked:after:translate-x-5 peer-checked:after:bg-on-primary-container"></span>
            </label>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Equipment
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

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('equipmentImageInput');
    const dropzone = document.getElementById('equipmentDropzone');
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePreviewPlaceholder');
    const removeBtn = document.getElementById('removeEquipmentImage');

    if (!input || !dropzone || !preview || !placeholder || !removeBtn) return;

    function clearPreview() {
        input.value = '';
        preview.removeAttribute('src');
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        removeBtn.classList.add('hidden');
        removeBtn.classList.remove('flex');
    }

    function setPreview(file) {
        if (!file) {
            clearPreview();
            return;
        }

        if (!file.type.startsWith('image/')) {
            clearPreview();
            alert('Please select a valid image file.');
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        removeBtn.classList.remove('hidden');
        removeBtn.classList.add('flex');
    }

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        setPreview(file);
    });

    dropzone.addEventListener('dragover', function (event) {
        event.preventDefault();
        dropzone.classList.add('border-primary-container', 'bg-surface-container-highest');
    });

    dropzone.addEventListener('dragleave', function () {
        dropzone.classList.remove('border-primary-container', 'bg-surface-container-highest');
    });

    dropzone.addEventListener('drop', function (event) {
        event.preventDefault();
        dropzone.classList.remove('border-primary-container', 'bg-surface-container-highest');

        const file = event.dataTransfer.files && event.dataTransfer.files[0];

        if (!file) {
            return;
        }

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        input.files = dataTransfer.files;

        setPreview(file);
    });

    removeBtn.addEventListener('click', function () {
        clearPreview();
    });
});
</script>

<?php
wp_app_page_end(true);
?>
