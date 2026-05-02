<?php
require_once 'functions.php';
require_login();
$page = max(1, (int)($_GET['paged_diet_plans'] ?? 1));
$response = api_get('/diet-plans?page=' . $page, auth: true);
$result = $response['result'] ?? [];
$plans = isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);

wp_app_page_start('Diet Plans');
?>
    <p class="text-on-surface-variant text-sm font-medium mt-1">Explore structured nutrition plans designed for different goals.</p>
</div>

    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($plans as $p): ?>
            <article class="bg-surface-container rounded-3xl border border-outline-variant/10 overflow-hidden group hover:-translate-y-1 transition-all duration-300 hover:shadow-2xl hover:shadow-primary-container/5 relative flex flex-col">
                <?php if (!empty($p['cover_image_url'])): ?>
                    <div class="h-48 w-full overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-t from-surface-container to-transparent z-10"></div>
                        <img src="<?= esc_url($p['cover_image_url']) ?>" alt="<?= esc_attr($p['name'] ?? 'Diet Plan') ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-60 mix-blend-overlay">
                    </div>
                <?php else: ?>
                    <div class="h-48 w-full bg-gradient-to-br from-surface-container-highest to-surface-container relative flex items-center justify-center overflow-hidden">
                        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjIiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4wMykiLz48L3N2Zz4=')] opacity-50 mix-blend-overlay"></div>
                        <span class="material-symbols-outlined text-8xl text-zinc-800" style="font-variation-settings: 'FILL' 1;">restaurant_menu</span>
                    </div>
                <?php endif; ?>

                <div class="p-8 pt-6 flex-1 flex flex-col">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="px-2 py-0.5 rounded bg-primary-container/20 text-primary-container text-[10px] font-black uppercase tracking-widest">Nutrition</span>
                    </div>
                    <h2 class="font-headline text-3xl font-black uppercase tracking-tight mb-3"><?= h($p['name'] ?? 'Plan') ?></h2>
                    <p class="text-sm text-zinc-400 font-medium leading-relaxed mb-6 flex-1"><?= h($p['goal_description'] ?? 'No description available.') ?></p>
                    
                    <button class="w-full py-3 rounded-full border border-outline-variant/30 text-xs font-black uppercase tracking-wider hover:bg-surface-container-high transition-colors flex items-center justify-center gap-2">
                        <span>View Details</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (!$plans): ?>
        <div class="bg-surface-container rounded-3xl p-12 border border-dashed border-outline-variant/30 text-center max-w-2xl mx-auto mt-8">
            <span class="material-symbols-outlined text-6xl text-zinc-700 mb-4">no_meals</span>
            <h3 class="text-xl font-bold text-zinc-500">No diet plans found</h3>
            <p class="text-zinc-600 text-sm mt-2">There are currently no active diet plans available. Check back later!</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php
    $meta = $result['meta'] ?? null;
    if ($meta && ($meta['last_page'] ?? 1) > 1):
        $currentPage = $meta['current_page'];
        $lastPage = $meta['last_page'];
        $queryParams = $_GET;
        unset($queryParams['paged_diet_plans']);
    ?>
        <div class="flex items-center justify-center gap-2 mt-12">
            <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                <?php 
                    $queryParams['paged_diet_plans'] = $i;
                    $pageUrl = home_url('/?' . http_build_query($queryParams));
                ?>
                <a href="<?= esc_url($pageUrl) ?>" class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-black border transition-all <?= $i === $currentPage ? 'bg-primary-container text-on-primary-container border-primary-container shadow-lg shadow-primary-container/20' : 'bg-surface-container border-outline-variant/10 text-zinc-400 hover:border-primary-container hover:text-primary-container' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

<?php
$GLOBALS['active'] = 'diet-plans';
wp_app_page_end(false);
