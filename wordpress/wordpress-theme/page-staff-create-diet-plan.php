<?php
/*
Template Name: Staff Create Diet Plan
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$create_diet_plan_url = home_url('/?pagename=staff-create-diet-plan');
$manage_diet_plans_url = home_url('/?pagename=staff-manage-diet-plans');

function diet_create_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    if ($value === '-' || $value === '—') {
        return '';
    }

    return $value;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_diet_plan_submit'])) {
    $payload = [
        'name' => trim((string)($_POST['diet_plan_name'] ?? '')),
        'goal_description' => trim((string)($_POST['goal_description'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== '-' && $value !== '—';
    });

    $create_response = fitapp_api_multipart_post('/diet-plans', $payload, $_FILES['image'] ?? null, 'image', true);

    if (($create_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_diet_plans_url . '&notice=created');
        exit;
    }

    $flash_error = api_message($create_response) ?: 'No se pudo crear el plan de dieta. Revisa los campos obligatorios.';
}

wp_app_page_start('Create Diet Plan', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Create Diet Plan</h2>
            <p class="text-sm text-on-surface-variant">
                Crea un nuevo plan de dieta con objetivo nutricional e imagen de portada.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_diet_plans_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to diet plans
        </a>
    </section>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" action="<?= esc_url($create_diet_plan_url) ?>" enctype="multipart/form-data" class="space-y-6">

            <input type="hidden" name="create_diet_plan_submit" value="1">

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Diet plan name
                </label>

                <input
                    type="text"
                    name="diet_plan_name"
                    value="<?= h(diet_create_value('diet_plan_name')) ?>"
                    class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Example: Definition, Bulk, Keto..."
                    maxlength="80"
                    required
                >
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                    Goal description
                </label>

                <textarea
                    name="goal_description"
                    rows="6"
                    maxlength="280"
                    class="w-full resize-none rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    placeholder="Describe el objetivo del plan: perder grasa, ganar masa muscular, mantener peso..."
                    required
                ><?= h(diet_create_value('goal_description')) ?></textarea>

                <p class="mt-1 text-xs text-on-surface-variant">
                    Máximo 280 caracteres. Este campo es obligatorio.
                </p>
            </div>

            <?php fitapp_render_image_dropzone('Cover image', 'Upload diet plan image', 'dietImageInput', 'dietDropzone', 'image', '', 'Diet plan image preview', 'restaurant'); ?>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create Diet Plan
                </button>

                <a
                    href="<?= esc_url($manage_diet_plans_url) ?>"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
                >
                    Cancel
                </a>
            </div>

        </form>
    </section>

</div>

<?php fitapp_render_image_dropzone_script('dietImageInput', 'dietDropzone'); ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const imageInput = document.getElementById('dietImageUrl');
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePreviewPlaceholder');

    if (!imageInput || !preview || !placeholder) return;

    function showPlaceholder() {
        preview.removeAttribute('src');
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }

    function updatePreview() {
        const url = imageInput.value.trim();

        if (!url || url === '-' || url === '—') {
            showPlaceholder();
            return;
        }

        preview.onload = function () {
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };

        preview.onerror = function () {
            showPlaceholder();
        };

        preview.src = url;
    }

    imageInput.addEventListener('input', updatePreview);
    updatePreview();
});
</script>

<?php
wp_app_page_end(true);
?>
