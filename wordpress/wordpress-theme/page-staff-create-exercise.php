<?php
/*
Template Name: Staff Create Exercise
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

function exercise_form_value(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'name'             => trim((string)($_POST['name'] ?? '')),
        'description'      => trim((string)($_POST['description'] ?? '')),
        'muscle_group'     => trim((string)($_POST['muscle_group'] ?? '')),
        'difficulty_level' => trim((string)($_POST['difficulty_level'] ?? '')),
        'equipment'        => trim((string)($_POST['equipment'] ?? '')),
        'image_url'        => trim((string)($_POST['image_url'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '';
    });

    $create_response = api_post('/exercises', $payload, auth: true);

    if (($create_response['result'] ?? false) !== false) {
        wp_redirect(home_url('/?pagename=staff-manage-exercises&notice=created'));
        exit;
    }

    $flash_error = api_message($create_response) ?: 'No se pudo crear el ejercicio.';
}

wp_app_page_start('Create Exercise', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Exercise</h2>
            <p class="text-sm text-on-surface-variant">
                Crea un nuevo ejercicio para usarlo en rutinas y entrenamientos.
            </p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-manage-exercises')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to exercises
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" class="space-y-6">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Exercise image URL</label>
                <input
                    id="exerciseImageUrl"
                    type="url"
                    name="image_url"
                    value="<?= h(exercise_form_value('image_url')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="https://example.com/exercise.jpg"
                >

                <div
                    id="imagePreviewWrap"
                    class="mt-4 flex h-[160px] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high transition hover:border-primary-container"
                >
                    <div id="imagePreviewPlaceholder" class="flex flex-col items-center justify-center text-center text-on-surface-variant">
                        <span class="material-symbols-outlined mb-2 text-4xl text-on-surface-variant/60">image</span>
                        <span class="text-xs font-bold">Image preview</span>
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
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Exercise name</label>
                <input
                    type="text"
                    name="name"
                    value="<?= h(exercise_form_value('name')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: Bench Press"
                    required
                >
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Muscle group</label>
                    <input
                        type="text"
                        name="muscle_group"
                        value="<?= h(exercise_form_value('muscle_group')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: Chest, Legs, Back"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Difficulty</label>
                    <select
                        name="difficulty_level"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    >
                        <option value="">Select difficulty</option>
                        <option value="beginner" <?= exercise_form_value('difficulty_level') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= exercise_form_value('difficulty_level') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= exercise_form_value('difficulty_level') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                        <option value="expert" <?= exercise_form_value('difficulty_level') === 'expert' ? 'selected' : '' ?>>Expert</option>
                    </select>
                </div>

            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Equipment</label>
                <input
                    type="text"
                    name="equipment"
                    value="<?= h(exercise_form_value('equipment')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: Dumbbells, Machine, Bodyweight"
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Description / Instructions</label>
                <textarea
                    name="description"
                    rows="6"
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Describe cómo realizar el ejercicio, técnica, postura y recomendaciones..."
                ><?= h(exercise_form_value('description')) ?></textarea>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Exercise
                </button>

                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-exercises')) ?>"
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

    function updatePreview() {
        const url = imageInput.value.trim();

        if (!url) {
            preview.src = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            return;
        }

        preview.src = url;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
    }

    imageInput.addEventListener('input', updatePreview);
    updatePreview();
});
</script>

<?php
wp_app_page_end(true);
?>