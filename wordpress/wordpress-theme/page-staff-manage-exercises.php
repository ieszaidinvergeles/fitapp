<?php
/*
Template Name: Staff Manage Exercises
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error = '';

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

function extract_exercises_response(array $response): array
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

function exercise_value(array $exercise, array $keys, $default = '-')
{
    foreach ($keys as $key) {
        if (isset($exercise[$key]) && $exercise[$key] !== null && $exercise[$key] !== '') {
            return $exercise[$key];
        }
    }

    return $default;
}

function format_exercise_muscle_group(string $value): string
{
    if ($value === '' || $value === '-') {
        return '-';
    }

    return ucwords(str_replace('_', ' ', $value));
}

function staff_exercise_page_url(int $page): string
{
    return home_url('/?pagename=staff-manage-exercises&page_num=' . $page);
}

/*
|--------------------------------------------------------------------------
| Cargar TODOS los ejercicios
|--------------------------------------------------------------------------
| La API solo devuelve 10 por página aunque pidamos per_page=1000.
| Por eso recorremos /exercises?page=1, /exercises?page=2, etc.
*/
$all_exercises = [];
$seen_ids = [];
$listResp = ['result' => []];

for ($api_page = 1; $api_page <= 50; $api_page++) {
    $response = api_get('/exercises?page=' . $api_page, auth: true);

    if (($response['result'] ?? null) === false) {
        $listResp = $response;
        break;
    }

    $items = extract_exercises_response($response);

    if (empty($items)) {
        break;
    }

    $added_this_page = 0;

    foreach ($items as $item) {
        $id = (int)($item['id'] ?? 0);

        if ($id <= 0) {
            continue;
        }

        if (isset($seen_ids[$id])) {
            continue;
        }

        $seen_ids[$id] = true;
        $all_exercises[] = $item;
        $added_this_page++;
    }

    /*
     * Si una página no añade nada nuevo, paramos.
     * Así evitamos bucles si la API repite siempre la página 1.
     */
    if ($added_this_page === 0) {
        break;
    }

    /*
     * Si la API devuelve menos de 10, normalmente significa última página.
     */
    if (count($items) < 10) {
        break;
    }

    $listResp = $response;
}

$total = count($all_exercises);
$last_page = max(1, (int)ceil($total / $per_page));

if ($page > $last_page) {
    $page = $last_page;
}

$current_page = $page;
$offset = ($current_page - 1) * $per_page;
$exercises = array_slice($all_exercises, $offset, $per_page);

$default_images = [
    'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=600&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=600&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=600&auto=format&fit=crop',
    'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?q=80&w=600&auto=format&fit=crop',
];

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

            <?php if ($total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> exercises registered · Page <?= h((string)$current_page) ?> of <?= h((string)$last_page) ?>
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
        <?php foreach ($exercises as $index => $exercise): ?>
            <?php
            $exercise_id = (int)($exercise['id'] ?? 0);

            $name = exercise_value($exercise, ['name', 'title'], 'Exercise');
            $muscle_group_raw = (string)exercise_value($exercise, ['target_muscle_group', 'muscle_group', 'target_muscle', 'body_part'], '-');
            $muscle_group = format_exercise_muscle_group($muscle_group_raw);
            $description = exercise_value($exercise, ['description', 'instructions'], '');

            $image = $exercise['image_url']
                ?? $exercise['cover_image_url']
                ?? $exercise['image']
                ?? $exercise['photo_url']
                ?? $default_images[$index % count($default_images)];
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <img
                                src="<?= esc_url($image) ?>"
                                alt="<?= h($name) ?>"
                                class="h-full w-full object-cover"
                            >
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

                            <p class="mt-1 text-sm text-on-surface-variant break-words">
                                Muscle group:
                                <span class="font-semibold text-on-surface"><?= h($muscle_group) ?></span>
                            </p>

                            <?php if ($description): ?>
                                <p class="mt-1 line-clamp-2 text-sm text-on-surface-variant break-words">
                                    <?= h((string)$description) ?>
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

    <?php if ($last_page > 1): ?>
        <section class="mt-8 rounded-2xl border border-outline-variant/20 bg-surface-container p-4">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                <p class="text-sm text-on-surface-variant">
                    Showing
                    <span class="font-bold text-on-surface"><?= h((string)($offset + 1)) ?></span>
                    -
                    <span class="font-bold text-on-surface"><?= h((string)min($offset + $per_page, $total)) ?></span>
                    of
                    <span class="font-bold text-on-surface"><?= h((string)$total) ?></span>
                    exercises
                </p>

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

                    <?php
                    $start = max(1, $current_page - 2);
                    $end = min($last_page, $current_page + 2);
                    ?>

                    <?php if ($start > 1): ?>
                        <a
                            href="<?= esc_url(staff_exercise_page_url(1)) ?>"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant/30 text-sm font-bold transition hover:bg-surface-container-high"
                        >
                            1
                        </a>

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
                        >
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($end < $last_page): ?>
                        <?php if ($end < $last_page - 1): ?>
                            <span class="px-1 text-sm text-on-surface-variant">...</span>
                        <?php endif; ?>

                        <a
                            href="<?= esc_url(staff_exercise_page_url($last_page)) ?>"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant/30 text-sm font-bold transition hover:bg-surface-container-high"
                        >
                            <?= $last_page ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($current_page < $last_page): ?>
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