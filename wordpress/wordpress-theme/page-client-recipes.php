<?php
require_once 'functions.php';
require_login();

$selectedType = $_GET['type'] ?? 'all';
$page = max(1, (int)($_GET['paged_recipes'] ?? 1));

// Fetch recipes with optional type filter
$endpoint = '/recipes?page=' . $page . '&per_page=6';
if ($selectedType !== 'all') {
    $endpoint .= '&type=' . $selectedType;
}

$response = api_get($endpoint, auth: true);
$result = $response['result'] ?? [];
$items = isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);

wp_app_page_start('Elite Nutrition');
?>
    <!-- Hero Section & Filters -->
    <section class="mb-12">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-10">
            <div>
                <p class="font-body text-on-surface-variant text-lg mt-4 max-w-lg font-medium">Fuel your performance with precision-engineered meals designed for athletes.</p>
            </div>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3 pt-2">
            <?php 
            $filters = [
                ['id' => 'all', 'label' => 'All', 'icon' => 'grid_view'],
                ['id' => 'breakfast', 'label' => 'Breakfast', 'icon' => 'coffee'],
                ['id' => 'lunch', 'label' => 'Lunch', 'icon' => 'restaurant'],
                ['id' => 'dinner', 'label' => 'Dinner', 'icon' => 'dinner_dining'],
                ['id' => 'snack', 'label' => 'Snack', 'icon' => 'cookie'],
                ['id' => 'pre_workout', 'label' => 'Pre/Post', 'icon' => 'bolt'],
            ];
            foreach ($filters as $f): 
                $isActive = ($selectedType === $f['id']);
                $baseClass = "flex flex-col items-center justify-center py-4 px-2 rounded-2xl border transition-all active:scale-95 group ";
                $activeClass = "bg-primary-container text-black border-primary-container shadow-[0_0_20px_rgba(212,251,0,0.3)]";
                $inactiveClass = "bg-surface-container-highest/30 backdrop-blur-md text-on-surface border-outline-variant/20 hover:bg-surface-container-high hover:border-primary-container/30";
            ?>
                <a href="?pagename=client-recipes&type=<?= $f['id'] ?>" class="<?= $baseClass ?> <?= $isActive ? $activeClass : $inactiveClass ?>">
                    <span class="material-symbols-outlined text-2xl mb-1 <?= $isActive ? '' : 'text-primary-container' ?>" style="font-variation-settings: 'FILL' 1;"><?= $f['icon'] ?></span>
                    <span class="font-headline font-bold text-[10px] uppercase tracking-widest"><?= $f['label'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>

    <!-- Recipes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($items as $r): ?>
            <?php 
                $calories = (int)($r['calories'] ?? 0);
                $macros = $r['macros'] ?? [];
                $protein = (int)($macros['protein'] ?? 0);
                $carbs = (int)($macros['carbs'] ?? 0);
                $fat = (int)($macros['fat'] ?? 0);
                $imageUrl = !empty($r['image_url']) ? $r['image_url'] : null;
            ?>
            <article class="bg-surface-container rounded-3xl overflow-hidden group border border-outline-variant/10 hover:border-primary-container/20 transition-all flex flex-col shadow-xl shadow-black/20">
                <a href="?pagename=client-recipe&id=<?= (int)$r['id'] ?>" class="relative h-64 w-full shrink-0 block">
                    <?php if ($imageUrl): ?>
                        <img src="<?= esc_url($imageUrl) ?>" alt="<?= h($r['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 opacity-80">
                    <?php else: ?>
                        <div class="w-full h-full bg-zinc-900 flex items-center justify-center opacity-30">
                            <span class="material-symbols-outlined text-8xl">restaurant</span>
                        </div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-surface-dim via-transparent to-transparent opacity-60"></div>
                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1.5 bg-primary-container rounded-xl text-black font-black font-headline text-[10px] flex items-center gap-1.5 uppercase tracking-widest border border-primary-container/20 shadow-lg">
                            <span class="material-symbols-outlined text-xs">local_fire_department</span>
                            <?= $calories ?> KCAL
                        </span>
                    </div>
                </a>

                <div class="p-8 flex-1 flex flex-col">
                    <div class="mb-6">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-primary-container mb-2 block"><?= h(str_replace('_', ' ', $r['type'] ?? 'General')) ?></span>
                        <a href="?pagename=client-recipe&id=<?= (int)$r['id'] ?>" class="block group/title">
                            <h3 class="font-headline text-3xl font-black tracking-tighter text-white uppercase italic leading-none mb-4 group-hover/title:text-primary-container transition-colors"><?= h($r['name'] ?? 'Recipe') ?></h3>
                        </a>
                        <p class="font-body text-sm text-on-surface-variant font-medium line-clamp-2 leading-relaxed"><?= h($r['description'] ?? 'Optimal fueling strategy for peak performance.') ?></p>
                    </div>

                    <div class="mt-auto grid grid-cols-3 gap-3 pt-6 border-t border-outline-variant/10">
                        <div class="text-center">
                            <p class="text-[8px] font-black uppercase tracking-widest text-zinc-500 mb-1">PRO</p>
                            <p class="font-headline font-black text-xl text-primary-container"><?= $protein ?>g</p>
                        </div>
                        <div class="text-center border-x border-outline-variant/10">
                            <p class="text-[8px] font-black uppercase tracking-widest text-zinc-500 mb-1">CHO</p>
                            <p class="font-headline font-black text-xl text-white"><?= $carbs ?>g</p>
                        </div>
                        <div class="text-center">
                            <p class="text-[8px] font-black uppercase tracking-widest text-zinc-500 mb-1">FAT</p>
                            <p class="font-headline font-black text-xl text-white"><?= $fat ?>g</p>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (!$items): ?>
        <div class="text-center py-32 bg-surface-container/20 rounded-[3rem] border border-dashed border-outline-variant/30 max-w-2xl mx-auto mt-12">
            <span class="material-symbols-outlined text-7xl text-zinc-800 mb-6">restaurant</span>
            <p class="text-zinc-500 font-black uppercase tracking-[0.3em] text-sm">No recipes found for this category.</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php
    $meta = $result['meta'] ?? $response['meta'] ?? null;
    if ($meta && ($meta['last_page'] ?? 1) > 1):
        $currentPage = $meta['current_page'];
        $lastPage = $meta['last_page'];
        $baseQuery = '?pagename=client-recipes&type=' . urlencode($selectedType);
    ?>
    <div class="mt-20 flex items-center justify-center gap-4 pb-12">
        <?php if ($currentPage > 1): ?>
            <a href="<?= $baseQuery ?>&paged_recipes=<?= $currentPage - 1 ?>" class="w-14 h-14 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary-container transition-all border border-outline-variant/20 hover:border-primary-container/30">
                <span class="material-symbols-outlined">chevron_left</span>
            </a>
        <?php endif; ?>
        
        <div class="px-8 py-4 rounded-2xl bg-surface-container border border-outline-variant/20 text-[10px] font-black uppercase tracking-[0.3em] text-zinc-500 shadow-xl shadow-black/20">
            Page <?= $currentPage ?> / <?= $lastPage ?>
        </div>
        
        <?php if ($currentPage < $lastPage): ?>
            <a href="<?= $baseQuery ?>&paged_recipes=<?= $currentPage + 1 ?>" class="w-14 h-14 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary-container transition-all border border-outline-variant/20 hover:border-primary-container/30">
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

<?php
$GLOBALS['active'] = 'recipes';
wp_app_page_end(false);
?>
