<?php
/*
Template Name: Staff Edit Routine
*/
require_once 'functions.php';
require_advanced();

$routine_id = (int)($_GET['id'] ?? 0);
$flash_error = '';

if ($routine_id <= 0) {
    wp_redirect(home_url('/?pagename=staff-manage-routines'));
    exit;
}

$routine_response = api_get('/routines/' . $routine_id, auth: true);
$routine_data = (($routine_response['result'] ?? false) !== false) ? $routine_response['result'] : null;

if (!$routine_data || !is_array($routine_data)) {
    wp_redirect(home_url('/?pagename=staff-manage-routines'));
    exit;
}

function routine_edit_value(string $key, array $routine_data, $default = '')
{
    return $_POST[$key] ?? ($routine_data[$key] ?? $default);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'name' => trim((string)($_POST['name'] ?? '')),
        'difficulty_level' => trim((string)($_POST['difficulty_level'] ?? '')),
        'goal' => trim((string)($_POST['goal'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '';
    });

    $update_response = api_put('/routines/' . $routine_id, $payload, auth: true);

    if (($update_response['result'] ?? false) !== false) {
        wp_redirect(home_url('/?pagename=staff-manage-routines&notice=updated'));
        exit;
    } else {
        $flash_error = api_message($update_response) ?: 'No se pudo actualizar la rutina.';
    }
}

$current_image = $routine_data['image_url']
    ?? $routine_data['image']
    ?? $routine_data['photo_url']
    ?? '';

wp_app_page_start('Edit Routine', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Routine</h2>
            <p class="text-sm text-on-surface-variant">
                Modifica los datos de la rutina y guarda los cambios.
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
                    <label class="flex min-h-[150px] cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high px-4 py-6 text-center transition hover:border-primary-container hover:bg-surface-container-highest">
                        <span class="material-symbols-outlined mb-2 text-4xl text-primary-container">upload</span>
                        <span class="text-sm font-bold text-on-surface">Change routine image</span>
                        <span class="mt-1 text-xs text-on-surface-variant">JPG, PNG or WEBP</span>

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
                        <div id="imagePreviewPlaceholder" class="<?= $current_image ? 'hidden' : 'flex' ?> flex-col items-center justify-center text-center text-on-surface-variant">
                            <span class="material-symbols-outlined mb-2 text-4xl text-on-surface-variant/60">image</span>
                            <span class="text-xs font-bold">No image selected</span>
                        </div>

                        <img
                            id="imagePreview"
                            src="<?= esc_url($current_image) ?>"
                            alt="Routine image preview"
                            class="<?= $current_image ? '' : 'hidden' ?> h-full w-full object-cover"
                        >

                        <button
                            id="removeRoutineImage"
                            type="button"
                            class="absolute inset-0 <?= $current_image ? 'flex' : 'hidden' ?> items-center justify-center bg-black/55 opacity-0 transition group-hover:opacity-100"
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
                    name="name"
                    value="<?= h(routine_edit_value('name', $routine_data)) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: Full Body Starter"
                    required
                >
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Difficulty</label>
                    <?php $difficulty = routine_edit_value('difficulty_level', $routine_data); ?>

                    <select
                        name="difficulty_level"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select difficulty</option>
                        <option value="beginner" <?= $difficulty === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= $difficulty === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= $difficulty === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Goal</label>
                    <input
                        type="text"
                        name="goal"
                        value="<?= h(routine_edit_value('goal', $routine_data)) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: Strength, Cardio, Hypertrophy"
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
                ><?= h(routine_edit_value('description', $routine_data)) ?></textarea>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Save Changes
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
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePreviewPlaceholder');
    const removeBtn = document.getElementById('removeRoutineImage');

    if (!input || !preview || !placeholder || !removeBtn) return;

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];

        if (!file) {
            preview.classList.add('hidden');
            removeBtn.classList.add('hidden');
            removeBtn.classList.remove('flex');
            preview.src = '';
            placeholder.classList.remove('hidden');
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        removeBtn.classList.remove('hidden');
        removeBtn.classList.add('flex');
    });

    removeBtn.addEventListener('click', function () {
        input.value = '';
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
        removeBtn.classList.add('hidden');
        removeBtn.classList.remove('flex');
    });
});
</script>

<?php
wp_app_page_end(true);
?>