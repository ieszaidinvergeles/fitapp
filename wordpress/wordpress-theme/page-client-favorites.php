<?php
require_once 'functions.php';
require_login();

$tab = $_GET['tab'] ?? 'routines';
$page = max(1, (int)($_GET['paged'] ?? 1));
$success = null;
$error = null;

// Handle Favorite Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['favorite_routine_id'])) {
        $routineId = (int)$_POST['favorite_routine_id'];
        $fav = api_post('/routines/' . $routineId . '/favorite', [], auth: true);
        if (!empty($fav['result']) && $fav['result'] !== false) {
            $success = api_message($fav) ?: 'Favorites updated.';
        } else {
            $error = api_message($fav) ?: 'Could not update favorites.';
        }
    } elseif (!empty($_POST['favorite_diet_plan_id'])) {
        $planId = (int)$_POST['favorite_diet_plan_id'];
        $fav = api_post('/diet-plans/' . $planId . '/favorite', [], auth: true);
        if (!empty($fav['result']) && $fav['result'] !== false) {
            $success = api_message($fav) ?: 'Favorites updated.';
        } else {
            $error = api_message($fav) ?: 'Could not update favorites.';
        }
    }
}

// Fetch Data based on active tab
$items = [];
$resData = [];

switch($tab) {
    case 'routines':
        $response = api_get('/routines?favorites=1&page=' . $page, auth: true);
        $resData = $response['result'] ?? [];
        $items = $resData['data'] ?? $resData ?? [];
        break;
    case 'diets':
        $response = api_get('/diet-plans?favorites=1&page=' . $page, auth: true);
        $resData = $response['result'] ?? [];
        $items = $resData['data'] ?? $resData ?? [];
        break;
    default:
        $tab = 'routines';
        $response = api_get('/routines?favorites=1&page=' . $page, auth: true);
        $resData = $response['result'] ?? [];
        $items = $resData['data'] ?? $resData ?? [];
        break;
}

wp_app_page_start('My Favorites');
?>
    <div class="mb-10">
        <p class="text-zinc-500 text-sm font-medium mt-1">Your curated collection of training and nutrition assets.</p>
    </div>

    <?php show_error($error); show_success($success); ?>

    <!-- Tab Navigation -->
    <div class="flex items-center gap-2 p-1 bg-surface-container rounded-2xl border border-outline-variant/10 mb-8 w-max">
        <a href="?pagename=client-favorites&tab=routines" 
           class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?= $tab === 'routines' ? 'bg-primary text-black shadow-lg shadow-primary/20' : 'text-zinc-500 hover:text-white' ?>">
           Routines
        </a>
        <a href="?pagename=client-favorites&tab=diets" 
           class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all <?= $tab === 'diets' ? 'bg-primary text-black shadow-lg shadow-primary/20' : 'text-zinc-500 hover:text-white' ?>">
           Diet Plans
        </a>
    </div>

    <?php if (empty($items)): ?>
        <div class="flex flex-col items-center justify-center py-20 bg-surface-container/30 rounded-[2.5rem] border border-dashed border-outline-variant/20">
            <div class="w-20 h-20 rounded-full bg-zinc-800 flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-4xl text-zinc-600">bookmark_add</span>
            </div>
            <h3 class="text-xl font-black uppercase tracking-tight text-white mb-2">The Vault is Empty</h3>
            <p class="text-zinc-500 text-sm max-w-xs text-center leading-relaxed">You haven't saved any <?= h($tab === 'diets' ? 'diet plans' : $tab) ?> yet. Start exploring and save your favorites for quick access.</p>
            <a href="?pagename=client-<?= h($tab === 'diets' ? 'diet-plans' : $tab) ?>" class="mt-8 px-8 py-3 bg-white/5 hover:bg-white/10 text-white rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 transition-all">
                Browse <?= h($tab === 'diets' ? 'diet plans' : $tab) ?>
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($items as $item): ?>
                <article class="bg-surface-container rounded-3xl border border-outline-variant/10 overflow-hidden group hover:-translate-y-1 transition-all duration-300 shadow-xl">
                    <div class="h-40 bg-zinc-800 relative">
                        <?php if (!empty($item['cover_image_url'])): ?>
                            <img src="<?= esc_url($item['cover_image_url']) ?>" class="w-full h-full object-cover opacity-60">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center opacity-10">
                                <span class="material-symbols-outlined text-6xl">
                                    <?= $tab === 'routines' ? 'fitness_center' : 'restaurant' ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <div class="absolute top-4 right-4">
                            <form method="POST">
                                <input type="hidden" name="<?= $tab === 'diets' ? 'favorite_diet_plan_id' : 'favorite_routine_id' ?>" value="<?= (int)$item['id'] ?>"/>
                                <button type="submit" class="w-10 h-10 rounded-full bg-black/50 backdrop-blur-md flex items-center justify-center text-primary border border-primary/20 hover:scale-110 transition-transform active:scale-95 group/fav btn-favorite is-favorite">
                                    <span class="material-symbols-outlined fill-1 transition-all">star</span>
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-black uppercase tracking-tight text-white mb-2"><?= h($item['name']) ?></h3>
                        <p class="text-xs text-zinc-500 line-clamp-2 mb-6"><?= h($item['description'] ?? 'No description available.') ?></p>
                        
                        <a href="?pagename=client-<?= h($tab === 'diets' ? 'diet-plan' : 'routine') ?>&id=<?= (int)$item['id'] ?>" 
                           class="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-primary hover:gap-3 transition-all">
                            View Details <span class="material-symbols-outlined text-sm">arrow_forward</span>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php
    $meta = $resData['meta'] ?? $response['meta'] ?? null;
    if ($meta && ($meta['last_page'] ?? 1) > 1):
        $currentPage = $meta['current_page'];
        $lastPage = $meta['last_page'];
        
        $baseQuery = '?pagename=client-favorites&tab=' . urlencode($tab);
    ?>
    <div class="mt-12 flex items-center justify-center gap-4">
        <?php if ($currentPage > 1): ?>
            <a href="<?= $baseQuery ?>&paged=<?= $currentPage - 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
                <span class="material-symbols-outlined">chevron_left</span>
            </a>
        <?php endif; ?>
        
        <div class="px-6 py-3 rounded-2xl bg-surface-container border border-outline-variant/20 text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">
            Page <?= $currentPage ?> / <?= $lastPage ?>
        </div>
        
        <?php if ($currentPage < $lastPage): ?>
            <a href="<?= $baseQuery ?>&paged=<?= $currentPage + 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

<?php
$GLOBALS['active'] = 'favorites';
wp_app_page_end(false);
?>
