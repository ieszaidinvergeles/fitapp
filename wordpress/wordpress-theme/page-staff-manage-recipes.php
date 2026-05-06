<?php
/*
Template Name: Staff Manage Recipes
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'deleted') {
    $flash_success = 'Receta eliminada correctamente.';
} elseif ($notice === 'created') {
    $flash_success = 'Receta creada correctamente.';
} elseif ($notice === 'updated') {
    $flash_success = 'Receta actualizada correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $recipe_id = (int)($_POST['recipe_id'] ?? 0);

    if ($recipe_id > 0) {
        $delete_response = api_delete('/recipes/' . $recipe_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_safe_redirect(home_url('/?pagename=staff-manage-recipes&notice=deleted'));
            exit;
        }

        $flash_error = api_message($delete_response) ?: 'No se pudo eliminar la receta.';
    }
}

function recipe_extract_list(array $response): array
{
    if (($response['result'] ?? false) === false) {
        return [];
    }

    if (!empty($response['result']['data']) && is_array($response['result']['data'])) {
        return $response['result']['data'];
    }

    if (!empty($response['result']) && is_array($response['result'])) {
        return $response['result'];
    }

    return [];
}

function recipe_value(array $recipe, array $keys, $default = '-')
{
    foreach ($keys as $key) {
        if (isset($recipe[$key]) && $recipe[$key] !== null && $recipe[$key] !== '') {
            return $recipe[$key];
        }
    }

    return $default;
}

function recipe_page_url(int $page): string
{
    return home_url('/?pagename=staff-manage-recipes&page_num=' . $page);
}

function recipe_short_text($value, int $limit = 90): string
{
    $text = trim((string)$value);

    if ($text === '' || $text === '-' || $text === '—') {
        return '-';
    }

    if (function_exists('mb_strlen') && mb_strlen($text) > $limit) {
        return mb_substr($text, 0, $limit) . '...';
    }

    if (strlen($text) > $limit) {
        return substr($text, 0, $limit) . '...';
    }

    return $text;
}

function recipe_macros_label($macros): string
{
    if (is_string($macros)) {
        $decoded = json_decode($macros, true);
        $macros = is_array($decoded) ? $decoded : [];
    }

    if (!is_array($macros) || !$macros) {
        return '-';
    }

    $fat = $macros['fat'] ?? null;
    $carbs = $macros['carbs'] ?? null;
    $protein = $macros['protein'] ?? null;

    $parts = [];

    if ($protein !== null) {
        $parts[] = 'P: ' . $protein . 'g';
    }

    if ($carbs !== null) {
        $parts[] = 'C: ' . $carbs . 'g';
    }

    if ($fat !== null) {
        $parts[] = 'F: ' . $fat . 'g';
    }

    return $parts ? implode(' · ', $parts) : '-';
}

function recipe_type_label($type): string
{
    $type = trim((string)$type);

    if ($type === '' || $type === '-') {
        return '-';
    }

    return ucwords(str_replace('_', ' ', $type));
}

/*
|--------------------------------------------------------------------------
| Cargar todas las recetas
|--------------------------------------------------------------------------
*/
$all_recipes = [];
$seen_ids = [];
$listResp = ['result' => []];

for ($api_page = 1; $api_page <= 50; $api_page++) {
    $response = api_get('/recipes?page=' . $api_page, auth: true);

    if (($response['result'] ?? null) === false) {
        $listResp = $response;
        break;
    }

    $items = recipe_extract_list($response);

    if (empty($items)) {
        break;
    }

    $added_this_page = 0;

    foreach ($items as $item) {
        $id = (int)($item['id'] ?? 0);

        if ($id > 0 && isset($seen_ids[$id])) {
            continue;
        }

        if ($id > 0) {
            $seen_ids[$id] = true;
        }

        $all_recipes[] = $item;
        $added_this_page++;
    }

    $listResp = $response;

    if ($added_this_page === 0 || count($items) < 10) {
        break;
    }
}

$total = count($all_recipes);
$last_page = max(1, (int)ceil($total / $per_page));

if ($page > $last_page) {
    $page = $last_page;
}

$current_page = $page;
$offset = ($current_page - 1) * $per_page;
$recipes = array_slice($all_recipes, $offset, $per_page);

$from = $total > 0 ? $offset + 1 : 0;
$to = $total > 0 ? min($total, $offset + count($recipes)) : 0;

wp_app_page_start('Manage Recipes', true);
?>

<?php if (($listResp['result'] ?? null) === false): ?>
    <?php show_error(api_message($listResp)); ?>
<?php endif; ?>

<?php if ($flash_success): ?>
    <?php show_success($flash_success); ?>
<?php endif; ?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Recipe List</h2>
            <p class="text-sm text-on-surface-variant">
                Gestiona recetas, ingredientes, pasos de preparación, calorías y macros.
            </p>

            <?php if ($total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> RECIPES REGISTERED · PAGE <?= h((string)$current_page) ?> OF <?= h((string)$last_page) ?>
                </p>
            <?php endif; ?>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-recipe')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
        >
            <span class="text-base leading-none">+</span>
            <span>Create recipe</span>
        </a>
    </section>

    <section class="space-y-3">
        <?php foreach ($recipes as $index => $recipe): ?>
            <?php
            $recipe_id = (int)($recipe['id'] ?? 0);

            // Cargar detalle completo de la receta
            if ($recipe_id > 0) {
                $detail_response = api_get('/recipes/' . $recipe_id, auth: true);

                if (($detail_response['result'] ?? false) !== false && is_array($detail_response['result'] ?? null)) {
                    $recipe = array_replace_recursive($recipe, $detail_response['result']);
                }
            }

            $name = recipe_value($recipe, ['name'], 'Recipe');
            $description = recipe_value($recipe, ['description'], '');
            $ingredients = recipe_value($recipe, ['ingredients'], '');
            $preparation_steps = recipe_value($recipe, ['preparation_steps'], '');
            $calories = recipe_value($recipe, ['calories'], '-');
            $type = recipe_type_label(recipe_value($recipe, ['type'], '-'));

            $macros_raw = $recipe['macros_json'] ?? [];

            if (is_string($macros_raw)) {
                $clean_macros = str_replace("'", '"', $macros_raw);
                $decoded_macros = json_decode($clean_macros, true);
                $macros_raw = is_array($decoded_macros) ? $decoded_macros : [];
            }

            $macros = recipe_macros_label($macros_raw);

            $image = fitapp_public_asset_url($recipe['image_url'] ?? '');
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <?php fitapp_render_image_or_placeholder($image, (string)$name, 'h-full w-full object-cover', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'restaurant', 'No image'); ?>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-lg font-bold break-words">
                                    <?= h($name) ?>
                                </p>

                                <span class="rounded-full bg-primary-container/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-primary-container">
                                    #<?= h((string)$recipe_id) ?>
                                </span>

                                <span class="rounded-full border border-outline-variant/30 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-on-surface-variant">
                                    <?= h($type) ?>
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-on-surface-variant break-words">
                                Calories:
                                <span class="font-semibold text-on-surface"><?= h((string)$calories) ?></span>
                                · Macros:
                                <span class="font-semibold text-on-surface"><?= h($macros) ?></span>
                            </p>

                            <?php if ($description && $description !== '-'): ?>
                                <p class="mt-1 line-clamp-2 text-sm text-on-surface-variant break-words">
                                    <?= h(recipe_short_text($description, 120)) ?>
                                </p>
                            <?php endif; ?>

                            <p class="mt-1 text-xs text-on-surface-variant break-words">
                                Ingredients:
                                <span class="text-on-surface"><?= h(recipe_short_text($ingredients, 100)) ?></span>
                            </p>

                            <p class="mt-1 text-xs text-on-surface-variant break-words">
                                Steps:
                                <span class="text-on-surface"><?= h(recipe_short_text($preparation_steps, 100)) ?></span>
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-recipe&id=' . $recipe_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-recipe&id=' . $recipe_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar esta receta?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="recipe_id" value="<?= $recipe_id ?>">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg border border-error/40 px-3 py-2 text-sm text-error transition hover:bg-error/10"
                            >
                                Delete
                            </button>
                        </form>
                    </div>

                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$recipes): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No recipes found.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($last_page > 1): ?>
        <section class="flex flex-col gap-4 rounded-xl border border-outline-variant/20 bg-surface-container p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-on-surface-variant">
                Showing
                <span class="font-bold text-on-surface"><?= h((string)$from) ?></span>
                -
                <span class="font-bold text-on-surface"><?= h((string)$to) ?></span>
                of
                <span class="font-bold text-on-surface"><?= h((string)$total) ?></span>
                recipes
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">
                <?php if ($current_page > 1): ?>
                    <a
                        href="<?= esc_url(recipe_page_url($current_page - 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        ← Previous
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        ← Previous
                    </span>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end = min($last_page, $current_page + 2);
                ?>

                <?php if ($start > 1): ?>
                    <a
                        href="<?= esc_url(recipe_page_url(1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        1
                    </a>

                    <?php if ($start > 2): ?>
                        <span class="px-1 text-sm text-on-surface-variant">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a
                        href="<?= esc_url(recipe_page_url($i)) ?>"
                        class="rounded-full border px-4 py-2 text-sm font-bold transition <?= $i === $current_page
                            ? 'border-primary-container bg-primary-container text-on-primary-container shadow-[0_0_18px_rgba(212,251,0,0.22)]'
                            : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                    >
                        <?= h((string)$i) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($end < $last_page): ?>
                    <?php if ($end < $last_page - 1): ?>
                        <span class="px-1 text-sm text-on-surface-variant">...</span>
                    <?php endif; ?>

                    <a
                        href="<?= esc_url(recipe_page_url($last_page)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        <?= h((string)$last_page) ?>
                    </a>
                <?php endif; ?>

                <?php if ($current_page < $last_page): ?>
                    <a
                        href="<?= esc_url(recipe_page_url($current_page + 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        Next →
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        Next →
                    </span>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
