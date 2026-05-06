<?php
require_once 'functions.php';
require_login();

// Handle AJAX toggle friend
if (isset($_POST['ajax_toggle'])) {
    while (ob_get_level()) ob_end_clean();
    $id = (int)($_POST['id'] ?? 0);
    $resp = api_post("/friends/{$id}/toggle", [], auth: true);
    header('Content-Type: application/json');
    echo json_encode($resp);
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    wp_redirect('?pagename=client-friends');
    exit;
}

$response = api_get("/friends/{$id}/profile", auth: true);
$profile = $response['result'] ?? null;

if (!$profile) {
    wp_app_page_start('Profile Not Found');
    echo '<div class="text-center py-20 bg-surface-container/30 rounded-3xl border border-dashed border-outline-variant/30 max-w-2xl mx-auto">';
    echo '<span class="material-symbols-outlined text-6xl text-zinc-800 mb-4">person_off</span>';
    echo '<p class="text-zinc-500 font-medium italic uppercase tracking-widest text-sm">' . h(api_message($response) ?: 'User profile not found.') . '</p>';
    echo '<a href="?pagename=client-friends" class="inline-block mt-8 px-10 py-4 bg-primary-container text-on-primary-container rounded-full text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-transform">Back to Network</a>';
    echo '</div>';
    wp_app_page_end(false);
    exit;
}

// Compute age from birth_date
$age = null;
if (!empty($profile['birth_date'])) {
    $birthDate = new DateTime($profile['birth_date']);
    $now = new DateTime();
    $age = $now->diff($birthDate)->y;
}

// Format join date
$joinDate = !empty($profile['member_since']) ? date('M Y', strtotime($profile['member_since'])) : null;

// Membership status styling
$statusColors = [
    'active' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/20', 'dot' => 'bg-emerald-400'],
    'inactive' => ['bg' => 'bg-zinc-500/10', 'text' => 'text-zinc-400', 'border' => 'border-zinc-500/20', 'dot' => 'bg-zinc-400'],
    'suspended' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/20', 'dot' => 'bg-amber-400'],
    'cancelled' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'border' => 'border-red-500/20', 'dot' => 'bg-red-400'],
];
$status = $profile['membership_status'] ?? 'inactive';
$sc = $statusColors[$status] ?? $statusColors['inactive'];

wp_app_page_start('Member Profile');
?>
    <div class="mb-8">
        <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-zinc-500 hover:text-primary-container transition-colors mb-8">
            <span class="material-symbols-outlined text-sm">arrow_back</span>
            Back
        </a>

        <!-- Hero Card -->
        <div class="bg-surface-container rounded-[2.5rem] border border-outline-variant/10 p-10 flex flex-col md:flex-row items-center gap-10 shadow-2xl shadow-black/50 overflow-hidden relative">
            <div class="absolute top-0 right-0 w-64 h-64 bg-primary-container/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
            
            <!-- Avatar -->
            <div class="relative">
                <div class="w-40 h-40 rounded-full overflow-hidden border-4 border-outline-variant/20 shadow-2xl relative z-10">
                    <?php if (!empty($profile['profile_photo_url'])): ?>
                        <img src="<?= esc_url($profile['profile_photo_url']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-zinc-800 flex items-center justify-center">
                            <span class="material-symbols-outlined text-6xl text-zinc-600">person</span>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Status badge -->
                <div class="absolute -bottom-2 -right-2 px-3 py-1.5 <?= $sc['bg'] ?> <?= $sc['text'] ?> border <?= $sc['border'] ?> rounded-full text-[8px] font-black uppercase tracking-widest z-20 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full <?= $sc['dot'] ?>"></span>
                    <?= h(ucfirst($status)) ?>
                </div>
            </div>

            <!-- Info -->
            <div class="text-center md:text-left relative z-10 flex-1">
                <div class="flex items-center justify-center md:justify-start gap-3 mb-2">
                    <p class="text-primary-container font-black uppercase tracking-[0.3em] text-[10px]">
                        <?= h(ucfirst($profile['role'] ?? 'member')) ?>
                    </p>
                    <?php if (!empty($profile['membership_plan'])): ?>
                        <span class="px-2.5 py-0.5 bg-primary-container/10 text-primary-container border border-primary-container/20 rounded-full text-[8px] font-black uppercase tracking-widest">
                            <?= h($profile['membership_plan']['name']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <h2 class="font-headline text-5xl md:text-7xl font-black uppercase tracking-tighter leading-none italic text-white mb-2"><?= h($profile['full_name'] ?: $profile['username']) ?></h2>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4">
                    <span class="text-zinc-500 font-bold uppercase tracking-widest text-sm">@<?= h($profile['username']) ?></span>
                    <?php if ($age): ?>
                        <span class="w-1 h-1 bg-zinc-700 rounded-full"></span>
                        <span class="text-zinc-500 font-bold uppercase tracking-widest text-sm"><?= $age ?> years</span>
                    <?php endif; ?>
                    <?php if ($joinDate): ?>
                        <span class="w-1 h-1 bg-zinc-700 rounded-full"></span>
                        <span class="text-zinc-500 font-bold uppercase tracking-widest text-sm">Joined <?= h($joinDate) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Friend Action Button -->
            <?php if (empty($profile['is_me'])): ?>
                <div class="relative z-10">
                    <?php if (!empty($profile['is_friend'])): ?>
                        <button id="friend-btn" onclick="toggleFriend(<?= (int)$profile['id'] ?>)" class="px-8 py-4 bg-error/10 hover:bg-error/20 text-error rounded-2xl text-[10px] font-black uppercase tracking-widest border border-error/20 transition-all flex items-center gap-2 active:scale-95">
                            <span class="material-symbols-outlined text-sm">person_remove</span>
                            <span class="btn-label">Remove Friend</span>
                        </button>
                    <?php else: ?>
                        <button id="friend-btn" onclick="toggleFriend(<?= (int)$profile['id'] ?>)" class="px-8 py-4 kinetic-gradient text-on-primary-container rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-primary-container/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">person_add</span>
                            <span class="btn-label">Add Friend</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Gym -->
        <div class="bg-surface-container rounded-3xl border border-outline-variant/10 p-8 flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-primary-container/10 border border-primary-container/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl text-primary-container">fitness_center</span>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-zinc-500 mb-1">Gym</p>
                <?php if (!empty($profile['gym'])): ?>
                    <p class="text-lg font-black text-white italic"><?= h($profile['gym']['name']) ?></p>
                    <?php if (!empty($profile['gym']['city'])): ?>
                        <p class="text-xs text-zinc-500 font-medium"><?= h($profile['gym']['city']) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-sm text-zinc-600 italic">No gym assigned</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Membership Plan -->
        <div class="bg-surface-container rounded-3xl border border-outline-variant/10 p-8 flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-primary-container/10 border border-primary-container/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl text-primary-container">workspace_premium</span>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-zinc-500 mb-1">Membership</p>
                <?php if (!empty($profile['membership_plan'])): ?>
                    <p class="text-lg font-black text-white italic"><?= h($profile['membership_plan']['name']) ?></p>
                    <p class="text-xs text-zinc-500 font-medium"><?= h(ucfirst($profile['membership_plan']['type'])) ?></p>
                <?php else: ?>
                    <p class="text-sm text-zinc-600 italic">No active plan</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Member Since -->
        <div class="bg-surface-container rounded-3xl border border-outline-variant/10 p-8 flex items-center gap-5">
            <div class="w-14 h-14 rounded-2xl bg-primary-container/10 border border-primary-container/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-2xl text-primary-container">calendar_month</span>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-zinc-500 mb-1">Member Since</p>
                <?php if ($joinDate): ?>
                    <p class="text-lg font-black text-white italic"><?= h($joinDate) ?></p>
                    <?php if ($age): ?>
                        <p class="text-xs text-zinc-500 font-medium"><?= $age ?> years old</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-sm text-zinc-600 italic">Unknown</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Body Metrics -->
        <section class="bg-surface-container rounded-[2.5rem] border border-outline-variant/10 p-10">
            <div class="flex items-center justify-between mb-8">
                <h3 class="font-headline text-2xl font-black uppercase italic tracking-tight text-white">Body Evolution</h3>
                <span class="material-symbols-outlined text-primary-container text-3xl">monitoring</span>
            </div>

            <?php if ($profile['metrics']): ?>
                <?php $m = $profile['metrics']; ?>
                <div class="grid grid-cols-2 gap-6">
                    <?php if (isset($m['weight_kg'])): ?>
                    <div class="p-6 rounded-3xl bg-surface-container-high border border-outline-variant/10">
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2">Weight</p>
                        <p class="text-4xl font-black text-white italic"><?= h($m['weight_kg']) ?><span class="text-sm font-bold text-primary-container not-italic ml-1">KG</span></p>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($m['body_fat_percentage'])): ?>
                    <div class="p-6 rounded-3xl bg-surface-container-high border border-outline-variant/10">
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2">Body Fat</p>
                        <p class="text-4xl font-black text-white italic"><?= h($m['body_fat_percentage']) ?><span class="text-sm font-bold text-primary-container not-italic ml-1">%</span></p>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($m['muscle_mass_kg'])): ?>
                    <div class="p-6 rounded-3xl bg-surface-container-high border border-outline-variant/10">
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2">Muscle Mass</p>
                        <p class="text-4xl font-black text-white italic"><?= h($m['muscle_mass_kg']) ?><span class="text-sm font-bold text-primary-container not-italic ml-1">KG</span></p>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($m['date'])): ?>
                    <div class="p-6 rounded-3xl bg-surface-container-high border border-outline-variant/10">
                        <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2">Last Update</p>
                        <p class="text-xl font-black text-white uppercase italic"><?= h(date('M d, Y', strtotime($m['date']))) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="py-12 text-center bg-zinc-900/50 rounded-3xl border border-dashed border-outline-variant/20">
                    <span class="material-symbols-outlined text-5xl text-zinc-800 mb-4"><?= $profile['privacy']['metrics_public'] ? 'trending_flat' : 'lock' ?></span>
                    <p class="text-zinc-500 text-sm font-medium italic uppercase tracking-widest px-10">
                        <?= $profile['privacy']['metrics_public'] ? 'No metrics recorded yet.' : 'This data is private.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Training History -->
        <section class="bg-surface-container rounded-[2.5rem] border border-outline-variant/10 p-10 flex flex-col">
            <div class="flex items-center justify-between mb-8">
                <h3 class="font-headline text-2xl font-black uppercase italic tracking-tight text-white">Training History</h3>
                <span class="material-symbols-outlined text-primary-container text-3xl">bolt</span>
            </div>

            <?php if ($profile['stats']): ?>
                <div class="flex-1 flex flex-col justify-center gap-6">
                    <div class="p-10 rounded-[2rem] bg-primary-container text-black shadow-2xl shadow-primary-container/20 text-center transform hover:scale-[1.02] transition-transform">
                        <p class="text-[10px] font-black uppercase tracking-[0.3em] mb-4">Classes Completed</p>
                        <p class="text-8xl font-headline font-black italic leading-none"><?= (int)$profile['stats']['completed_classes'] ?></p>
                        <div class="h-1 w-12 bg-black mx-auto mt-6"></div>
                    </div>
                    
                    <div class="p-6 rounded-3xl bg-surface-container-high border border-outline-variant/10 text-center">
                        <p class="text-[9px] font-black text-zinc-500 uppercase tracking-widest mb-2">Total Bookings</p>
                        <p class="text-3xl font-black text-white italic"><?= (int)$profile['stats']['total_bookings'] ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex-1 flex flex-col items-center justify-center py-12 bg-zinc-900/50 rounded-3xl border border-dashed border-outline-variant/20">
                    <span class="material-symbols-outlined text-5xl text-zinc-800 mb-4"><?= $profile['privacy']['stats_public'] ? 'fitness_center' : 'visibility_off' ?></span>
                    <p class="text-zinc-500 text-sm font-medium italic uppercase tracking-widest px-10 text-center">
                        <?= $profile['privacy']['stats_public'] ? 'No training history yet.' : 'Training activity is hidden.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script>
    async function toggleFriend(id) {
        const btn = document.getElementById('friend-btn');
        const label = btn.querySelector('.btn-label');
        const isRemoving = label.textContent.includes('Remove');
        
        if (isRemoving && !confirm('Are you sure you want to remove this friend?')) return;

        btn.disabled = true;
        btn.style.opacity = '0.5';
        label.textContent = 'Processing...';

        try {
            const formData = new FormData();
            formData.append('ajax_toggle', '1');
            formData.append('id', id);

            const resp = await fetch(window.location.href, { method: 'POST', body: formData });
            const data = await resp.json();

            if (data.result) {
                // Reload to refresh state
                window.location.reload();
            } else {
                alert(data.message?.general || 'Error');
                btn.disabled = false;
                btn.style.opacity = '1';
                label.textContent = isRemoving ? 'Remove Friend' : 'Add Friend';
            }
        } catch (err) {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.style.opacity = '1';
            label.textContent = isRemoving ? 'Remove Friend' : 'Add Friend';
        }
    }
    </script>

<?php
$GLOBALS['active'] = 'friends';
wp_app_page_end(false);
?>
