<?php
/*
Template Name: Staff Create Recipe
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$manage_recipes_url = home_url('/?pagename=staff-manage-recipes');

function recipe_create_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
}

$recipe_types = [
    'breakfast' => 'Breakfast',
    'lunch' => 'Lunch',
    'dinner' => 'Dinner',
    'snack' => 'Snack',
    'pre_workout' => 'Pre Workout',
    'post_workout' => 'Post Workout',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_recipe_submit'])) {
    $fat = $_POST['fat'] !== '' ? (float)$_POST['fat'] : null;
    $carbs = $_POST['carbs'] !== '' ? (float)$_POST['carbs'] : null;
    $protein = $_POST['protein'] !== '' ? (float)$_POST['protein'] : null;

    $macros = [];
    if ($fat !== null) {
        $macros['fat'] = $fat;
    }
    if ($carbs !== null) {
        $macros['carbs'] = $carbs;
    }
    if ($protein !== null) {
        $macros['protein'] = $protein;
    }

    $payload = [
        'name' => trim((string)($_POST['recipe_name'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'ingredients' => trim((string)($_POST['ingredients'] ?? '')),
        'preparation_steps' => trim((string)($_POST['preparation_steps'] ?? '')),
        'calories' => $_POST['calories'] !== '' ? (int)$_POST['calories'] : null,
        'macros_json' => !empty($macros) ? json_encode($macros) : '',
        'type' => trim((string)($_POST['type'] ?? '')),
        'image_url' => trim((string)($_POST['image_url'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        if (is_array($value)) {
            return !empty($value);
        }

        return $value !== '' && $value !== null && $value !== '-' && $value !== '—';
    });

    $create_response = api_post('/recipes', $payload, auth: true);

    if (($create_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_recipes_url . '&notice=created');
        exit;
    }

    $flash_error = api_message($create_response) ?: 'No se pudo crear la receta. Revisa los campos obligatorios.';
}

wp_app_page_start('Create Recipe', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Recipe</h2>
            <p class="text-sm text-on-surface-variant">
                Crea una nueva receta con ingredientes, pasos, calorías y macros.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_recipes_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to recipes
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" action="" class="space-y-6">

            <input type="hidden" name="create_recipe_submit" value="1">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Recipe name
                </label>

                <input
                    type="text"
                    name="recipe_name"
                    value="<?= h(recipe_create_value('recipe_name')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: Oats with Banana"
                    maxlength="120"
                    required
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Recipe type
                </label>

                <?php $selected_type = recipe_create_value('type'); ?>

                <select
                    name="type"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    required
                >
                    <option value="">Select type</option>

                    <?php foreach ($recipe_types as $value => $label): ?>
                        <option value="<?= h($value) ?>" <?= $selected_type === $value ? 'selected' : '' ?>>
                            <?= h($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Description
                </label>

                <textarea
                    name="description"
                    rows="4"
                    maxlength="280"
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Describe brevemente la receta..."
                    required
                ><?= h(recipe_create_value('description')) ?></textarea>

                <p class="mt-1 text-xs text-on-surface-variant">
                    Máximo 280 caracteres.
                </p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Ingredients
                </label>

                <textarea
                    name="ingredients"
                    rows="5"
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: oats, banana, milk, cinnamon..."
                    required
                ><?= h(recipe_create_value('ingredients')) ?></textarea>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Preparation steps
                </label>

                <textarea
                    name="preparation_steps"
                    rows="6"
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Explain how to prepare the recipe step by step..."
                    required
                ><?= h(recipe_create_value('preparation_steps')) ?></textarea>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Calories
                    </label>

                    <input
                        type="number"
                        name="calories"
                        min="0"
                        value="<?= h(recipe_create_value('calories')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="350"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Protein
                    </label>

                    <input
                        type="number"
                        step="0.1"
                        name="protein"
                        min="0"
                        value="<?= h(recipe_create_value('protein')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="12"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Carbs
                    </label>

                    <input
                        type="number"
                        step="0.1"
                        name="carbs"
                        min="0"
                        value="<?= h(recipe_create_value('carbs')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="60"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Fat
                    </label>

                    <input
                        type="number"
                        step="0.1"
                        name="fat"
                        min="0"
                        value="<?= h(recipe_create_value('fat')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="8"
                    >
                </div>

            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Recipe image URL
                </label>

                <input
                    id="recipeImageUrl"
                    type="url"
                    name="image_url"
                    value="<?= h(recipe_create_value('image_url')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="https://example.com/recipe.jpg"
                >

                <div
                    id="imagePreviewWrap"
                    class="mt-4 flex h-[190px] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high transition hover:border-primary-container"
                >
                    <div id="imagePreviewPlaceholder" class="flex flex-col items-center justify-center text-center text-on-surface-variant">
                        <span class="material-symbols-outlined mb-2 text-4xl text-on-surface-variant/60">restaurant</span>
                        <span class="text-xs font-bold">Image preview</span>
                        <span class="mt-1 text-[11px] text-on-surface-variant/70">Optional</span>
                    </div>

                    <img
                        id="imagePreview"
                        src=""
                        alt="Recipe image preview"
                        class="hidden h-full w-full object-cover"
                    >
                </div>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Recipe
                </button>

                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-recipes')) ?>"
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
    const imageInput = document.getElementById('recipeImageUrl');
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