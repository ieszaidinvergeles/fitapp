<?php
/*
Template Name: Staff Manage Routines
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'deleted') {
    $flash_success = 'Routine deleted successfully.';
} elseif ($notice === 'created') {
    $flash_success = 'Routine created successfully.';
} elseif ($notice === 'updated') {
    $flash_success = 'Routine updated successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $routine_id = (int)($_POST['routine_id'] ?? 0);

    if ($routine_id > 0) {
        $delete_response = api_delete('/routines/' . $routine_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_safe_redirect(home_url('/?pagename=staff-manage-routines&notice=deleted'));
            exit;
        }

        $flash_error = api_message($delete_response) ?: 'Could not delete the routine.';
    }
}

function routine_extract_list(array $response): array
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

function routine_response_last_page(array $response): ?int
{
    $last_page = $response['result']['meta']['last_page']
        ?? $response['result']['last_page']
        ?? null;

    return $last_page !== null ? max(1, (int)$last_page) : null;
}

function routine_build_lookup_by_id(array $items): array
{
    $lookup = [];

    foreach ($items as $item) {
        if (is_array($item) && isset($item['id'])) {
            $lookup[(int)$item['id']] = $item;
        }
    }

    return $lookup;
}

function routine_value(array $routine, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (!isset($routine[$key]) || $routine[$key] === null) {
            continue;
        }

        $clean_value = trim((string)$routine[$key]);

        if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€”' && strtoupper($clean_value) !== 'NULL') {
            return $routine[$key];
        }
    }

    return $default;
}

function routine_page_url(int $page): string
{
    return home_url('/?pagename=staff-manage-routines&page_num=' . $page);
}

function routine_format_label($value): string
{
    $value = trim((string)$value);

    if ($value === '' || $value === '-' || $value === '—' || $value === 'â€”' || strtoupper($value) === 'NULL') {
        return '';
    }

    return ucfirst(str_replace('_', ' ', $value));
}

function routine_short_text($value, int $limit = 110): string
{
    $text = trim((string)$value);

    if ($text === '' || $text === '-' || $text === '—' || $text === 'â€”' || strtoupper($text) === 'NULL') {
        return '';
    }

    if (function_exists('mb_strlen') && mb_strlen($text) > $limit) {
        return mb_substr($text, 0, $limit) . '...';
    }

    if (strlen($text) > $limit) {
        return substr($text, 0, $limit) . '...';
    }

    return $text;
}
/*
|--------------------------------------------------------------------------
| Load all routines
|--------------------------------------------------------------------------
| Walk several pages in case Laravel returns batches of 10.
*/
$paged = fitapp_api_get_page('/routines', $page, $per_page, true);
$all_routines = [];
$seen_ids = [];
$listResp = ['result' => []];

for ($api_page = 1; $api_page <= 0; $api_page++) {
    $response = api_get('/routines?page=' . $api_page, auth: true);

    if (($response['result'] ?? null) === false) {
        $listResp = $response;
        break;
    }

    $items = routine_extract_list($response);

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

        $all_routines[] = $item;
        $added_this_page++;
    }

    $listResp = $response;

    $last_api_page = routine_response_last_page($response);

    if ($added_this_page === 0 || ($last_api_page !== null && $api_page >= $last_api_page)) {
        break;
    }
}

usort($all_routines, function ($a, $b) {
    return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
});

$all_diet_plans = [];
$seen_diet_plan_ids = [];

for ($api_page = 1; $api_page <= 0; $api_page++) {
    $diet_response = api_get('/diet-plans?page=' . $api_page, auth: true);

    if (($diet_response['result'] ?? null) === false) {
        break;
    }

    $diet_items = routine_extract_list($diet_response);

    if (!$diet_items) {
        break;
    }

    $added_this_page = 0;

    foreach ($diet_items as $diet_item) {
        $diet_id = (int)($diet_item['id'] ?? 0);

        if ($diet_id > 0 && isset($seen_diet_plan_ids[$diet_id])) {
            continue;
        }

        if ($diet_id > 0) {
            $seen_diet_plan_ids[$diet_id] = true;
        }

        $all_diet_plans[] = $diet_item;
        $added_this_page++;
    }

    $last_api_page = routine_response_last_page($diet_response);

    if ($added_this_page === 0 || ($last_api_page !== null && $api_page >= $last_api_page)) {
        break;
    }
}

$diet_plans_by_id = routine_build_lookup_by_id($all_diet_plans);

$total = count($all_routines);
$last_page = max(1, (int)ceil($total / $per_page));

if ($page > $last_page) {
    $page = $last_page;
}

$current_page = $page;
$offset = ($current_page - 1) * $per_page;
$routines = array_slice($all_routines, $offset, $per_page);

$from = $total > 0 ? $offset + 1 : 0;
$to = $total > 0 ? min($total, $offset + count($routines)) : 0;

$listResp = $paged['response'];
$routines = $paged['items'];
$pagination = $paged['meta'];
$current_page = $pagination['current_page'];
$last_page = $pagination['last_page'];
$total = $pagination['total'];
$from = $pagination['from'];
$to = $pagination['to'];

wp_app_page_start('Manage Routines', true);
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
            <h2 class="text-lg font-bold">Routine List</h2>
            <p class="text-sm text-on-surface-variant">
                Manage routines, edit them, or delete them easily.
            </p>

            <?php if ($total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> ROUTINES REGISTERED · PAGE <?= h((string)$current_page) ?> OF <?= h((string)$last_page) ?>
                </p>
            <?php endif; ?>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-routine')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition hover:scale-[1.01]"
        >
            <span>+</span>
            <span>Create routine</span>
        </a>
    </section>

    <section class="space-y-3">

        <?php foreach ($routines as $index => $r): ?>
            <?php
            $routine_id = (int)($r['id'] ?? 0);

            $name = routine_value($r, ['name'], 'Routine');
            $difficulty = routine_format_label(routine_value($r, ['difficulty_level'], ''));
            $duration = routine_value($r, ['estimated_duration_min'], '');
            $description = routine_value($r, ['description'], '');

            $creator = $r['creator'] ?? [];
            $creator_name = routine_value($creator, ['full_name', 'username', 'email'], '');
            $diet_plan_id = (int)routine_value($r, ['associated_diet_plan_id'], 0);
            $diet_plan = $r['diet_plan'] ?? ($diet_plans_by_id[$diet_plan_id] ?? []);
            $diet_plan_name = routine_value($diet_plan, ['name'], '');
            $routine_meta_bits = [];

            if ($difficulty !== '') {
                $routine_meta_bits[] = 'Difficulty: ' . h($difficulty);
            }

            if ($duration !== '' && $duration !== '-') {
                $routine_meta_bits[] = 'Duration: ' . h((string)$duration) . ' min';
            }

            if ($creator_name !== '') {
                $routine_meta_bits[] = 'Creator: ' . h((string)$creator_name);
            }

            if ($diet_plan_name !== '') {
                $routine_meta_bits[] = 'Diet plan: ' . h((string)$diet_plan_name);
            } elseif ($diet_plan_id > 0) {
                $routine_meta_bits[] = 'Diet plan: #' . h((string)$diet_plan_id);
            }

            $img = fitapp_public_asset_url($r['cover_image_url']
                ?? $r['image_url']
                ?? $r['image']
                ?? $r['photo_url']
                ?? '');
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 flex-1 gap-4">

                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <?php fitapp_render_image_or_placeholder($img, (string)$name, 'h-full w-full object-cover', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'fitness_center', 'No image'); ?>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-lg font-bold break-words">
                                    <?= h($name) ?>
                                </p>

                                <span class="rounded-full bg-primary-container/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-primary-container">
                                    #<?= h((string)$routine_id) ?>
                                </span>
                            </div>

                            <?php if ($routine_meta_bits): ?>
                                <p class="mt-1 text-sm text-on-surface-variant">
                                    <?= implode(' | ', $routine_meta_bits) ?>
                                </p>
                            <?php endif; ?>

                            <?php $short_description = routine_short_text($description, 110); ?>

                            <?php if ($short_description): ?>
                                <p class="mt-1 text-sm text-on-surface-variant break-words">
                                    <?= h($short_description) ?>
                                </p>
                            <?php endif; ?>
                        </div>

                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-routine&id=' . $routine_id)) ?>"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-outline-variant/30 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-routine&id=' . $routine_id)) ?>"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-outline-variant/30 text-sm transition hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('Delete this routine?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="routine_id" value="<?= $routine_id ?>">

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-error/40 text-sm text-error transition hover:bg-error/10"
                            >
                                Delete
                            </button>
                        </form>

                    </div>

                </div>
            </article>

        <?php endforeach; ?>

        <?php if (!$routines): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No routines found.</p>
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
                routines
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">
                <?php if ($current_page > 1): ?>
                    <a
                        href="<?= esc_url(routine_page_url($current_page - 1)) ?>"
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
                        href="<?= esc_url(routine_page_url(1)) ?>"
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
                        href="<?= esc_url(routine_page_url($i)) ?>"
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
                        href="<?= esc_url(routine_page_url($last_page)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        <?= h((string)$last_page) ?>
                    </a>
                <?php endif; ?>

                <?php if ($current_page < $last_page): ?>
                    <a
                        href="<?= esc_url(routine_page_url($current_page + 1)) ?>"
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
