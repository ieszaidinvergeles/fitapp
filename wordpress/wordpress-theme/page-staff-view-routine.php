<?php
/*
Template Name: Staff View Routine
*/
require_once 'functions.php';
require_advanced();

$routine_id = (int)($_GET['id'] ?? 0);

if ($routine_id <= 0) {
    wp_redirect(home_url('/?pagename=staff-manage-routines'));
    exit;
}

function view_routine_value(array $data = null, string $key, $default = '-')
{
    if (!$data || !isset($data[$key]) || $data[$key] === null || $data[$key] === '') {
        return $default;
    }

    return $data[$key];
}

$routine_response = api_get('/routines/' . $routine_id, auth: true);
$routine = (($routine_response['result'] ?? false) !== false) ? $routine_response['result'] : null;
echo '<pre>';
print_r($routine);
echo '</pre>';
die();

if (!$routine || !is_array($routine)) {
    wp_redirect(home_url('/?pagename=staff-manage-routines'));
    exit;
}

$routine_name = view_routine_value($routine, 'name', 'Routine');
$difficulty = view_routine_value($routine, 'difficulty_level', '-');
$goal = view_routine_value($routine, 'goal', '-');
$description = view_routine_value($routine, 'description', 'No description available.');
$duration = view_routine_value($routine, 'estimated_duration_min', '-');

$exercises = $routine['ordered_exercises']
    ?? $routine['orderedExercises']
    ?? $routine['exercises']
    ?? [];

wp_app_page_start('View Routine', true);
?>

<?php if (($routine_response['result'] ?? null) === false): ?>
    <?php show_error(api_message($routine_response)); ?>
<?php endif; ?>

<div class="space-y-6">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Routine Details</h2>
            <p class="text-sm text-on-surface-variant">
                Consulta la información completa de esta rutina.
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <a
                href="<?= esc_url(home_url('/?pagename=staff-edit-routine&id=' . $routine_id)) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
            >
                Edit routine
            </a>

            <a
                href="<?= esc_url(home_url('/?pagename=staff-manage-routines')) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
            >
                ← Back to routines
            </a>
        </div>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-start">

            <div class="flex h-[150px] w-full shrink-0 items-center justify-center rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high sm:w-[180px]">
                <div class="flex flex-col items-center justify-center text-center text-on-surface-variant">
                    <span class="material-symbols-outlined mb-2 text-4xl text-on-surface-variant/60">fitness_center</span>
                    <span class="text-xs font-bold">Routine</span>
                </div>
            </div>

            <div class="min-w-0 flex-1 space-y-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-primary-container">
                        Routine #<?= h((string)$routine_id) ?>
                    </p>

                    <h3 class="mt-1 text-2xl font-black uppercase tracking-tight text-on-surface">
                        <?= h($routine_name) ?>
                    </h3>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                        <p class="text-xs uppercase text-on-surface-variant">Difficulty</p>
                        <p class="mt-1 font-bold"><?= h(ucfirst((string)$difficulty)) ?></p>
                    </div>

                    <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                        <p class="text-xs uppercase text-on-surface-variant">Goal</p>
                        <p class="mt-1 font-bold"><?= h((string)$goal) ?></p>
                    </div>

                    <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                        <p class="text-xs uppercase text-on-surface-variant">Duration</p>
                        <p class="mt-1 font-bold">
                            <?= $duration !== '-' ? h((string)$duration) . ' min' : '-' ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <h3 class="mb-2 text-lg font-bold">Description</h3>
        <p class="text-sm leading-relaxed text-on-surface-variant">
            <?= h($description) ?>
        </p>
    </section>

    <section class="space-y-3">
        <div>
            <h3 class="text-lg font-bold">Routine Exercises</h3>
            <p class="text-sm text-on-surface-variant">
                Ejercicios asociados a esta rutina.
            </p>
        </div>

        <?php if (!empty($exercises) && is_array($exercises)): ?>
            <?php foreach ($exercises as $index => $exercise): ?>
                <?php
                $exercise_name = $exercise['name'] ?? 'Exercise';
                $exercise_description = $exercise['description'] ?? '-';

                $pivot = $exercise['pivot'] ?? [];
                $sets = $pivot['recommended_sets'] ?? '-';
                $reps = $pivot['recommended_reps'] ?? '-';
                $rest = $pivot['rest_seconds'] ?? '-';
                $order = $pivot['order_index'] ?? ($index + 1);
                ?>

                <article class="rounded-2xl border border-outline-variant/20 bg-surface-container p-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">

                        <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <span class="material-symbols-outlined text-primary-container">exercise</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-black uppercase tracking-widest text-primary-container">
                                Exercise <?= h((string)$order) ?>
                            </p>

                            <h4 class="text-lg font-bold">
                                <?= h($exercise_name) ?>
                            </h4>

                            <p class="text-sm text-on-surface-variant">
                                <?= h($exercise_description) ?>
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-center sm:w-[260px]">
                            <div class="rounded-xl bg-surface-container-high p-3">
                                <p class="text-xs text-on-surface-variant">Sets</p>
                                <p class="font-bold"><?= h((string)$sets) ?></p>
                            </div>

                            <div class="rounded-xl bg-surface-container-high p-3">
                                <p class="text-xs text-on-surface-variant">Reps</p>
                                <p class="font-bold"><?= h((string)$reps) ?></p>
                            </div>

                            <div class="rounded-xl bg-surface-container-high p-3">
                                <p class="text-xs text-on-surface-variant">Rest</p>
                                <p class="font-bold"><?= h((string)$rest) ?>s</p>
                            </div>
                        </div>

                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="rounded-2xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-sm text-on-surface-variant">
                    No exercises added to this routine yet.
                </p>
            </div>
        <?php endif; ?>
    </section>

</div>

<?php
wp_app_page_end(true);
?>