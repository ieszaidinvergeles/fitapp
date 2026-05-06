<?php
/*
Template Name: Staff Edit Routine
*/
require_once 'functions.php';
require_advanced();

$routine_id = (int)($_GET['id'] ?? 0);
$flash_error = '';

if ($routine_id <= 0) {
    wp_redirect(home_url('/?pagename=staff-manage-routines'));
    exit;
}

$routine_response = api_get('/routines/' . $routine_id, auth: true);
$routine_result = (($routine_response['result'] ?? false) !== false) ? $routine_response['result'] : null;
$routine_data = is_array($routine_result) ? ($routine_result['data'] ?? $routine_result) : null;

if (!$routine_data || !is_array($routine_data)) {
    wp_redirect(home_url('/?pagename=staff-manage-routines'));
    exit;
}

function routine_edit_value(string $key, array $routine_data, $default = '')
{
    $value = $_POST[$key] ?? ($routine_data[$key] ?? $default);

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'name' => trim((string)($_POST['routine_name'] ?? '')),
        'difficulty_level' => trim((string)($_POST['difficulty_level'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'estimated_duration_min' => !empty($_POST['estimated_duration_min']) ? (int)$_POST['estimated_duration_min'] : null,
    ];

    /*
     * Si más adelante el backend acepta goal, se puede activar.
     * Ahora mismo la tabla/API de routines no parece tener campo goal.
     */
    if (!empty($_POST['goal'])) {
        $payload['goal'] = trim((string)$_POST['goal']);
    }

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== null && $value !== '-' && $value !== '—';
    });

    $update_response = fitapp_api_multipart_update('/routines/' . $routine_id, $payload, $_FILES['image'] ?? null, 'image', true);

    if (($update_response['result'] ?? false) !== false) {
        wp_redirect(home_url('/?pagename=staff-manage-routines&notice=updated'));
        exit;
    } else {
        $flash_error = api_message($update_response) ?: 'No se pudo actualizar la rutina.';
    }
}

$current_image = fitapp_public_asset_url($routine_data['cover_image_url']
    ?? $routine_data['image_url']
    ?? $routine_data['image']
    ?? $routine_data['photo_url']
    ?? '');

wp_app_page_start('Edit Routine', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Routine</h2>
            <p class="text-sm text-on-surface-variant">
                Modifica los datos de la rutina y guarda los cambios.
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

            <?php fitapp_render_image_dropzone('Routine image', 'Change routine image', 'routineImageInput', 'routineDropzone', 'image', $current_image, 'Routine image preview', 'fitness_center'); ?>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Routine name</label>
                <input
                    type="text"
                    name="routine_name"
                    value="<?= h($_POST['routine_name'] ?? ($routine_data['name'] ?? ''), '') ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: Full Body Starter"
                    required
                >
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Difficulty</label>
                    <?php $difficulty = routine_edit_value('difficulty_level', $routine_data); ?>

                    <select
                        name="difficulty_level"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface [color-scheme:dark] focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <option value="">Select difficulty</option>
                        <option value="beginner" <?= $difficulty === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= $difficulty === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= $difficulty === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                        <option value="expert" <?= $difficulty === 'expert' ? 'selected' : '' ?>>Expert</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Duration</label>
                    <input
                        type="number"
                        name="estimated_duration_min"
                        min="1"
                        value="<?= h($_POST['estimated_duration_min'] ?? ($routine_data['estimated_duration_min'] ?? ''), '') ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: 45"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Goal</label>
                    <input
                        type="text"
                        name="goal"
                        value="<?= h(routine_edit_value('goal', $routine_data), '') ?>"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        placeholder="Example: Strength, Cardio"
                    >
                </div>

            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Description</label>
                <textarea
                    name="description"
                    rows="5"
                    maxlength="280"
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Describe brevemente para qué sirve esta rutina..."
                ><?= h(routine_edit_value('description', $routine_data), '') ?></textarea>

                <p class="mt-1 text-xs text-on-surface-variant">
                    Máximo 280 caracteres.
                </p>
            </div>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Save Changes
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

<?php
wp_app_page_end(true);
?>
