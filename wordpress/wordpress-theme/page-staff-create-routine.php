<?php
/*
Template Name: Staff Create Routine
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

function routine_form_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
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

$diet_paged = fitapp_api_get_page('/diet-plans', 1, 10, true);
$diet_plans = $diet_paged['items'];
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

        $diet_plans[] = $diet_item;
        $added_this_page++;
    }

    $last_api_page = routine_response_last_page($diet_response);

    if ($added_this_page === 0 || ($last_api_page !== null && $api_page >= $last_api_page)) {
        break;
    }
}

$exercise_paged = fitapp_api_get_page('/exercises', 1, 10, true);
$exercises = $exercise_paged['items'];
$seen_exercise_ids = [];

for ($api_page = 1; $api_page <= 0; $api_page++) {
    $exercise_response = api_get('/exercises?page=' . $api_page, auth: true);

    if (($exercise_response['result'] ?? null) === false) {
        break;
    }

    $exercise_items = routine_extract_list($exercise_response);

    if (!$exercise_items) {
        break;
    }

    $added_this_page = 0;

    foreach ($exercise_items as $exercise_item) {
        $exercise_id = (int)($exercise_item['id'] ?? 0);

        if ($exercise_id > 0 && isset($seen_exercise_ids[$exercise_id])) {
            continue;
        }

        if ($exercise_id > 0) {
            $seen_exercise_ids[$exercise_id] = true;
        }

        $exercises[] = $exercise_item;
        $added_this_page++;
    }

    $last_api_page = routine_response_last_page($exercise_response);

    if ($added_this_page === 0 || ($last_api_page !== null && $api_page >= $last_api_page)) {
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'name' => trim((string)($_POST['routine_name'] ?? '')),
        'difficulty_level' => trim((string)($_POST['difficulty_level'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'estimated_duration_min' => !empty($_POST['estimated_duration_min']) ? (int)$_POST['estimated_duration_min'] : null,
        'associated_diet_plan_id' => !empty($_POST['associated_diet_plan_id']) ? (int)$_POST['associated_diet_plan_id'] : null,
    ];


    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== null && $value !== '-' && $value !== '—';
    });

    $create_response = fitapp_api_multipart_post('/routines', $payload, $_FILES['image'] ?? null, 'image', true);

    if (($create_response['result'] ?? false) !== false) {
        $created_routine = $create_response['result']['data'] ?? $create_response['result'] ?? [];
        $created_routine_id = (int)($created_routine['id'] ?? 0);
        $selected_exercise_ids = array_values(array_unique(array_map('intval', $_POST['selected_exercises'] ?? [])));
        $exercise_errors = [];

        if ($created_routine_id > 0) {
            foreach ($selected_exercise_ids as $index => $exercise_id) {
                if ($exercise_id <= 0) {
                    continue;
                }

                $exercise_payload = [
                    'exercise_id' => $exercise_id,
                    'order_index' => max(1, (int)($_POST['exercise_order'][$exercise_id] ?? ($index + 1))),
                    'recommended_sets' => max(1, (int)($_POST['exercise_sets'][$exercise_id] ?? 3)),
                    'recommended_reps' => max(1, (int)($_POST['exercise_reps'][$exercise_id] ?? 10)),
                    'rest_seconds' => max(0, (int)($_POST['exercise_rest'][$exercise_id] ?? 60)),
                ];

                $exercise_response = api_post('/routines/' . $created_routine_id . '/exercises', $exercise_payload, auth: true);

                if (($exercise_response['result'] ?? false) === false) {
                    $exercise_errors[] = api_message($exercise_response) ?: 'No se pudo asignar un ejercicio.';
                }
            }
        } elseif ($selected_exercise_ids) {
            $exercise_errors[] = 'No se pudo detectar la rutina creada para asignar ejercicios.';
        }

        if ($exercise_errors) {
            $flash_error = 'Rutina creada, pero algunos ejercicios no se pudieron asignar: ' . implode(' ', array_unique($exercise_errors));
        } else {
            wp_redirect(home_url('/?pagename=staff-manage-routines&notice=created'));
            exit;
        }
    } else {
        $flash_error = api_message($create_response) ?: 'No se pudo crear la rutina.';
    }
}

wp_app_page_start('Create Routine', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Routine</h2>
            <p class="text-sm text-on-surface-variant">
                Crea una nueva rutina para los usuarios del gimnasio.
            </p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-manage-routines')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to routines
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" enctype="multipart/form-data" class="space-y-6">

            <?php fitapp_render_image_dropzone('Routine image', 'Upload routine image', 'routineImageInput', 'routineDropzone', 'image', '', 'Routine image preview', 'fitness_center'); ?>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Routine name</label>
                <input
                    type="text"
                    name="routine_name"
                    value="<?= h(routine_form_value('routine_name'), '') ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: Full Body Starter"
                    required
                >
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Difficulty</label>
                    <select
                        name="difficulty_level"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select difficulty</option>
                        <option value="beginner" <?= routine_form_value('difficulty_level') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= routine_form_value('difficulty_level') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= routine_form_value('difficulty_level') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                        <option value="expert" <?= routine_form_value('difficulty_level') === 'expert' ? 'selected' : '' ?>>Expert</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Duration</label>
                    <input
                        type="number"
                        name="estimated_duration_min"
                        min="1"
                        value="<?= h(routine_form_value('estimated_duration_min'), '') ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: 45"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Associated diet plan</label>
                    <select
                        name="associated_diet_plan_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    >
                        <option value="">No diet plan</option>
                        <?php foreach ($diet_plans as $diet_plan): ?>
                            <?php $diet_id = (int)($diet_plan['id'] ?? 0); ?>
                            <option value="<?= $diet_id ?>" <?= (int)routine_form_value('associated_diet_plan_id', 0) === $diet_id ? 'selected' : '' ?>>
                                <?= h($diet_plan['name'] ?? ('Diet plan #' . $diet_id)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Description</label>
                <textarea
                    name="description"
                    rows="5"
                    required
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Describe brevemente para qué sirve esta rutina..."
                ><?= h(routine_form_value('description'), '') ?></textarea>
            </div>

            <section class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4 sm:p-5">
                <div class="mb-4">
                    <h3 class="text-base font-bold">Routine exercises</h3>
                    <p class="text-sm text-on-surface-variant">
                        Selecciona ejercicios y define orden, series, repeticiones y descanso.
                    </p>
                </div>

                <?php if ($exercises): ?>
                    <?php $selected_ids = array_map('intval', $_POST['selected_exercises'] ?? []); ?>

                    <div class="mb-4 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
                        <label class="relative block">
                            <span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xl text-on-surface-variant">search</span>
                            <input
                                id="routineExerciseSearch"
                                type="search"
                                class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container px-11 py-3 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                                placeholder="Search exercise or muscle group"
                            >
                        </label>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container px-4 py-3 text-sm font-semibold text-on-surface-variant">
                            <span id="routineExerciseVisibleCount"><?= count($exercises) ?></span> / <span id="routineExerciseTotalCount"><?= count($exercises) ?></span> visible · <span id="routineExerciseSelectedCount"><?= count($selected_ids) ?></span> selected
                        </div>
                    </div>

                    <div id="routineExerciseList" class="max-h-[560px] space-y-3 overflow-y-auto pr-1 sm:max-h-[620px]">
                        <?php foreach ($exercises as $exercise_index => $exercise): ?>
                            <?php
                            $exercise_id = (int)($exercise['id'] ?? 0);

                            if ($exercise_id <= 0) {
                                continue;
                            }

                            $is_selected = in_array($exercise_id, $selected_ids, true);
                            $exercise_name = h($exercise['name'] ?? ('Exercise #' . $exercise_id));
                            $muscle_group = h(ucwords(str_replace('_', ' ', (string)($exercise['target_muscle_group'] ?? ''))));
                            $exercise_filter = strtolower(trim(
                                (string)($exercise['name'] ?? '') . ' ' .
                                (string)($exercise['target_muscle_group'] ?? '') . ' ' .
                                (string)($exercise['description'] ?? '')
                            ));
                            ?>

                            <article
                                data-routine-exercise-item
                                data-routine-exercise-filter="<?= esc_attr($exercise_filter) ?>"
                                class="rounded-2xl border border-outline-variant/20 bg-surface-container p-4"
                            >
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <label class="flex min-w-0 cursor-pointer items-center gap-3">
                                        <input
                                            type="checkbox"
                                            name="selected_exercises[]"
                                            value="<?= $exercise_id ?>"
                                            class="peer sr-only"
                                            <?= $is_selected ? 'checked' : '' ?>
                                        >
                                        <span class="relative block h-7 w-12 shrink-0 rounded-full border border-outline-variant/30 bg-surface-container-high transition after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-on-surface-variant after:shadow-md after:transition-all after:content-[''] peer-checked:border-primary-container peer-checked:bg-primary-container peer-checked:after:translate-x-5 peer-checked:after:bg-on-primary-container"></span>

                                        <span class="min-w-0">
                                            <span class="block font-bold text-on-surface"><?= $exercise_name ?></span>
                                            <?php if ($muscle_group !== ''): ?>
                                                <span class="block text-xs text-on-surface-variant"><?= $muscle_group ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </label>

                                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:w-[520px]">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">
                                            Order
                                            <input
                                                type="number"
                                                name="exercise_order[<?= $exercise_id ?>]"
                                                min="1"
                                                value="<?= h($_POST['exercise_order'][$exercise_id] ?? (string)($exercise_index + 1)) ?>"
                                                class="mt-1 w-full rounded-xl border border-outline-variant/20 bg-surface-container-high px-3 py-2 text-sm text-on-surface focus:border-primary-container focus:outline-none"
                                            >
                                        </label>

                                        <label class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">
                                            Sets
                                            <input
                                                type="number"
                                                name="exercise_sets[<?= $exercise_id ?>]"
                                                min="1"
                                                value="<?= h($_POST['exercise_sets'][$exercise_id] ?? '3') ?>"
                                                class="mt-1 w-full rounded-xl border border-outline-variant/20 bg-surface-container-high px-3 py-2 text-sm text-on-surface focus:border-primary-container focus:outline-none"
                                            >
                                        </label>

                                        <label class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">
                                            Reps
                                            <input
                                                type="number"
                                                name="exercise_reps[<?= $exercise_id ?>]"
                                                min="1"
                                                value="<?= h($_POST['exercise_reps'][$exercise_id] ?? '10') ?>"
                                                class="mt-1 w-full rounded-xl border border-outline-variant/20 bg-surface-container-high px-3 py-2 text-sm text-on-surface focus:border-primary-container focus:outline-none"
                                            >
                                        </label>

                                        <label class="text-xs font-semibold uppercase tracking-wide text-on-surface-variant">
                                            Rest
                                            <input
                                                type="number"
                                                name="exercise_rest[<?= $exercise_id ?>]"
                                                min="0"
                                                value="<?= h($_POST['exercise_rest'][$exercise_id] ?? '60') ?>"
                                                class="mt-1 w-full rounded-xl border border-outline-variant/20 bg-surface-container-high px-3 py-2 text-sm text-on-surface focus:border-primary-container focus:outline-none"
                                            >
                                        </label>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <p id="routineExerciseEmpty" class="mt-3 hidden rounded-xl border border-outline-variant/20 bg-surface-container px-4 py-3 text-sm text-on-surface-variant">
                        No exercises match your search.
                    </p>
                <?php else: ?>
                    <p class="rounded-xl border border-outline-variant/20 bg-surface-container px-4 py-3 text-sm text-on-surface-variant">
                        No exercises available.
                    </p>
                <?php endif; ?>
            </section>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Routine
                </button>

                <a
                    href="<?= esc_url(home_url('/?pagename=staff-manage-routines')) ?>"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
                >
                    Cancel
                </a>
            </div>

        </form>
    </section>

</div>

<?php fitapp_render_image_dropzone_script('routineImageInput', 'routineDropzone'); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('routineExerciseSearch');
    const list = document.getElementById('routineExerciseList');

    if (!search || !list) {
        return;
    }

    const items = Array.from(list.querySelectorAll('[data-routine-exercise-item]'));
    const emptyState = document.getElementById('routineExerciseEmpty');
    const visibleCount = document.getElementById('routineExerciseVisibleCount');
    const selectedCount = document.getElementById('routineExerciseSelectedCount');

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
        let selected = 0;

        items.forEach(function (item) {
            const checkbox = item.querySelector('input[name="selected_exercises[]"]');

            if (checkbox && checkbox.checked) {
                selected++;
            }

            const haystack = normalize(item.dataset.routineExerciseFilter || item.textContent);
            const matches = query === '' || haystack.includes(query);
            item.classList.toggle('hidden', !matches);

            if (matches) {
                visible++;
            }
        });

        if (visibleCount) {
            visibleCount.textContent = visible;
        }

        if (selectedCount) {
            selectedCount.textContent = selected;
        }

        if (emptyState) {
            emptyState.classList.toggle('hidden', visible !== 0);
        }
    }

    search.addEventListener('input', updateExerciseList);

    items.forEach(function (item) {
        const checkbox = item.querySelector('input[name="selected_exercises[]"]');

        if (checkbox) {
            checkbox.addEventListener('change', updateExerciseList);
        }
    });

    updateExerciseList();
});
</script>

<?php
wp_app_page_end(true);
?>
