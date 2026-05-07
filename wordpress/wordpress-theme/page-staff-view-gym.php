<?php
/*
Template Name: Staff View Gym
*/
require_once 'functions.php';
require_advanced();

$gym_id = (int)($_GET['id'] ?? 0);

$manage_gyms_url = home_url('/?pagename=staff-manage-gyms');
$edit_gym_url = home_url('/?pagename=staff-edit-gym&id=' . $gym_id);

$flash_error = '';

if ($gym_id <= 0) {
    wp_safe_redirect($manage_gyms_url);
    exit;
}

function gym_view_value(array $gym, array $keys, $default = '')
{
    foreach ($keys as $key) {
        if (!isset($gym[$key]) || $gym[$key] === null) {
            continue;
        }

        $clean_value = trim((string)$gym[$key]);

        if ($clean_value !== '' && $clean_value !== '-' && $clean_value !== '—' && $clean_value !== 'â€”' && strtoupper($clean_value) !== 'NULL') {
            return $gym[$key];
        }
    }

    return $default;
}

$gym_response = api_get('/gyms/' . $gym_id, auth: true);
$gym = [];

if (($gym_response['result'] ?? false) !== false && is_array($gym_response['result'] ?? null)) {
    $gym = $gym_response['result'];
} else {
    $flash_error = api_message($gym_response) ?: 'Could not load the gym.';
}

$name = gym_view_value($gym, ['name'], 'Gym');
$address = h((string)gym_view_value($gym, ['address'], ''));
$city = h((string)gym_view_value($gym, ['city'], ''));
$phone = h((string)gym_view_value($gym, ['phone'], ''));
$location_coords = h((string)gym_view_value($gym, ['location_coords'], ''));
$logo_url = fitapp_public_asset_url(gym_view_value($gym, ['logo_url', 'image_url', 'image', 'photo_url'], ''));
$manager_id = h((string)gym_view_value($gym, ['manager_id'], ''));

$manager_name = '';
if (!empty($gym['manager']) && is_array($gym['manager'])) {
    $manager_name = $gym['manager']['full_name']
        ?? $gym['manager']['username']
        ?? $gym['manager']['email']
        ?? '';
}

$manager_value = h((string)($manager_name ?: $manager_id));
$gym_hero_bits = array_filter([$address, $city], static function ($value) {
    return $value !== '';
});
$gym_stat_cards = [];

if ($address !== '') {
    $gym_stat_cards[] = ['label' => 'Address', 'value' => $address];
}

if ($city !== '') {
    $gym_stat_cards[] = ['label' => 'City', 'value' => $city];
}

if ($phone !== '') {
    $gym_stat_cards[] = ['label' => 'Phone', 'value' => $phone];
}

if ($manager_value !== '') {
    $gym_stat_cards[] = ['label' => 'Manager', 'value' => $manager_value];
}

wp_app_page_start('View Gym', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.2em] text-primary-container">
                Gym #<?= (int)$gym_id ?>
            </p>

            <h2 class="mt-2 text-3xl font-black uppercase tracking-tight sm:text-4xl">
                <?= h($name) ?>
            </h2>

            <p class="mt-2 text-sm text-on-surface-variant">
                Full view of the selected location.
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row">
            <a
                href="<?= esc_url($edit_gym_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
            >
                Edit gym
            </a>

            <a
                href="<?= esc_url($manage_gyms_url) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
            >
                ← Back to gyms
            </a>
        </div>
    </section>

    <?php if ($gym): ?>
        <section class="overflow-hidden rounded-3xl border border-outline-variant/20 bg-surface-container shadow-lg">

            <div class="relative h-[280px] overflow-hidden bg-surface-container-high">
                <?php fitapp_render_image_or_placeholder($logo_url, (string)$name, 'h-full w-full object-cover opacity-70', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'location_city', 'No image'); ?>

                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-transparent"></div>

                <div class="absolute bottom-0 left-0 p-6">
                    <span class="mb-3 inline-flex rounded-full bg-primary-container px-3 py-1 text-xs font-black uppercase tracking-wide text-on-primary-container">
                        Gym Center
                    </span>

                    <h3 class="font-headline text-4xl font-black uppercase tracking-tight text-white">
                        <?= h($name) ?>
                    </h3>

                    <?php if ($gym_hero_bits): ?>
                        <p class="mt-2 text-sm text-white/80">
                            <?= implode(' | ', $gym_hero_bits) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($gym_stat_cards): ?>
                <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
                    <?php foreach ($gym_stat_cards as $card): ?>
                        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container-high p-4">
                            <p class="text-[10px] font-black uppercase tracking-widest text-on-surface-variant">
                                <?= h($card['label']) ?>
                            </p>
                            <p class="mt-2 text-sm font-bold leading-relaxed">
                                <?= $card['value'] ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <?php if ($location_coords !== ''): ?>
                <article class="rounded-3xl border border-outline-variant/20 bg-surface-container p-5 sm:p-6">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-container">map</span>
                        <h3 class="text-lg font-bold">Location coordinates</h3>
                    </div>

                    <p class="text-sm leading-7 text-on-surface-variant">
                        <?= $location_coords ?>
                    </p>

                    <a
                        href="https://www.google.com/maps/search/?api=1&query=<?= urlencode((string)$location_coords) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-4 inline-flex items-center gap-2 rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold text-primary-container hover:bg-surface-container-high"
                    >
                        Open in Google Maps
                        <span class="material-symbols-outlined text-base">open_in_new</span>
                    </a>
                </article>
            <?php endif; ?>

            <article class="rounded-3xl border border-outline-variant/20 bg-surface-container p-5 sm:p-6">
                <div class="mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-container">image</span>
                    <h3 class="text-lg font-bold">Logo / Image</h3>
                </div>

                <?php if ($logo_url): ?>
                    <a
                        href="<?= esc_url($logo_url) ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 text-sm font-bold text-primary-container hover:underline"
                    >
                        Open image
                        <span class="material-symbols-outlined text-base">open_in_new</span>
                    </a>
                <?php else: ?>
                    <p class="text-sm text-on-surface-variant">
                        No custom logo assigned.
                    </p>
                <?php endif; ?>
            </article>

        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
