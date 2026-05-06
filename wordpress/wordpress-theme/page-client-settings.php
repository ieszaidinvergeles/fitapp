<?php
require_once 'functions.php';
require_login();

$success = null;
$error = null;

// Handle AJAX Profile Update
if (isset($_POST['ajax_profile_update'])) {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json');
    
    // 1. Update Info
    $infoData = [
        'username' => $_POST['username'] ?? '',
        'full_name' => $_POST['full_name'] ?? '',
    ];
    $infoResp = api_put('/me/update', $infoData, auth: true);
    
    if (empty($infoResp['result'])) {
        echo json_encode([
            'result' => false, 
            'message' => ['general' => api_message($infoResp) ?: 'Failed to update info']
        ]);
        exit;
    }
    
    // 2. Update Photo if file exists
    if (!empty($_FILES['profile_photo']['tmp_name'])) {
        $photoResp = api_post_file('/me/photo', 'image', $_FILES['profile_photo']['tmp_name'], $_FILES['profile_photo']['name'], auth: true);
        if (empty($photoResp['result'])) {
            echo json_encode([
                'result' => false,
                'message' => ['general' => api_message($photoResp) ?: 'Failed to upload photo']
            ]);
            exit;
        }
    }
    
    echo json_encode(['result' => true, 'message' => ['general' => 'Profile updated.']]);
    exit;
}

// Handle Metric Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax_profile_update'])) {
    $body = [
        'language_preference' => $_POST['language_preference'] ?? 'es',
        'share_workout_stats' => !empty($_POST['share_workout_stats']),
        'share_body_metrics' => !empty($_POST['share_body_metrics']),
        'share_attendance' => !empty($_POST['share_attendance']),
    ];
    
    $save = api_put('/settings', $body, auth: true);
    if (!empty($save['result'])) {
        $success = 'Settings updated successfully.';
    } else {
        $error = api_message($save) ?: 'Failed to update settings.';
    }
}

// Fetch Current Settings
$settingsResponse = api_get('/settings', auth: true);
$settings = $settingsResponse['result'] ?? [];

// Fetch Booking History for precise stats
$bookingsResponse = api_get('/bookings?include_past=1&per_page=100', auth: true);
$allBookings = $bookingsResponse['result']['data'] ?? $bookingsResponse['result'] ?? [];

$classesCompleted = 0;
$streak = 0;
$activityDays = [];
$today = date('Y-m-d');

foreach ($allBookings as $b) {
    $status = $b['status'] ?? '';
    $startTime = $b['gym_class']['start_time'] ?? '';
    if (!$startTime) continue;
    
    $bookingDate = date('Y-m-d', strtotime($startTime));
    $isPast = strtotime($startTime) < time();
    
    if ($status === 'active' && $isPast) {
        $classesCompleted++;
        $activityDays[$bookingDate] = true;
    }
}

// Calculate Streak from activityDays
$activityDates = array_keys($activityDays);
rsort($activityDates);
if (!empty($activityDates)) {
    $checkDate = $today;
    // If no activity today, check if there was activity yesterday to continue streak
    if (!isset($activityDays[$today])) {
        $checkDate = date('Y-m-d', strtotime('-1 day'));
    }
    
    while (isset($activityDays[$checkDate])) {
        $streak++;
        $checkDate = date('Y-m-d', strtotime("-1 day", strtotime($checkDate)));
    }
}

// Check for "Today's Activity" (Classes or Meals)
$mealsResponse = api_get("/meal-schedule?date=$today", auth: true);
$mealsToday = count($mealsResponse['result']['data'] ?? $mealsResponse['result'] ?? []);
$hasActivityToday = isset($activityDays[$today]) || $mealsToday > 0;

// Fetch Dashboard data for stats and user info (most reliable source)
$dashResponse = api_get('/dashboard', auth: true);
$dashData = $dashResponse['result'] ?? [];
$user = !empty($dashData['user']) ? $dashData['user'] : ($_SESSION['user'] ?? []);

// Extract Identity
$fullName = !empty($user['full_name']) ? $user['full_name'] : (!empty($user['username']) ? $user['username'] : 'Athlete Profile');
$username = !empty($user['username']) ? $user['username'] : 'athlete';
$nameParts = explode(' ', $fullName);
$firstName = $nameParts[0] ?? '';
$lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';

wp_app_page_start('Athlete Profile');
?>
    <?php if ($settingsResponse['result'] === false) { show_error(api_message($settingsResponse)); } ?>
    <?php if ($success) show_success($success); ?>

    <section class="mt-4 mb-12">
        <div class="flex items-end justify-between mb-10">
            <div>
                <p class="font-headline text-[0.7rem] uppercase tracking-[0.3em] text-primary mb-2">@<?= h($username) ?></p>
                <h2 class="font-headline text-5xl font-black tracking-tighter uppercase leading-none"><?= h($fullName) ?></h2>
            </div>
            <div class="flex flex-col items-end gap-3">
                <button onclick="openEditProfileModal()" class="flex items-center gap-2 px-4 py-2 bg-surface-container-highest border border-outline-variant/30 rounded-xl text-[10px] font-black uppercase tracking-widest text-zinc-400 hover:text-primary-container hover:border-primary-container/50 transition-all">
                    <span class="material-symbols-outlined text-sm">edit</span>
                    Edit Profile
                </button>
                <div class="bg-primary-container px-4 py-2 rotate-[-2deg] shadow-[4px_4px_0_rgba(212,251,0,0.2)]" style="border-radius: 1.5rem 0.5rem 1.5rem 0.5rem;">
                    <span class="font-headline text-on-primary-container font-black text-xs italic tracking-widest"><?= strtoupper(h($user['role'] ?? 'MEMBER')) ?></span>
                </div>
            </div>
        </div>

        <!-- Edit Profile Modal -->
        <div id="edit-profile-modal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/80 backdrop-blur-md" onclick="closeEditProfileModal()"></div>
            <div class="relative bg-surface-container w-full max-w-lg rounded-[2.5rem] border border-outline-variant/20 shadow-2xl overflow-hidden animate-in zoom-in duration-300">
                <div class="p-8 border-b border-outline-variant/10 flex items-center justify-between">
                    <h3 class="font-headline text-2xl font-black uppercase italic tracking-tight text-white">Edit Profile</h3>
                    <button onclick="closeEditProfileModal()" class="text-zinc-500 hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form id="edit-profile-form" class="p-8 space-y-6">
                    <!-- Photo Section -->
                    <div class="flex flex-col items-center gap-4 mb-8">
                        <div class="relative group">
                            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-outline-variant/20 shadow-2xl">
                                <?php if (!empty($user['profile_photo_url'])): ?>
                                    <img id="modal-avatar-preview" src="<?= esc_url($user['profile_photo_url']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div id="modal-avatar-placeholder" class="w-full h-full bg-zinc-800 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-5xl text-zinc-600">person</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <label class="absolute inset-0 flex items-center justify-center bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer rounded-full">
                                <span class="material-symbols-outlined text-white text-3xl">add_a_photo</span>
                                <input type="file" id="profile-photo-input" class="hidden" accept="image/*" onchange="previewPhoto(this)">
                            </label>
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500">Tap to change photo</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2 px-1">Username</label>
                            <input type="text" name="username" value="<?= h($username) ?>" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/20 px-6 py-4 text-sm font-bold text-white outline-none focus:border-primary/50 transition-colors" placeholder="Username">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2 px-1">Full Name</label>
                            <input type="text" name="full_name" value="<?= h($fullName) ?>" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/20 px-6 py-4 text-sm font-bold text-white outline-none focus:border-primary/50 transition-colors" placeholder="Full Name">
                        </div>
                    </div>

                    <div id="modal-error" class="hidden p-4 bg-error/10 border border-error/20 rounded-2xl text-error text-xs font-bold text-center"></div>

                    <button type="submit" class="w-full h-16 rounded-2xl bg-primary text-black font-headline font-black uppercase tracking-widest shadow-xl shadow-primary/10 hover:scale-[1.02] transition-all active:scale-[0.98]">
                        Save Changes
                    </button>
                </form>
            </div>
        </div>

        <script>
        function openEditProfileModal() {
            document.getElementById('edit-profile-modal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEditProfileModal() {
            document.getElementById('edit-profile-modal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    let preview = document.getElementById('modal-avatar-preview');
                    if (!preview) {
                        const placeholder = document.getElementById('modal-avatar-placeholder');
                        preview = document.createElement('img');
                        preview.id = 'modal-avatar-preview';
                        preview.className = 'w-full h-full object-cover';
                        placeholder.parentNode.replaceChild(preview, placeholder);
                    }
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.getElementById('edit-profile-form').onsubmit = async function(e) {
            e.preventDefault();
            const btn = e.target.querySelector('button[type="submit"]');
            const errorDiv = document.getElementById('modal-error');
            const originalText = btn.textContent;
            
            btn.disabled = true;
            btn.textContent = 'Saving...';
            errorDiv.classList.add('hidden');

            try {
                const formData = new FormData(this);
                formData.append('ajax_profile_update', '1');
                
                // Add photo if selected
                const photoInput = document.getElementById('profile-photo-input');
                if (photoInput.files && photoInput.files[0]) {
                    formData.append('profile_photo', photoInput.files[0]);
                }

                const resp = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await resp.json();

                if (!data.result) {
                    throw new Error(data.message?.general || 'Failed to update profile');
                }

                // Success!
                window.location.reload();
            } catch (err) {
                errorDiv.textContent = err.message;
                errorDiv.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = originalText;
            }
        };
        </script>

        <!-- Stats Boxes -->
        <div class="grid grid-cols-2 gap-4 mb-12">
            <div class="bg-surface-container rounded-2xl p-6 flex flex-col justify-between h-40 border border-outline-variant/10 shadow-xl group hover:border-primary/30 transition-all">
                <div class="flex justify-between items-start">
                    <span class="material-symbols-outlined text-primary text-3xl">event_available</span>
                    <?php if ($hasActivityToday): ?>
                        <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="font-headline text-4xl font-black tracking-tighter text-on-surface"><?= (int)$classesCompleted ?></div>
                    <div class="font-label text-[0.65rem] uppercase tracking-widest text-on-surface-variant font-black">Classes Completed</div>
                </div>
            </div>
            <div class="bg-surface-container rounded-2xl p-6 flex flex-col justify-between h-40 border border-outline-variant/10 shadow-xl group hover:border-secondary/30 transition-all">
                <div class="flex justify-between items-start">
                    <span class="material-symbols-outlined text-secondary text-3xl">local_fire_department</span>
                    <span class="text-[10px] font-black text-secondary uppercase tracking-widest">Active</span>
                </div>
                <div>
                    <div class="font-headline text-4xl font-black tracking-tighter text-on-surface"><?= (int)$streak ?></div>
                    <div class="font-label text-[0.65rem] uppercase tracking-widest text-on-surface-variant font-black">Day Streak</div>
                </div>
            </div>
        </div>

        <h3 class="font-headline text-[0.7rem] uppercase tracking-[0.3em] text-on-surface-variant px-2 mb-4">Account Settings</h3>
        
        <form method="POST" class="space-y-4">
            <div class="bg-surface-container rounded-2xl p-6 border border-outline-variant/10 space-y-6 shadow-xl">
                
                <div class="flex items-center justify-between group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-surface-container-high border border-outline-variant/20 flex items-center justify-center transition-colors group-hover:border-primary/50">
                            <span class="material-symbols-outlined text-primary">language</span>
                        </div>
                        <div class="text-left">
                            <div class="font-headline text-sm font-bold uppercase tracking-tight">Language</div>
                            <div class="font-label text-[0.7rem] text-on-surface-variant">System interface language</div>
                        </div>
                    </div>
                    <select name="language_preference" class="bg-surface-container-highest rounded-xl border border-outline-variant/20 px-4 py-2.5 text-xs font-black uppercase tracking-widest focus:ring-primary focus:border-primary outline-none">
                        <option value="es" <?= ($settings['language_preference'] ?? 'es') === 'es' ? 'selected' : '' ?>>Español</option>
                        <option value="en" <?= ($settings['language_preference'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
                    </select>
                </div>

                <div class="h-[1px] w-full bg-outline-variant/5"></div>

                <label class="flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-surface-container-high border border-outline-variant/20 flex items-center justify-center transition-colors group-hover:border-primary/50">
                            <span class="material-symbols-outlined text-primary">fitness_center</span>
                        </div>
                        <div class="text-left">
                            <div class="font-headline text-sm font-bold uppercase tracking-tight">Share Workouts</div>
                            <div class="font-label text-[0.7rem] text-on-surface-variant">Allow others to see your progress</div>
                        </div>
                    </div>
                    <div class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="share_workout_stats" value="1" <?= !empty($settings['share_workout_stats']) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-zinc-400 after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-container peer-checked:after:bg-black"></div>
                    </div>
                </label>

                <label class="flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-surface-container-high border border-outline-variant/20 flex items-center justify-center transition-colors group-hover:border-primary/50">
                            <span class="material-symbols-outlined text-primary">monitor_weight</span>
                        </div>
                        <div class="text-left">
                            <div class="font-headline text-sm font-bold uppercase tracking-tight">Share Metrics</div>
                            <div class="font-label text-[0.7rem] text-on-surface-variant">Visibility for body composition</div>
                        </div>
                    </div>
                    <div class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="share_body_metrics" value="1" <?= !empty($settings['share_body_metrics']) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-zinc-400 after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-container peer-checked:after:bg-black"></div>
                    </div>
                </label>

                <label class="flex items-center justify-between cursor-pointer group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-surface-container-high border border-outline-variant/20 flex items-center justify-center transition-colors group-hover:border-primary/50">
                            <span class="material-symbols-outlined text-primary">calendar_month</span>
                        </div>
                        <div class="text-left">
                            <div class="font-headline text-sm font-bold uppercase tracking-tight">Share Attendance</div>
                            <div class="font-label text-[0.7rem] text-on-surface-variant">Public log of gym visits</div>
                        </div>
                    </div>
                    <div class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="share_attendance" value="1" <?= !empty($settings['share_attendance']) ? 'checked' : '' ?> class="sr-only peer">
                        <div class="w-11 h-6 bg-surface-container-highest peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-zinc-400 after:border-zinc-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-container peer-checked:after:bg-black"></div>
                    </div>
                </label>

            </div>

            <button type="submit" class="w-full mt-6 h-16 rounded-2xl bg-primary text-black font-headline font-black uppercase tracking-widest shadow-xl shadow-primary/10 hover:shadow-primary/20 hover:scale-[1.02] transition-all active:scale-[0.98]">
                Update My Preferences
            </button>
        </form>

        <div class="mt-20 mb-8 flex flex-col items-center">
            <div class="w-full h-[1px] bg-outline-variant/10 mb-12"></div>
            <a href="<?= esc_url(home_url('/?pagename=logout')); ?>" class="group flex items-center gap-4 px-8 py-4 rounded-2xl border border-error/20 bg-error/5 hover:bg-error/10 hover:border-error/40 transition-all active:scale-95">
                <span class="material-symbols-outlined text-error opacity-60 group-hover:opacity-100">logout</span>
                <span class="font-headline text-xs font-bold uppercase tracking-[0.2em] text-error">Terminate Session</span>
            </a>
            <p class="mt-6 text-[10px] font-black uppercase tracking-[0.3em] text-zinc-600">Volt Gym Ecosystem • v2.4.0</p>
        </div>
    </section>
<?php
$GLOBALS['active'] = 'settings';
wp_app_page_end(false);
