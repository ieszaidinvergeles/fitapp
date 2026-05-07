<?php
/*
Template Name: Staff Manage Diet Plans
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'deleted') {
    $flash_success = 'Plan de dieta eliminado correctamente.';
} elseif ($notice === 'created') {
    $flash_success = 'Plan de dieta creado correctamente.';
} elseif ($notice === 'updated') {
    $flash_success = 'Plan de dieta actualizado correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $diet_plan_id = (int)($_POST['diet_plan_id'] ?? 0);

    if ($diet_plan_id > 0) {
        $delete_response = api_delete('/diet-plans/' . $diet_plan_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_safe_redirect(home_url('/?pagename=staff-manage-diet-plans&notice=deleted'));
            exit;
        }

        $flash_error = api_message($delete_response) ?: 'No se pudo eliminar el plan de dieta.';
    }
}

if (!function_exists('diet_plan_extract_list')) {
    function diet_plan_extract_list(array $response): array
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
}

if (!function_exists('diet_plan_value')) {
    function diet_plan_value(array $plan, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (!isset($plan[$key]) || $plan[$key] === null) {
                continue;
            }

            $clean_value = trim((string)$plan[$key]);

            if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€”' && strtoupper($clean_value) !== 'NULL') {
                return $plan[$key];
            }
        }

        return $default;
    }
}

/*
 * IMPORTANTE:
 * El backend de diet_plans parece devolver lista simple, no paginación real.
 * Por eso pedimos muchos registros y paginamos aquí manualmente.
 */
$all_diet_plans = [];
$listResp = ['result' => []];

$seen_ids = [];

for ($api_page = 1; $api_page <= 20; $api_page++) {
    $pageResp = api_get('/diet-plans?page=' . $api_page, auth: true);

    if (($pageResp['result'] ?? null) === false) {
        $listResp = $pageResp;
        break;
    }

    $items = diet_plan_extract_list($pageResp);

    if (!$items) {
        break;
    }

    $added_this_page = 0;

    foreach ($items as $item) {
        $item_id = (int)($item['id'] ?? 0);

        if ($item_id > 0 && isset($seen_ids[$item_id])) {
            continue;
        }

        if ($item_id > 0) {
            $seen_ids[$item_id] = true;
        }

        $all_diet_plans[] = $item;
        $added_this_page++;
    }

    $listResp = $pageResp;

    if ($added_this_page === 0 || count($items) < 10) {
        break;
    }
}

$total = count($all_diet_plans);
$last_page = max(1, (int)ceil($total / $per_page));

if ($page > $last_page) {
    $page = $last_page;
}

$current_page = $page;
$offset = ($current_page - 1) * $per_page;
$diet_plans = array_slice($all_diet_plans, $offset, $per_page);

$from = $total > 0 ? $offset + 1 : 0;
$to = $total > 0 ? min($total, $offset + count($diet_plans)) : 0;

wp_app_page_start('Manage Diet Plans', true);
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
            <h2 class="text-lg font-bold">Diet Plan List</h2>
            <p class="text-sm text-on-surface-variant">
                Gestiona planes de dieta, objetivos nutricionales y descripciones.
            </p>

            <?php if ($total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> DIET PLANS REGISTERED · PAGE <?= h((string)$current_page) ?> OF <?= h((string)$last_page) ?>
                </p>
            <?php endif; ?>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-diet-plan')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
        >
            <span class="text-base leading-none">+</span>
            <span>Create diet plan</span>
        </a>
    </section>

    <section class="space-y-3">
        <?php foreach ($diet_plans as $index => $plan): ?>
            <?php
            $real_index = $offset + $index;
            $diet_plan_id = (int)($plan['id'] ?? 0);

            $name = diet_plan_value($plan, ['name', 'title'], 'Diet Plan');
            $goal_description = h((string)diet_plan_value($plan, ['goal_description', 'description', 'notes', 'summary'], ''));

            $image = fitapp_public_asset_url($plan['cover_image_url']
                ?? $plan['image_url']
                ?? $plan['image']
                ?? $plan['photo_url']
                ?? '');
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
                                    #<?= h((string)$diet_plan_id) ?>
                                </span>
                            </div>

                            <?php if ($goal_description !== ''): ?>
                                <p class="mt-1 text-sm text-on-surface-variant break-words">
                                    Goal description:
                                    <span class="font-semibold text-on-surface">
                                        <?= $goal_description ?>
                                    </span>
                                </p>
                            <?php else: ?>
                                <p class="mt-1 text-sm italic text-on-surface-variant">
                                    No description available.
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-diet-plan&id=' . $diet_plan_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-diet-plan&id=' . $diet_plan_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar este plan de dieta?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="diet_plan_id" value="<?= $diet_plan_id ?>">
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

        <?php if (!$diet_plans): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No diet plans found.</p>
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
                diet plans
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">

                <?php if ($current_page > 1): ?>
                    <a
                        href="<?= esc_url(home_url('/?pagename=staff-manage-diet-plans&page_num=' . ($current_page - 1))) ?>"
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
                        href="<?= esc_url(home_url('/?pagename=staff-manage-diet-plans&page_num=1')) ?>"
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
                        href="<?= esc_url(home_url('/?pagename=staff-manage-diet-plans&page_num=' . $i)) ?>"
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
                        href="<?= esc_url(home_url('/?pagename=staff-manage-diet-plans&page_num=' . $last_page)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        <?= h((string)$last_page) ?>
                    </a>
                <?php endif; ?>

                <?php if ($current_page < $last_page): ?>
                    <a
                        href="<?= esc_url(home_url('/?pagename=staff-manage-diet-plans&page_num=' . ($current_page + 1))) ?>"
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
