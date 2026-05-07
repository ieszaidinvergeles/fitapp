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

function view_routine_value(array $data = null, string $key, $default = '')
{
    if (!$data || !isset($data[$key]) || $data[$key] === null) {
        return $default;
    }

    $clean_value = trim((string)$data[$key]);

    if ($clean_value === '' || $clean_value === '-' || $clean_value === '—' || $clean_value === 'â€”' || strtoupper($clean_value) === 'NULL') {
        return $default;
    }

    return $data[$key];
}

$routine_response = api_get('/routines/' . $routine_id, auth: true);
$routine_result = (($routine_response['result'] ?? false) !== false) ? $routine_response['result'] : null;
$routine = is_array($routine_result) ? ($routine_result['data'] ?? $routine_result) : null;

if (!$routine || !is_array($routine)) {
    wp_redirect(home_url('/?pagename=staff-manage-routines'));
    exit;
}

$routine_name = view_routine_value($routine, 'name', 'Routine');
$difficulty = h(ucfirst(str_replace('_', ' ', (string)view_routine_value($routine, 'difficulty_level', ''))));
$description = view_routine_value($routine, 'description', '');
$duration = h((string)view_routine_value($routine, 'estimated_duration_min', ''));
$creator = is_array($routine['creator'] ?? null) ? $routine['creator'] : [];
$creator_name = h($creator['full_name'] ?? $creator['username'] ?? $creator['email'] ?? '');
$diet_plan = is_array($routine['diet_plan'] ?? null) ? $routine['diet_plan'] : [];
$diet_plan_id = (int)view_routine_value($routine, 'associated_diet_plan_id', 0);
$diet_plan_name = h($diet_plan['name'] ?? '');
$image_url = fitapp_public_asset_url(
    $routine['cover_image_url']
    ?? $routine['image_url']
    ?? $routine['image']
    ?? $routine['photo_url']
    ?? ''
);

$exercises = $routine['ordered_exercises']
    ?? $routine['orderedExercises']
    ?? $routine['exercises']
    ?? [];

$routine_stat_cards = [];

if ($difficulty !== '') {
    $routine_stat_cards[] = ['label' => 'Difficulty', 'value' => $difficulty];
}

if ($duration !== '') {
    $routine_stat_cards[] = ['label' => 'Duration', 'value' => $duration . ' min'];
}

if ($creator_name !== '') {
    $routine_stat_cards[] = ['label' => 'Creator', 'value' => $creator_name];
}

if ($diet_plan_name !== '') {
    $routine_stat_cards[] = ['label' => 'Diet plan', 'value' => $diet_plan_name];
} elseif ($diet_plan_id > 0) {
    $routine_stat_cards[] = ['label' => 'Diet plan', 'value' => '#' . h((string)$diet_plan_id)];
}

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

            <div class="flex h-[150px] w-full shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high sm:w-[180px]">
                <?php fitapp_render_image_or_placeholder((string)$image_url, (string)$routine_name, 'h-full w-full object-cover', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'fitness_center', 'No image'); ?>
            </div>

            <div class="min-w-0 flex-1 space-y-4">
                <div>
                    <p class="text-xs font-black uppercase tracking-widest text-primary-container">
                        Routine #<?= h((string)$routine_id) ?>
                    </p>

                    <h3 class="mt-1 break-words text-2xl font-black uppercase tracking-tight text-on-surface [overflow-wrap:anywhere]">
                        <?= h($routine_name) ?>
                    </h3>
                </div>

                <?php if ($routine_stat_cards): ?>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <?php foreach ($routine_stat_cards as $card): ?>
                            <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                                <p class="text-xs uppercase text-on-surface-variant"><?= h($card['label']) ?></p>
                                <p class="mt-1 font-bold"><?= $card['value'] ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <h3 class="mb-2 text-lg font-bold">Description</h3>
        <p class="break-words text-sm leading-relaxed text-on-surface-variant [overflow-wrap:anywhere]">
            <?= h($description, 'No description available.') ?>
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
            <div class="grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
                <label class="relative block">
                    <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xl text-on-surface-variant">search</span>
                    <input
                        id="viewRoutineExerciseSearch"
                        type="search"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container px-11 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Search exercise or muscle group"
                    >
                </label>

                <div class="rounded-2xl border border-outline-variant/20 bg-surface-container px-4 py-3 text-sm font-semibold text-on-surface-variant">
                    <span id="viewRoutineExerciseVisibleCount"><?= count($exercises) ?></span> / <span id="viewRoutineExerciseTotalCount"><?= count($exercises) ?></span> visible
                </div>
            </div>

            <div id="viewRoutineExerciseList" class="grid max-h-[560px] gap-3 overflow-y-auto pr-1 sm:max-h-[620px] xl:grid-cols-2">
            <?php foreach ($exercises as $index => $exercise): ?>
                <?php
                $exercise_name = $exercise['name'] ?? 'Exercise';
                $exercise_description = h($exercise['description'] ?? '');
                $exercise_filter = strtolower(trim(
                    (string)($exercise['name'] ?? '') . ' ' .
                    (string)($exercise['target_muscle_group'] ?? '') . ' ' .
                    (string)($exercise['description'] ?? '')
                ));

                $pivot = $exercise['pivot'] ?? [];
                $sets = h((string)($exercise['sets'] ?? ($pivot['recommended_sets'] ?? '')));
                $reps = h((string)($exercise['reps'] ?? ($pivot['recommended_reps'] ?? '')));
                $rest = h((string)($exercise['rest'] ?? ($pivot['rest_seconds'] ?? '')));
                $order = $exercise['order'] ?? ($pivot['order_index'] ?? ($index + 1));
                $exercise_stat_cards = [];

                if ($sets !== '') {
                    $exercise_stat_cards[] = ['label' => 'Sets', 'value' => $sets];
                }

                if ($reps !== '') {
                    $exercise_stat_cards[] = ['label' => 'Reps', 'value' => $reps];
                }

                if ($rest !== '') {
                    $exercise_stat_cards[] = ['label' => 'Rest', 'value' => $rest . 's'];
                }
                ?>

                <article
                    data-routine-view-exercise-item
                    data-routine-view-exercise-filter="<?= esc_attr($exercise_filter) ?>"
                    class="rounded-2xl border border-outline-variant/20 bg-surface-container p-4"
                >
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

                            <?php if ($exercise_description !== ''): ?>
                                <p class="break-words text-sm text-on-surface-variant [overflow-wrap:anywhere]">
                                    <?= $exercise_description ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <?php if ($exercise_stat_cards): ?>
                            <div class="grid grid-cols-3 gap-2 text-center sm:w-[260px]">
                                <?php foreach ($exercise_stat_cards as $card): ?>
                                    <div class="rounded-xl bg-surface-container-high p-3">
                                        <p class="text-xs text-on-surface-variant"><?= h($card['label']) ?></p>
                                        <p class="font-bold"><?= $card['value'] ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </article>
            <?php endforeach; ?>
            </div>

            <p id="viewRoutineExerciseEmpty" class="hidden rounded-xl border border-outline-variant/20 bg-surface-container px-4 py-3 text-sm text-on-surface-variant">
                No exercises match your search.
            </p>
        <?php else: ?>
            <div class="rounded-2xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-sm text-on-surface-variant">
                    No exercises added to this routine yet.
                </p>
            </div>
        <?php endif; ?>
    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('viewRoutineExerciseSearch');
    const list = document.getElementById('viewRoutineExerciseList');

    if (!search || !list) {
        return;
    }

    const items = Array.from(list.querySelectorAll('[data-routine-view-exercise-item]'));
    const emptyState = document.getElementById('viewRoutineExerciseEmpty');
    const visibleCount = document.getElementById('viewRoutineExerciseVisibleCount');

    function normalize(value) {
        return (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function updateExerciseList() {
        const query = normalize(search.value);
        let visible = 0;

        items.forEach(function (item) {
            const haystack = normalize(item.dataset.routineViewExerciseFilter || item.textContent);
            const matches = query === '' || haystack.includes(query);
            item.classList.toggle('hidden', !matches);

            if (matches) {
                visible++;
            }
        });

        if (visibleCount) {
            visibleCount.textContent = visible;
        }

        if (emptyState) {
            emptyState.classList.toggle('hidden', visible !== 0);
        }
    }

    search.addEventListener('input', updateExerciseList);
    updateExerciseList();
});
</script>

<?php
wp_app_page_end(true);
?>
