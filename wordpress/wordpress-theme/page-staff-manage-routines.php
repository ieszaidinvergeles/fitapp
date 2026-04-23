<?php
require_once 'functions.php';
require_advanced();
$page = max(1, (int)($_GET['page'] ?? 1));
$response = api_get('/routines?page=' . $page, auth: true);
$rows = (is_array($response['result'] ?? null)) ? $response['result'] : [];
wp_app_page_start('Manage Routines', true);
?>
    <?php if (($response['result'] ?? null) === false) { show_error(api_message($response)); } ?>
    <div class="flex flex-col gap-4">
        <?php foreach ($rows as $index => $r): ?>
            <?php
            $images = [
                'https://lh3.googleusercontent.com/aida-public/AB6AXuDeQ_f2ABuaS7hiVzZk24lQeV2ubJ7_e8MYlr40qr6EablOsa5smVVZGGp83J0_mzaJuHi0R3rip_1sOnuQRIP5Dr9KG60B23-KeKXcd3x05XcbUuYmnSpg7s3SRQjM88nzTk5LNe3A-fbrEmULsw0CaRUFNzi7DyiL0c6KIX29Ha815mJRHhx1jeFUR1bImSRbeMI4Wqauw-kMqFPUrbA_OhfFLHEiCUq2efZ7fiKGWI3K_YRZDSrFurESfOfHzspCYzF_BhR3WFE',
                'https://lh3.googleusercontent.com/aida-public/AB6AXuAajDhzvGaeBmgXgAx-K_KnNcQrAY4ABPdGTEJHoglqPsmQjfFT4RgFwRfgEBFhDzyaYzmhYfTkYUFSFwDOyLLo4_4mpg5T9kcG_VeTGOyNvMFForvirhC4klJ7nN3ULUsZFtmzbTOkeZFsbBWQiP8K8qgtx6y6PZ2yMpdJaCfQ-qZU5Ls784zDQQUgnjwPh2cSILY-IimhDcJOAAvCJXWUiowqg-3Lbh6gmwkkjEpd8HXqD-iH4vOam-7294tV_eE5z3FD1XtVdG0',
                'https://lh3.googleusercontent.com/aida-public/AB6AXuAueJjdLa2ynyLQWf9Gkk8n8tfWJVQChuhgdPsddvexYYGTNnTvJ73jq6HbBJnZYHq_G6SCQrPXEkG9HnReS8jb3QB0WmT0sYbFxhc3CiAqfWsBd6aeXUiKYPB5GsOR8STmyp89pq5KoNssqgGfcObUOjfPhs2IgHrjZ17jTJcDAqXlQlY7aiucGYydhRyN1XP0uZpX-jL_LsCw84U_txx2W9ERxkssaV2gzLZbFhxI7lQi_EpMlPPnEW4CcpeumOEuNmSMfXBF-w4',
                'https://lh3.googleusercontent.com/aida-public/AB6AXuDDjPHtCo-K3vR3TwHc5I8_0IuxhR-bXMGKpDLRuarSvja_fc17nY2yB-s_MuGK4D1x7Mt40zmMNjJah5uiu30GjKff8B0Z6rFf7vOZzMyeJSImW6uxrA4pEBYB6fINCX27gk67bJz85w1wGoVQl5s73VBJWDkTeD_ncTiakKhK1U_VduDe2UANDEO2B1JSMsrQurZtP2zZ6x-DZL1V0GXEDXrgGSW0F6WtdOU7H9HSAa5xPVVfFzNFk7-NQEpCp5IGCihjYZwz-iQ'
            ];
            $img = $images[$index % count($images)];
            $partNum = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
            ?>
            <div class="relative w-full h-[320px] overflow-hidden group rounded-3xl border border-outline-variant/20">
                <img alt="Routine image" class="absolute inset-0 w-full h-full object-cover scale-105 group-hover:scale-110 transition-transform duration-700 opacity-60" src="<?= $img ?>"/>
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent"></div>
                <div class="absolute top-4 right-4 flex flex-col gap-3 z-20">
                    <button class="w-10 h-10 flex items-center justify-center rounded-full bg-black/40 backdrop-blur-md text-primary border border-primary/20 cursor-pointer hover:bg-black/60 transition-colors">
                        <span class="material-symbols-outlined">edit</span>
                    </button>
                    <button class="w-10 h-10 flex items-center justify-center rounded-full bg-black/40 backdrop-blur-md text-error border border-error/20 cursor-pointer hover:bg-black/60 transition-colors">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </div>
                <div class="absolute bottom-0 left-0 p-8 w-full z-10">
                    <span class="text-primary text-[10px] font-black tracking-[0.4em] uppercase mb-2 block text-shadow-strong">ROUTINE <?= $partNum ?></span>
                    <h3 class="text-4xl md:text-5xl font-headline font-black italic text-white uppercase tracking-tighter leading-tight text-shadow-strong">
                        <?= h($r['name'] ?? 'Routine') ?>
                    </h3>
                    <div class="mt-4 flex items-center gap-4">
                        <p class="text-white/80 text-xs font-bold uppercase tracking-widest">Diff: <?= h($r['difficulty_level'] ?? '-') ?></p>
                        <span class="w-1 h-1 bg-primary rounded-full"></span>
                        <p class="text-primary text-xs font-black uppercase tracking-widest italic">GOAL: <?= h($r['goal'] ?? 'General') ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <p class="text-on-surface-variant text-center py-12">No routines found.</p>
        <?php endif; ?>

        <div class="mt-8 flex flex-col">
            <button class="w-full h-16 rounded-full bg-gradient-to-r from-[#d4fb00] to-[#e6ff66] flex items-center justify-center gap-4 shadow-[0_0_20px_rgba(212,251,0,0.3)] hover:shadow-[0_0_30px_rgba(212,251,0,0.5)] transition-all active:scale-[0.98]">
                <span class="font-headline font-black text-black uppercase tracking-[0.1em] text-lg">Create New Routine</span>
                <span class="material-symbols-outlined text-black font-bold" style="font-variation-settings: 'FILL' 1;">add</span>
            </button>
        </div>
    </div>
<?php
wp_app_page_end(true);

