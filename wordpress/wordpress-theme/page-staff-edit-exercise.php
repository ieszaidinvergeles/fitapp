<?php
/*
Template Name: Staff Edit Exercise
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';
$exercise_id = (int)($_GET['id'] ?? $_POST['exercise_id'] ?? 0);

$manage_exercises_url = home_url('/?pagename=staff-manage-exercises');
$edit_exercise_url = home_url('/?pagename=staff-edit-exercise&id=' . $exercise_id);

$muscle_groups = [
    'chest' => 'Chest',
    'upper_back' => 'Upper Back',
    'lower_back' => 'Lower Back',
    'shoulders' => 'Shoulders',
    'biceps' => 'Biceps',
    'triceps' => 'Triceps',
    'forearms' => 'Forearms',
    'core' => 'Core',
    'obliques' => 'Obliques',
    'quadriceps' => 'Quadriceps',
    'hamstrings' => 'Hamstrings',
    'glutes' => 'Glutes',
    'calves' => 'Calves',
    'hip_flexors' => 'Hip Flexors',
    'adductors' => 'Adductors',
    'abductors' => 'Abductors',
    'traps' => 'Traps',
    'lats' => 'Lats',
    'neck' => 'Neck',
    'full_body' => 'Full Body',
];

function exercise_edit_value(array $exercise, string $key, $default = '')
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];

        if ($value === '-' || $value === '—') {
            return '';
        }

        return $value;
    }

    return $exercise[$key] ?? $default;
}

if ($exercise_id <= 0) {
    wp_safe_redirect($manage_exercises_url);
    exit;
}

$exercise_response = api_get('/exercises/' . $exercise_id, auth: true);
$exercise = [];

if (($exercise_response['result'] ?? false) !== false && is_array($exercise_response['result'] ?? null)) {
    $exercise = $exercise_response['result'];
} else {
    $flash_error = api_message($exercise_response) ?: 'No se pudo cargar el ejercicio.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_exercise_submit'])) {
    $payload = [
        'name' => trim((string)($_POST['exercise_name'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'target_muscle_group' => trim((string)($_POST['target_muscle_group'] ?? '')),
        'video_url' => trim((string)($_POST['video_url'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== '-' && $value !== '—';
    });

    $update_response = fitapp_api_multipart_update('/exercises/' . $exercise_id, $payload, $_FILES['image'] ?? null, 'image', true);

    if (($update_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_exercises_url . '&notice=updated');
        exit;
    }

    $flash_error = api_message($update_response) ?: 'No se pudo actualizar el ejercicio.';
}

wp_app_page_start('Edit Exercise', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Exercise</h2>
            <p class="text-sm text-on-surface-variant">
                Actualiza la información técnica del ejercicio.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_exercises_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to exercises
        </a>
    </section>

    <?php if ($exercise): ?>
        <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
            <form method="post" action="<?= esc_url($edit_exercise_url) ?>" enctype="multipart/form-data" class="space-y-6">

                <input type="hidden" name="edit_exercise_submit" value="1">
                <input type="hidden" name="exercise_id" value="<?= (int)$exercise_id ?>">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Exercise name
                    </label>

                    <input
                        type="text"
                        name="exercise_name"
                        value="<?= h(exercise_edit_value($exercise, 'exercise_name', $exercise['name'] ?? '')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: Bench Press"
                        maxlength="80"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Muscle group
                    </label>

                    <?php
                    $selected_group = $_POST['target_muscle_group']
                        ?? ($exercise['target_muscle_group'] ?? '');
                    ?>

                    <select
                        name="target_muscle_group"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    >
                        <option value="">Select muscle group</option>

                        <?php foreach ($muscle_groups as $value => $label): ?>
                            <option value="<?= h($value) ?>" <?= $selected_group === $value ? 'selected' : '' ?>>
                                <?= h($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Description / Instructions
                    </label>

                    <textarea
                        name="description"
                        rows="6"
                        maxlength="280"
                        class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Describe cómo realizar el ejercicio, técnica, postura y recomendaciones..."
                        required
                    ><?= h(exercise_edit_value($exercise, 'description')) ?></textarea>

                    <p class="mt-1 text-xs text-on-surface-variant">
                        Máximo 280 caracteres. Este campo es obligatorio.
                    </p>
                </div>

                <?php fitapp_render_image_dropzone('Exercise image', 'Change exercise image', 'exerciseImageInput', 'exerciseDropzone', 'image', fitapp_public_asset_url($exercise['image_url'] ?? ''), 'Exercise image preview', 'fitness_center'); ?>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Video URL
                    </label>

                    <input
                        type="url"
                        name="video_url"
                        value="<?= h(exercise_edit_value($exercise, 'video_url')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="https://example.com/video.mp4"
                    >
                </div>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button
                        type="submit"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                    >
                        Save Changes
                    </button>

                    <a
                        href="<?= esc_url($manage_exercises_url) ?>"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
                    >
                        Cancel
                    </a>
                </div>

            </form>
        </section>
    <?php endif; ?>

</div>

<?php fitapp_render_image_dropzone_script('exerciseImageInput', 'exerciseDropzone'); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('exerciseImageUrl');
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
