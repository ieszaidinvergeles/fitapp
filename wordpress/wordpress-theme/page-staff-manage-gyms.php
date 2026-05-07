<?php
/*
Template Name: Staff Manage Gyms
*/
require_once 'functions.php';
require_advanced();

$page = max(1, (int)($_GET['page_num'] ?? 1));
$per_page = 10;

$flash_success = '';
$flash_error = '';

$notice = $_GET['notice'] ?? '';
if ($notice === 'deleted') {
    $flash_success = 'Gym deleted successfully.';
} elseif ($notice === 'created') {
    $flash_success = 'Gym created successfully.';
} elseif ($notice === 'updated') {
    $flash_success = 'Gym updated successfully.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $gym_id = (int)($_POST['gym_id'] ?? 0);

    if ($gym_id > 0) {
        $delete_response = api_delete('/gyms/' . $gym_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            wp_safe_redirect(home_url('/?pagename=staff-manage-gyms&notice=deleted'));
            exit;
        }

        $flash_error = api_message($delete_response) ?: 'Could not delete the gym.';
    }
}

function gym_extract_list(array $response): array
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

function gym_value(array $gym, array $keys, $default = '')
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

function gym_page_url(int $page): string
{
    return home_url('/?pagename=staff-manage-gyms&page_num=' . max(1, $page));
}

function gym_response_has_pagination_meta(array $response): bool
{
    $result = is_array($response['result'] ?? null) ? $response['result'] : [];
    $meta = is_array($result['meta'] ?? null) ? $result['meta'] : [];

    return isset($meta['total'])
        || isset($meta['last_page'])
        || isset($result['total'])
        || isset($result['last_page']);
}

/*
|--------------------------------------------------------------------------
| Load paginated gyms
|--------------------------------------------------------------------------
*/
$paged = fitapp_api_get_page('/gyms', $page, $per_page, true);
$listResp = $paged['response'];
$gyms = $paged['items'];
$pagination = $paged['meta'];
$current_page = $pagination['current_page'];
$last_page = $pagination['last_page'];
$total = $pagination['total'];
$from = $pagination['from'];
$to = $pagination['to'];
$has_next = !empty($pagination['has_next']);

if (!gym_response_has_pagination_meta($listResp)) {
    $item_count = count($gyms);
    $current_page = max(1, $page);
    $from = $item_count > 0 ? (($current_page - 1) * $per_page) + 1 : 0;
    $to = $item_count > 0 ? $from + $item_count - 1 : 0;
    $total = $to;
    $last_page = max(1, $current_page);
    $has_next = false;

    if ($item_count >= $per_page) {
        $next_page = $current_page + 1;
        $next_probe = fitapp_api_get_page('/gyms', $next_page, $per_page, true);

        if (($next_probe['response']['result'] ?? null) !== false) {
            $next_count = count($next_probe['items']);
            $has_next = $next_count > 0;

            if ($has_next) {
                $last_page = $next_page;
                $total = $to + $next_count;
            }
        }
    }
}

wp_app_page_start('Manage Gyms', true);
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

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold">Gym List</h2>
            <p class="text-sm text-on-surface-variant">
                Manage locations, addresses, managers, and basic gym details.
            </p>

            <?php if ($total > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total) ?> GYMS REGISTERED · PAGE <?= h((string)$current_page) ?> OF <?= h((string)$last_page) ?>
                </p>
            <?php endif; ?>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-create-gym')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
        >
            <span class="text-base leading-none">+</span>
            <span>Create gym</span>
        </a>
    </section>

    <section class="space-y-3">
        <?php foreach ($gyms as $index => $gym): ?>
            <?php
            $gym_id = (int)($gym['id'] ?? 0);

            $name = gym_value($gym, ['name', 'title'], 'Gym');
            $address = h((string)gym_value($gym, ['address', 'location', 'street'], ''));
            $city = h((string)gym_value($gym, ['city'], ''));
            $phone = h((string)gym_value($gym, ['phone', 'telephone'], ''));

            $manager = gym_value($gym, ['manager_name', 'responsible_name'], '');
            if (!$manager && !empty($gym['manager']) && is_array($gym['manager'])) {
                $manager = $gym['manager']['full_name'] ?? $gym['manager']['username'] ?? $gym['manager']['email'] ?? '';
            }

            $coordinates = h((string)gym_value($gym, ['location_coords'], ''));
            $gym_location_bits = [];
            $gym_contact_bits = [];

            if ($address !== '') {
                $gym_location_bits[] = 'Address: ' . $address;
            }

            if ($city !== '') {
                $gym_location_bits[] = 'City: ' . $city;
            }

            if (h((string)$manager) !== '') {
                $gym_contact_bits[] = 'Manager: ' . h((string)$manager);
            }

            if ($phone !== '') {
                $gym_contact_bits[] = 'Phone: ' . $phone;
            }

            if ($coordinates !== '') {
                $gym_contact_bits[] = 'Coordinates: ' . $coordinates;
            }

            $image = fitapp_public_asset_url($gym['logo_url']
                ?? $gym['image_url']
                ?? $gym['image']
                ?? $gym['photo_url']
                ?? '');
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-outline-variant/20 bg-surface-container-high">
                            <?php fitapp_render_image_or_placeholder($image, (string)$name, 'h-full w-full object-cover', 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant', 'location_city', 'No image'); ?>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-lg font-bold break-words">
                                    <?= h($name) ?>
                                </p>

                                <span class="rounded-full bg-primary-container/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-primary-container">
                                    #<?= h((string)$gym_id) ?>
                                </span>
                            </div>

                            <?php if ($gym_location_bits): ?>
                                <p class="mt-1 text-sm text-on-surface-variant break-words">
                                    <?= implode(' | ', $gym_location_bits) ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($gym_contact_bits): ?>
                                <p class="mt-1 text-sm text-on-surface-variant break-words">
                                    <?= implode(' | ', $gym_contact_bits) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start lg:max-w-[320px] lg:justify-end">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-edit-gym&id=' . $gym_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-view-gym&id=' . $gym_id)) ?>"
                            class="inline-flex items-center justify-center rounded-lg border border-outline-variant/30 px-3 py-2 text-sm transition hover:bg-surface-container-high"
                        >
                            View
                        </a>

                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this gym?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="gym_id" value="<?= $gym_id ?>">
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

        <?php if (!$gyms): ?>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-4">
                <p class="text-on-surface-variant">No gyms found.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php if ($last_page > 1 || $current_page > 1 || $has_next): ?>
        <section class="flex flex-col gap-4 rounded-xl border border-outline-variant/20 bg-surface-container p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-on-surface-variant">
                Showing
                <span class="font-bold text-on-surface"><?= h((string)$from) ?></span>
                -
                <span class="font-bold text-on-surface"><?= h((string)$to) ?></span>
                of
                <span class="font-bold text-on-surface"><?= h((string)$total) ?></span>
                gyms
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">
                <?php if ($current_page > 1): ?>
                    <a
                        href="<?= esc_url(gym_page_url($current_page - 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        ← Previous
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        ← Previous
                    </span>
                <?php endif; ?>

                <?php
                $start = max(1, $current_page - 2);
                $end = min($last_page, $current_page + 2);
                ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a
                        href="<?= esc_url(gym_page_url($i)) ?>"
                        class="rounded-full border px-4 py-2 text-sm font-bold transition <?= $i === $current_page
                            ? 'border-primary-container bg-primary-container text-on-primary-container shadow-[0_0_18px_rgba(212,251,0,0.22)]'
                            : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                    >
                        <?= h((string)$i) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $last_page || $has_next): ?>
                    <a
                        href="<?= esc_url(gym_page_url($current_page + 1)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        Next →
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        Next →
                    </span>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
