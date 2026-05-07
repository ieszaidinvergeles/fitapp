<?php
/*
Template Name: Staff View Exercise
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';
$exercise_id = (int)($_GET['id'] ?? 0);

$manage_exercises_url = home_url('/?pagename=staff-manage-exercises');
$edit_exercise_url = home_url('/?pagename=staff-edit-exercise&id=' . $exercise_id);

function view_exercise_value(array $exercise, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (!isset($exercise[$key]) || $exercise[$key] === null) {
            continue;
        }

        $clean_value = trim((string)$exercise[$key]);

        if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€”' && strtoupper($clean_value) !== 'NULL') {
            return $exercise[$key];
        }
    }

    return $default;
}

function view_exercise_label(string $value): string
{
    if ($value === '' || $value === '-' || $value === '—' || $value === 'â€”' || strtoupper($value) === 'NULL') {
        return '';
    }

    return ucwords(str_replace('_', ' ', $value));
}

if ($exercise_id <= 0) {
    wp_safe_redirect($manage_exercises_url);
    exit;
}

$exercise_response = api_get('/exercises/' . $exercise_id, auth: true);
$exercise = [];

if (($exercise_response['result'] ?? false) !== false && is_array($exercise_response['result'] ?? null)) {
    $exercise = $exercise_response['result'];
} else {
    $flash_error = api_message($exercise_response) ?: 'No se pudo cargar el ejercicio.';
}

$name = $exercise ? view_exercise_value($exercise, ['name', 'title'], 'Exercise') : 'Exercise';
$description = $exercise ? view_exercise_value($exercise, ['description', 'instructions'], 'No description available.') : '';
$muscle_group = $exercise ? view_exercise_label((string)view_exercise_value($exercise, ['target_muscle_group', 'muscle_group', 'target_muscle', 'body_part'], '')) : '';
$image_url = $exercise ? fitapp_public_asset_url(view_exercise_value($exercise, ['image_url', 'cover_image_url', 'image', 'photo_url'], '')) : '';
$video_url = $exercise ? view_exercise_value($exercise, ['video_url', 'video'], '') : '';

wp_app_page_start('View Exercise', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-primary-container">
                Exercise #<?= (int)$exercise_id ?>
            </p>

            <h2 class="mt-2 text-3xl font-black uppercase tracking-tight sm:text-4xl">
                <?= h($name) ?>
            </h2>

            <p class="mt-2 text-sm text-on-surface-variant">
                Consulta la información completa de este ejercicio.
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <a
                href="<?= esc_url($edit_exercise_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
            >
                Edit exercise
            </a>

            <a
                href="<?= esc_url($manage_exercises_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
            >
                ← Back to exercises
            </a>
        </div>
    </section>

    <?php if ($exercise): ?>
        <section class="overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container shadow-lg">
            <div class="grid grid-cols-1 lg:grid-cols-[360px_minmax(0,1fr)]">

                <div class="relative min-h-[260px] border-b border-outline-variant/20 bg-surface-container-high lg:border-b-0 lg:border-r">
                    <?php fitapp_render_image_or_placeholder($image_url, (string)$name, 'absolute inset-0 h-full w-full object-cover', 'absolute inset-0 min-h-[260px] flex-col items-center justify-center p-8 text-center text-on-surface-variant', 'fitness_center', 'No image'); ?>
                    <?php if ($image_url): ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-background/80 via-background/20 to-transparent"></div>
                    <?php endif; ?>
                </div>

                <div class="p-5 sm:p-8">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <?php if ($muscle_group !== ''): ?>
                            <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                                <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                    Muscle group
                                </p>
                                <p class="mt-2 text-lg font-bold">
                                    <?= h($muscle_group) ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                Video
                            </p>

                            <?php if ($video_url): ?>
                                <a
                                    href="<?= esc_url($video_url) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="mt-2 inline-flex items-center gap-2 text-sm font-bold text-primary-container hover:underline"
                                >
                                    Open video
                                    <span class="material-symbols-outlined text-base">open_in_new</span>
                                </a>
                            <?php else: ?>
                                <p class="mt-2 text-sm italic text-on-surface-variant">No video available.</p>
                            <?php endif; ?>
                        </div>

                    </div>

                    <div class="mt-5 rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4 sm:p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                            Description / Instructions
                        </p>

                        <p class="mt-3 whitespace-pre-line text-sm leading-7 text-on-surface-variant">
                            <?= h($description) ?>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
