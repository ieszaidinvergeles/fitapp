<?php
/*
Template Name: Staff Create Exercise
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$create_exercise_url = home_url('/?pagename=staff-create-exercise');
$manage_exercises_url = home_url('/?pagename=staff-manage-exercises');

function exercise_create_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_exercise_submit'])) {
    $payload = [
        'name' => trim((string)($_POST['exercise_name'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'target_muscle_group' => trim((string)($_POST['target_muscle_group'] ?? '')),
        'image_url' => trim((string)($_POST['image_url'] ?? '')),
        'video_url' => trim((string)($_POST['video_url'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== '-' && $value !== '—';
    });

    $create_response = api_post('/exercises', $payload, auth: true);

    if (($create_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_exercises_url . '&notice=created');
        exit;
    }

    $flash_error = api_message($create_response) ?: 'No se pudo crear el ejercicio. Revisa los campos obligatorios.';
}

wp_app_page_start('Create Exercise', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Exercise</h2>
            <p class="text-sm text-on-surface-variant">
                Crea un nuevo ejercicio para usarlo en rutinas y entrenamientos.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_exercises_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to exercises
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" action="<?= esc_url($create_exercise_url) ?>" class="space-y-6">

            <input type="hidden" name="create_exercise_submit" value="1">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Exercise name
                </label>

                <input
                    type="text"
                    name="exercise_name"
                    value="<?= h(exercise_create_value('exercise_name')) ?>"
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

                <?php $selected_group = exercise_create_value('target_muscle_group'); ?>

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
                ><?= h(exercise_create_value('description')) ?></textarea>

                <p class="mt-1 text-xs text-on-surface-variant">
                    Máximo 280 caracteres. Este campo es obligatorio.
                </p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Exercise image URL
                </label>

                <input
                    id="exerciseImageUrl"
                    type="url"
                    name="image_url"
                    value="<?= h(exercise_create_value('image_url')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="https://example.com/exercise.jpg"
                >

                <div
                    id="imagePreviewWrap"
                    class="mt-4 flex h-[180px] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high transition hover:border-primary-container"
                >
                    <div id="imagePreviewPlaceholder" class="flex flex-col items-center justify-center text-center text-on-surface-variant">
                        <span class="material-symbols-outlined mb-2 text-4xl text-on-surface-variant/60">image</span>
                        <span class="text-xs font-bold">Image preview</span>
                        <span class="mt-1 text-[11px] text-on-surface-variant/70">Optional</span>
                    </div>

                    <img
                        id="imagePreview"
                        src=""
                        alt="Exercise image preview"
                        class="hidden h-full w-full object-cover"
                    >
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Video URL
                </label>

                <input
                    type="url"
                    name="video_url"
                    value="<?= h(exercise_create_value('video_url')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="https://example.com/video.mp4"
                >
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Exercise
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

</div>

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