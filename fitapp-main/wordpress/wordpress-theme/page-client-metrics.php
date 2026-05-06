<?php
require_once 'functions.php';
require_login();

$error = null;
$success = null;

// Handle Metric Save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get latest to fill missing required height_cm
    $latestResponse = api_get('/body-metrics?page=1', auth: true);
    $latestData = $latestResponse['result']['data'][0] ?? null;
    $fallbackHeight = $latestData['height_cm'] ?? 0;

    $body = [
        'date' => $_POST['date'] ?? date('Y-m-d'),
        'weight_kg' => (float)($_POST['weight_kg'] ?? 0),
        'height_cm' => !empty($_POST['height_cm']) ? (float)$_POST['height_cm'] : (float)$fallbackHeight,
    ];
    
    if (!empty($_POST['body_fat_pct'])) $body['body_fat_pct'] = (float)$_POST['body_fat_pct'];
    if (!empty($_POST['muscle_mass_pct'])) $body['muscle_mass_pct'] = (float)$_POST['muscle_mass_pct'];
    
    $save = api_post('/body-metrics', $body, auth: true);
    
    if (!empty($save['result']) && $save['result'] !== false) {
        $success = 'Metrics updated successfully.';
        // Refresh data after save
        $response = api_get('/body-metrics?page=' . $page, auth: true);
        $rows = $response['result']['data'] ?? [];
        $latest = $rows[0] ?? null;
    } else {
        $error = api_message($save) ?: 'Failed to save metrics. Make sure all fields are correct.';
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/body-metrics?page=' . $page, auth: true);
$rows = $response['result']['data'] ?? [];

// Fetch latest metric from dashboard (more reliable)
$dashResponse = api_get('/dashboard', auth: true);
$metric = $dashResponse['result']['latest_metric'] ?? null;
$latest = $metric; // Alias for compatibility

// Developer Debug (Only if ?debug_api=1 is in URL)
if (isset($_GET['debug_api'])) {
    echo '<div class="fixed top-0 left-0 w-full z-[9999] bg-black text-green-500 p-4 text-[10px] font-mono border-b border-green-500/50 max-h-[300px] overflow-auto">';
    echo "<b>DEBUG MODE ACTIVE</b><br>";
    echo "USER ID: " . ($_SESSION['user']['id'] ?? 'N/A') . " | TOKEN: " . (empty($_SESSION['user']['token']) ? 'MISSING' : 'PRESENT') . "<br>";
    echo "DASH METRIC: " . ($metric ? 'FOUND' : 'NOT FOUND') . "<br>";
    echo "ROWS COUNT: " . count($rows) . "<br>";
    echo "RAW DASH: " . htmlspecialchars(json_encode($dashResponse)) . "<br>";
    echo '</div>';
}

wp_app_page_start('Metrics Vault');
?>

<div class="mb-10">
    <h2 class="font-headline text-6xl md:text-7xl font-black tracking-tighter uppercase mb-2">
        <span class="text-white">Metrics</span>
        <span class="text-primary italic">Vault</span>
    </h2>
    <div class="flex items-center gap-3">
        <div class="h-[2px] w-12 bg-primary"></div>
        <p class="text-on-surface-variant font-bold uppercase tracking-[0.2em] text-[10px]">Your body composition & vital statistics</p>
    </div>
</div>

<?php if ($error || $success): ?>
    <div class="mb-8 animate-in fade-in slide-in-from-top-4 duration-500">
        <?php if ($error) show_error($error); ?>
        <?php if ($success) show_success($success); ?>
    </div>
<?php endif; ?>

<?php if (($response['result'] ?? null) === false): ?>
    <div class="bg-error/10 border border-error/20 p-6 rounded-3xl text-error text-center mb-10">
        <span class="material-symbols-outlined text-4xl mb-2">cloud_off</span>
        <p class="font-bold">API Connection Failure</p>
        <p class="text-xs opacity-70"><?= api_message($response) ?: 'Check Laravel server and API_BASE constant.' ?></p>
    </div>
<?php elseif (empty($rows) && !$latest): ?>
    <div class="bg-primary/5 border border-primary/10 p-6 rounded-3xl text-center mb-10">
        <span class="material-symbols-outlined text-primary text-4xl mb-2">database_off</span>
        <p class="text-white font-bold">No Metrics Recorded Yet</p>
        <p class="text-zinc-500 text-xs">Use the button below to start tracking your progress.</p>
    </div>
<?php endif; ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
    <!-- Weight Node -->
    <div class="bg-surface-container rounded-3xl p-8 relative overflow-hidden group border border-outline-variant/10">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="font-headline text-zinc-400 text-xs uppercase font-black tracking-widest mb-1">Weight</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="font-display text-5xl font-black tracking-tighter text-white"><?= h($latest['weight_kg'] ?? '--') ?></span>
                        <span class="text-[10px] font-black text-primary uppercase tracking-widest">KG</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-surface-container-high flex items-center justify-center border border-white/5">
                    <span class="material-symbols-outlined text-primary text-2xl">scale</span>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-4 border-t border-white/5 mt-auto">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-500">Last Update:</span>
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white"><?= !empty($latest['date']) ? date('M j, Y', strtotime($latest['date'])) : 'Never' ?></span>
            </div>
        </div>
    </div>

    <!-- Body Fat Node -->
    <div class="bg-surface-container rounded-3xl p-8 relative overflow-hidden group border border-outline-variant/10">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="font-headline text-zinc-400 text-xs uppercase font-black tracking-widest mb-1">Body Fat</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="font-display text-5xl font-black tracking-tighter text-white"><?= h($latest['body_fat_pct'] ?? '--') ?></span>
                        <span class="text-[10px] font-black text-primary uppercase tracking-widest">%</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-surface-container-high flex items-center justify-center border border-white/5">
                    <span class="material-symbols-outlined text-primary text-2xl">analytics</span>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-4 border-t border-white/5 mt-auto">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-500">Composition Status:</span>
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white"><?= isset($latest['body_fat_pct']) ? ($latest['body_fat_pct'] < 15 ? 'LEAN' : 'OPTIMAL') : '--' ?></span>
            </div>
        </div>
    </div>

    <!-- Muscle Mass Node -->
    <div class="bg-surface-container rounded-3xl p-8 relative overflow-hidden group border border-outline-variant/10">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="font-headline text-zinc-400 text-xs uppercase font-black tracking-widest mb-1">Muscle Mass</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="font-display text-5xl font-black tracking-tighter text-white"><?= h($latest['muscle_mass_pct'] ?? '--') ?></span>
                        <span class="text-[10px] font-black text-primary uppercase tracking-widest">%</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-surface-container-high flex items-center justify-center border border-white/5">
                    <span class="material-symbols-outlined text-primary text-2xl">fitness_center</span>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-4 border-t border-white/5 mt-auto">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-500">Muscle Ratio:</span>
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white">Advanced</span>
            </div>
        </div>
    </div>

    <!-- Height Node -->
    <div class="bg-surface-container rounded-3xl p-8 relative overflow-hidden group border border-outline-variant/10">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="font-headline text-zinc-400 text-xs uppercase font-black tracking-widest mb-1">Height</h3>
                    <div class="flex items-baseline gap-2">
                        <span class="font-display text-5xl font-black tracking-tighter text-white"><?= h($latest['height_cm'] ?? '--') ?></span>
                        <span class="text-[10px] font-black text-primary uppercase tracking-widest">CM</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-surface-container-high flex items-center justify-center border border-white/5">
                    <span class="material-symbols-outlined text-primary text-2xl">height</span>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-4 border-t border-white/5 mt-auto">
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-zinc-500">BMI Index:</span>
                <span class="text-[9px] font-black uppercase tracking-[0.2em] text-white"><?= h($latest['bmi'] ?? '--') ?></span>
            </div>
        </div>
    </div>
</div>

<div class="flex justify-center mb-20">
    <button onclick="document.getElementById('update-modal').classList.remove('hidden'); document.getElementById('update-modal').classList.add('flex');" 
            class="bg-primary text-black font-headline font-black uppercase tracking-widest py-5 px-12 rounded-2xl hover:scale-[1.02] transition-transform shadow-2xl shadow-primary/20 flex items-center gap-3">
        Update Metrics
        <span class="material-symbols-outlined">sync</span>
    </button>
</div>

<!-- History Section -->
<section>
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-xl font-headline font-black uppercase tracking-tight italic">Metric History</h3>
        <div class="h-[1px] flex-1 bg-outline-variant/20 mx-6"></div>
    </div>
    
    <div class="space-y-4">
        <?php foreach ($rows as $m): ?>
            <div class="bg-surface-container-low rounded-2xl p-6 border border-outline-variant/10 flex flex-col md:flex-row md:items-center justify-between gap-6 group hover:border-primary/20 transition-colors">
                <div>
                    <p class="text-[10px] font-black text-primary uppercase tracking-[0.2em] mb-1"><?= date('M j, Y', strtotime($m['date'])) ?></p>
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col">
                            <span class="text-[9px] text-zinc-500 font-bold uppercase tracking-widest">Weight</span>
                            <span class="text-lg font-bold text-white"><?= h($m['weight_kg']) ?> kg</span>
                        </div>
                        <div class="w-[1px] h-8 bg-outline-variant/20"></div>
                        <div class="flex flex-col">
                            <span class="text-[9px] text-zinc-500 font-bold uppercase tracking-widest">Body Fat</span>
                            <span class="text-lg font-bold text-white"><?= h($m['body_fat_pct'] ?? '--') ?>%</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <span class="text-[9px] text-zinc-500 font-bold uppercase tracking-widest block">BMI</span>
                        <span class="text-sm font-black text-primary"><?= h($m['bmi'] ?? '--') ?></span>
                    </div>
                    <span class="material-symbols-outlined text-zinc-700 group-hover:text-primary transition-colors">chevron_right</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Update Modal -->
<div id="update-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 backdrop-blur-md p-4">
    <div class="bg-surface-container rounded-[2.5rem] max-w-lg w-full border border-outline-variant/20 shadow-2xl overflow-hidden p-8">
        <div class="flex items-center justify-between mb-8">
            <h3 class="text-2xl font-headline font-black uppercase tracking-tight">Log New Metrics</h3>
            <button onclick="document.getElementById('update-modal').classList.add('hidden'); document.getElementById('update-modal').classList.remove('flex');" class="text-zinc-500 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="text-[10px] text-zinc-500 font-black uppercase tracking-widest mb-2 block">Date</label>
                    <input type="date" name="date" value="<?= date('Y-m-d') ?>" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/10 px-5 py-4 text-sm focus:border-primary/50 outline-none">
                </div>
                <div>
                    <label class="text-[10px] text-zinc-500 font-black uppercase tracking-widest mb-2 block">Weight (kg)</label>
                    <input type="number" step="0.1" name="weight_kg" value="<?= $latest['weight_kg'] ?? '' ?>" required class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/10 px-5 py-4 text-sm focus:border-primary/50 outline-none">
                </div>
                <div>
                    <label class="text-[10px] text-zinc-500 font-black uppercase tracking-widest mb-2 block">Height (cm)</label>
                    <input type="number" step="0.1" name="height_cm" value="<?= $latest['height_cm'] ?? '' ?>" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/10 px-5 py-4 text-sm focus:border-primary/50 outline-none">
                </div>
                <div>
                    <label class="text-[10px] text-zinc-500 font-black uppercase tracking-widest mb-2 block">Body Fat (%)</label>
                    <input type="number" step="0.1" name="body_fat_pct" value="<?= $latest['body_fat_pct'] ?? '' ?>" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/10 px-5 py-4 text-sm focus:border-primary/50 outline-none">
                </div>
                <div>
                    <label class="text-[10px] text-zinc-500 font-black uppercase tracking-widest mb-2 block">Muscle Mass (%)</label>
                    <input type="number" step="0.1" name="muscle_mass_pct" value="<?= $latest['muscle_mass_pct'] ?? '' ?>" class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/10 px-5 py-4 text-sm focus:border-primary/50 outline-none">
                </div>
            </div>

            <button type="submit" class="w-full bg-primary text-black font-black py-5 rounded-2xl text-sm uppercase tracking-widest mt-6 hover:scale-[1.02] transition-transform">
                Save Statistics
            </button>
        </form>
    </div>
</div>

<?php
wp_app_page_end();


