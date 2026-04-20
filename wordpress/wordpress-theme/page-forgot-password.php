<?php
require_once 'functions.php';
$page_title = 'Forgot Password';
$GLOBALS['hide_global_header'] = true;
$GLOBALS['hide_global_footer'] = true;

$error = null;
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
<main class="min-h-screen flex items-center justify-center px-6 py-20">
    <section class="w-full max-w-md bg-surface-container rounded-3xl p-8 border border-outline-variant/20">
        <h1 class="font-headline text-3xl font-black uppercase tracking-tight mb-3">Forgot Password</h1>
        <p class="text-sm text-on-surface-variant mb-6">Introduce tu email y te enviaremos un enlace en la siguiente iteracion WP.</p>
        <?php show_error($error); ?>
        <?php show_success($success); ?>
        <form method="POST" action="page-forgot-password.php" class="space-y-4">
            <input type="email" name="email" class="w-full bg-surface-container-highest border border-outline-variant/30 rounded-xl px-4 py-3" placeholder="you@voltgym.com" required />
            <button type="submit" class="w-full kinetic-gradient text-on-primary-container py-3 rounded-full font-black uppercase tracking-widest text-xs">
                Send Reset Link
            </button>
        </form>
        <a href="front-page.php" class="inline-block mt-6 text-xs font-black uppercase tracking-wider text-primary-container hover:underline">Back to login</a>
    </section>
</main>
<?php
get_footer();
unset($GLOBALS['hide_global_header'], $GLOBALS['hide_global_footer']);

