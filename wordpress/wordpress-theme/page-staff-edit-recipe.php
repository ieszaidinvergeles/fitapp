<?php
/*
Template Name: Staff Edit Recipe
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$recipe_id = (int)($_GET['id'] ?? $_POST['recipe_id'] ?? 0);

$manage_recipes_url = home_url('/?pagename=staff-manage-recipes');
$edit_recipe_url = home_url('/?pagename=staff-edit-recipe&id=' . $recipe_id);

if ($recipe_id <= 0) {
    wp_safe_redirect($manage_recipes_url);
    exit;
}

function recipe_edit_value(array $recipe, string $key, $default = '')
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];

        if ($value === '-' || $value === '—') {
            return '';
        }

        return $value;
    }

    return $recipe[$key] ?? $default;
}

function recipe_decode_macros($raw): array
{
    if (is_array($raw)) {
        return $raw;
    }

    if (is_string($raw)) {
        $clean = str_replace("'", '"', $raw);
        $decoded = json_decode($clean, true);

        return is_array($decoded) ? $decoded : [];
    }

    return [];
}

$recipe_types = [
    'breakfast' => 'Breakfast',
    'lunch' => 'Lunch',
    'dinner' => 'Dinner',
    'snack' => 'Snack',
    'pre_workout' => 'Pre Workout',
    'post_workout' => 'Post Workout',
];

$recipe_response = api_get('/recipes/' . $recipe_id, auth: true);
$recipe = [];

if (($recipe_response['result'] ?? false) !== false && is_array($recipe_response['result'] ?? null)) {
    $recipe = $recipe_response['result'];
} else {
    $flash_error = api_message($recipe_response) ?: 'No se pudo cargar la receta.';
}

$macros = recipe_decode_macros($recipe['macros_json'] ?? []);
$current_image = $recipe['image_url'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_recipe_submit'])) {
    $fat = $_POST['fat'] !== '' ? (float)$_POST['fat'] : null;
    $carbs = $_POST['carbs'] !== '' ? (float)$_POST['carbs'] : null;
    $protein = $_POST['protein'] !== '' ? (float)$_POST['protein'] : null;

    $macros_payload = [];

    if ($fat !== null) {
        $macros_payload['fat'] = $fat;
    }

    if ($carbs !== null) {
        $macros_payload['carbs'] = $carbs;
    }

    if ($protein !== null) {
        $macros_payload['protein'] = $protein;
    }

    $payload = [
        'name' => trim((string)($_POST['recipe_name'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'ingredients' => trim((string)($_POST['ingredients'] ?? '')),
        'preparation_steps' => trim((string)($_POST['preparation_steps'] ?? '')),
        'calories' => $_POST['calories'] !== '' ? (int)$_POST['calories'] : null,
        'macros_json' => !empty($macros_payload) ? json_encode($macros_payload) : '',
        'type' => trim((string)($_POST['type'] ?? '')),
        'image_url' => trim((string)($_POST['image_url'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== null && $value !== '-' && $value !== '—';
    });

    $update_response = api_put('/recipes/' . $recipe_id, $payload, auth: true);

    if (($update_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_recipes_url . '&notice=updated');
        exit;
    }

    $flash_error = api_message($update_response) ?: 'No se pudo actualizar la receta.';
}

wp_app_page_start('Edit Recipe', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Recipe</h2>
            <p class="text-sm text-on-surface-variant">
                Modifica los datos de la receta, sus macros y pasos de preparación.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_recipes_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to recipes
        </a>
    </section>

    <?php if ($recipe): ?>
        <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
            <form method="post" action="<?= esc_url($edit_recipe_url) ?>" class="space-y-6">

                <input type="hidden" name="edit_recipe_submit" value="1">
                <input type="hidden" name="recipe_id" value="<?= (int)$recipe_id ?>">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Recipe name
                    </label>

                    <input
                        type="text"
                        name="recipe_name"
                        value="<?= h($_POST['recipe_name'] ?? ($recipe['name'] ?? '')) ?>"
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

                    <?php $selected_type = $_POST['type'] ?? ($recipe['type'] ?? ''); ?>

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
                    ><?= h($_POST['description'] ?? ($recipe['description'] ?? '')) ?></textarea>

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
                    ><?= h($_POST['ingredients'] ?? ($recipe['ingredients'] ?? '')) ?></textarea>
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
                    ><?= h($_POST['preparation_steps'] ?? ($recipe['preparation_steps'] ?? '')) ?></textarea>
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
                            value="<?= h($_POST['calories'] ?? ($recipe['calories'] ?? '')) ?>"
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
                            value="<?= h($_POST['protein'] ?? ($macros['protein'] ?? '')) ?>"
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
                            value="<?= h($_POST['carbs'] ?? ($macros['carbs'] ?? '')) ?>"
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
                            value="<?= h($_POST['fat'] ?? ($macros['fat'] ?? '')) ?>"
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
                        value="<?= h($_POST['image_url'] ?? ($recipe['image_url'] ?? '')) ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="https://example.com/recipe.jpg"
                    >

                    <div
                        id="imagePreviewWrap"
                        class="mt-4 flex h-[190px] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high transition hover:border-primary-container"
                    >
                        <div id="imagePreviewPlaceholder" class="<?= $current_image ? 'hidden' : 'flex' ?> flex-col items-center justify-center text-center text-on-surface-variant">
                            <span class="material-symbols-outlined mb-2 text-4xl text-on-surface-variant/60">restaurant</span>
                            <span class="text-xs font-bold">Image preview</span>
                            <span class="mt-1 text-[11px] text-on-surface-variant/70">Optional</span>
                        </div>

                        <img
                            id="imagePreview"
                            src="<?= esc_url($current_image) ?>"
                            alt="Recipe image preview"
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
                        href="<?= esc_url($manage_recipes_url) ?>"
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