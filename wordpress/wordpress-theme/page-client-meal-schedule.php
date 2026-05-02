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

$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/meal-schedule?page=' . $page, auth: true);
$resData = $response['result'] ?? [];
$rows = isset($resData['data']) ? $resData['data'] : (is_array($resData) ? $resData : []);

$recipesRes = api_get('/recipes', auth: true);
$recData = $recipesRes['result'] ?? [];
$recipes = isset($recData['data']) ? $recData['data'] : (is_array($recData) ? $recData : []);

wp_app_page_start('Meal Schedule');
?>
    <?php show_error($error); show_success($success); ?>

    <!-- Add Meal Form -->
    <div class="bg-surface-container rounded-3xl p-8 border border-outline-variant/20 mb-10 shadow-xl overflow-hidden relative group">
        <div class="absolute top-0 right-0 p-8 opacity-5">
            <span class="material-symbols-outlined text-8xl">restaurant</span>
        </div>
        <h3 class="font-headline text-xl font-black uppercase tracking-tight mb-6 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-primary/20 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-sm">add</span>
            </span>
            Log New Meal
        </h3>
        
        <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="action" value="create"/>
            
            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-widest text-zinc-500 ml-2">Date</label>
                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required
                       class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/30 px-5 py-4 text-sm text-on-surface focus:border-primary transition-all"/>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-widest text-zinc-500 ml-2">Meal Type</label>
                <select name="meal_type" required
                        class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/30 px-5 py-4 text-sm text-on-surface focus:border-primary transition-all">
                    <option value="breakfast">Breakfast</option>
                    <option value="lunch">Lunch</option>
                    <option value="dinner">Dinner</option>
                    <option value="snack">Snack</option>
                    <option value="pre_workout">Pre workout</option>
                    <option value="post_workout">Post workout</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black uppercase tracking-widest text-zinc-500 ml-2">Recipe (<?= count($recipes) ?> found)</label>
                <select name="recipe_id"
                        class="w-full bg-surface-container-highest rounded-2xl border border-outline-variant/30 px-5 py-4 text-sm text-on-surface focus:border-primary transition-all">
                    <option value="">Custom / No Recipe</option>
                    <?php foreach ($recipes as $r): ?>
                        <option value="<?= (int)($r['id'] ?? 0) ?>">
                            <?= h($r['name'] ?? '') ?> (<?= (int)($r['calories'] ?? 0) ?> kcal)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full h-[54px] rounded-2xl kinetic-gradient text-on-primary-container font-black uppercase tracking-[0.1em] text-xs shadow-lg shadow-primary/10 hover:scale-[1.02] active:scale-95 transition-all">
                    Add to Schedule
                </button>
            </div>
        </form>
    </div>

    <!-- Scheduled Meals List -->
    <div class="grid grid-cols-1 gap-4">
        <?php foreach ($rows as $m): ?>
            <?php 
                $isConsumed = !empty($m['is_consumed']);
                $mealDate = !empty($m['date']) ? date('M j, Y', strtotime($m['date'])) : '-';
                $mealType = ucfirst(str_replace('_', ' ', $m['meal_type'] ?? ''));
                
                // Handle Laravel Resource wrapping (check for 'data' key)
                $recipe = $m['recipe'] ?? null;
                if (isset($recipe['data'])) {
                    $recipe = $recipe['data'];
                }

                // TEMP DEBUG
                if ($m === reset($rows)) {
                    echo "<pre style='font-size:10px; color: #a3e635; background: #1e2117; padding: 10px; border-radius: 10px; margin-bottom: 10px; overflow: auto;'>" . print_r($m, true) . "</pre>";
                }
            ?>
            <article class="bg-surface-container rounded-2xl border border-outline-variant/20 p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:bg-surface-container-high transition-all">
                <div class="flex gap-6 items-center">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center <?= $isConsumed ? 'bg-primary/20 text-primary' : 'bg-zinc-800 text-zinc-500' ?> border border-outline-variant/30">
                        <span class="material-symbols-outlined text-2xl"><?= $isConsumed ? 'check_circle' : 'schedule' ?></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h4 class="font-headline text-xl font-black uppercase tracking-tight group-hover:text-primary transition-colors">
                                <?= $recipe ? h($recipe['name']) : h($mealType) ?>
                            </h4>
                            <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-primary/10 text-primary border border-primary/20">
                                <?= $recipe ? h($mealType) : 'Custom' ?>
                            </span>
                            <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded bg-surface-container-highest text-zinc-400">
                                <?= h($mealDate) ?>
                            </span>
                        </div>
                        
                        <?php if ($recipe): ?>
                            <p class="text-xs text-zinc-500 mb-3 line-clamp-1 max-w-md">
                                <?= h($recipe['description'] ?? 'No description available.') ?>
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Cals: <span class="text-on-surface"><?= (int)$recipe['calories'] ?></span></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Prot: <span class="text-on-surface"><?= (int)($recipe['macros']['protein'] ?? 0) ?>g</span></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Carbs: <span class="text-on-surface"><?= (int)($recipe['macros']['carbs'] ?? 0) ?>g</span></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Fat: <span class="text-on-surface"><?= (int)($recipe['macros']['fat'] ?? 0) ?>g</span></span>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-zinc-500 italic">Custom entry - No nutritional data</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center gap-3 self-end md:self-center">
                    <form method="POST">
                        <input type="hidden" name="action" value="toggle"/>
                        <input type="hidden" name="id" value="<?= (int)($m['id'] ?? 0) ?>"/>
                        <input type="hidden" name="is_consumed" value="<?= (int)($m['is_consumed'] ?? 0) ?>"/>
                        <button class="px-6 py-2.5 rounded-full border border-outline-variant/30 text-[10px] font-black uppercase tracking-widest transition-all <?= $isConsumed ? 'bg-zinc-800 text-zinc-400 border-zinc-700' : 'bg-surface-container-highest text-on-surface hover:bg-zinc-700' ?>">
                            <?= $isConsumed ? 'Mark as Planned' : 'Mark as Eaten' ?>
                        </button>
                    </form>
                    <form method="POST" onsubmit="event.preventDefault(); showConfirmModal(this);">
                        <input type="hidden" name="action" value="delete"/>
                        <input type="hidden" name="id" value="<?= (int)($m['id'] ?? 0) ?>"/>
                        <button class="w-10 h-10 rounded-full flex items-center justify-center text-zinc-600 hover:text-error transition-colors">
                            <span class="material-symbols-outlined text-xl">delete</span>
                        </button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$rows): ?>
            <div class="text-center py-20 bg-surface-container/30 rounded-3xl border border-dashed border-outline-variant/30">
                <span class="material-symbols-outlined text-6xl text-zinc-800 mb-4">no_meals</span>
                <p class="text-zinc-500 font-medium">No meals scheduled yet. Start by adding one above!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php
    $meta = $resData['meta'] ?? $response['meta'] ?? null;
    if ($meta && ($meta['last_page'] ?? 1) > 1):
        $currentPage = $meta['current_page'];
        $lastPage = $meta['last_page'];
    ?>
    <div class="mt-12 flex items-center justify-center gap-4">
        <?php if ($currentPage > 1): ?>
            <a href="?pagename=client-meal-schedule&page=<?= $currentPage - 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
                <span class="material-symbols-outlined">chevron_left</span>
            </a>
        <?php endif; ?>
        
        <div class="px-6 py-3 rounded-2xl bg-surface-container border border-outline-variant/20 text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">
            Page <?= $currentPage ?> / <?= $lastPage ?>
        </div>
        
        <?php if ($currentPage < $lastPage): ?>
            <a href="?pagename=client-meal-schedule&page=<?= $currentPage + 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

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
    </script>

<?php
$GLOBALS['active'] = 'meals';
wp_app_page_end(false);
?>
