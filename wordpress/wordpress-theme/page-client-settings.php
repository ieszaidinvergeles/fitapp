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

$user = $_SESSION['user'] ?? [];
$nameParts = explode(' ', $user['full_name'] ?? $user['username'] ?? 'Athlete Profile');
$firstName = $nameParts[0] ?? '';
$lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';

wp_app_page_start('Athlete Profile');
?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <?php show_success($success); ?>

    <section class="mt-4 mb-12">
        <div class="flex items-end justify-between mb-6">
            <div>
                <p class="font-headline text-[0.7rem] uppercase tracking-[0.3em] text-primary mb-2">Authenticated Athlete</p>
                <h2 class="font-headline text-5xl font-black tracking-tighter uppercase leading-none"><?= h($firstName) ?><br/><?= h($lastName) ?></h2>
            </div>
            <div class="bg-primary-container px-4 py-2 rotate-[-2deg]" style="border-radius: 1.5rem 0.5rem 1.5rem 0.5rem;">
                <span class="font-headline text-on-primary-container font-black text-xs italic tracking-widest"><?= strtoupper(h($user['role'] ?? 'MEMBER')) ?></span>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-surface-container rounded-xl p-6 flex flex-col justify-between h-40 border border-outline-variant/20">
                <span class="material-symbols-outlined text-primary text-3xl">bolt</span>
                <div>
                    <div class="font-headline text-4xl font-bold tracking-tighter text-on-surface">128</div>
                    <div class="font-label text-[0.65rem] uppercase tracking-widest text-on-surface-variant">Workouts Done</div>
                </div>
            </div>
            <div class="bg-surface-container-high rounded-xl p-6 flex flex-col justify-between h-40 border border-outline-variant/20">
                <span class="material-symbols-outlined text-secondary text-3xl">local_fire_department</span>
                <div>
                    <div class="font-headline text-4xl font-bold tracking-tighter text-on-surface">14</div>
                    <div class="font-label text-[0.65rem] uppercase tracking-widest text-on-surface-variant">Day Streak</div>
                </div>
            </div>
            <div class="col-span-2 bg-surface-container-highest rounded-xl p-6 overflow-hidden relative group border border-outline-variant/20">
                <div class="relative z-10">
                    <div class="font-label text-[0.65rem] uppercase tracking-widest text-primary mb-1">Current Progress</div>
                    <div class="font-headline text-2xl font-bold tracking-tight mb-4">Phase: Hypertrophy IV</div>
                    <div class="w-full bg-surface-dim h-3 rounded-full overflow-hidden">
                        <div class="h-full w-3/4 rounded-full shadow-[0_0_15px_rgba(212,251,0,0.5)] bg-gradient-to-r from-primary to-primary-dim"></div>
                    </div>
                </div>
                <div class="absolute inset-0 opacity-10 group-hover:opacity-20 transition-opacity">
                    <img alt="texture" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBqOSnisGOSp0y97JGgaiCuPEE7EncU6UkMzH_GFKWwGmVw950YJToqRWwaBe6AuaBgNOEmVBctB2KX8XHXLtQld_fLIzFDgjTl8Cm6YlViZ-qRZYmDSuf4eB2ILqq5XaWYybin9tob7leUYvKIfaNMuYjMZDjU88RBX7EJ41p6ORY2dFD8P3jVxidzX_6JlX3FdLi5x3-GstQSl_5KlUgMn09YraCFGr8jVCSlPjev4M7zpwADWmbysds3_VshyxI5vHdhuJRbf3Y"/>
                </div>
            </div>
        </div>
    </section>

    <section class="space-y-3">
        <h3 class="font-headline text-[0.7rem] uppercase tracking-[0.3em] text-on-surface-variant px-2 mb-4">Account Settings</h3>
        
        <form method="POST" class="space-y-4">
            <div class="bg-surface-container-high rounded-xl p-5 border border-outline-variant/20 space-y-6">
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-black flex items-center justify-center">
                            <span class="material-symbols-outlined text-primary">language</span>
                        </div>
                        <div class="text-left">
                            <div class="font-headline text-sm font-bold uppercase tracking-tight">Language</div>
                            <div class="font-label text-[0.7rem] text-on-surface-variant">System language</div>
                        </div>
                    </div>
                    <select name="language_preference" class="bg-surface-container-highest rounded-lg border border-outline-variant/20 px-3 py-2 text-sm focus:ring-primary focus:border-primary">
                        <option value="es" <?= ($settings['language_preference'] ?? 'es') === 'es' ? 'selected' : '' ?>>ES</option>
                        <option value="en" <?= ($settings['language_preference'] ?? '') === 'en' ? 'selected' : '' ?>>EN</option>
                    </select>
                </div>

                <label class="flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-black flex items-center justify-center group-hover:bg-surface-container-highest transition-colors">
                            <span class="material-symbols-outlined text-primary">dark_mode</span>
                        </div>
                        <div class="text-left">
                            <div class="font-headline text-sm font-bold uppercase tracking-tight">Dark Theme</div>
                            <div class="font-label text-[0.7rem] text-on-surface-variant">Enable dark mode</div>
                        </div>
                    </div>
                    <input type="checkbox" name="theme_preference" value="1" <?= !empty($settings['theme_preference']) ? 'checked' : '' ?> class="w-5 h-5 rounded border-outline-variant/40 bg-surface-container-highest text-primary focus:ring-primary focus:ring-offset-background"/>
                </label>

                <label class="flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-black flex items-center justify-center group-hover:bg-surface-container-highest transition-colors">
                            <span class="material-symbols-outlined text-primary">fitness_center</span>
                        </div>
                        <div class="text-left">
                            <div class="font-headline text-sm font-bold uppercase tracking-tight">Share Workouts</div>
                            <div class="font-label text-[0.7rem] text-on-surface-variant">Public workout stats</div>
                        </div>
                    </div>
                    <input type="checkbox" name="share_workout_stats" value="1" <?= !empty($settings['share_workout_stats']) ? 'checked' : '' ?> class="w-5 h-5 rounded border-outline-variant/40 bg-surface-container-highest text-primary focus:ring-primary focus:ring-offset-background"/>
                </label>

                <label class="flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-black flex items-center justify-center group-hover:bg-surface-container-highest transition-colors">
                            <span class="material-symbols-outlined text-primary">monitor_weight</span>
                        </div>
                        <div class="text-left">
                            <div class="font-headline text-sm font-bold uppercase tracking-tight">Share Metrics</div>
                            <div class="font-label text-[0.7rem] text-on-surface-variant">Public body metrics</div>
                        </div>
                    </div>
                    <input type="checkbox" name="share_body_metrics" value="1" <?= !empty($settings['share_body_metrics']) ? 'checked' : '' ?> class="w-5 h-5 rounded border-outline-variant/40 bg-surface-container-highest text-primary focus:ring-primary focus:ring-offset-background"/>
                </label>

                <label class="flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-black flex items-center justify-center group-hover:bg-surface-container-highest transition-colors">
                            <span class="material-symbols-outlined text-primary">calendar_month</span>
                        </div>
                        <div class="text-left">
                            <div class="font-headline text-sm font-bold uppercase tracking-tight">Share Attendance</div>
                            <div class="font-label text-[0.7rem] text-on-surface-variant">Public gym visits</div>
                        </div>
                    </div>
                    <input type="checkbox" name="share_attendance" value="1" <?= !empty($settings['share_attendance']) ? 'checked' : '' ?> class="w-5 h-5 rounded border-outline-variant/40 bg-surface-container-highest text-primary focus:ring-primary focus:ring-offset-background"/>
                </label>

            </div>

            <button class="w-full mt-4 h-14 rounded-full bg-gradient-to-r from-primary to-primary-dim text-black font-headline font-black uppercase tracking-[0.1em] shadow-[0_0_20px_rgba(212,251,0,0.2)] hover:shadow-[0_0_30px_rgba(212,251,0,0.4)] transition-all active:scale-[0.98]">
                Save Settings
            </button>
        </form>

        <div class="mt-12 mb-8 text-center">
            <a href="page-logout.php" class="inline-block font-headline text-xs font-bold uppercase tracking-[0.2em] text-[#D7FF00] hover:opacity-80 transition-all px-8 py-4 border border-[#D7FF00] bg-[#D7FF00]/5 rounded-full active:scale-95 duration-150">
                Sign Out of Account
            </a>
        </div>
    </section>
<?php
$GLOBALS['active'] = 'settings';
wp_app_page_end(false);

