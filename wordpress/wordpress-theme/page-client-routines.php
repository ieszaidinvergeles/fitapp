<?php
require_once 'functions.php';
require_login();
$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['activate_routine_id'])) {
    $routineId = (int)$_POST['activate_routine_id'];
    $activate = api_post('/routines/' . $routineId . '/activate', [], auth: true);
    if (!empty($activate['result']) && $activate['result'] !== false) {
        $success = 'Routine activated successfully.';
    } else {
        $error = api_message($activate) ?: 'Could not activate routine.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['favorite_routine_id'])) {
    $routineId = (int)$_POST['favorite_routine_id'];
    $fav = api_post('/routines/' . $routineId . '/favorite', [], auth: true);
    if (!empty($fav['result']) && $fav['result'] !== false) {
        $success = api_message($fav) ?: 'Favorites updated.';
    } else {
        $error = api_message($fav) ?: 'Could not update favorites.';
    }
}

$page = max(1, (int)($_GET['paged_routines'] ?? 1));
$listResp = api_get('/routines?page=' . $page, auth: true);
$resData = $listResp['result'] ?? [];
$listData = $resData['data'] ?? [];

wp_app_page_start('Training Routines');
?>
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-on-surface-variant text-sm font-medium mt-1">Select and activate a training plan tailored to your goals.</p>
        </div>
    </div>

    <?php show_error($error); show_success($success); ?>
    <?php if (($listResp['result'] ?? null) === false) { show_error(api_message($listResp)); } ?>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($listData as $r): ?>
            <?php 
                $difficulty = $r['difficulty_level'] ?? 'Medium';
                $duration = (int)($r['estimated_duration_min'] ?? 0);
                $exerciseCount = (int)($r['exercises_count'] ?? 0);
                $coverUrl = !empty($r['cover_image_url']) ? $r['cover_image_url'] : null;
            ?>
            <article class="bg-surface-container rounded-3xl border border-outline-variant/10 overflow-hidden group hover:-translate-y-1 transition-all duration-300 hover:shadow-2xl hover:shadow-primary/5 flex flex-col">
                <div class="h-40 w-full relative overflow-hidden bg-zinc-800">
                    <?php if ($coverUrl): ?>
                        <img src="<?= esc_url($coverUrl) ?>" alt="<?= h($r['name']) ?>" class="w-full h-full object-cover opacity-60 group-hover:scale-110 transition-transform duration-700">
                    <?php else: ?>
                        <div class="absolute inset-0 flex items-center justify-center opacity-20">
                            <span class="material-symbols-outlined text-8xl">fitness_center</span>
                        </div>
                    <?php endif; ?>
                    <div class="absolute top-4 left-4 right-4 flex justify-between items-start">
                        <span class="px-3 py-1 rounded-full bg-black/50 backdrop-blur-md text-[10px] font-black uppercase tracking-widest text-primary border border-primary/20">
                            <?= h($difficulty) ?>
                        </span>
                        <form method="POST">
                            <input type="hidden" name="favorite_routine_id" value="<?= (int)$r['id'] ?>"/>
                            <button type="submit" class="w-10 h-10 rounded-full bg-black/50 backdrop-blur-md flex items-center justify-center text-primary border border-primary/20 hover:scale-110 transition-transform active:scale-95 group/fav btn-favorite <?= !empty($r['is_favorite']) ? 'is-favorite' : '' ?>">
                                <span class="material-symbols-outlined <?= !empty($r['is_favorite']) ? 'fill-1' : '' ?> group-hover/fav:fill-1 transition-all">star</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="p-6 flex-1 flex flex-col">
                    <h2 class="font-headline text-2xl font-black uppercase tracking-tight mb-4 group-hover:text-primary transition-colors"><?= h($r['name'] ?? 'Routine') ?></h2>
                    
                    <div class="flex items-center gap-6 mb-8">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-0.5">Duration</span>
                            <span class="text-sm font-bold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-primary">timer</span>
                                <?= $duration ?> min
                            </span>
                        </div>
                        <div class="w-[1px] h-6 bg-zinc-800"></div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 mb-0.5">Exercises</span>
                            <span class="text-sm font-bold flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm text-primary">format_list_bulleted</span>
                                <?= $exerciseCount ?>
                            </span>
                        </div>
                    </div>

                    <div class="mt-auto flex items-center gap-3">
                        <a href="?pagename=client-routine&id=<?= (int)($r['id'] ?? 0) ?>" class="flex-1 py-3 rounded-full bg-surface-container-high border border-outline-variant/20 text-[10px] font-black uppercase tracking-widest text-center hover:bg-zinc-800 transition-colors">Details</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (!$listData): ?>
        <div class="text-center py-20 bg-surface-container/30 rounded-3xl border border-dashed border-outline-variant/30 max-w-2xl mx-auto">
            <span class="material-symbols-outlined text-6xl text-zinc-800 mb-4">fitness_center</span>
            <p class="text-zinc-500 font-medium">No training routines available yet.</p>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php
    $meta = $resData['meta'] ?? $listResp['meta'] ?? null;
    if ($meta && ($meta['last_page'] ?? 1) > 1):
        $currentPage = $meta['current_page'];
        $lastPage = $meta['last_page'];
        
        $baseQuery = '?pagename=client-routines';
    ?>
    <div class="mt-12 flex items-center justify-center gap-4">
        <?php if ($currentPage > 1): ?>
            <a href="<?= $baseQuery ?>&paged_routines=<?= $currentPage - 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
                <span class="material-symbols-outlined">chevron_left</span>
            </a>
        <?php endif; ?>
        
        <div class="px-6 py-3 rounded-2xl bg-surface-container border border-outline-variant/20 text-[10px] font-black uppercase tracking-[0.2em] text-zinc-500">
            Page <?= $currentPage ?> / <?= $lastPage ?>
        </div>
        
        <?php if ($currentPage < $lastPage): ?>
            <a href="<?= $baseQuery ?>&paged_routines=<?= $currentPage + 1 ?>" class="w-12 h-12 rounded-2xl bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
                <span class="material-symbols-outlined">chevron_right</span>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

<?php
$GLOBALS['active'] = 'routines';
wp_app_page_end(false);
?>
