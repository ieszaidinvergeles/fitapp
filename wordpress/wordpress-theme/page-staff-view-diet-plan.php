<?php
/*
Template Name: Staff View Diet Plan
*/
require_once 'functions.php';
require_advanced();

$diet_plan_id = (int)($_GET['id'] ?? 0);

$manage_diet_plans_url = home_url('/?pagename=staff-manage-diet-plans');
$edit_diet_plan_url = home_url('/?pagename=staff-edit-diet-plan&id=' . $diet_plan_id);

$flash_error = '';

if ($diet_plan_id <= 0) {
    wp_safe_redirect($manage_diet_plans_url);
    exit;
}

$diet_response = api_get('/diet-plans/' . $diet_plan_id, auth: true);
$diet_plan = [];

if (($diet_response['result'] ?? false) !== false && is_array($diet_response['result'] ?? null)) {
    $diet_plan = $diet_response['result'];
} else {
    $flash_error = api_message($diet_response) ?: 'No se pudo cargar el plan de dieta.';
}

$name = $diet_plan['name'] ?? 'Diet Plan';
$goal_description = $diet_plan['goal_description'] ?? '';
$cover_image_url = fitapp_public_asset_url($diet_plan['cover_image_url']
    ?? $diet_plan['image_url']
    ?? $diet_plan['image']
    ?? $diet_plan['photo_url']
    ?? '');

wp_app_page_start('View Diet Plan', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-primary-container">
                Diet Plan #<?= (int)$diet_plan_id ?>
            </p>

            <h2 class="mt-2 text-3xl font-black uppercase tracking-tight sm:text-4xl">
                <?= h($name) ?>
            </h2>

            <p class="mt-2 text-sm text-on-surface-variant">
                Vista completa del plan de dieta seleccionado.
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <a
                href="<?= esc_url($edit_diet_plan_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
            >
                Edit diet plan
            </a>

            <a
                href="<?= esc_url($manage_diet_plans_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
            >
                ← Back to diet plans
            </a>
        </div>
    </section>

    <?php if ($diet_plan): ?>
        <section class="overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container shadow-lg">

            <div class="relative h-[280px] overflow-hidden bg-surface-container-high">
                <?php fitapp_render_image_or_placeholder($cover_image_url, (string)$name, 'h-full w-full object-cover opacity-70', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'restaurant', 'No image'); ?>

                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-transparent"></div>

                <div class="absolute bottom-0 left-0 p-6">
                    <span class="mb-3 inline-flex rounded-full bg-primary-container px-3 py-1 text-xs font-black uppercase tracking-wide text-on-primary-container">
                        Nutrition Plan
                    </span>

                    <h3 class="font-headline text-4xl font-black uppercase tracking-tight text-white">
                        <?= h($name) ?>
                    </h3>
                </div>
            </div>

            <div class="p-5 sm:p-7">
                <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-5">
                    <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                        Goal description
                    </p>

                    <p class="mt-3 text-sm leading-7 text-on-surface-variant">
                        <?= h($goal_description, 'No description available.') ?>
                    </p>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                            Cover image
                        </p>

                        <?php if ($cover_image_url): ?>
                            <a
                                href="<?= esc_url($cover_image_url) ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="mt-3 inline-flex items-center gap-2 text-sm font-bold text-primary-container hover:underline"
                            >
                                Open image
                                <span class="material-symbols-outlined text-base">open_in_new</span>
                            </a>
                        <?php else: ?>
                            <p class="mt-3 text-sm text-on-surface-variant">No custom image assigned.</p>
                        <?php endif; ?>
                    </div>

                    <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                            Status
                        </p>

                        <p class="mt-3 inline-flex rounded-full bg-primary-container/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-primary-container">
                            Active
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
