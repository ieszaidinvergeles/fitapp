<?php
/*
Template Name: Staff Admin Users
*/
require_once 'functions.php';
require_user_management();

$page_num = max(1, (int)($_GET['page_num'] ?? 1));
$search = trim((string)($_GET['search'] ?? ''));
$flash_success = '';
$flash_error = '';
$per_page = 6;

$notice = $_GET['notice'] ?? '';
if ($notice === 'created') {
    $flash_success = 'Usuario creado correctamente.';
} elseif ($notice === 'updated') {
    $flash_success = 'Usuario actualizado correctamente.';
} elseif ($notice === 'deleted') {
    $flash_success = 'Usuario eliminado correctamente.';
}

/**
 * Eliminar usuario
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action_type'] ?? '') === 'delete') {
    $user_id = (int)($_POST['user_id'] ?? 0);

    if ($user_id > 0) {
        $delete_response = api_delete('/users/' . $user_id, auth: true);

        if (($delete_response['result'] ?? false) !== false) {
            $redirect_url = home_url('/?pagename=staff-admin-users&notice=deleted');

            if ($search !== '') {
                $redirect_url .= '&search=' . urlencode($search);
            }

            wp_redirect($redirect_url);
            exit;
        } else {
            $flash_error = api_message($delete_response);
        }
    }
}

/**
 * Extraer lista genérica de una respuesta API.
 */
function staff_admin_extract_list(array $response): array
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

/**
 * Construir mapa por ID.
 */
function staff_admin_lookup_by_id(array $items): array
{
    $map = [];

    foreach ($items as $item) {
        if (is_array($item) && isset($item['id'])) {
            $map[(int)$item['id']] = $item;
        }
    }

    return $map;
}

/**
 * Helper: construir URL manteniendo búsqueda.
 */
function users_page_url(int $page, string $search = ''): string
{
    $url = home_url('/?pagename=staff-admin-users&page_num=' . $page);

    if ($search !== '') {
        $url .= '&search=' . urlencode($search);
    }

    return $url;
}

/**
 * Cargar todos los usuarios desde la API.
 */
$first_response = api_get('/users?page=1', auth: true);
$api_error = (($first_response['result'] ?? null) === false);

$all_users = [];

if (!$api_error) {
    $all_users = staff_admin_extract_list($first_response);

    $api_page = 2;

    while (true) {
        $page_response = api_get('/users?page=' . $api_page, auth: true);

        if (($page_response['result'] ?? null) === false) {
            $flash_error = api_message($page_response);
            break;
        }

        $page_users = staff_admin_extract_list($page_response);

        if (empty($page_users)) {
            break;
        }

        $all_users = array_merge($all_users, $page_users);

        if (count($page_users) < 10) {
            break;
        }

        $api_page++;

        if ($api_page > 50) {
            break;
        }
    }
}

/**
 * Cargar relaciones auxiliares para mostrar gym y plan.
 */
$gyms_response = api_get('/gyms', auth: true);
$plans_response = api_get('/membership-plans', auth: true);

$gyms = staff_admin_extract_list($gyms_response);
$plans = staff_admin_extract_list($plans_response);

$gyms_by_id = staff_admin_lookup_by_id($gyms);
$plans_by_id = staff_admin_lookup_by_id($plans);

/**
 * Ordenar por más recientes primero.
 */
usort($all_users, function ($a, $b) {
    return (int)($b['id'] ?? 0) <=> (int)($a['id'] ?? 0);
});

/**
 * Filtrar por búsqueda.
 */
$filtered_users = $all_users;

if ($search !== '') {
    $search_lower = mb_strtolower($search);

    $filtered_users = array_values(array_filter($all_users, function ($u) use ($search_lower, $gyms_by_id, $plans_by_id) {
        $full_name = mb_strtolower((string)($u['full_name'] ?? ''));
        $email = mb_strtolower((string)($u['email'] ?? ''));
        $username = mb_strtolower((string)($u['username'] ?? ''));
        $role = mb_strtolower((string)($u['role'] ?? ''));

        $gym_id = (int)($u['current_gym_id'] ?? $u['gym_id'] ?? $u['gym']['id'] ?? $u['current_gym']['id'] ?? 0);
        $plan_id = (int)($u['membership_plan_id'] ?? $u['membership_plan']['id'] ?? 0);

        $gym_name = mb_strtolower((string)(
            $u['gym']['name']
            ?? $u['current_gym']['name']
            ?? $gyms_by_id[$gym_id]['name']
            ?? ''
        ));

        $plan_name = mb_strtolower((string)(
            $u['membership_plan']['name']
            ?? $plans_by_id[$plan_id]['name']
            ?? $u['membership_status']
            ?? ''
        ));

        return str_contains($full_name, $search_lower)
            || str_contains($email, $search_lower)
            || str_contains($username, $search_lower)
            || str_contains($role, $search_lower)
            || str_contains($gym_name, $search_lower)
            || str_contains($plan_name, $search_lower);
    }));
}

/**
 * Paginación local.
 */
$total_users = count($filtered_users);
$total_pages = max(1, (int)ceil($total_users / $per_page));
$page_num = min($page_num, $total_pages);
$page_num = max(1, $page_num);

$offset = ($page_num - 1) * $per_page;
$users = array_slice($filtered_users, $offset, $per_page);

$from = $total_users > 0 ? $offset + 1 : 0;
$to = $total_users > 0 ? min($offset + count($users), $total_users) : 0;

wp_app_page_start('Manage Users', true);
?>

<?php if ($api_error): ?>
    <?php show_error(api_message($first_response)); ?>
<?php endif; ?>

<?php if ($flash_success): ?>
    <?php show_success($flash_success); ?>
<?php endif; ?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6 pb-28">

    <section class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <h2 class="text-lg font-bold">User List</h2>
            <p class="text-sm text-on-surface-variant break-words">
                Gestiona usuarios, edita sus datos o elimínalos.
            </p>

            <?php if ($total_users > 0): ?>
                <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-primary-container">
                    <?= h((string)$total_users) ?> USERS REGISTERED · PAGE <?= h((string)$page_num) ?> OF <?= h((string)$total_pages) ?>
                </p>
            <?php endif; ?>
        </div>

        <div class="flex w-full flex-col gap-3 lg:w-auto lg:flex-row">
            <form method="get" action="<?= esc_url(home_url('/')) ?>" class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                <input type="hidden" name="pagename" value="staff-admin-users">
                <input type="hidden" name="page_num" value="1">

                <div class="relative w-full sm:min-w-[280px] lg:w-[320px]">
                    <input
                        type="text"
                        name="search"
                        value="<?= h($search, '') ?>"
                        placeholder="Buscar por nombre, email, usuario, rol, gym o plan"
                        class="w-full rounded-full border border-outline-variant/30 bg-surface-container-high px-4 py-3 pr-12 text-sm text-on-surface placeholder:text-on-surface-variant/60 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    >
                    <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-on-surface-variant">⌕</span>
                </div>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-full border border-outline-variant/30 px-4 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high whitespace-nowrap"
                    >
                        Buscar
                    </button>

                    <?php if ($search !== ''): ?>
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-admin-users')) ?>"
                            class="inline-flex items-center justify-center rounded-full border border-outline-variant/30 px-4 py-3 text-sm font-semibold text-on-surface-variant transition hover:border-outline/50 hover:bg-surface-container-high whitespace-nowrap"
                        >
                            Limpiar
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <a
                href="<?= esc_url(home_url('/?pagename=staff-admin-user-create')) ?>"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 self-start rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105 whitespace-nowrap"
            >
                <span class="text-base leading-none">+</span>
                <span>Create user</span>
            </a>
        </div>
    </section>

    <?php if ($search !== ''): ?>
        <div class="rounded-2xl border border-outline-variant/20 bg-surface-container p-4 text-sm text-on-surface-variant">
            Búsqueda activa:
            <span class="font-semibold text-on-surface"><?= h($search, '') ?></span>
        </div>
    <?php endif; ?>

    <section class="space-y-3">
        <?php foreach ($users as $u): ?>
            <?php
            $user_id = (int)($u['id'] ?? 0);

            $gym_id = (int)($u['current_gym_id'] ?? $u['gym_id'] ?? $u['gym']['id'] ?? $u['current_gym']['id'] ?? 0);
            $plan_id = (int)($u['membership_plan_id'] ?? $u['membership_plan']['id'] ?? 0);

            $gym_name = $u['gym']['name']
                ?? $u['current_gym']['name']
                ?? $gyms_by_id[$gym_id]['name']
                ?? '';

            $plan_name = $u['membership_plan']['name']
                ?? $plans_by_id[$plan_id]['name']
                ?? $u['membership_status']
                ?? '';

            $booking_blocked = !empty($u['is_blocked_from_booking']);
            $role = (string)($u['role'] ?? '');
            ?>

            <article class="rounded-xl border border-outline-variant/20 bg-surface-container p-4 transition hover:border-primary-container/30 hover:bg-surface-container-high">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="font-bold text-lg break-words">
                                <?= h($u['full_name'] ?? $u['email'] ?? 'User') ?>
                            </p>

                            <?php if ($user_id > 0): ?>
                                <span class="rounded-full bg-primary-container/10 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-primary-container">
                                    #<?= h((string)$user_id) ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($role !== ''): ?>
                                <span class="rounded-full border border-outline-variant/30 px-2.5 py-1 text-[10px] font-black uppercase tracking-wide text-on-surface-variant">
                                    <?= h($role) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="text-sm text-on-surface-variant min-w-0 mt-1">
                            <p class="break-words">
                                <?= h($u['email'] ?? '', '') ?>
                                <?php if (!empty($u['username'])): ?>
                                    · <?= h($u['username'], '') ?>
                                <?php endif; ?>
                            </p>
                        </div>

                        <p class="mt-1 text-sm text-on-surface-variant break-words">
                            Gym:
                            <span class="font-semibold text-on-surface">
                                <?= $gym_name !== '' ? h($gym_name) : 'No gym assigned' ?>
                            </span>
                            · Plan:
                            <span class="font-semibold text-on-surface">
                                <?= $plan_name !== '' ? h($plan_name) : 'No plan assigned' ?>
                            </span>
                        </p>

                        <p class="mt-1 text-sm break-words <?= $booking_blocked ? 'text-error' : 'text-primary-container' ?>">
                            <?= $booking_blocked ? 'Blocked from booking' : 'Booking enabled' ?>
                            · Strikes: <?= (int)($u['cancellation_strikes'] ?? 0) ?>
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 self-start sm:self-auto sm:shrink-0">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-admin-user-edit&id=' . $user_id)) ?>"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-outline-variant/30 text-sm transition hover:bg-surface-container-high"
                        >
                            Edit
                        </a>

                        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="user_id" value="<?= $user_id ?>">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-error/40 text-sm text-error transition hover:bg-error/10"
                            >
                                Delete
                            </button>
                        </form>
                    </div>

                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$users): ?>
            <div class="rounded-2xl border border-outline-variant/20 bg-surface-container px-4 py-6 text-center text-on-surface-variant">
                No users found.
            </div>
        <?php endif; ?>
    </section>

    <?php if ($total_pages > 1): ?>
        <section class="flex flex-col gap-4 rounded-xl border border-outline-variant/20 bg-surface-container p-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-on-surface-variant">
                Showing
                <span class="font-bold text-on-surface"><?= h((string)$from) ?></span>
                -
                <span class="font-bold text-on-surface"><?= h((string)$to) ?></span>
                of
                <span class="font-bold text-on-surface"><?= h((string)$total_users) ?></span>
                users
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2">

                <?php if ($page_num > 1): ?>
                    <a
                        href="<?= esc_url(users_page_url($page_num - 1, $search)) ?>"
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
                $start = max(1, $page_num - 2);
                $end = min($total_pages, $page_num + 2);
                ?>

                <?php if ($start > 1): ?>
                    <a
                        href="<?= esc_url(users_page_url(1, $search)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        1
                    </a>

                    <?php if ($start > 2): ?>
                        <span class="px-1 text-sm text-on-surface-variant">...</span>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a
                        href="<?= esc_url(users_page_url($i, $search)) ?>"
                        class="rounded-full border px-4 py-2 text-sm font-bold transition <?= $i === $page_num
                            ? 'border-primary-container bg-primary-container text-on-primary-container shadow-[0_0_18px_rgba(212,251,0,0.22)]'
                            : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                    >
                        <?= h((string)$i) ?>
                    </a>
                <?php endfor; ?>

                <?php if ($end < $total_pages): ?>
                    <?php if ($end < $total_pages - 1): ?>
                        <span class="px-1 text-sm text-on-surface-variant">...</span>
                    <?php endif; ?>

                    <a
                        href="<?= esc_url(users_page_url($total_pages, $search)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        <?= h((string)$total_pages) ?>
                    </a>
                <?php endif; ?>

                <?php if ($page_num < $total_pages): ?>
                    <a
                        href="<?= esc_url(users_page_url($page_num + 1, $search)) ?>"
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