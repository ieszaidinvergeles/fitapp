<?php
/*
Template Name: Staff Manage Exercises
*/
require_once 'functions.php';
require_advanced();

$page     = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error   = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'deleted') {
    $flash_success = 'Ejercicio eliminado correctamente.';
} elseif ($notice === 'created') {
    $flash_success = 'Ejercicio creado correctamente.';
} elseif ($notice === 'updated') {
    $flash_success = 'Ejercicio actualizado correctamente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $exercise_id = (int)($_POST['exercise_id'] ?? 0);

    if ($exercise_id > 0) {
        $delete_response = api_delete('/exercises/' . $exercise_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_redirect(home_url('/?pagename=staff-manage-exercises&notice=deleted'));
            exit;
        }

        $flash_error = api_message($delete_response) ?: 'No se pudo eliminar el ejercicio.';
    }
}

if (!function_exists('exercise_value')) {
    function exercise_value(array $exercise, array $keys, $default = '')
    {
        foreach ($keys as $key) {
            if (!isset($exercise[$key]) || $exercise[$key] === null) {
                continue;
            }

            $clean_value = trim((string)$exercise[$key]);

            if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€"' && strtoupper($clean_value) !== 'NULL') {
                return $exercise[$key];
            }
        }

        return $default;
    }
}

if (!function_exists('format_exercise_muscle_group')) {
    function format_exercise_muscle_group(string $value): string
    {
        if ($value === '' || $value === '-' || $value === '—' || $value === 'â€"' || strtoupper($value) === 'NULL') {
            return '';
        }

        return ucwords(str_replace('_', ' ', $value));
    }
}

if (!function_exists('staff_exercise_page_url')) {
    function staff_exercise_page_url(int $page): string
    {
        return home_url('/?pagename=staff-manage-exercises&page_num=' . $page);
    }
}

/*
|--------------------------------------------------------------------------
| Cargar ejercicios paginados desde backend
|--------------------------------------------------------------------------
| Una sola llamada por request — sin loops, sin riesgo de 504.
*/
$paged        = fitapp_api_get_page('/exercises', $page, $per_page, true);
$listResp     = $paged['response'];
$exercises    = $paged['items'];
$pagination   = $paged['meta'];
$current_page = $pagination['current_page'];
$last_page    = $pagination['last_page'];
$total        = $pagination['total'];
$offset       = max(0, $pagination['from'] - 1);

/*
 * Si el backend no confirma last_page pero hay $per_page resultados,
 * habilitamos Next para explorar (no sabemos si hay más).
 */
$has_next_unknown = ($last_page <= $current_page && count($exercises) >= $per_page);
$total_known      = ($last_page > 1 || $total > $per_page);

wp_app_page_start('Manage Exercises', true);
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
            <h2 class="text-lg font-bold">Exercise List</h2>
            <p class="text-sm text-on-surface-variant">
                Gestiona ejercicios, grupos musculares e información técnica.
            </p>

            <?php if ($total_known && $total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> exercises · Page <?= h((string)$current_page) ?> of <?= h((string)$last_page) ?>
                </p>
            <?php elseif ($exercises): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    Exercise list · Page <?= h((string)$current_page) ?>
                </p>
            <?php endif; ?>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-exercise')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
        >
            <span class="text-base leading-none">+</span>
            <span>Create exercise</span>
        </a>
    </section>

    <section class="space-y-3">
        <?php foreach ($exercises as $exercise): ?>
            <?php
            $exercise_id      = (int)($exercise['id'] ?? 0);
            $name             = exercise_value($exercise, ['name', 'title'], 'Exercise');
            $muscle_group_raw = (string)exercise_value($exercise, ['target_muscle_group', 'muscle_group', 'target_muscle', 'body_part'], '');
            $muscle_group     = format_exercise_muscle_group($muscle_group_raw);
            $description      = (string)exercise_value($exercise, ['description', 'instructions'], '');

            $image = fitapp_public_asset_url(
                $exercise['image_url']
                ?? $exercise['cover_image_url']
                ?? $exercise['image']
                ?? $exercise['photo_url']
                ?? ''
            );
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <?php fitapp_render_image_or_placeholder($image, (string)$name, 'h-full w-full object-cover', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'fitness_center', 'No image'); ?>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-lg font-bold break-words">
                                    <?= h($name) ?>
                                </p>

                                <span class="rounded-full bg-primary-container/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-primary-container">
                                    #<?= h((string)$exercise_id) ?>
                                </span>
                            </div>

                            <?php if ($muscle_group !== ''): ?>
                                <p class="mt-1 text-sm text-on-surface-variant break-words">
                                    Muscle group:
                                    <span class="font-semibold text-on-surface"><?= h($muscle_group) ?></span>
                                </p>
                            <?php endif; ?>

                            <?php if ($description !== ''): ?>
                                <p class="mt-1 line-clamp-2 text-sm text-on-surface-variant break-words">
                                    <?= h($description) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-exercise&id=' . $exercise_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-exercise&id=' . $exercise_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar este ejercicio?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="exercise_id" value="<?= $exercise_id ?>">
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

        <?php if (!$exercises): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No exercises found.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($current_page > 1 || $current_page < $last_page || $has_next_unknown): ?>
        <section class="mt-8 rounded-2xl border border-outline-variant/20 bg-surface-container p-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                <?php if ($total_known && $total > 0): ?>
                    <p class="text-sm text-on-surface-variant">
                        Showing
                        <span class="font-bold text-on-surface"><?= h((string)($offset + 1)) ?></span>
                        –
                        <span class="font-bold text-on-surface"><?= h((string)min($offset + $per_page, $total)) ?></span>
                        of
                        <span class="font-bold text-on-surface"><?= h((string)$total) ?></span>
                        exercises
                    </p>
                <?php else: ?>
                    <p class="text-sm text-on-surface-variant">
                        Page <span class="font-bold text-on-surface"><?= h((string)$current_page) ?></span>
                    </p>
                <?php endif; ?>

                <div class="flex flex-wrap items-center gap-2">

                    <?php if ($current_page > 1): ?>
                        <a
                            href="<?= esc_url(staff_exercise_page_url($current_page - 1)) ?>"
                            class="inline-flex items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-semibold transition hover:bg-surface-container-high"
                        >
                            ← Previous
                        </a>
                    <?php else: ?>
                        <span class="inline-flex cursor-not-allowed items-center justify-center rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-semibold text-on-surface-variant/40">
                            ← Previous
                        </span>
                    <?php endif; ?>

                    <?php if ($last_page > 1): ?>
                        <?php
                        $start = max(1, $current_page - 2);
                        $end   = min($last_page, $current_page + 2);
                        ?>

                        <?php if ($start > 1): ?>
                            <a
                                href="<?= esc_url(staff_exercise_page_url(1)) ?>"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant/30 text-sm font-bold transition hover:bg-surface-container-high"
                            >1</a>
                            <?php if ($start > 2): ?>
                                <span class="px-1 text-sm text-on-surface-variant">...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <a
                                href="<?= esc_url(staff_exercise_page_url($i)) ?>"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border text-sm font-bold transition <?= $i === $current_page
                                    ? 'border-primary-container bg-primary-container text-on-primary-container shadow-[0_8px_24px_rgba(212,251,0,0.18)]'
                                    : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                            ><?= $i ?></a>
                        <?php endfor; ?>

                        <?php if ($end < $last_page): ?>
                            <?php if ($end < $last_page - 1): ?>
                                <span class="px-1 text-sm text-on-surface-variant">...</span>
                            <?php endif; ?>
                            <a
                                href="<?= esc_url(staff_exercise_page_url($last_page)) ?>"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant/30 text-sm font-bold transition hover:bg-surface-container-high"
                            ><?= $last_page ?></a>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-primary-container bg-primary-container text-sm font-bold text-on-primary-container">
                            <?= $current_page ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($current_page < $last_page || $has_next_unknown): ?>
                        <a
                            href="<?= esc_url(staff_exercise_page_url($current_page + 1)) ?>"
                            class="inline-flex items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-semibold transition hover:bg-surface-container-high"
                        >
                            Next →
                        </a>
                    <?php else: ?>
                        <span class="inline-flex cursor-not-allowed items-center justify-center rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-semibold text-on-surface-variant/40">
                            Next →
                        </span>
                    <?php endif; ?>

                </div>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
