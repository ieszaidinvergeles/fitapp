<?php
require_once 'functions.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$error = null;
$success = null;

$response = $id > 0 ? api_get('/diet-plans/' . $id, auth: true) : ['result' => false, 'message' => ['general' => 'Diet plan not found.']];
$resData = $response['result'] ?? [];
$plan = $resData['data'] ?? (isset($resData['id']) ? $resData : null);

// Fetch some recipes to show as "Included in this plan"
$recipesRes = api_get('/recipes?per_page=4', auth: true);
$recData = $recipesRes['result'] ?? [];
$recipes = isset($recData['data']) ? $recData['data'] : (is_array($recData) ? $recData : []);

wp_app_page_start('Diet Plan Details');
?>

    <div class="mb-8 flex items-center gap-4">
        <a href="?pagename=client-diet-plans" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary-container transition-all border border-outline-variant/20 hover:border-primary-container/30 active:scale-90">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h1 class="font-headline text-3xl font-black uppercase tracking-tight leading-none">Plan Overview</h1>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-primary-container mt-1">Nutritional Strategy</p>
        </div>
    </div>

    <?php show_error($error); show_success($success); ?>

    <?php if (($response['result'] ?? null) === false || !$plan): ?>
        <div class="text-center py-20 bg-surface-container/30 rounded-3xl border border-dashed border-outline-variant/30 max-w-2xl mx-auto">
            <span class="material-symbols-outlined text-6xl text-zinc-800 mb-4">no_meals</span>
            <p class="text-zinc-500 font-medium italic uppercase tracking-widest text-sm"><?= h(api_message($response) ?? 'Diet plan not found.') ?></p>
            <a href="?pagename=client-diet-plans" class="inline-block mt-8 px-10 py-4 bg-primary-container text-on-primary-container rounded-full text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-transform shadow-lg shadow-primary-container/20">Return to Catalog</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
            <!-- Left Column: Plan Info Card -->
            <div class="xl:col-span-1">
                <div class="bg-surface-container rounded-[2.5rem] border border-outline-variant/10 overflow-hidden sticky top-28 shadow-2xl shadow-black/50">
                    <?php if (!empty($plan['cover_image_url'])): ?>
                        <div class="h-64 w-full relative">
                            <img src="<?= esc_url($plan['cover_image_url']) ?>" class="w-full h-full object-cover opacity-60">
                            <div class="absolute inset-0 bg-gradient-to-t from-surface-container via-surface-container/20 to-transparent"></div>
                            
                            <div class="absolute top-6 right-6">
                                <span class="bg-primary-container/20 backdrop-blur-md text-primary-container px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border border-primary-container/30 shadow-xl">
                                    Active Plan
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="h-48 w-full bg-gradient-to-br from-zinc-800 to-zinc-900 flex items-center justify-center opacity-30">
                            <span class="material-symbols-outlined text-8xl">restaurant_menu</span>
                        </div>
                    <?php endif; ?>

                    <div class="p-10 -mt-16 relative z-10">
                        <h2 class="font-headline text-5xl font-black uppercase tracking-tighter leading-[0.9] mb-6 italic text-white"><?= h($plan['name'] ?? 'Plan') ?></h2>
                        <p class="text-on-surface-variant text-sm leading-relaxed mb-10 font-medium"><?= h($plan['goal_description'] ?? 'High-performance nutritional protocol optimized for metabolic health and systemic recovery.') ?></p>

                        <div class="space-y-4 mb-10">
                            <div class="p-6 rounded-3xl bg-surface-container-high border border-outline-variant/10 group hover:border-primary-container/30 transition-colors">
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 block mb-2">Primary Goal</span>
                                <span class="text-xl font-bold flex items-center gap-3 text-white uppercase italic tracking-tight">
                                    <span class="material-symbols-outlined text-primary-container" style="font-variation-settings: 'FILL' 1;">target</span>
                                    Fat Oxidation
                                </span>
                            </div>
                            <div class="p-6 rounded-3xl bg-surface-container-high border border-outline-variant/10 group hover:border-primary-container/30 transition-colors">
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500 block mb-2">Duration</span>
                                <span class="text-xl font-bold flex items-center gap-3 text-white uppercase italic tracking-tight">
                                    <span class="material-symbols-outlined text-primary-container" style="font-variation-settings: 'FILL' 1;">calendar_today</span>
                                    12 Weeks
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Content -->
            <div class="xl:col-span-2 space-y-12">
                <!-- Macros Summary -->
                <section>
                    <h3 class="font-headline text-xl font-black uppercase tracking-widest text-zinc-500 flex items-center gap-4 mb-8">
                        Daily Macro Targets
                        <span class="h-[1px] flex-1 bg-zinc-800/50"></span>
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php 
                        $stats = [
                            ['label' => 'Calories', 'value' => '2,400', 'unit' => 'kcal', 'color' => 'primary-container'],
                            ['label' => 'Protein', 'value' => '180', 'unit' => 'g', 'color' => 'white'],
                            ['label' => 'Carbs', 'value' => '220', 'unit' => 'g', 'color' => 'white'],
                            ['label' => 'Fats', 'value' => '65', 'unit' => 'g', 'color' => 'white'],
                        ];
                        foreach ($stats as $s): ?>
                            <div class="bg-surface-container rounded-3xl p-6 border border-outline-variant/10 text-center">
                                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-2"><?= $s['label'] ?></p>
                                <p class="text-3xl font-black italic tracking-tighter text-<?= $s['color'] ?>"><?= $s['value'] ?></p>
                                <p class="text-[10px] font-bold text-zinc-600 mt-1 uppercase"><?= $s['unit'] ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Recommended Recipes -->
                <section>
                    <div class="flex items-center justify-between mb-8">
                        <h3 class="font-headline text-xl font-black uppercase tracking-widest text-zinc-500 flex items-center gap-4 flex-1">
                            Recommended Recipes
                            <span class="h-[1px] flex-1 bg-zinc-800/50"></span>
                        </h3>
                        <a href="?pagename=client-recipes" class="text-[10px] font-black uppercase tracking-widest text-primary-container ml-4 hover:underline">View All</a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($recipes as $r): ?>
                            <article class="bg-surface-container rounded-3xl border border-outline-variant/10 overflow-hidden group hover:border-primary-container/20 transition-all flex h-32">
                                <div class="w-32 h-full overflow-hidden shrink-0">
                                    <?php if (!empty($r['image_url'])): ?>
                                        <img src="<?= esc_url($r['image_url']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500 opacity-60">
                                    <?php else: ?>
                                        <div class="w-full h-full bg-zinc-800 flex items-center justify-center opacity-20">
                                            <span class="material-symbols-outlined">restaurant</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-5 flex flex-col justify-center min-w-0">
                                    <span class="text-[8px] font-black uppercase tracking-widest text-primary-container mb-1"><?= h($r['type'] ?? 'Meal') ?></span>
                                    <h4 class="font-headline font-black uppercase italic tracking-tight text-white text-lg truncate mb-1"><?= h($r['name'] ?? 'Recipe') ?></h4>
                                    <div class="flex items-center gap-3 text-zinc-500 text-[10px] font-bold">
                                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">bolt</span> <?= (int)($r['calories'] ?? 0) ?> kcal</span>
                                    </div>
                                </div>
                                <div class="ml-auto flex items-center pr-6">
                                    <a href="?pagename=client-recipe&id=<?= (int)$r['id'] ?>" class="w-8 h-8 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-400 hover:text-primary-container hover:bg-zinc-700 transition-all">
                                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Guideline / Tips -->
                <section class="bg-primary-container/5 rounded-[2.5rem] border border-primary-container/10 p-10">
                    <div class="flex items-start gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-primary-container flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-on-primary-container font-black">lightbulb</span>
                        </div>
                        <div>
                            <h4 class="font-headline text-2xl font-black uppercase italic tracking-tight text-white mb-3">Professional Strategy</h4>
                            <p class="text-on-surface-variant text-sm leading-relaxed mb-6">This plan focuses on high-density nutrition to support your workouts while maintaining a lean metabolic profile. Ensure you stay hydrated and follow the suggested meal timings for optimal results.</p>
                            <ul class="space-y-3">
                                <li class="flex items-center gap-3 text-xs font-bold text-zinc-400">
                                    <span class="material-symbols-outlined text-primary-container text-sm">check_circle</span>
                                    Drink at least 3 liters of water daily.
                                </li>
                                <li class="flex items-center gap-3 text-xs font-bold text-zinc-400">
                                    <span class="material-symbols-outlined text-primary-container text-sm">check_circle</span>
                                    Prioritize whole, unprocessed protein sources.
                                </li>
                                <li class="flex items-center gap-3 text-xs font-bold text-zinc-400">
                                    <span class="material-symbols-outlined text-primary-container text-sm">check_circle</span>
                                    Consistent meal timing supports circadian rhythm.
                                </li>
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    <?php endif; ?>

<?php
$GLOBALS['active'] = 'diet-plans';
wp_app_page_end(false);
?>
