<?php
require_once 'functions.php';
require_login();
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    if ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $consumed = (int)($_POST['is_consumed'] ?? 0);
        $res = api_put('/meal-schedule/' . $id, ['is_consumed' => !$consumed], auth: true);
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $res = api_delete('/meal-schedule/' . $id, auth: true);
    } else {
        $body = [
            'date' => $_POST['date'] ?? '',
            'meal_type' => $_POST['meal_type'] ?? '',
            'is_consumed' => false
        ];
        if (!empty($_POST['recipe_id'])) $body['recipe_id'] = (int)$_POST['recipe_id'];
        $res = api_post('/meal-schedule', $body, auth: true);
    }
    if (!empty($res['result']) && $res['result'] !== false) {
        $success = api_message($res) ?? 'Action successful.';
    } else {
        $error = api_message($res) ?? 'Action failed.';
    }
}

$selectedDate = $_GET['date'] ?? date('Y-m-d');
$includePast = isset($_GET['include_past']) && $_GET['include_past'] === '1';
$hideConsumed = isset($_GET['hide_consumed']) && $_GET['hide_consumed'] === '1';

// Fetch all recent meals (high per_page) and filter by date in PHP to ensure compatibility
$endpoint = "/meal-schedule?per_page=100";
if ($includePast) $endpoint .= '&include_past=1';
if ($hideConsumed) $endpoint .= '&hide_consumed=1';

$response = api_get($endpoint, auth: true);
$resData = $response['result'] ?? [];
$allRows = isset($resData['data']) ? $resData['data'] : (is_array($resData) ? $resData : []);

// Filter by the selected date in PHP
$rows = array_filter($allRows, function($m) use ($selectedDate) {
    if (empty($m['date'])) return false;
    return date('Y-m-d', strtotime($m['date'])) === $selectedDate;
});

$recipesRes = api_get('/recipes', auth: true);
$recData = $recipesRes['result'] ?? [];
$recipes = isset($recData['data']) ? $recData['data'] : (is_array($recData) ? $recData : []);

wp_app_page_start('Meal Schedule');
?>
    <?php
    // Calculate Daily Stats from current rows (filtered by date if the API does it)
    $totalCals = 0;
    $totalProt = 0;
    $totalCarbs = 0;
    $totalFats = 0;
    
    foreach ($rows as $m) {
        $r = $m['recipe'] ?? null;
        if (isset($r['data'])) $r = $r['data'];
        if ($r) {
            $macros = $r['macros_json'] ?? $r['macros'] ?? $r;
            if (is_string($macros)) {
                $decoded = json_decode($macros, true);
                if (is_array($decoded)) $macros = $decoded;
            }
            $totalCals += (int)($r['calories'] ?? 0);
            $totalProt += (int)($macros['protein'] ?? 0);
            $totalCarbs += (int)($macros['carbs'] ?? 0);
            $totalFats += (int)($macros['fat'] ?? 0);
        }
    }
    
    // Targets (Static or derived from somewhere else, using placeholders for now)
    $targetCals = 2500;
    $targetProt = 180;
    $targetCarbs = 250;
    $targetFats = 70;
    
    $calPct = min(100, ($totalCals / $targetCals) * 100);
    $protPct = min(100, ($totalProt / $targetProt) * 100);
    $carbPct = min(100, ($totalCarbs / $targetCarbs) * 100);
    $fatPct = min(100, ($totalFats / $targetFats) * 100);
    ?>

    <!-- Page Title & Date Selector -->
    <section class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-12">
        <div>
        </div>

        <!-- Horizontal Date Scroller -->
        <div class="flex gap-4 overflow-x-auto py-8 px-4 -mx-4 no-scrollbar relative z-10">
            <?php 
            for ($i = -3; $i <= 3; $i++): 
                $d = date('Y-m-d', strtotime("$i days"));
                $isCurrent = ($d === $selectedDate);
                $dayName = strtoupper(date('D', strtotime($d)));
                $dayNum = date('j', strtotime($d));
            ?>
                <a href="?pagename=client-meal-schedule&date=<?= $d ?>" 
                   class="flex flex-col items-center justify-center min-w-[70px] py-4 px-2 rounded-2xl transition-all duration-500
                          <?= $isCurrent ? 'bg-primary-container text-black shadow-[0_0_30px_rgba(212,251,0,0.5)] scale-110' : 'bg-surface-container-high text-on-surface-variant hover:bg-surface-container-highest hover:translate-y-[-4px]' ?>">
                    <span class="text-[10px] font-black uppercase mb-1 tracking-widest"><?= $dayName ?></span>
                    <span class="text-xl font-black font-headline"><?= $dayNum ?></span>
                </a>
            <?php endfor; ?>
        </div>
    </section>

    <!-- Main Bento Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Daily Overview Column -->
        <aside class="lg:col-span-4 space-y-8">
            <div class="bg-surface-container rounded-[2rem] p-8 border border-outline-variant/10 relative group shadow-2xl">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary/5 rounded-full blur-3xl -mr-10 -mt-10"></div>
                <h3 class="font-headline font-black text-xl mb-10 text-on-surface flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary-container text-2xl">bolt</span>
                    DAILY TARGETS
                </h3>

                <!-- Calorie Ring -->
                <div class="relative w-56 h-56 mx-auto mb-10 flex items-center justify-center">
                    <svg class="w-full h-full transform -rotate-90 overflow-visible" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="8"></circle>
                        <circle cx="50" cy="50" r="42" fill="none" stroke="currentColor" stroke-width="8" 
                                stroke-dasharray="263.9" stroke-dashoffset="<?= 263.9 * (1 - $calPct/100) ?>" 
                                class="text-primary-container drop-shadow-[0_0_12px_rgba(212,251,0,0.6)] transition-all duration-1000" stroke-linecap="round"></circle>
                    </svg>
                    <div class="absolute flex flex-col items-center justify-center text-center">
                        <span class="font-headline font-black text-4xl text-on-surface leading-none tracking-tighter"><?= number_format($totalCals) ?></span>
                        <span class="font-label text-[10px] text-zinc-500 uppercase tracking-widest mt-2">/ <?= number_format($targetCals) ?> kcal</span>
                    </div>
                </div>

                <!-- Macro Breakdown -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-surface-container-highest/30 rounded-2xl p-4 border border-outline-variant/5">
                        <span class="block font-label text-[9px] font-black uppercase text-zinc-500 mb-2">Protein</span>
                        <span class="block font-headline font-black text-lg text-primary-container"><?= $totalProt ?>g <span class="text-[10px] font-medium text-zinc-600">/ <?= $targetProt ?>g</span></span>
                        <div class="w-full bg-black/40 h-1 rounded-full mt-3 overflow-hidden">
                            <div class="bg-primary-container h-full transition-all duration-1000" style="width: <?= $protPct ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-surface-container-highest/30 rounded-2xl p-4 border border-outline-variant/5">
                        <span class="block font-label text-[9px] font-black uppercase text-zinc-500 mb-2">Carbs</span>
                        <span class="block font-headline font-black text-lg text-primary-container"><?= $totalCarbs ?>g <span class="text-[10px] font-medium text-zinc-600">/ <?= $targetCarbs ?>g</span></span>
                        <div class="w-full bg-black/40 h-1 rounded-full mt-3 overflow-hidden">
                            <div class="bg-primary-container h-full transition-all duration-1000" style="width: <?= $carbPct ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-surface-container-highest/30 rounded-2xl p-4 border border-outline-variant/5">
                        <span class="block font-label text-[9px] font-black uppercase text-zinc-500 mb-2">Fats</span>
                        <span class="block font-headline font-black text-lg text-primary-container"><?= $totalFats ?>g <span class="text-[10px] font-medium text-zinc-600">/ <?= $targetFats ?>g</span></span>
                        <div class="w-full bg-black/40 h-1 rounded-full mt-3 overflow-hidden">
                            <div class="bg-primary-container h-full transition-all duration-1000" style="width: <?= $fatPct ?>%"></div>
                        </div>
                    </div>
                    <div class="bg-surface-container-highest/30 rounded-2xl p-4 border border-outline-variant/5 flex items-center justify-center flex-col text-center">
                        <span class="material-symbols-outlined text-primary-container/40 text-3xl">nutrition</span>
                        <span class="text-[9px] font-black uppercase text-zinc-600 mt-1">Fuel Status</span>
                    </div>
                </div>
            </div>

            <!-- Add Meal Quick Action -->
            <button onclick="openAddMealModal()" 
                    class="w-full py-5 rounded-[2rem] bg-surface-container-high border border-primary-container/20 text-on-surface font-black uppercase tracking-widest text-xs flex items-center justify-center gap-3 hover:bg-primary-container hover:text-black transition-all group">
                <span class="material-symbols-outlined text-lg group-hover:rotate-90 transition-transform">add</span>
                Log New Meal
            </button>
        </aside>

        <!-- Meal List Column -->
        <main class="lg:col-span-8 space-y-6">
            <?php if (empty($rows)): ?>
                <div class="bg-surface-container/50 rounded-[2rem] p-20 text-center border-2 border-dashed border-outline-variant/10">
                    <span class="material-symbols-outlined text-7xl text-zinc-800 mb-6">restaurant_menu</span>
                    <h3 class="text-2xl font-headline font-black uppercase tracking-tight text-zinc-600">No Meals Logged</h3>
                    <p class="text-zinc-500 mt-2 max-w-xs mx-auto">You haven't scheduled any meals for this day yet. Start fueling your ambition.</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($rows as $m): 
                        $recipe = $m['recipe'] ?? null;
                        if (isset($recipe['data'])) $recipe = $recipe['data'];
                        $isConsumed = !empty($m['is_consumed']);
                        $mealTypeKey = strtolower($m['meal_type'] ?? '');
                        $mealTypeLabel = ucfirst(str_replace('_', ' ', $mealTypeKey));
                        $time = !empty($m['created_at']) ? date('h:i A', strtotime($m['created_at'])) : '--:--';

                        // Robust image selection: Database first, then type-specific default, then generic fallback
                        $typeDefaults = [
                            'breakfast' => 'https://images.unsplash.com/photo-1484723091739-30a097e8f929?w=400&h=400&fit=crop',
                            'lunch' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=400&fit=crop',
                            'dinner' => 'https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=400&h=400&fit=crop',
                            'snack' => 'https://images.unsplash.com/photo-1511690656952-34342bb7c2f2?w=400&h=400&fit=crop',
                            'pre_workout' => 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?w=400&h=400&fit=crop',
                            'post_workout' => 'https://images.unsplash.com/photo-1593073196024-573566361371?w=400&h=400&fit=crop',
                        ];
                        $mealPhoto = !empty($recipe['image_url']) ? $recipe['image_url'] : (!empty($m['image_url']) ? $m['image_url'] : ($typeDefaults[$mealTypeKey] ?? 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&h=400&fit=crop'));
                    ?>
                        <article class="bg-surface-container rounded-[2rem] overflow-hidden flex flex-col md:flex-row border border-outline-variant/10 hover:border-primary-container/30 transition-all duration-500 group shadow-lg">
                            <div class="w-full md:w-48 h-48 md:h-auto relative overflow-hidden">
                                <img src="<?= $mealPhoto ?>" 
                                     alt="<?= h($recipe['name'] ?? $mealTypeLabel) ?>" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                <div class="absolute bottom-4 left-4">
                                    <span class="text-[9px] font-black uppercase tracking-widest text-primary-container bg-black/40 px-2 py-1 rounded backdrop-blur-sm"><?= $mealTypeLabel ?></span>
                                </div>
                            </div>
                            
                            <div class="p-8 flex-grow flex flex-col justify-between">
                                <div>
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="text-2xl font-headline font-black uppercase tracking-tight text-on-surface"><?= $recipe ? h($recipe['name']) : 'Custom Meal' ?></h4>
                                        <div class="flex items-center gap-2">
                                            <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest"><?= $time ?></span>
                                            <?php if ($isConsumed): ?>
                                                <span class="material-symbols-outlined text-primary-container text-xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <p class="text-xs text-zinc-500 line-clamp-2 max-w-md mb-6 leading-relaxed"><?= $recipe ? h($recipe['description']) : 'Personalized intake for high-performance maintenance.' ?></p>
                                </div>

                                <div class="flex items-center justify-between pt-6 border-t border-outline-variant/5">
                                    <div class="flex gap-6">
                                        <?php 
                                        $mMacros = $recipe['macros_json'] ?? $recipe['macros'] ?? $recipe; 
                                        if (is_string($mMacros)) {
                                            $decoded = json_decode($mMacros, true);
                                            if (is_array($decoded)) $mMacros = $decoded;
                                        }
                                        ?>
                                        <div class="text-center">
                                            <span class="block text-[9px] font-black uppercase text-zinc-600 tracking-widest mb-1">Cals</span>
                                            <span class="block font-headline font-black text-lg text-primary-container"><?= $recipe ? (int)$recipe['calories'] : '--' ?></span>
                                        </div>
                                        <div class="text-center">
                                            <span class="block text-[9px] font-black uppercase text-zinc-600 tracking-widest mb-1">Prot</span>
                                            <span class="block font-headline font-black text-lg text-on-surface"><?= $recipe ? (int)($mMacros['protein'] ?? 0) : '--' ?>g</span>
                                        </div>
                                        <div class="text-center">
                                            <span class="block text-[9px] font-black uppercase text-zinc-600 tracking-widest mb-1">Carbs</span>
                                            <span class="block font-headline font-black text-lg text-on-surface"><?= $recipe ? (int)($mMacros['carbs'] ?? 0) : '--' ?>g</span>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="toggle"/>
                                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>"/>
                                            <input type="hidden" name="is_consumed" value="<?= (int)$m['is_consumed'] ?>"/>
                                            <button class="w-10 h-10 rounded-full border border-outline-variant/20 flex items-center justify-center text-zinc-400 hover:bg-primary-container hover:text-black transition-all" title="<?= $isConsumed ? 'Undo' : 'Mark Eaten' ?>">
                                                <span class="material-symbols-outlined text-xl"><?= $isConsumed ? 'undo' : 'check' ?></span>
                                            </button>
                                        </form>
                                        <form method="POST" onsubmit="event.preventDefault(); showConfirmModal(this);">
                                            <input type="hidden" name="action" value="delete"/>
                                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>"/>
                                            <button class="w-10 h-10 rounded-full border border-outline-variant/20 flex items-center justify-center text-zinc-400 hover:text-error transition-all">
                                                <span class="material-symbols-outlined text-xl">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Add Meal Modal -->
    <div id="add-meal-modal" class="fixed inset-0 z-[110] hidden items-center justify-center bg-black/90 backdrop-blur-md p-4 transition-all duration-300">
        <div id="add-meal-box" class="bg-surface-container rounded-[2.5rem] max-w-2xl w-full border border-outline-variant/20 shadow-2xl overflow-hidden scale-95 opacity-0 transition-all duration-300">
            <div class="p-8 pb-4 flex items-center justify-between">
                <h3 class="font-headline font-black text-3xl uppercase tracking-tighter italic">Log New <span class="text-primary-container">Fuel</span></h3>
                <button onclick="closeAddMealModal()" class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-zinc-400 hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form method="POST" class="p-8 space-y-8">
                <input type="hidden" name="action" value="create"/>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 ml-4">Deployment Date</label>
                        <input type="date" name="date" value="<?= $selectedDate ?>" required
                               class="w-full bg-surface-container-highest/50 rounded-2xl border border-outline-variant/10 px-6 py-4 text-sm font-bold text-on-surface focus:border-primary-container transition-all"/>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 ml-4">Meal Category</label>
                        <select name="meal_type" required
                                class="w-full bg-surface-container-highest/50 rounded-2xl border border-outline-variant/10 px-6 py-4 text-sm font-bold text-on-surface focus:border-primary-container appearance-none transition-all">
                            <option value="breakfast">Breakfast</option>
                            <option value="lunch">Lunch</option>
                            <option value="dinner">Dinner</option>
                            <option value="snack">Snack</option>
                            <option value="pre_workout">Pre-Workout</option>
                            <option value="post_workout">Post-Workout</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 ml-4">Tactical Recipe</label>
                    <select name="recipe_id"
                            class="w-full bg-surface-container-highest/50 rounded-2xl border border-outline-variant/10 px-6 py-4 text-sm font-bold text-on-surface focus:border-primary-container appearance-none transition-all">
                        <option value="">Manual Entry (Custom)</option>
                        <?php foreach ($recipes as $r): ?>
                            <option value="<?= (int)($r['id'] ?? 0) ?>">
                                <?= h($r['name'] ?? '') ?> (<?= (int)($r['calories'] ?? 0) ?> kcal)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="w-full py-5 rounded-2xl bg-primary-container text-on-primary-container font-black uppercase tracking-widest text-xs shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-95 transition-all flex items-center justify-center gap-3">
                    <span class="material-symbols-outlined">analytics</span>
                    Commit Fuel to Schedule
                </button>
            </form>
        </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-surface-container rounded-3xl p-8 max-w-sm w-full border border-outline-variant/30 shadow-2xl scale-95 transition-all duration-300 opacity-0" id="modal-box">
            <div class="w-16 h-16 bg-error/10 text-error rounded-full flex items-center justify-center mb-6 mx-auto">
                <span class="material-symbols-outlined text-3xl">delete_forever</span>
            </div>
            <h3 class="text-xl font-black uppercase tracking-tight text-center mb-2">Remove Meal?</h3>
            <p class="text-zinc-400 text-sm text-center mb-8">Are you sure you want to remove this meal from your schedule? This action cannot be undone.</p>
            <div class="flex gap-3">
                <button id="modal-cancel-btn" class="flex-1 px-6 py-3 rounded-full bg-surface-container-high text-xs font-black uppercase tracking-wider hover:bg-surface-container-highest transition-all">No, Cancel</button>
                <button id="modal-confirm-btn" class="flex-1 px-6 py-3 rounded-full bg-error text-on-error text-xs font-black uppercase tracking-wider hover:scale-105 transition-all shadow-lg shadow-error/20">Yes, Remove</button>
            </div>
        </div>
    </div>

    <script>
        let pendingForm = null;
        const modal = document.getElementById('confirm-modal');
        const modalBox = document.getElementById('modal-box');

        const addMealModal = document.getElementById('add-meal-modal');
        const addMealBox = document.getElementById('add-meal-box');

        function openAddMealModal() {
            addMealModal.classList.remove('hidden');
            addMealModal.classList.add('flex');
            setTimeout(() => {
                addMealBox.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }

        function closeAddMealModal() {
            addMealBox.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                addMealModal.classList.add('hidden');
                addMealModal.classList.remove('flex');
            }, 300);
        }

        function showConfirmModal(form) {
            pendingForm = form;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modalBox.classList.remove('scale-95', 'opacity-0');
            }, 10);
        }

        function hideModal() {
            modalBox.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                pendingForm = null;
            }, 300);
        }

        document.getElementById('modal-cancel-btn').addEventListener('click', hideModal);
        document.getElementById('modal-confirm-btn').addEventListener('click', () => {
            if (pendingForm) pendingForm.submit();
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) hideModal();
        });

        addMealModal.addEventListener('click', (e) => {
            if (e.target === addMealModal) closeAddMealModal();
        });
    </script>

<?php
$GLOBALS['active'] = 'meals';
wp_app_page_end(false);
?>
