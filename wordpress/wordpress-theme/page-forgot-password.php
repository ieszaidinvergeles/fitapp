<?php
require_once 'functions.php';

$page_title                     = 'Forgot Password';
$GLOBALS['hide_global_header']  = true;
$GLOBALS['hide_global_footer']  = true;

$error   = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = api_post('/auth/forgot-password', [
        'email' => $_POST['email'] ?? '',
    ]);
    if (!empty($response['result'])) {
        $success = api_message($response) ?? 'Reset link sent. Check your email.';
    } else {
        $error = api_message($response) ?? 'Request failed.';
    }
}

get_header();
?>

<!-- ── Fixed top bar ─────────────────────────────────────── -->
<header class="bg-[#0d0f08] flex justify-between items-center w-full px-6 py-6 fixed top-0 z-50">
    <div class="font-headline font-black text-2xl text-[#d4fb00] tracking-tighter uppercase">
        VOLT GYM
    </div>
    <a class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors duration-200"
       href="front-page.php">
        <span class="material-symbols-outlined text-sm">arrow_back</span>
        <span class="font-label text-xs font-bold tracking-widest uppercase">Login</span>
    </a>
</header>

<!-- ── Main centred content ──────────────────────────────── -->
<main class="flex-grow flex items-center justify-center px-4 pt-20 pb-12 relative overflow-hidden min-h-screen">

    <div class="w-full max-w-md z-10">

        <!-- Icon + headline -->
        <div class="mb-12 flex flex-col items-center text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-xl bg-surface-container-high border-t-4 border-primary-container mb-8">
                <span class="material-symbols-outlined text-primary-container text-3xl">lock_reset</span>
            </div>
            <h1 class="font-headline text-5xl md:text-6xl font-bold tracking-tighter text-on-surface mb-4 leading-none">
                FORGOT <span class="text-primary-container">ACCESS?</span>
            </h1>
            <p class="text-on-surface-variant text-lg leading-relaxed max-w-xs">
                No worries, we'll send you a link to get back in the game.
            </p>
        </div>

        <!-- API feedback notices -->
        <?php show_error($error); ?>
        <?php show_success($success); ?>

        <!-- Reset form -->
        <form method="POST" action="page-forgot-password.php" class="space-y-6">

            <div class="group relative">
                <label class="block font-label text-[10px] font-extrabold uppercase tracking-widest text-primary-fixed-dim mb-2 ml-1"
                       for="fp-email">
                    Email Address
                </label>
                <div class="relative">
                    <input
                        id="fp-email"
                        name="email"
                        type="email"
                        placeholder="athlete@volt.gym"
                        required
                        value="<?= h($_POST['email'] ?? '', '') ?>"
                        class="w-full bg-surface-container-highest border-0 rounded-xl px-5 py-4 text-on-surface placeholder:text-outline focus:ring-0 focus:bg-surface-bright transition-all duration-200"
                    />
                    <!-- Animated bottom border on focus -->
                    <div class="absolute bottom-0 left-0 h-0.5 w-0 group-focus-within:w-full bg-secondary transition-all duration-500"></div>
                </div>
            </div>

            <div class="pt-4">
                <button
                    id="fp-submit"
                    type="submit"
                    class="kinetic-gradient glow-button w-full py-5 rounded-full text-on-primary font-headline font-extrabold text-lg uppercase flex items-center justify-center gap-2 group transition-transform active:scale-95"
                >
                    <span class="tracking-widest">Reset Password</span>
                    <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </button>
            </div>

        </form>

        <!-- Support link -->
        <div class="mt-12 flex flex-col items-center gap-6">
            <a class="text-on-surface-variant hover:text-on-surface font-label text-sm font-bold uppercase tracking-widest flex items-center gap-2 transition-colors"
               href="front-page.php">
                <span class="material-symbols-outlined text-base">support_agent</span>
                Contact Support
            </a>
        </div>

    </div><!-- /.max-w-md -->
</main>

<!-- ── Decorative right-side panel (desktop only) ───────── -->
<div class="hidden lg:block fixed right-0 top-0 bottom-0 w-1/3 z-0">
    <div class="h-full w-full relative">
        <img
            class="h-full w-full object-cover opacity-40 mix-blend-luminosity grayscale"
            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAuUiVwd_fhTWQ7AJLuETVHotmoAdmPkp6pXIVjrf18rjRjtij78SwwNvKXK3zlWUXCBHyW9cN7UyqikzlYKjE96_XuTBk32_zCT8-wSonWvAT5OeHljdwNIZ41VXIihxNQ1fcNWLKAB-IDzhq5JhgMkWHOAMGdBAAN5Fq6Mnmafloe8eDXc2XyvGwCNUcED5kmBN5WZpYbnDrf-T70dz1LTM31WfQLUzldaAsG5SJtS_YkgwCQtfZdjm45iHLG7U6Ir4N4Nohhdzc"
            alt="Athlete training"
        />
        <!-- Left gradient fade -->
        <div class="absolute inset-0 bg-gradient-to-l from-transparent via-background/80 to-background"></div>
        <!-- Motivational glass card -->
        <!-- <div class="absolute bottom-12 left-0 -translate-x-1/2 p-8 glass-panel rounded-tl-[3rem] rounded-br-[3rem] max-w-sm border-l border-t border-white/5">
            <span class="font-headline text-primary-container text-4xl font-black italic block mb-2 tracking-tighter">STAY READY.</span>
            <p class="text-on-surface-variant font-body text-sm leading-relaxed">
                Performance isn't just about the sweat. It's about the security and focus to keep pushing your limits every single day.
            </p>
        </div> -->
    </div>
</div>

<?php
get_footer();
unset($GLOBALS['hide_global_header'], $GLOBALS['hide_global_footer']);
?>
