<?php
require_once 'functions.php';

$page_title = 'Login';
$GLOBALS['hide_global_header'] = true;
$GLOBALS['hide_global_footer'] = true;

if (is_logged_in()) {
    header('Location: ' . get_role_home_path());
    exit;
}

$error   = null;
$success = null;

if (!empty($_GET['registered'])) {
    $success = 'Account created. Please log in.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Keep login logic equivalent to legacy client: always send email + password.
    $email = $_POST['email'] ?? $_POST['staff_email'] ?? '';
    $password = $_POST['password'] ?? $_POST['staff_password'] ?? '';

    $response = api_post('/auth/login', [
        'email'    => $email,
        'password' => $password,
    ]);

    if (!empty($response['token'])) {
        $_SESSION['token'] = $response['token'];
        $_SESSION['user']  = $response['user'];
        header('Location: ' . get_role_home_path());
        exit;
    }

    $error = api_message($response) ?? 'Login failed.';
}

get_header();
?>
<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-gradient-to-b from-surface-dim/40 via-surface-dim/80 to-surface-dim z-10"></div>
    <div class="w-full h-full bg-[radial-gradient(circle_at_top,_rgba(212,251,0,0.18),_transparent_45%),linear-gradient(160deg,_#11150d_0%,_#0d0f08_100%)]"></div>
</div>
<header class="fixed top-0 left-0 w-full px-6 h-16 flex justify-between items-center z-50">
    <div class="text-[#d7ff00] text-2xl font-black tracking-[-0.04em] font-headline uppercase">VOLT GYM</div>
</header>
<main class="relative z-20 min-h-screen flex flex-col items-center justify-center px-4 py-20">
    <div class="w-full max-w-md">
        <div class="mb-12 text-center md:text-left">
            <span class="text-primary-container font-headline font-bold uppercase tracking-[0.2em] text-xs mb-2 block">Performance Portal</span>
            <h1 class="font-headline text-5xl md:text-7xl font-extrabold tracking-tighter leading-none uppercase italic">
                DESPIERTA <br/>
                <span class="text-primary-container">EL TRUENO.</span>
            </h1>
        </div>
        <div class="bg-surface-container-high/80 backdrop-blur-xl asymmetric-card p-8 md:p-10 shadow-2xl border border-outline-variant/10">

            <?php show_error($error); ?>
            <?php show_success($success); ?>

            <div class="relative flex bg-surface-container-lowest rounded-full p-1.5 mb-10 overflow-hidden" id="login-toggle">
                <div class="absolute inset-y-1.5 left-1.5 w-[calc(50%-6px)] bg-primary-container rounded-full transition-all duration-300" id="toggle-indicator"></div>
                <button type="button" class="relative z-10 flex-1 py-3 text-sm font-headline font-bold uppercase tracking-wider text-on-primary-container transition-colors duration-300" id="user-tab" onclick="switchTab('user')">User</button>
                <button type="button" class="relative z-10 flex-1 py-3 text-sm font-headline font-bold uppercase tracking-wider text-on-surface-variant transition-colors duration-300" id="employee-tab" onclick="switchTab('staff')">Staff</button>
            </div>

            <form method="POST" action="front-page.php">
                <div id="user-fields" class="space-y-6">
                    <input type="hidden" name="tab" value="user"/>
                    <div class="space-y-2">
                        <label class="text-[10px] font-label font-black uppercase tracking-widest text-on-surface-variant px-1">Email Address</label>
                        <input class="w-full bg-surface-container-highest border-none rounded-xl px-4 py-4 focus:ring-2 focus:ring-primary-container text-on-surface placeholder:text-outline font-medium transition-all" name="email" placeholder="runner@apex.com" type="email" value="<?= h($_POST['email'] ?? '', '') ?>"/>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <label class="text-[10px] font-label font-black uppercase tracking-widest text-on-surface-variant">Password</label>
                            <a class="text-[10px] font-label font-black uppercase tracking-widest text-primary-fixed-dim hover:text-primary transition-colors" href="page-forgot-password.php">Forgot?</a>
                        </div>
                        <input class="w-full bg-surface-container-highest border-none rounded-xl px-4 py-4 focus:ring-2 focus:ring-primary-container text-on-surface placeholder:text-outline font-medium transition-all" name="password" placeholder="" type="password"/>
                    </div>
                    <button class="w-full kinetic-gradient py-5 rounded-full font-headline font-black uppercase tracking-[0.15em] text-on-primary-container text-sm shadow-[0_10px_30px_rgba(212,251,0,0.2)] active:scale-95 transition-all duration-300 flex items-center justify-center gap-3" type="submit">
                        Enter Workspace
                        <span class="material-symbols-outlined text-lg">arrow_forward</span>
                    </button>
                </div>

                <div id="staff-fields" class="space-y-6 hidden">
                    <input type="hidden" name="tab" value="staff"/>
                    <div class="space-y-2">
                        <label class="text-[10px] font-label font-black uppercase tracking-widest text-on-surface-variant px-1">Staff Email</label>
                        <input class="w-full bg-surface-container-highest border-none rounded-xl px-4 py-4 focus:ring-2 focus:ring-primary-container text-on-surface placeholder:text-outline font-medium transition-all" name="staff_email" placeholder="staff@voltgym.com" type="email" value="<?= h($_POST['staff_email'] ?? '', '') ?>"/>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-label font-black uppercase tracking-widest text-on-surface-variant px-1">Access Code</label>
                        <input class="w-full bg-surface-container-highest border-none rounded-xl px-4 py-4 focus:ring-2 focus:ring-primary-container text-on-surface placeholder:text-outline font-medium transition-all" name="staff_password" placeholder="" type="password"/>
                    </div>
                    <button class="w-full kinetic-gradient py-5 rounded-full font-headline font-black uppercase tracking-[0.15em] text-on-primary-container text-sm shadow-[0_10px_30px_rgba(212,251,0,0.2)] active:scale-95 transition-all duration-300 flex items-center justify-center gap-3" type="submit">
                        Staff Access
                        <span class="material-symbols-outlined text-lg">badge</span>
                    </button>
                </div>
            </form>

            <p class="mt-10 text-center text-[10px] font-label font-bold uppercase tracking-widest text-on-surface-variant">
                Not a member? <a class="text-primary-container hover:underline underline-offset-4 decoration-2" href="page-register.php">Join the Elite</a>
            </p>
        </div>
    </div>
</main>
<div class="fixed bottom-0 left-0 w-full h-1/3 bg-gradient-to-t from-surface-dim to-transparent pointer-events-none z-10"></div>
<div class="fixed top-0 left-0 w-full h-[2px] bg-primary-container/10 z-50"></div>
<script>
function switchTab(tab) {
    const userFields   = document.getElementById('user-fields');
    const staffFields  = document.getElementById('staff-fields');
    const indicator    = document.getElementById('toggle-indicator');
    const userTab      = document.getElementById('user-tab');
    const employeeTab  = document.getElementById('employee-tab');

    if (tab === 'user') {
        userFields.classList.remove('hidden');
        staffFields.classList.add('hidden');
        indicator.style.transform = 'translateX(0)';
        userTab.classList.replace('text-on-surface-variant', 'text-on-primary-container');
        employeeTab.classList.replace('text-on-primary-container', 'text-on-surface-variant');

        userFields.querySelectorAll('input').forEach(i => i.disabled = false);
        staffFields.querySelectorAll('input').forEach(i => i.disabled = true);
    } else {
        staffFields.classList.remove('hidden');
        userFields.classList.add('hidden');
        indicator.style.transform = 'translateX(100%)';
        employeeTab.classList.replace('text-on-surface-variant', 'text-on-primary-container');
        userTab.classList.replace('text-on-primary-container', 'text-on-surface-variant');

        staffFields.querySelectorAll('input').forEach(i => i.disabled = false);
        userFields.querySelectorAll('input').forEach(i => i.disabled = true);
    }

    userFields.querySelectorAll('input[name="tab"]').forEach(i => i.value = 'user');
    staffFields.querySelectorAll('input[name="tab"]').forEach(i => i.value = 'staff');
}
switchTab('user');
<?php if (isset($_POST['tab']) && $_POST['tab'] === 'staff'): ?>
switchTab('staff');
<?php endif; ?>
</script>
<?php
get_footer();
unset($GLOBALS['hide_global_header']);
unset($GLOBALS['hide_global_footer']);
?>

