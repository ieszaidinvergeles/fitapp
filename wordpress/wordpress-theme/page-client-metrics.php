<?php
require_once 'functions.php';
require_login();

$error = null;
$success = null;

$page = max(1, (int)($_GET['page'] ?? 1));

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
    } else {
        $error = api_message($save) ?: 'Failed to save metrics. Make sure all fields are correct.';
    }
}

$response = api_get('/body-metrics?page=' . $page, auth: true);
$resData = $response['result'] ?? [];
$rows = isset($resData['data']) ? $resData['data'] : (is_array($resData) ? $resData : []);
if (!is_array($rows)) $rows = [];

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
<section class="mt-20">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
        <div>
            <h3 class="font-headline font-black text-4xl uppercase tracking-tighter italic m-0">Metric <span class="text-primary">Timeline</span></h3>
            <p class="text-zinc-500 text-[10px] font-black uppercase tracking-[0.2em] mt-2">Historical data tracking your physical evolution</p>
        </div>
        
        <div class="flex gap-2">
            <div class="px-4 py-2 rounded-xl bg-surface-container border border-outline-variant/10 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                <span class="text-[9px] font-black uppercase tracking-widest text-zinc-400"><?= count($rows) ?> Total Records</span>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 gap-4">
        <?php foreach ($rows as $index => $m): 
            $prev = $rows[$index + 1] ?? null;
            $weightDiff = $prev ? $m['weight_kg'] - $prev['weight_kg'] : 0;
        ?>
            <article class="bg-surface-container rounded-[2rem] p-8 border border-outline-variant/10 flex flex-col lg:flex-row lg:items-center justify-between gap-8 group hover:border-primary/30 transition-all duration-500 shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-full bg-gradient-to-l from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 rounded-2xl bg-surface-container-high border border-outline-variant/20 flex flex-col items-center justify-center text-center">
                        <span class="text-[10px] font-black uppercase text-zinc-500 leading-none mb-1"><?= date('M', strtotime($m['date'])) ?></span>
                        <span class="text-2xl font-headline font-black text-white leading-none"><?= date('j', strtotime($m['date'])) ?></span>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-zinc-600 uppercase tracking-[0.3em] mb-1">Recorded At</p>
                        <p class="text-sm font-bold text-on-surface"><?= date('l, Y', strtotime($m['date'])) ?></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 flex-grow max-w-3xl gap-8">
                    <!-- Weight -->
                    <div>
                        <span class="block text-[9px] font-black uppercase text-zinc-500 tracking-widest mb-2">Weight</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-headline font-black text-white"><?= number_format((float)($m['weight_kg'] ?? 0), 1) ?></span>
                            <span class="text-xs font-bold text-zinc-600 uppercase">kg</span>
                            <?php if ($weightDiff != 0): ?>
                                <span class="text-[10px] font-black <?= $weightDiff > 0 ? 'text-error' : 'text-primary' ?> flex items-center">
                                    <span class="material-symbols-outlined text-sm"><?= $weightDiff > 0 ? 'trending_up' : 'trending_down' ?></span>
                                    <?= abs(number_format((float)$weightDiff, 1)) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Body Fat -->
                    <div>
                        <span class="block text-[9px] font-black uppercase text-zinc-500 tracking-widest mb-2">Body Fat</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-headline font-black text-white"><?= !empty($m['body_fat_pct']) ? number_format((float)$m['body_fat_pct'], 1) : '--' ?></span>
                            <span class="text-xs font-bold text-zinc-600 uppercase">%</span>
                        </div>
                    </div>

                    <!-- Muscle Mass -->
                    <div>
                        <span class="block text-[9px] font-black uppercase text-zinc-500 tracking-widest mb-2">Muscle</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-headline font-black text-white"><?= !empty($m['muscle_mass_pct']) ? number_format((float)$m['muscle_mass_pct'], 1) : '--' ?></span>
                            <span class="text-xs font-bold text-zinc-600 uppercase">%</span>
                        </div>
                    </div>

                    <!-- Height / BMI -->
                    <div>
                        <span class="block text-[9px] font-black uppercase text-zinc-500 tracking-widest mb-2">BMI Index</span>
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-headline font-black text-primary"><?= !empty($m['bmi']) ? number_format((float)$m['bmi'], 1) : '--' ?></span>
                        </div>
                    </div>
                </div>

                <div class="flex lg:flex-col items-center justify-end gap-3">
                    <button class="w-12 h-12 rounded-full border border-outline-variant/20 flex items-center justify-center text-zinc-500 hover:bg-primary hover:text-black transition-all">
                        <span class="material-symbols-outlined text-xl">edit_note</span>
                    </button>
                </div>
            </article>
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


