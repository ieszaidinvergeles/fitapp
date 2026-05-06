<?php
/*
Template Name: Staff View Recipe
*/
require_once 'functions.php';
require_advanced();

$recipe_id = (int)($_GET['id'] ?? 0);
$flash_error = '';

$manage_recipes_url = home_url('/?pagename=staff-manage-recipes');
$edit_recipe_url = home_url('/?pagename=staff-edit-recipe&id=' . $recipe_id);

if ($recipe_id <= 0) {
    wp_safe_redirect($manage_recipes_url);
    exit;
}

function view_recipe_value(array $recipe, array $keys, $default = '-')
{
    foreach ($keys as $key) {
        if (isset($recipe[$key]) && $recipe[$key] !== null && $recipe[$key] !== '') {
            return $recipe[$key];
        }
    }

    return $default;
}

function view_recipe_type_label($type): string
{
    $type = trim((string)$type);

    if ($type === '' || $type === '-' || $type === '—') {
        return '-';
    }

    return ucwords(str_replace('_', ' ', $type));
}

function view_recipe_decode_macros($raw): array
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

$recipe_response = api_get('/recipes/' . $recipe_id, auth: true);
$recipe = [];

if (($recipe_response['result'] ?? false) !== false && is_array($recipe_response['result'] ?? null)) {
    $recipe = $recipe_response['result'];
} else {
    $flash_error = api_message($recipe_response) ?: 'No se pudo cargar la receta.';
}

$name = view_recipe_value($recipe, ['name'], 'Recipe');
$description = view_recipe_value($recipe, ['description'], 'No description available.');
$ingredients = view_recipe_value($recipe, ['ingredients'], '-');
$preparation_steps = view_recipe_value($recipe, ['preparation_steps'], '-');
$calories = view_recipe_value($recipe, ['calories'], '-');
$type = view_recipe_type_label(view_recipe_value($recipe, ['type'], '-'));
$image_url = view_recipe_value($recipe, ['image_url'], '');

$macros = view_recipe_decode_macros($recipe['macros_json'] ?? []);
$protein = $macros['protein'] ?? '-';
$carbs = $macros['carbs'] ?? '-';
$fat = $macros['fat'] ?? '-';

$default_image = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?q=80&w=1200&auto=format&fit=crop';

wp_app_page_start('View Recipe', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-primary-container">
                Recipe #<?= (int)$recipe_id ?>
            </p>

            <h2 class="mt-2 text-3xl font-black uppercase tracking-tight sm:text-4xl">
                <?= h($name) ?>
            </h2>

            <p class="mt-2 text-sm text-on-surface-variant">
                Vista completa de la receta seleccionada.
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <a
                href="<?= esc_url($edit_recipe_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
            >
                Edit recipe
            </a>

            <a
                href="<?= esc_url($manage_recipes_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
            >
                ← Back to recipes
            </a>
        </div>
    </section>

    <?php if ($recipe): ?>
        <section class="overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container shadow-lg">
            <div class="grid grid-cols-1 lg:grid-cols-[380px_minmax(0,1fr)]">

                <div class="relative min-h-[300px] border-b border-outline-variant/20 bg-surface-container-high lg:border-b-0 lg:border-r">
                    <img
                        src="<?= esc_url($image_url ?: $default_image) ?>"
                        alt="<?= h($name) ?>"
                        class="absolute inset-0 h-full w-full object-cover"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-background/85 via-background/25 to-transparent"></div>

                    <div class="absolute bottom-0 left-0 p-5">
                        <span class="mb-2 inline-flex rounded-full bg-primary-container px-3 py-1 text-[10px] font-black uppercase tracking-wide text-on-primary-container">
                            <?= h($type) ?>
                        </span>
                    </div>
                </div>

                <div class="p-5 sm:p-8">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Calories</p>
                            <p class="mt-2 text-lg font-bold"><?= h((string)$calories) ?></p>
                        </div>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Protein</p>
                            <p class="mt-2 text-lg font-bold"><?= h((string)$protein) ?>g</p>
                        </div>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Carbs</p>
                            <p class="mt-2 text-lg font-bold"><?= h((string)$carbs) ?>g</p>
                        </div>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">Fat</p>
                            <p class="mt-2 text-lg font-bold"><?= h((string)$fat) ?>g</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-outline-variant/20 bg-surface-container-high p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                            Description
                        </p>

                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-on-surface-variant">
                            <?= h($description) ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <article class="rounded-3xl border border-outline-variant/20 bg-surface-container p-5 sm:p-6">
                <div class="mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-container">shopping_basket</span>
                    <h3 class="text-lg font-bold">Ingredients</h3>
                </div>

                <p class="whitespace-pre-line text-sm leading-7 text-on-surface-variant">
                    <?= h($ingredients) ?>
                </p>
            </article>

            <article class="rounded-3xl border border-outline-variant/20 bg-surface-container p-5 sm:p-6">
                <div class="mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-container">restaurant_menu</span>
                    <h3 class="text-lg font-bold">Preparation Steps</h3>
                </div>

                <p class="whitespace-pre-line text-sm leading-7 text-on-surface-variant">
                    <?= h($preparation_steps) ?>
                </p>
            </article>
        </section>

        <?php if ($image_url): ?>
            <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-5 sm:p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                    Image URL
                </p>

                <a
                    href="<?= esc_url($image_url) ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-primary-container hover:underline"
                >
                    Open image
                    <span class="material-symbols-outlined text-base">open_in_new</span>
                </a>
            </section>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>