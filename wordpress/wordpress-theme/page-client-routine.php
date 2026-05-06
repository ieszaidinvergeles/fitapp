<?php
require_once 'functions.php';
require_login();
$id = (int)($_GET['id'] ?? 0);
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['activate_id'])) {
    $activate = api_post('/routines/' . (int)$_POST['activate_id'] . '/activate', [], auth: true);
    if (!empty($activate['result']) && $activate['result'] !== false) {
        $success = api_message($activate) ?? 'Routine activated successfully.';
    } else {
        $error = api_message($activate) ?? 'Could not activate routine.';
    }
}

$response = $id > 0 ? api_get('/routines/' . $id, auth: true) : ['result' => false, 'message' => ['general' => 'Routine not found.']];
$resData = $response['result'] ?? [];
$routine = $resData['data'] ?? (isset($resData['id']) ? $resData : null);
$exercises = $routine['exercises'] ?? [];

wp_app_page_start('Workout Detail');
?>

    <div class="mb-6 flex items-center gap-4">
        <a href="?pagename=client-routines" class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-zinc-400 hover:text-primary transition-colors border border-outline-variant/20">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h1 class="font-headline text-2xl font-black uppercase tracking-tight">Routine Details</h1>
    </div>

    <?php show_error($error); show_success($success); ?>

    <?php if (($response['result'] ?? null) === false || !$routine): ?>
        <div class="text-center py-20 bg-surface-container/30 rounded-3xl border border-dashed border-outline-variant/30">
            <span class="material-symbols-outlined text-6xl text-zinc-800 mb-4">error</span>
            <p class="text-zinc-500 font-medium"><?= h(api_message($response) ?? 'Routine not found.') ?></p>
            <a href="?pagename=client-routines" class="inline-block mt-6 px-8 py-3 bg-primary text-on-primary rounded-full text-xs font-black uppercase tracking-wider">Back to Routines</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Routine Header Info -->
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-surface-container rounded-3xl border border-outline-variant/10 overflow-hidden sticky top-8">
                    <?php if (!empty($routine['cover_image_url'])): ?>
                        <div class="h-56 w-full relative">
                            <img src="<?= esc_url($routine['cover_image_url']) ?>" class="w-full h-full object-cover opacity-50">
                            <div class="absolute inset-0 bg-gradient-to-t from-surface-container to-transparent"></div>
                        </div>
                    <?php else: ?>
                        <div class="h-40 w-full bg-zinc-800 flex items-center justify-center opacity-20">
                            <span class="material-symbols-outlined text-7xl">fitness_center</span>
                        </div>
                    <?php endif; ?>

                    <div class="p-8 -mt-12 relative z-10">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="px-3 py-1 rounded-full bg-primary/20 text-primary text-[10px] font-black uppercase tracking-widest border border-primary/20">
                                <?= h($routine['difficulty_level'] ?? 'Medium') ?>
                            </span>
                        </div>
                        <h2 class="font-headline text-4xl font-black uppercase tracking-tight leading-none mb-4"><?= h($routine['name'] ?? 'Routine') ?></h2>
                        <p class="text-zinc-400 text-sm leading-relaxed mb-8"><?= h($routine['description'] ?? 'No description available for this routine.') ?></p>

                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <div class="p-4 rounded-2xl bg-surface-container-high border border-outline-variant/10">
                                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 block mb-1">Total Time</span>
                                <span class="text-lg font-bold flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">timer</span>
                                    <?= (int)($routine['estimated_duration_min'] ?? 0) ?> min
                                </span>
                            </div>
                            <div class="p-4 rounded-2xl bg-surface-container-high border border-outline-variant/10">
                                <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 block mb-1">Exercises</span>
                                <span class="text-lg font-bold flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">format_list_bulleted</span>
                                    <?= count($exercises) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exercises List -->
            <div class="xl:col-span-2 space-y-6">
                <h3 class="font-headline text-xl font-black uppercase tracking-widest text-zinc-500 flex items-center gap-3">
                    Training Sequence
                    <span class="h-[1px] flex-1 bg-zinc-800"></span>
                </h3>

                <div class="space-y-4">
                    <?php foreach ($exercises as $index => $e): ?>
                        <?php 
                            $sets = $e['sets'] ?? '-';
                            $reps = $e['reps'] ?? '-';
                            $rest = $e['rest'] ?? '-';
                        ?>
                        <article class="bg-surface-container rounded-2xl p-6 border border-outline-variant/10 hover:bg-[#1e2117] transition-all group">
                            <div class="flex flex-col md:flex-row gap-6">
                                <div class="w-12 h-12 rounded-xl bg-zinc-800 border border-outline-variant/20 flex items-center justify-center text-primary font-black text-xl shrink-0">
                                    <?= $index + 1 ?>
                                </div>
                                
                                <div class="flex-1">
                                    <div class="flex items-center justify-between gap-4 mb-2">
                                        <h4 class="text-xl font-black uppercase tracking-tight group-hover:text-primary transition-colors"><?= h($e['name']) ?></h4>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-zinc-600"><?= h($e['target_muscle_group'] ?? 'General') ?></span>
                                    </div>
                                    
                                    <p class="text-sm text-zinc-500 line-clamp-2 mb-6"><?= h($e['description'] ?? 'Perform the exercise with proper form.') ?></p>
                                    
                                    <div class="flex flex-wrap items-center gap-x-8 gap-y-4">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-zinc-600 mb-0.5">Sets</span>
                                            <span class="text-sm font-bold text-on-surface"><?= h($sets) ?></span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-zinc-600 mb-0.5">Reps</span>
                                            <span class="text-sm font-bold text-on-surface"><?= h($reps) ?></span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-black uppercase tracking-widest text-zinc-600 mb-0.5">Rest</span>
                                            <span class="text-sm font-bold text-primary flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-sm">history</span>
                                                <?= h($rest) ?>s
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($e['video_url'])): ?>
                                    <div class="shrink-0 flex items-center">
                                        <a href="<?= esc_url($e['video_url']) ?>" target="_blank" class="w-12 h-12 rounded-full bg-zinc-800 flex items-center justify-center text-zinc-500 hover:text-primary hover:bg-zinc-700 transition-all border border-outline-variant/10">
                                            <span class="material-symbols-outlined">play_arrow</span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php
wp_app_page_end(false);
?>
