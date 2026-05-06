<?php
/**
 * Template Name: Client Notifications
 */

if (!is_user_logged_in()) {
    wp_redirect(home_url('/login'));
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/me/alerts?page=' . $page, auth: true);

// Mark all as read when entering this page
api_post('/me/notifications/read', [], auth: true);

$notifications = $response['result']['data'] ?? [];
$pagination = $response['result']['meta'] ?? null;

// Group notifications by date
$grouped = [];
foreach ($notifications as $n) {
    $date = date('Y-m-d', strtotime($n['created_at']));
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    $groupName = 'Older';
    if ($date === $today) $groupName = 'Today';
    elseif ($date === $yesterday) $groupName = 'Yesterday';
    else $groupName = date('F j, Y', strtotime($date));

    $grouped[$groupName][] = $n;
}

wp_app_page_start('System Alerts');
?>

<div class="mb-12 relative">
    <h1 class="font-display font-black text-6xl tracking-tighter text-on-surface uppercase leading-none" style="letter-spacing: -0.04em;">
        SYSTEM <span class="text-primary italic">ALERTS</span>
    </h1>
    <p class="mt-4 font-body text-zinc-400 text-lg max-w-md">Stay updated with your latest gym activity and system notifications.</p>
    <div class="absolute -top-10 -left-10 w-40 h-40 bg-primary opacity-5 blur-3xl rounded-full pointer-events-none"></div>
</div>

<?php 
if (($response['result'] ?? null) === false) {
    show_error(api_message($response));
}
?>

<div class="space-y-12">
    <?php if (empty($notifications)): ?>
        <div class="bg-surface-container rounded-3xl p-12 text-center border border-outline-variant/10">
            <span class="material-symbols-outlined text-zinc-600 text-6xl mb-4">notifications_off</span>
            <p class="text-zinc-400 font-medium">Your inbox is empty. We'll alert you when there's news!</p>
        </div>
    <?php else: ?>
        <?php foreach ($grouped as $group => $items): ?>
            <section>
                <h2 class="font-headline font-bold text-white mb-6 uppercase tracking-widest flex items-center gap-4 border-l-4 border-primary pl-4 text-2xl">
                    <?= h($group) ?>
                </h2>
                <div class="grid gap-4">
                    <?php foreach ($items as $n): 
                        $type = $n['target_audience'] ?? 'global';
                        $icon = 'notifications';
                        $label = 'Notice';
                        
                        switch($type) {
                            case 'specific_gym': $icon = 'fitness_center'; $label = 'Gym'; break;
                            case 'global': $icon = 'public'; $label = 'Global'; break;
                            case 'staff_only': $icon = 'badge'; $label = 'Staff'; break;
                            case 'specific_user': $icon = 'emoji_events'; $label = 'Personal'; break;
                        }
                    ?>
                        <div class="rounded-2xl border-t-2 border-t-primary shadow-xl relative overflow-hidden group bg-surface-container animate-in fade-in slide-in-from-bottom-4 duration-500">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center gap-3">
                                        <span class="material-symbols-outlined text-sm text-primary"><?= $icon ?></span>
                                        <span class="bg-primary/10 text-primary border border-primary/30 text-[10px] font-bold uppercase tracking-widest px-2 py-0.5 rounded">
                                            <?= $label ?>
                                        </span>
                                        <?php if (empty($n['read_at'])): ?>
                                            <span class="bg-error text-white text-[9px] font-black uppercase tracking-tighter px-1.5 py-0.5 rounded-full animate-pulse">NEW</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="font-label text-xs font-bold text-zinc-500">
                                        <?= time_ago($n['created_at']) ?>
                                    </span>
                                </div>
                                <h3 class="font-headline font-bold text-xl text-white mb-2"><?= h($n['title']) ?></h3>
                                <div class="font-body text-sm text-zinc-400 leading-relaxed">
                                    <?= nl2br(h($n['body'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
        
        <?php if ($pagination && $pagination['last_page'] > 1): ?>
            <div class="flex justify-center gap-4 pt-8">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="px-6 py-2 bg-surface-container border border-outline-variant/30 rounded-full text-xs font-bold text-white hover:border-primary transition-all uppercase tracking-widest">Previous</a>
                <?php endif; ?>
                <?php if ($page < $pagination['last_page']): ?>
                    <a href="?page=<?= $page + 1 ?>" class="px-6 py-2 bg-surface-container border border-outline-variant/30 rounded-full text-xs font-bold text-white hover:border-primary transition-all uppercase tracking-widest">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php 
wp_app_page_end();
?>
