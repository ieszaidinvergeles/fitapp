<?php
require_once 'functions.php';
require_login();
$error = null;
$success = null;

// Handle Favorite Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['favorite_diet_plan_id'])) {
    $planId = (int)$_POST['favorite_diet_plan_id'];
    $fav = api_post('/diet-plans/' . $planId . '/favorite', [], auth: true);
    if (!empty($fav['result']) && $fav['result'] !== false) {
        $success = api_message($fav) ?: 'Favorites updated.';
    } else {
        $error = api_message($fav) ?: 'Could not update favorites.';
    }
}

$page = max(1, (int)($_GET['paged_diet_plans'] ?? 1));
$response = api_get('/diet-plans?page=' . $page, auth: true);
$result = $response['result'] ?? [];
$plans = isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);

wp_app_page_start('Diet Plans');
?>
    <!-- Hero Section -->
    <section class="mb-12 relative overflow-hidden">
        <p class="font-body text-on-surface-variant max-w-md text-lg leading-relaxed">High-performance nutrition plans designed for your success.</p>
    </section>

    <?php show_error($error); show_success($success); ?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($plans as $p): ?>
            <?php 
                $coverUrl = !empty($p['cover_image_url']) ? $p['cover_image_url'] : null;
                $isFavorite = !empty($p['is_favorite']);
            ?>
            <article class="group relative overflow-hidden rounded-xl bg-surface-container aspect-[4/5] border border-zinc-800 flex flex-col">
                <div class="absolute inset-0 z-0">
                    <?php if ($coverUrl): ?>
                        <img src="<?= esc_url($coverUrl) ?>" alt="<?= h($p['name']) ?>" class="w-full h-full object-cover opacity-50 group-hover:scale-105 transition-transform duration-1000">
                    <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-surface-container-highest to-surface-container flex items-center justify-center opacity-30">
                            <span class="material-symbols-outlined text-8xl">restaurant_menu</span>
                        </div>
                    <?php endif; ?>
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
                </div>

                <div class="relative z-10 p-6 h-full flex flex-col justify-end">
                    <div class="absolute top-4 right-4">
                        <form method="POST">
                            <input type="hidden" name="favorite_diet_plan_id" value="<?= (int)$p['id'] ?>"/>
                            <button type="submit" class="w-10 h-10 rounded-xl bg-black/40 backdrop-blur-md border border-white/10 text-primary-container flex items-center justify-center hover:scale-110 transition-transform active:scale-95 group/fav btn-favorite <?= $isFavorite ? 'is-favorite' : '' ?>">
                                <span class="material-symbols-outlined <?= $isFavorite ? 'fill-1' : '' ?> group-hover/fav:fill-1 transition-all">star</span>
                            </button>
                        </form>
                    </div>

                    <div class="flex gap-3 mb-3">
                        <span class="bg-primary-container/20 backdrop-blur-md text-primary-container px-3 py-1 rounded-md text-[10px] font-bold tracking-tighter uppercase border border-primary-container/30">Nutrition Plan</span>
                    </div>

                    <h3 class="font-headline text-3xl font-black text-white mb-2 uppercase italic tracking-tighter leading-tight"><?= h($p['name'] ?? 'Plan') ?></h3>
                    <p class="font-body text-zinc-300 mb-6 text-xs line-clamp-2"><?= h($p['goal_description'] ?? 'Targeted metabolic optimization.') ?></p>
                    
                    <div class="flex justify-between items-end">
                        <div class="flex-1 mr-4">
                            <p class="text-[10px] text-zinc-500 uppercase tracking-widest font-bold mb-1">Focus Area</p>
                            <p class="text-primary-container font-headline font-bold uppercase text-base truncate">Metabolic Health</p>
                        </div>
                        <a href="?pagename=client-diet-plan&id=<?= (int)$p['id'] ?>" class="bg-zinc-900 border border-primary-container/50 text-primary-container px-5 py-2.5 rounded-md text-[10px] font-bold uppercase tracking-widest hover:bg-primary-container hover:text-black transition-all">Details</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (!$plans): ?>
        <div class="text-center py-20 bg-surface-container/30 rounded-3xl border border-dashed border-outline-variant/30 max-w-2xl mx-auto mt-8">
            <span class="material-symbols-outlined text-6xl text-zinc-800 mb-4">no_meals</span>
            <p class="text-zinc-500 font-medium italic uppercase tracking-widest text-sm">No diet plans found.</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php
    $meta = $result['meta'] ?? $response['meta'] ?? null;
    if ($meta && ($meta['last_page'] ?? 1) > 1):
        $currentPage = $meta['current_page'];
        $lastPage = $meta['last_page'];
        
        $baseQuery = '?pagename=client-diet-plans';
    ?>
    <div class="mt-12 flex items-center justify-center gap-4">
        <?php if ($currentPage > 1): ?>
            <a href="<?= $baseQuery ?>&paged_diet_plans=<?= $currentPage - 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
                <span class="material-symbols-outlined">chevron_left</span>
            </a>
        <?php endif; ?>
        
        <div class="px-6 py-3 rounded-2xl bg-surface-container border border-outline-variant/20 text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">
            Page <?= $currentPage ?> / <?= $lastPage ?>
        </div>
        
        <?php if ($currentPage < $lastPage): ?>
            <a href="<?= $baseQuery ?>&paged_diet_plans=<?= $currentPage + 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

<?php
$GLOBALS['active'] = 'diet-plans';
wp_app_page_end(false);
?>
