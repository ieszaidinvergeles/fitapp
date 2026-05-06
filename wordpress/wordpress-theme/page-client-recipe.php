<?php
require_once 'functions.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$error = null;

$response = $id > 0 ? api_get('/recipes/' . $id, auth: true) : ['result' => false, 'message' => ['general' => 'Recipe not found.']];
$resData = $response['result'] ?? [];
$recipe = $resData['data'] ?? (isset($resData['id']) ? $resData : null);

wp_app_page_start('Recipe Details');
?>
    <div class="mb-8 flex items-center gap-4">
        <a href="?pagename=client-recipes" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary-container transition-all border border-outline-variant/20 hover:border-primary-container/30 active:scale-90">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
    </div>

    <?php if (($response['result'] ?? null) === false || !$recipe): ?>
        <div class="text-center py-20 bg-surface-container/30 rounded-3xl border border-outline-variant/30 max-w-2xl mx-auto">
            <span class="material-symbols-outlined text-6xl text-zinc-800 mb-4">restaurant</span>
            <p class="text-zinc-500 font-medium italic uppercase tracking-widest text-sm"><?= h(api_message($response) ?? 'Recipe not found.') ?></p>
            <a href="?pagename=client-recipes" class="inline-block mt-8 px-10 py-4 bg-primary-container text-on-primary-container rounded-full text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-transform">Back to Catalog</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-10">
            <!-- Left: Hero Card -->
            <div class="xl:col-span-1">
                <div class="bg-surface-container rounded-[2.5rem] border border-outline-variant/10 overflow-hidden sticky top-28 shadow-2xl shadow-black/50">
                    <?php if (!empty($recipe['image_url'])): ?>
                        <div class="h-80 w-full relative">
                            <img src="<?= esc_url($recipe['image_url']) ?>" class="w-full h-full object-cover opacity-70">
                            <div class="absolute inset-0 bg-gradient-to-t from-surface-container via-transparent to-transparent"></div>
                        </div>
                    <?php else: ?>
                        <div class="h-64 w-full bg-zinc-900 flex items-center justify-center opacity-30">
                            <span class="material-symbols-outlined text-9xl">restaurant</span>
                        </div>
                    <?php endif; ?>

                    <div class="p-10 -mt-20 relative z-10 text-center">
                        <span class="inline-block px-4 py-2 bg-primary-container/20 text-primary-container rounded-xl text-[10px] font-black uppercase tracking-widest border border-primary-container/30 mb-4">
                            <?= h(str_replace('_', ' ', $recipe['type'] ?? 'General')) ?>
                        </span>
                        
                        <div class="flex items-center justify-center gap-4 mb-6">
                            <h2 class="font-headline text-5xl font-black uppercase tracking-tighter leading-[0.9] italic text-white"><?= h($recipe['name'] ?? 'Recipe') ?></h2>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="p-4 rounded-3xl bg-surface-container-high border border-outline-variant/10">
                                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 block mb-1">Calories</span>
                                <span class="text-2xl font-black text-primary-container italic"><?= (int)($recipe['calories'] ?? 0) ?></span>
                            </div>
                            <div class="p-4 rounded-3xl bg-surface-container-high border border-outline-variant/10">
                                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 block mb-1">Time</span>
                                <span class="text-2xl font-black text-white italic">25m</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 py-6 border-t border-outline-variant/10">
                            <div>
                                <p class="text-[8px] font-black text-zinc-500 uppercase tracking-widest">PRO</p>
                                <p class="text-lg font-black text-primary-container"><?= (int)($recipe['macros']['protein'] ?? 0) ?>g</p>
                            </div>
                            <div class="border-x border-outline-variant/10">
                                <p class="text-[8px] font-black text-zinc-500 uppercase tracking-widest">CHO</p>
                                <p class="text-lg font-black text-white"><?= (int)($recipe['macros']['carbs'] ?? 0) ?>g</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black text-zinc-500 uppercase tracking-widest">FAT</p>
                                <p class="text-lg font-black text-white"><?= (int)($recipe['macros']['fat'] ?? 0) ?>g</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Content -->
            <div class="xl:col-span-2 space-y-12">
                <!-- Description -->
                <section>
                    <h3 class="font-headline text-xl font-black uppercase tracking-widest text-zinc-500 flex items-center gap-4 mb-6">
                        The Protocol
                        <span class="h-[1px] flex-1 bg-zinc-800/50"></span>
                    </h3>
                    <p class="text-on-surface-variant text-lg leading-relaxed font-medium italic">
                        <?= h($recipe['description'] ?? 'No description available for this recipe.') ?>
                    </p>
                </section>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <!-- Ingredients -->
                    <section>
                        <h3 class="font-headline text-xl font-black uppercase tracking-widest text-zinc-500 flex items-center gap-4 mb-8">
                            Components
                            <span class="h-[1px] flex-1 bg-zinc-800/50"></span>
                        </h3>
                        <div class="bg-surface-container rounded-3xl p-8 border border-outline-variant/10">
                            <div class="prose prose-invert prose-sm max-w-none text-zinc-400 font-medium">
                                <?= nl2br(h($recipe['ingredients'] ?? 'Ingredients list currently being optimized by our nutritionists.')) ?>
                            </div>
                        </div>
                    </section>

                    <!-- Preparation -->
                    <section>
                        <h3 class="font-headline text-xl font-black uppercase tracking-widest text-zinc-500 flex items-center gap-4 mb-8">
                            Execution
                            <span class="h-[1px] flex-1 bg-zinc-800/50"></span>
                        </h3>
                        <div class="bg-primary-container/5 rounded-3xl p-8 border border-primary-container/10">
                            <div class="prose prose-invert prose-sm max-w-none text-on-surface-variant font-medium leading-loose">
                                <?= nl2br(h($recipe['preparation_steps'] ?? 'Execution steps under technical review.')) ?>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Add to Schedule -->
                <section class="bg-surface-container rounded-[2.5rem] border border-outline-variant/10 p-10 flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="flex items-center gap-6">
                        <div class="w-16 h-16 rounded-3xl bg-zinc-900 border border-primary-container/30 flex items-center justify-center text-primary-container">
                            <span class="material-symbols-outlined text-3xl">calendar_add_on</span>
                        </div>
                        <div>
                            <h4 class="font-headline text-2xl font-black uppercase italic text-white">Plan your fueling</h4>
                            <p class="text-zinc-500 text-xs font-bold tracking-widest uppercase mt-1">Add this recipe to your weekly schedule</p>
                        </div>
                    </div>
                    <a href="?pagename=client-meal-schedule" class="px-10 py-5 kinetic-gradient text-on-primary-container rounded-3xl text-[10px] font-black uppercase tracking-[0.2em] shadow-xl shadow-primary-container/20 hover:scale-[1.02] active:scale-95 transition-all">
                        Go to Schedule
                    </a>
                </section>
            </div>
        </div>
    <?php endif; ?>

<?php
$GLOBALS['active'] = 'recipes';
wp_app_page_end(false);
?>
