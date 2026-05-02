<?php
/*
Template Name: Staff Manage Exercises
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page'] ?? 1));
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

$listResp = api_get('/exercises?page=' . $page, auth: true);
$exercises = [];
$pagination = [];

if (($listResp['result'] ?? null) !== false) {
    $exercises = extract_exercises_response($listResp);

    if (!empty($listResp['result']['meta']) && is_array($listResp['result']['meta'])) {
        $pagination = $listResp['result']['meta'];
    }
}

$current_page = max(1, (int)($pagination['current_page'] ?? $page));
$last_page = max(1, (int)($pagination['last_page'] ?? 1));

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

<div class="space-y-6">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Exercise List</h2>
            <p class="text-sm text-on-surface-variant">
                Gestiona ejercicios, grupos musculares, dificultad e información técnica.
            </p>
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
            $muscle_group = exercise_value($exercise, ['muscle_group', 'target_muscle', 'body_part'], '-');
            $difficulty = exercise_value($exercise, ['difficulty_level', 'difficulty'], '-');
            $equipment = exercise_value($exercise, ['equipment', 'equipment_name'], '-');
            $description = exercise_value($exercise, ['description', 'instructions'], '');

            $image = $exercise['image_url']
                ?? $exercise['image']
                ?? $exercise['photo_url']
                ?? $default_images[$index % count($default_images)];
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
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
                            <p class="text-lg font-bold break-words">
                                <?= h($name) ?>
                            </p>

                            <p class="text-sm text-on-surface-variant break-words">
                                Muscle group: <?= h((string)$muscle_group) ?>
                                • Difficulty: <?= h(ucfirst((string)$difficulty)) ?>
                            </p>

                            <p class="text-sm text-on-surface-variant break-words">
                                Equipment: <?= h((string)$equipment) ?>
                            </p>

                            <?php if ($description): ?>
                                <p class="mt-1 line-clamp-2 text-sm text-on-surface-variant break-words">
                                    <?= h((string)$description) ?>
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
        <section class="flex flex-wrap items-center justify-center gap-2 pt-2">
            <?php if ($current_page > 1): ?>
                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-exercises&page=' . ($current_page - 1))) ?>"
                    class="rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                >
                    Previous
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $current_page - 2);
            $end = min($last_page, $current_page + 2);
            ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-exercises&page=' . $i)) ?>"
                    class="rounded-lg border px-3 py-2 text-sm transition <?= $i === $current_page
                        ? 'border-primary-container bg-primary-container font-bold text-on-primary-container'
                        : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                >
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($current_page < $last_page): ?>
                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-exercises&page=' . ($current_page + 1))) ?>"
                    class="rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                >
                    Next
                </a>
            <?php endif; ?>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>