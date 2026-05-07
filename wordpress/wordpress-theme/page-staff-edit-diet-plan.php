<?php
/*
Template Name: Staff Edit Diet Plan
*/
require_once 'functions.php';
require_advanced();

$flash_error = '';

$diet_plan_id = (int)($_GET['id'] ?? $_POST['diet_plan_id'] ?? 0);

$manage_diet_plans_url = home_url('/?pagename=staff-manage-diet-plans');
$edit_diet_plan_url = home_url('/?pagename=staff-edit-diet-plan&id=' . $diet_plan_id);

if ($diet_plan_id <= 0) {
    wp_safe_redirect($manage_diet_plans_url);
    exit;
}

function diet_edit_value(array $plan, string $key, $default = '')
{
    if (isset($_POST[$key])) {
        $value = $_POST[$key];

        if ($value === '-' || $value === '—') {
            return '';
        }

        return $value;
    }

    return $plan[$key] ?? $default;
}

$diet_response = api_get('/diet-plans/' . $diet_plan_id, auth: true);
$diet_plan = [];

if (($diet_response['result'] ?? false) !== false && is_array($diet_response['result'] ?? null)) {
    $diet_plan = $diet_response['result'];
} else {
    $flash_error = api_message($diet_response) ?: 'No se pudo cargar el plan de dieta.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_diet_plan_submit'])) {
    $payload = [
        'name' => trim((string)($_POST['diet_plan_name'] ?? '')),
        'goal_description' => trim((string)($_POST['goal_description'] ?? '')),
    ];

    $payload = array_filter($payload, function ($value) {
        return $value !== '' && $value !== '-' && $value !== '—';
    });

    $update_response = fitapp_api_multipart_update('/diet-plans/' . $diet_plan_id, $payload, $_FILES['image'] ?? null, 'image', true);

    if (($update_response['result'] ?? false) !== false) {
        wp_safe_redirect($manage_diet_plans_url . '&notice=updated');
        exit;
    }

    $flash_error = api_message($update_response) ?: 'No se pudo actualizar el plan de dieta.';
}

$current_image = fitapp_public_asset_url(diet_edit_value($diet_plan, 'cover_image_url'));

wp_app_page_start('Edit Diet Plan', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Edit Diet Plan</h2>
            <p class="text-sm text-on-surface-variant">
                Modifica el nombre, objetivo nutricional e imagen de portada.
            </p>
        </div>

        <a
            href="<?= esc_url($manage_diet_plans_url) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to diet plans
        </a>
    </section>

    <?php if ($diet_plan): ?>
        <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
            <form method="post" action="<?= esc_url($edit_diet_plan_url) ?>" enctype="multipart/form-data" class="space-y-6">

                <input type="hidden" name="edit_diet_plan_submit" value="1">
                <input type="hidden" name="diet_plan_id" value="<?= (int)$diet_plan_id ?>">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">
                        Diet plan name
                    </label>

                    <input
                        type="text"
                        name="diet_plan_name"
                        value="<?= h($_POST['diet_plan_name'] ?? ($diet_plan['name'] ?? '')) ?>"
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
                    ><?= h($_POST['goal_description'] ?? ($diet_plan['goal_description'] ?? '')) ?></textarea>

                    <p class="mt-1 text-xs text-on-surface-variant">
                        Máximo 280 caracteres. Este campo es obligatorio.
                    </p>
                </div>

                <?php fitapp_render_image_dropzone('Cover image', 'Change diet plan image', 'dietImageInput', 'dietDropzone', 'image', $current_image, 'Diet plan image preview', 'restaurant'); ?>

                <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                    <button
                        type="submit"
                        class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                    >
                        Save Changes
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
    <?php endif; ?>

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
