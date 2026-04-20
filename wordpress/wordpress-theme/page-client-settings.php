<?php
require_once 'functions.php';
require_login();
$success = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = [
        'language_preference' => $_POST['language_preference'] ?? 'es',
        'theme_preference' => !empty($_POST['theme_preference']),
        'share_workout_stats' => !empty($_POST['share_workout_stats']),
        'share_body_metrics' => !empty($_POST['share_body_metrics']),
        'share_attendance' => !empty($_POST['share_attendance']),
    ];
    $save = api_put('/settings', $body, auth: true);
    if (!empty($save['result'])) {
        $success = 'Settings updated.';
    } else {
        show_error(api_message($save));
    }
}
$response = api_get('/settings', auth: true);
$settings = $response['result'] ?? [];
wp_app_page_start('Settings');
?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <?php show_success($success); ?>
    <form method="POST" class="bg-surface-container rounded-2xl p-6 border border-outline-variant/20 space-y-4">
        <label class="block text-xs uppercase tracking-widest text-on-surface-variant">Language
            <select name="language_preference" class="mt-2 w-full bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-3">
                <option value="es" <?= ($settings['language_preference'] ?? 'es') === 'es' ? 'selected' : '' ?>>Spanish</option>
                <option value="en" <?= ($settings['language_preference'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
            </select>
        </label>
        <label class="flex items-center justify-between text-sm">
            <span class="font-bold">Dark theme</span>
            <input type="checkbox" name="theme_preference" value="1" <?= !empty($settings['theme_preference']) ? 'checked' : '' ?> />
        </label>
        <label class="flex items-center justify-between text-sm">
            <span class="font-bold">Share workout stats</span>
            <input type="checkbox" name="share_workout_stats" value="1" <?= !empty($settings['share_workout_stats']) ? 'checked' : '' ?> />
        </label>
        <label class="flex items-center justify-between text-sm">
            <span class="font-bold">Share body metrics</span>
            <input type="checkbox" name="share_body_metrics" value="1" <?= !empty($settings['share_body_metrics']) ? 'checked' : '' ?> />
        </label>
        <label class="flex items-center justify-between text-sm">
            <span class="font-bold">Share attendance</span>
            <input type="checkbox" name="share_attendance" value="1" <?= !empty($settings['share_attendance']) ? 'checked' : '' ?> />
        </label>
        <button class="kinetic-gradient text-on-primary-container px-6 py-3 rounded-full font-black uppercase tracking-widest text-xs">Save</button>
    </form>
<?php
wp_app_page_end(false);

