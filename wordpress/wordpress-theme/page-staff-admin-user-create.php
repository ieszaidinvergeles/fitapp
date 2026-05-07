<?php
/*
Template Name: Staff Admin User Create
*/
require_once 'functions.php';
require_user_management();

$flash_error = '';

function form_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    return ($value === '-' || $value === '—' || $value === 'â€”') ? '' : $value;
}

/**
 * Procesar creación
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'username' => trim((string)($_POST['username'] ?? '')),
        'full_name' => trim((string)($_POST['full_name'] ?? '')),
        'email' => trim((string)($_POST['email'] ?? '')),
        'role' => trim((string)($_POST['role'] ?? '')),
        'dni' => trim((string)($_POST['dni'] ?? '')),
        'birth_date' => trim((string)($_POST['birth_date'] ?? '')),
        'current_gym_id' => !empty($_POST['current_gym_id']) ? (int)$_POST['current_gym_id'] : null,
        'membership_plan_id' => !empty($_POST['membership_plan_id']) ? (int)$_POST['membership_plan_id'] : null,
        'membership_status' => !empty($_POST['membership_status']) ? trim((string)$_POST['membership_status']) : null,
        'cancellation_strikes' => isset($_POST['cancellation_strikes']) ? (int)$_POST['cancellation_strikes'] : 0,
        'is_blocked_from_booking' => !empty($_POST['is_blocked_from_booking']),
    ];

    $password = trim((string)($_POST['password'] ?? ''));
    if ($password !== '') {
        $payload['password_hash'] = $password;
    }

    $payload = array_filter($payload, function ($value) {
        return $value !== null;
    });

    $create_response = api_post('/users', $payload, auth: true);

    if (($create_response['result'] ?? false) !== false) {
        wp_redirect(home_url('/?pagename=staff-admin-users&notice=created'));
        exit;
    } else {
        $flash_error = api_message($create_response);
    }
}

/**
 * Cargar combos
 */
$gyms_response = api_get('/gyms', auth: true);
$gyms = $gyms_response['result']['data'] ?? ($gyms_response['result'] ?? []);

$plans_response = api_get('/membership-plans', auth: true);
$plans = $plans_response['result']['data'] ?? ($plans_response['result'] ?? []);

wp_app_page_start('Create User', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-xl font-bold">Create User</h2>
            <p class="text-sm text-on-surface-variant">
                Crea un nuevo usuario desde este formulario.
            </p>
        </div>

        <a
            href="<?= esc_url(home_url('/?pagename=staff-admin-users')) ?>"
            class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
        >
            ← Back to users
        </a>
    </div>

    <section class="rounded-3xl border border-outline-variant/20 bg-surface-container p-4 sm:p-6 shadow-lg">
        <form method="post" class="space-y-6">

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Full name</label>
                    <input
                        type="text"
                        name="full_name"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h(form_value('full_name', '')) ?>"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Username</label>
                    <input
                        type="text"
                        name="username"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h(form_value('username', '')) ?>"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h(form_value('email', '')) ?>"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">DNI</label>
                    <input
                        type="text"
                        name="dni"
                        maxlength="9"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h(form_value('dni', '')) ?>"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Birth date</label>
                    <input
                        type="date"
                        name="birth_date"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h(form_value('birth_date', '')) ?>"
                        required
                    >
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Role</label>
                    <select
                        name="role"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        required
                    >
                        <?php $roles = ['client', 'staff', 'assistant', 'manager', 'admin', 'user_online']; ?>
                        <option value="">Select role</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= h($role) ?>" <?= form_value('role', '') === $role ? 'selected' : '' ?>>
                                <?= h(ucfirst(str_replace('_', ' ', $role))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Gym</label>
                    <select
                        name="current_gym_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    >
                        <option value="">Select gym</option>
                        <?php foreach ($gyms as $gym): ?>
                            <?php $gym_id = (int)($gym['id'] ?? 0); ?>
                            <option value="<?= $gym_id ?>" <?= (int)form_value('current_gym_id', 0) === $gym_id ? 'selected' : '' ?>>
                                <?= h($gym['name'] ?? 'Gym') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Membership plan</label>
                    <select
                        name="membership_plan_id"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    >
                        <option value="">Select plan</option>
                        <?php foreach ($plans as $plan): ?>
                            <?php $plan_id = (int)($plan['id'] ?? 0); ?>
                            <option value="<?= $plan_id ?>" <?= (int)form_value('membership_plan_id', 0) === $plan_id ? 'selected' : '' ?>>
                                <?= h($plan['name'] ?? 'Plan') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Membership status</label>
                    <select
                        name="membership_status"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                    >
                        <option value="">Select status</option>
                        <option value="active" <?= form_value('membership_status', '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="paused" <?= form_value('membership_status', '') === 'paused' ? 'selected' : '' ?>>Paused</option>
                        <option value="expired" <?= form_value('membership_status', '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Cancellation strikes</label>
                    <input
                        type="number"
                        name="cancellation_strikes"
                        min="0"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h((string)form_value('cancellation_strikes', '0')) ?>"
                    >
                </div>
            </div>

            <label for="isBlockedToggle" class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 transition hover:border-primary-container/50 hover:bg-surface-container-highest">
                <span class="text-sm font-medium text-on-surface">Blocked from booking</span>

                <div class="relative shrink-0">
                    <input
                        id="isBlockedToggle"
                        type="checkbox"
                        name="is_blocked_from_booking"
                        value="1"
                        class="peer sr-only"
                        <?= !empty($_POST['is_blocked_from_booking']) ? 'checked' : '' ?>
                    >

                    <span class="relative block h-7 w-12 rounded-full border border-outline-variant/30 bg-surface-container transition after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-on-surface-variant after:shadow-md after:transition-all after:content-[''] peer-checked:border-primary-container peer-checked:bg-primary-container peer-checked:after:translate-x-5 peer-checked:after:bg-on-primary-container"></span>
                </div>
            </label>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Create User
                </button>

                <a
                    href="<?= esc_url(home_url('/?pagename=staff-admin-users')) ?>"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full border border-outline-variant/30 px-5 py-3 text-sm font-semibold text-on-surface transition hover:border-outline/50 hover:bg-surface-container-high"
                >
                    Cancel
                </a>
            </div>

        </form>
    </section>
</div>

<?php
wp_app_page_end(true);
?>
