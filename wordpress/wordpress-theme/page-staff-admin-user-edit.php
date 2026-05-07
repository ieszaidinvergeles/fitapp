<?php
/*
Template Name: Staff Admin User Edit
*/
require_once 'functions.php';
require_user_management();

$user_id = (int)($_GET['id'] ?? 0);
$flash_error = '';

if (($_GET['photo_error'] ?? '') === '1') {
    $flash_error = 'User saved, but the photo could not be uploaded. Try again from this screen.';
}

if ($user_id <= 0) {
    wp_redirect(home_url('/?pagename=staff-admin-users'));
    exit;
}

function form_post_value(string $key, $default = '')
{
    $value = $_POST[$key] ?? $default;

    return ($value === '-' || $value === '—' || $value === 'â€”') ? '' : $value;
}

function user_field(array $user = null, string $key, $default = '')
{
    if (!$user) {
        return $default;
    }
    $value = $user[$key] ?? $default;

    return ($value === '-' || $value === '—' || $value === 'â€”') ? '' : $value;
}

function user_birth_date_value(array $user = null): string
{
    $raw = user_field($user, 'birth_date', '');
    if (!$raw) {
        return '';
    }
    return substr((string)$raw, 0, 10);
}

/**
 * Load user
 */
$user_response = api_get('/users/' . $user_id, auth: true);
$editing_user = (($user_response['result'] ?? false) !== false) ? $user_response['result'] : null;

if (!$editing_user) {
    wp_redirect(home_url('/?pagename=staff-admin-users'));
    exit;
}

/**
 * Process edit
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photo_file = $_FILES['profile_photo'] ?? null;
    $photo_error = fitapp_upload_error_message($photo_file);

    if ($photo_error !== null) {
        $flash_error = $photo_error;
    } else {
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

        $update_response = api_put('/users/' . $user_id, $payload, auth: true);

        if (($update_response['result'] ?? false) !== false) {
            if (fitapp_has_uploaded_file($photo_file)) {
                $photo_response = api_post_file(
                    '/users/' . $user_id . '/photo',
                    'image',
                    $photo_file['tmp_name'],
                    $photo_file['name'] ?? 'profile-photo',
                    true
                );

                if (($photo_response['result'] ?? false) === false) {
                    $flash_error = api_message($photo_response) ?: 'Details saved, but the photo could not be uploaded.';
                } else {
                    wp_redirect(home_url('/?pagename=staff-admin-users&notice=updated'));
                    exit;
                }
            } else {
                wp_redirect(home_url('/?pagename=staff-admin-users&notice=updated'));
                exit;
            }
        } else {
            $flash_error = api_message($update_response);
        }
    }
}

/**
 * Cargar combos
 */
$gyms_response = api_get('/gyms', auth: true);
$gyms = $gyms_response['result']['data'] ?? ($gyms_response['result'] ?? []);

$plans_response = api_get('/membership-plans', auth: true);
$plans = $plans_response['result']['data'] ?? ($plans_response['result'] ?? []);

$is_postback = $_SERVER['REQUEST_METHOD'] === 'POST';

$form_full_name = $is_postback ? form_post_value('full_name', '') : user_field($editing_user, 'full_name', '');
$form_username = $is_postback ? form_post_value('username', '') : user_field($editing_user, 'username', '');
$form_email = $is_postback ? form_post_value('email', '') : user_field($editing_user, 'email', '');
$form_dni = $is_postback ? form_post_value('dni', '') : user_field($editing_user, 'dni', '');
$form_birth_date = $is_postback ? form_post_value('birth_date', '') : user_birth_date_value($editing_user);
$form_role = $is_postback ? form_post_value('role', '') : user_field($editing_user, 'role', '');
$form_membership_status = $is_postback ? form_post_value('membership_status', '') : user_field($editing_user, 'membership_status', '');
$form_cancellation_strikes = $is_postback
    ? (string)form_post_value('cancellation_strikes', '0')
    : (string)user_field($editing_user, 'cancellation_strikes', 0);

$form_is_blocked = $is_postback
    ? !empty($_POST['is_blocked_from_booking'])
    : !empty($editing_user['is_blocked_from_booking']);

$form_current_gym_id = 0;
if ($is_postback) {
    $form_current_gym_id = (int)form_post_value('current_gym_id', 0);
} else {
    $form_current_gym_id = (int)(
        $editing_user['current_gym_id']
        ?? $editing_user['gym']['id']
        ?? $editing_user['current_gym']['id']
        ?? 0
    );
}

$form_membership_plan_id = 0;
if ($is_postback) {
    $form_membership_plan_id = (int)form_post_value('membership_plan_id', 0);
} else {
    $form_membership_plan_id = (int)(
        $editing_user['membership_plan_id']
        ?? $editing_user['membership_plan']['id']
        ?? 0
    );
}

$current_profile_photo = fitapp_public_asset_url(
    $editing_user['profile_photo_url']
    ?? $editing_user['profile_image_url']
    ?? $editing_user['avatar_url']
    ?? $editing_user['photo_url']
    ?? ''
);

wp_app_page_start('Edit User', true);
?>

<?php if ($flash_error): ?>
    <?php show_error($flash_error); ?>
<?php endif; ?>

<div class="space-y-6">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-xl font-bold">Edit User</h2>
            <p class="text-sm text-on-surface-variant">
                Update the user details and save the changes.
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
        <form method="post" enctype="multipart/form-data" class="space-y-6">

            <?php fitapp_render_image_dropzone('Profile photo', 'Change profile photo', 'userPhotoInput', 'userPhotoDropzone', 'profile_photo', $current_profile_photo, 'Profile photo preview', 'person'); ?>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Full name</label>
                    <input
                        type="text"
                        name="full_name"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h($form_full_name) ?>"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Username</label>
                    <input
                        type="text"
                        name="username"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h($form_username) ?>"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h($form_email) ?>"
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
                        value="<?= h($form_dni) ?>"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Birth date</label>
                    <input
                        type="date"
                        name="birth_date"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h($form_birth_date) ?>"
                        required
                    >
                </div>

                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Password (leave empty to keep current one)</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
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
                            <option value="<?= h($role) ?>" <?= $form_role === $role ? 'selected' : '' ?>>
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
                            <option value="<?= $gym_id ?>" <?= $form_current_gym_id === $gym_id ? 'selected' : '' ?>>
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
                            <option value="<?= $plan_id ?>" <?= $form_membership_plan_id === $plan_id ? 'selected' : '' ?>>
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
                        <option value="active" <?= $form_membership_status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="paused" <?= $form_membership_status === 'paused' ? 'selected' : '' ?>>Paused</option>
                        <option value="expired" <?= $form_membership_status === 'expired' ? 'selected' : '' ?>>Expired</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-on-surface-variant">Cancellation strikes</label>
                    <input
                        type="number"
                        name="cancellation_strikes"
                        min="0"
                        class="w-full rounded-2xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 focus:border-primary-container focus:outline-none focus:ring-2 focus:ring-primary-container/20"
                        value="<?= h($form_cancellation_strikes) ?>"
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
                        <?= $form_is_blocked ? 'checked' : '' ?>
                    >

                    <span class="relative block h-7 w-12 rounded-full border border-outline-variant/30 bg-surface-container transition after:absolute after:left-1 after:top-1 after:h-5 after:w-5 after:rounded-full after:bg-on-surface-variant after:shadow-md after:transition-all after:content-[''] peer-checked:border-primary-container peer-checked:bg-primary-container peer-checked:after:translate-x-5 peer-checked:after:bg-on-primary-container"></span>
                </div>
            </label>

            <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                <button
                    type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-full bg-primary-container px-5 py-3 text-sm font-black uppercase tracking-wide text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.18)] transition-all duration-200 hover:scale-[1.01] hover:brightness-105"
                >
                    Save changes
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

<?php fitapp_render_image_dropzone_script('userPhotoInput', 'userPhotoDropzone'); ?>

<?php
wp_app_page_end(true);
?>
