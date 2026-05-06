<?php
/*
Template Name: Staff Create Routine
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

function routine_form_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'name' => trim((string)($_POST['routine_name'] ?? '')),
        'difficulty_level' => trim((string)($_POST['difficulty_level'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'estimated_duration_min' => !empty($_POST['estimated_duration_min']) ? (int)$_POST['estimated_duration_min'] : null,
    ];


    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== null && $value !== '-' && $value !== '—';
    });

    $create_response = api_post('/routines', $payload, auth: true);

    if (($create_response['result'] ?? false) !== false) {
        wp_redirect(home_url('/?pagename=staff-manage-routines&notice=created'));
        exit;
    }

    $flash_error = api_message($create_response) ?: 'No se pudo crear la rutina.';
}

wp_app_page_start('Create Routine', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Routine</h2>
            <p class="text-sm text-on-surface-variant">
                Crea una nueva rutina para los usuarios del gimnasio.
            </p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-manage-routines')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to routines
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" enctype="multipart/form-data" class="space-y-6">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Routine image</label>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_180px]">
                    <label
                        id="routineDropzone"
                        class="flex min-h-[150px] cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high px-4 py-6 text-center transition hover:border-primary-container hover:bg-surface-container-highest"
                    >
                        <span class="material-symbols-outlined mb-2 text-4xl text-primary-container">upload</span>
                        <span class="text-sm font-bold text-on-surface">Upload routine image</span>
                        <span class="mt-1 text-xs text-on-surface-variant">JPG, PNG or WEBP</span>
                        <span class="mt-1 text-[11px] text-on-surface-variant/70">Click or drag and drop</span>

                        <input
                            id="routineImageInput"
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
                            alt="Routine image preview"
                            class="hidden h-full w-full object-cover"
                        >

                        <button
                            id="removeRoutineImage"
                            type="button"
                            class="absolute inset-0 hidden items-center justify-center bg-black/55 opacity-0 transition group-hover:opacity-100"
                        >
                            <span class="material-symbols-outlined rounded-full bg-error p-2 text-white">close</span>
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Routine name</label>
                <input
                    type="text"
                    name="routine_name"
                    value="<?= h(routine_form_value('routine_name'), '') ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: Full Body Starter"
                    required
                >
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Difficulty</label>
                    <select
                        name="difficulty_level"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select difficulty</option>
                        <option value="beginner" <?= routine_form_value('difficulty_level') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= routine_form_value('difficulty_level') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= routine_form_value('difficulty_level') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                        <option value="expert" <?= routine_form_value('difficulty_level') === 'expert' ? 'selected' : '' ?>>Expert</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Duration</label>
                    <input
                        type="number"
                        name="estimated_duration_min"
                        min="1"
                        value="<?= h(routine_form_value('estimated_duration_min'), '') ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: 45"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Goal</label>
                    <input
                        type="text"
                        name="goal"
                        value="<?= h(routine_form_value('goal'), '') ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: Strength, Cardio"
                    >
                </div>

            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Description</label>
                <textarea
                    name="description"
                    rows="5"
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Describe brevemente para qué sirve esta rutina..."
                ><?= h(routine_form_value('description'), '') ?></textarea>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Routine
                </button>

                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-routines')) ?>"
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
    const input = document.getElementById('routineImageInput');
    const dropzone = document.getElementById('routineDropzone');
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePreviewPlaceholder');
    const removeBtn = document.getElementById('removeRoutineImage');

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