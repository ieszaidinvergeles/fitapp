<?php
require_once 'functions.php';

$page_title = 'Join the Elite';
$GLOBALS['hide_global_header'] = true;
$GLOBALS['hide_global_footer'] = true;

if (is_logged_in()) {
    header('Location: ' . (is_advanced() ? 'page-staff-dashboard.php' : 'page-client-dashboard.php'));
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = api_post('/auth/register', [
        'username'              => $_POST['username']              ?? '',
        'full_name'             => $_POST['full_name']             ?? '',
        'email'                 => $_POST['email']                 ?? '',
        'password'              => $_POST['password']              ?? '',
        'password_confirmation' => $_POST['password_confirmation'] ?? '',
        'dni'                   => $_POST['dni']                   ?? '',
        'birth_date'            => $_POST['birth_date']            ?? '',
    ]);

    $msg = api_message($response);

    if (strpos($msg ?? '', 'successful') !== false || strpos($msg ?? '', 'Registration') !== false) {
        header('Location: front-page.php?registered=1');
        exit;
    }

    $error = $msg ?? 'Registration failed.';
}

get_header();
?>
<header class="bg-[#0d0f08] flex justify-between items-center w-full px-6 py-4 fixed top-0 z-50">
    <div class="flex items-center gap-4">
        <span class="material-symbols-outlined text-[#d4fb00] text-2xl">menu</span>
        <span class="font-headline font-black italic text-3xl text-[#d4fb00] tracking-tighter uppercase">VOLT GYM</span>
    </div>
    <a href="front-page.php" class="text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined">close</span>
    </a>
</header>
<main class="min-h-screen flex flex-col md:flex-row pt-20 pb-12 px-6">
    <section class="hidden md:flex flex-1 flex-col justify-end p-12 relative overflow-hidden rounded-3xl bg-surface-container mr-6">
        <div class="absolute inset-0 z-0">
            <div class="w-full h-full bg-[radial-gradient(circle_at_top_left,_rgba(212,251,0,0.24),_transparent_45%),linear-gradient(160deg,_#171b10_0%,_#0d0f08_100%)]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-transparent"></div>
        </div>
        <div class="relative z-10 space-y-4">
            <h2 class="font-headline text-7xl font-black italic tracking-tighter text-primary-container leading-none uppercase">
                Start your<br/>journey
            </h2>
            <p class="font-body text-xl text-on-surface-variant max-w-md">
                Join the elite community of athletes pushing the boundaries of human performance.
            </p>
        </div>
    </section>
    <section class="flex-1 flex flex-col justify-center items-center md:items-start py-10">
        <div class="w-full max-w-md">
            <div class="md:hidden mb-8">
                <h2 class="font-headline text-5xl font-black italic tracking-tighter text-primary-container leading-none uppercase mb-2">Start your journey</h2>
            </div>
            <div class="mb-10">
                <h1 class="font-headline text-2xl font-bold tracking-tight uppercase">Create Account</h1>
                <p class="text-on-surface-variant text-sm mt-1">Enter your details to access the VOLT ecosystem.</p>
            </div>

            <?php show_error($error); ?>

            <form method="POST" action="page-register.php" class="space-y-6">
                <div class="space-y-2">
                    <label class="font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant ml-1">Full Name</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 group-focus-within:text-primary transition-colors">person</span>
                        <input class="w-full bg-surface-container-highest border-0 rounded-xl py-4 pl-12 pr-4 text-on-surface placeholder:text-zinc-600 focus:ring-1 focus:ring-primary/30 transition-all" name="full_name" placeholder="Max Power" type="text" value="<?= h($_POST['full_name'] ?? '', '') ?>"/>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant ml-1">Username</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 group-focus-within:text-primary transition-colors">alternate_email</span>
                        <input class="w-full bg-surface-container-highest border-0 rounded-xl py-4 pl-12 pr-4 text-on-surface placeholder:text-zinc-600 focus:ring-1 focus:ring-primary/30 transition-all" name="username" placeholder="maxpower" maxlength="20" required type="text" value="<?= h($_POST['username'] ?? '', '') ?>"/>
                    </div>
                </div>                <div class="space-y-2">
                    <label class="font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant ml-1">Email Address</label>
                    <div class="relative group">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 group-focus-within:text-primary transition-colors">mail</span>
                        <input class="w-full bg-surface-container-highest border-0 rounded-xl py-4 pl-12 pr-4 text-on-surface placeholder:text-zinc-600 focus:ring-1 focus:ring-primary/30 transition-all" name="email" placeholder="max@voltgym.com" type="email" value="<?= h($_POST['email'] ?? '', '') ?>"/>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label class="font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant ml-1">DNI</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 group-focus-within:text-primary transition-colors">badge</span>
                            <input class="w-full bg-surface-container-highest border-0 rounded-xl py-4 pl-12 pr-4 text-on-surface placeholder:text-zinc-600 focus:ring-1 focus:ring-primary/30 transition-all" name="dni" placeholder="12345678A" maxlength="9" type="text" value="<?= h($_POST['dni'] ?? '', '') ?>"/>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant ml-1">Birth Date</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 group-focus-within:text-primary transition-colors">cake</span>
                            <input class="w-full bg-surface-container-highest border-0 rounded-xl py-4 pl-12 pr-4 text-on-surface placeholder:text-zinc-600 focus:ring-1 focus:ring-primary/30 transition-all" name="birth_date" type="date" value="<?= h($_POST['birth_date'] ?? '', '') ?>"/>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label class="font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant ml-1">Password</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 group-focus-within:text-primary transition-colors">lock</span>
                            <input class="w-full bg-surface-container-highest border-0 rounded-xl py-4 pl-12 pr-4 text-on-surface placeholder:text-zinc-600 focus:ring-1 focus:ring-primary/30 transition-all" name="password" placeholder="��������" type="password"/>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="font-label text-[10px] font-bold uppercase tracking-widest text-on-surface-variant ml-1">Confirm</label>
                        <div class="relative group">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500 group-focus-within:text-primary transition-colors">verified_user</span>
                            <input class="w-full bg-surface-container-highest border-0 rounded-xl py-4 pl-12 pr-4 text-on-surface placeholder:text-zinc-600 focus:ring-1 focus:ring-primary/30 transition-all" name="password_confirmation" placeholder="��������" type="password"/>
                        </div>
                    </div>
                </div>
                <button class="kinetic-gradient w-full py-5 rounded-full font-headline font-black italic uppercase tracking-wider text-on-primary-container shadow-[0_10px_30px_rgba(212,251,0,0.2)] hover:scale-[1.02] active:scale-95 transition-all" type="submit">Join the Elite</button>
                <div class="text-center pt-2">
                    <p class="text-sm text-on-surface-variant">Already have an account? <a class="text-primary font-bold uppercase tracking-tight ml-1 hover:underline" href="front-page.php">Log in</a></p>
                </div>
            </form>
        </div>
    </section>
</main>
<div class="fixed bottom-10 right-10 hidden lg:block">
    <div class="bg-surface-container-high/60 backdrop-blur-xl p-6 rounded-3xl border border-primary/10 max-w-[200px] shadow-2xl">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-2 h-2 rounded-full bg-primary-container animate-pulse"></div>
            <span class="font-label text-[10px] font-black uppercase text-primary-container tracking-widest">Live Updates</span>
        </div>
        <p class="text-[10px] text-on-surface leading-tight font-medium">New athletes joining VOLT every day.</p>
    </div>
</div>
<?php
get_footer();
unset($GLOBALS['hide_global_header']);
unset($GLOBALS['hide_global_footer']);
?>

