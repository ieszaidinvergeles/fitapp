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
 * Helper: construir URL manteniendo búsqueda
 */
function users_page_url(int $page, string $search = ''): string
{
    $url = home_url('/?pagename=staff-admin-users&page_num=' . $page);
    if ($search !== '') {
        $url .= '&search=' . urlencode($search);
    }
    return $url;
}

function staff_user_avatar_url(array $user): string
{
    return fitapp_public_asset_url(
        $user['profile_photo_url']
        ?? $user['profile_image_url']
        ?? $user['avatar_url']
        ?? $user['photo_url']
        ?? ''
    );
}

function staff_user_initials(array $user): string
{
    $source = trim((string)(
        $user['full_name']
        ?? $user['username']
        ?? $user['email']
        ?? ''
    ));

    if ($source === '') {
        return 'U';
    }

    $parts = preg_split('/\s+/', $source, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $initials = '';

    if (!empty($parts[0])) {
        $initials .= mb_substr((string)$parts[0], 0, 1);
    }

    if (!empty($parts[1])) {
        $initials .= mb_substr((string)$parts[1], 0, 1);
    }

    if ($initials === '') {
        $initials = mb_substr($source, 0, 1);
    }

    return mb_strtoupper($initials ?: 'U');
}

/**
 * Cargar usuarios paginados desde la API
 */
$paged = fitapp_api_get_page('/users', $page_num, $per_page, true, $search !== '' ? ['search' => $search] : []);
$first_response = $paged['response'];
$api_error = (($first_response['result'] ?? null) === false);

$users = $paged['items'];
$pagination = $paged['meta'];
$page_num = $pagination['current_page'];
$total_pages = $pagination['last_page'];
$total_users = $pagination['total'];
$from = $pagination['from'];
$to = $pagination['to'];

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

<div class="space-y-6">

    <section class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="min-w-0">
            <h2 class="text-lg font-bold">User List</h2>
            <p class="text-sm text-on-surface-variant break-words">
                Gestiona usuarios, edita sus datos o elimínalos.
            </p>
        </div>

        <div class="flex w-full flex-col gap-3 lg:w-auto lg:flex-row">
            <form method="get" action="<?= esc_url(home_url('/')) ?>" class="flex w-full flex-col gap-3 sm:flex-row lg:w-auto">
                <input type="hidden" name="pagename" value="staff-admin-users">

                <div class="relative w-full sm:min-w-[280px] lg:w-[320px]">
                    <input
                        type="text"
                        name="search"
                        value="<?= h($search) ?>"
                        placeholder="Buscar por nombre, email, username o rol"
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

    <div class="flex flex-col gap-2 text-sm text-on-surface-variant sm:flex-row sm:items-center sm:justify-between">
        <p>
            Total usuarios: <span class="font-semibold text-on-surface"><?= (int)$total_users ?></span>
        </p>

        <?php if ($search !== ''): ?>
            <p>
                Búsqueda activa: <span class="font-semibold text-on-surface"><?= h($search) ?></span>
            </p>
        <?php endif; ?>
    </div>

    <section class="space-y-3">
        <?php foreach ($users as $u): ?>
            <?php
            $avatar_url = staff_user_avatar_url($u);
            $avatar_initials = staff_user_initials($u);
            $user_meta_bits = [];
            $user_gym = h($u['gym']['name'] ?? $u['current_gym']['name'] ?? '');
            $user_plan = h($u['membership_plan']['name'] ?? $u['membership_status'] ?? '');

            if ($user_gym !== '') {
                $user_meta_bits[] = 'Gym: ' . $user_gym;
            }

            if ($user_plan !== '') {
                $user_meta_bits[] = 'Plan: ' . $user_plan;
            }
            ?>
            <article class="bg-surface-container rounded-xl p-4 border border-outline-variant/20">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                    <div class="flex min-w-0 flex-1 gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full border border-outline-variant/20 bg-surface-container-high text-sm font-black uppercase text-primary-container shadow-[0_0_18px_rgba(212,251,0,0.08)]">
                            <?php if ($avatar_url !== ''): ?>
                                <img
                                    src="<?= esc_url($avatar_url) ?>"
                                    alt="<?= h($u['full_name'] ?? $u['email'] ?? 'User') ?>"
                                    class="h-full w-full object-cover"
                                    onerror="this.classList.add('hidden'); if (this.nextElementSibling) { this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex'); }"
                                >
                                <span class="hidden h-full w-full items-center justify-center"><?= h($avatar_initials) ?></span>
                            <?php else: ?>
                                <span><?= h($avatar_initials) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="min-w-0 flex-1">
                        <p class="font-bold text-lg break-words">
                            <?= h($u['full_name'] ?? $u['email'] ?? 'User') ?>
                        </p>

                        <div class="text-sm text-on-surface-variant min-w-0">
                            <div class="relative max-w-full overflow-hidden">
                                <p class="truncate pr-8">
                                    <?= h($u['email'] ?? '') ?>
                                    <?php if (!empty($u['username'])): ?>
                                        • <?= h($u['username']) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($u['role'])): ?>
                                        • <?= h($u['role']) ?>
                                    <?php endif; ?>
                                </p>
                                <div class="pointer-events-none absolute inset-y-0 right-0 w-8 bg-gradient-to-l from-surface-container to-transparent"></div>
                            </div>
                        </div>

                        <?php if ($user_meta_bits): ?>
                            <p class="text-sm text-on-surface-variant break-words">
                                <?= implode(' | ', $user_meta_bits) ?>
                            </p>
                        <?php endif; ?>

                        <p class="text-sm break-words <?= !empty($u['is_blocked_from_booking']) ? 'text-error' : 'text-primary-container' ?>">
                            <?= !empty($u['is_blocked_from_booking']) ? 'Blocked from booking' : 'Booking enabled' ?>
                            • Strikes: <?= (int)($u['cancellation_strikes'] ?? 0) ?>
                        </p>
                    </div>

                        </div>

                    <div class="flex flex-wrap gap-2 self-start sm:self-auto sm:shrink-0">
                        <a
                            href="<?= esc_url(home_url('/?pagename=staff-admin-user-edit&id=' . (int)($u['id'] ?? 0))) ?>"
                            class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-outline-variant/30 text-sm"
                        >
                            Edit
                        </a>

                        <form method="post" onsubmit="return confirm('¿Seguro que quieres eliminar este usuario?');">
                            <input type="hidden" name="action_type" value="delete">
                            <input type="hidden" name="user_id" value="<?= (int)($u['id'] ?? 0) ?>">
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-lg border border-error/40 text-sm text-error"
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
                        &larr; Previous
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        &larr; Previous
                    </span>
                <?php endif; ?>

            <?php
            $start = max(1, $page_num - 2);
            $end = min($total_pages, $page_num + 2);

            if ($start > 1):
            ?>
                <a
                    href="<?= esc_url(users_page_url(1, $search)) ?>"
                    class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                >
                    1
                </a>
                <?php if ($start > 2): ?>
                    <span class="px-2 text-on-surface-variant">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start; $i <= $end; $i++): ?>
                <a
                    href="<?= esc_url(users_page_url($i, $search)) ?>"
                    class="rounded-full border px-4 py-2 text-sm font-bold transition <?= $i === $page_num
                        ? 'border-primary-container bg-primary-container text-on-primary-container shadow-[0_0_18px_rgba(212,251,0,0.22)]'
                        : 'border-outline-variant/30 hover:bg-surface-container-high' ?>"
                >
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?>
                    <span class="px-2 text-on-surface-variant">...</span>
                <?php endif; ?>
                <a
                    href="<?= esc_url(users_page_url($total_pages, $search)) ?>"
                    class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                >
                    <?= $total_pages ?>
                </a>
            <?php endif; ?>

                <?php if ($page_num < $total_pages): ?>
                    <a
                        href="<?= esc_url(users_page_url($page_num + 1, $search)) ?>"
                        class="rounded-full border border-outline-variant/30 px-4 py-2 text-sm font-bold transition hover:bg-surface-container-high"
                    >
                        Next &rarr;
                    </a>
                <?php else: ?>
                    <span class="rounded-full border border-outline-variant/10 px-4 py-2 text-sm font-bold text-on-surface-variant/40">
                        Next &rarr;
                    </span>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<?php
wp_app_page_end(true);
?>
