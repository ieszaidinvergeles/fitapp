<?php
/**
 * functions.php — Theme Functions & API Hub
 * ------------------------------------------
 * Central file for all theme helper functions, template tags, and API
 * communication wrappers. Mirrors the role of WordPress's functions.php.
 *
 * SRP: Each function group has a single responsibility.
 * OCP: New helpers can be added without modifying existing ones.
 * DIP: All page templates depend on this abstraction, never on raw PHP
 *      superglobals or cURL directly.
 */

define('API_BASE', 'http://nginx:8000/api/v1');
define('THEME_DIR', __DIR__);
define('THEME_NAME', 'Volt Gym');

if (session_status() === PHP_SESSION_NONE) {
    // Isolate WP client session from the legacy client running on same host.
    session_name('VOLTGYM_WPSESSID');
    session_start();
}

// ─────────────────────────────────────────────────────────────────
// WORDPRESS-STYLE TEMPLATE INCLUDE TAGS
// ─────────────────────────────────────────────────────────────────

/**
 * Load and render the theme header template.
 * Mimics WordPress get_header().
 *
 * @param string|null $name Optional template suffix (e.g. 'staff' loads header-staff.php).
 * @return void
 */
function voltgym_get_header(?string $name = null): void
{
    $file = THEME_DIR . '/header' . ($name ? "-{$name}" : '') . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

/**
 * Load and render the theme footer template.
 * Mimics WordPress get_footer().
 *
 * @param string|null $name Optional template suffix (e.g. 'staff' loads footer-staff.php).
 * @return void
 */
function voltgym_get_footer(?string $name = null): void
{
    $file = THEME_DIR . '/footer' . ($name ? "-{$name}" : '') . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
}

/**
 * Load and render the theme sidebar template.
 * Mimics WordPress get_sidebar().
 *
 * @param string|null $name Optional template suffix.
 * @return void
 */
function voltgym_get_sidebar(?string $name = null): void
{
    $file = THEME_DIR . '/sidebar' . ($name ? "-{$name}" : '') . '.php';
    if (file_exists($file)) {
        require $file;
    }
}

/**
 * Load and render a reusable template part.
 * Mimics WordPress get_template_part().
 *
 * @param string      $slug Template slug (e.g. 'template-parts/nav').
 * @param string|null $name Optional name suffix (e.g. 'client' loads template-parts/nav-client.php).
 * @return void
 */
function voltgym_get_template_part(string $slug, ?string $name = null): void
{
    $file = THEME_DIR . '/' . $slug . ($name ? "-{$name}" : '') . '.php';
    if (file_exists($file)) {
        require $file;
    }
}

// ─────────────────────────────────────────────────────────────────
// WORDPRESS-STYLE TEMPLATE TAGS (bloginfo, the_title, etc.)
// ─────────────────────────────────────────────────────────────────

/**
 * Output blog/theme information values.
 * Mimics WordPress bloginfo().
 *
 * @param string $show The information key to retrieve.
 * @return void
 */
// function bloginfo(string $show): void
// {
//     $info = [
//         'name'        => THEME_NAME,
//         'description' => 'Cliente WordPress para Volt Gym - despierta el trueno',
//         'version'     => '1.0.0',
//         'charset'     => 'UTF-8',
//         'language'    => 'en',
//     ];
//     echo htmlspecialchars($info[$show] ?? '');
// }

// /**
//  * Output the page title with optional blog name suffix.
//  * Mimics WordPress wp_title().
//  *
//  * @param string $sep       Separator between page title and blog name.
//  * @param bool   $display   Whether to echo or return.
//  * @param string $seplocation Direction of the separator ('left' or 'right').
//  * @return string|void
//  */
// function wp_title(string $sep = '|', bool $display = true, string $seplocation = 'right')
// {
//     global $page_title;
//     $title = !empty($page_title) ? $page_title : THEME_NAME;
//     $full  = ($seplocation === 'right')
//         ? THEME_NAME . ' ' . $sep . ' ' . $title
//         : $title . ' ' . $sep . ' ' . THEME_NAME;

//     if (!empty($page_title)) {
//         if ($display) {
//             echo htmlspecialchars($full);
//         } else {
//             return htmlspecialchars($full);
//         }
//     } else {
//         if ($display) {
//             echo htmlspecialchars(THEME_NAME);
//         } else {
//             return htmlspecialchars(THEME_NAME);
//         }
//     }
// }

// /**
//  * Output the current user's display name.
//  * Mimics WordPress the_author().
//  *
//  * @return void
//  */
// function the_author(): void
// {
//     echo h($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'User');
// }

// /**
//  * Return the current user's display name.
//  * Mimics WordPress get_the_author().
//  *
//  * @return string
//  */
// function get_the_author(): string
// {
//     return h($_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'User');
// }

// /**
//  * Enqueue a CSS stylesheet link into the page head.
//  * Mimics WordPress wp_enqueue_style() — in standalone mode it just echoes the link.
//  *
//  * @param string $handle Unique handle identifier.
//  * @param string $src    URL to the stylesheet.
//  * @return void
//  */
// function wp_enqueue_style(string $handle, string $src): void
// {
//     echo '<link rel="stylesheet" id="' . htmlspecialchars($handle) . '-css" href="' . htmlspecialchars($src) . '">' . PHP_EOL;
// }

// /**
//  * Returns the stylesheet directory URI (root of the theme).
//  * Mimics WordPress get_stylesheet_directory_uri().
//  *
//  * @return string
//  */
// function get_stylesheet_directory_uri(): string
// {
//     $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
//     $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
//     $dir      = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
//     return $protocol . '://' . $host . $dir;
// }

// ─────────────────────────────────────────────────────────────────
// API COMMUNICATION FUNCTIONS
// ─────────────────────────────────────────────────────────────────

/**
 * Send an HTTP request to the Laravel API backend.
 *
 * @param string     $method   HTTP verb: GET | POST | PUT | DELETE.
 * @param string     $endpoint API path e.g. '/auth/login'.
 * @param array|null $body     JSON-encodable request body.
 * @param bool       $auth     Whether to inject the Bearer token from session.
 * @return array Decoded JSON response as associative array.
 */
function fitapp_request(string $method, string $endpoint, ?array $body = null, bool $auth = false): array
{
    $url     = API_BASE . $endpoint;
    $headers = ['Content-Type: application/json', 'Accept: application/json'];

    if ($auth && isset($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return ['result' => false, 'message' => ['general' => 'Could not initialize HTTP client (cURL).']];
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  strtoupper($method));
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT,        10);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        $detail = $curlErr !== '' ? " cURL: {$curlErr}" : '';
        return ['result' => false, 'message' => ['general' => 'Could not connect to the API.' . $detail]];
    }

    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        return ['result' => false, 'message' => ['general' => "API returned non-JSON (HTTP $httpCode)."]];
    }

    return $decoded;
}

/**
 * Send a GET request to the API.
 *
 * @param string $endpoint API path.
 * @param bool   $auth     Include Bearer token.
 * @return array
 */
function api_get(string $endpoint, bool $auth = false): array
{
    return fitapp_request('GET', $endpoint, null, $auth);
}

/**
 * Send a POST request to the API.
 *
 * @param string $endpoint API path.
 * @param array  $body     Request body.
 * @param bool   $auth     Include Bearer token.
 * @return array
 */
function api_post(string $endpoint, array $body = [], bool $auth = false): array
{
    return fitapp_request('POST', $endpoint, $body, $auth);
}

/**
 * Send a PUT request to the API.
 *
 * @param string $endpoint API path.
 * @param array  $body     Request body.
 * @param bool   $auth     Include Bearer token.
 * @return array
 */
function api_put(string $endpoint, array $body = [], bool $auth = false): array
{
    return fitapp_request('PUT', $endpoint, $body, $auth);
}

/**
 * Send a DELETE request to the API.
 *
 * @param string $endpoint API path.
 * @param bool   $auth     Include Bearer token.
 * @return array
 */
function api_delete(string $endpoint, bool $auth = false): array
{
    return fitapp_request('DELETE', $endpoint, null, $auth);
}

// ─────────────────────────────────────────────────────────────────
// AUTHENTICATION GUARDS
// ─────────────────────────────────────────────────────────────────

/**
 * Check whether the current visitor is authenticated.
 *
 * @return bool
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['token']);
}

/**
 * Redirect to the login page if not authenticated.
 * Mimics WordPress auth_redirect().
 *
 * @return void
 */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . home_url('/'));
        exit;
    }
}

/**
 * Require a staff-portal role (admin, manager, assistant, staff).
 * Redirects to the client dashboard if the user does not qualify.
 *
 * @return void
 */
function require_advanced(): void
{
    require_login();
    if (!is_advanced()) {
        header('Location: ' . get_role_home_path());
        exit;
    }
}

/**
 * Require a user-management role (admin, manager, assistant).
 * Redirects to the staff dashboard for other logged-in staff users.
 *
 * @return void
 */
function require_user_management(): void
{
    require_login();
    if (!can_manage_members()) {
        header('Location: ' . get_role_home_path());
        exit;
    }
}

/**
 * Require the admin role specifically.
 * Redirects to the client dashboard for non-admins.
 *
 * @return void
 */
function require_admin(): void
{
    require_login();
    if (get_user_role() !== 'admin') {
        header('Location: ' . get_role_home_path());
        exit;
    }
}

/**
 * Return the current user's role string.
 *
 * @return string
 */
function get_user_role(): string
{
    return $_SESSION['user']['role'] ?? '';
}

/**
 * Check whether the current user belongs to the staff portal.
 *
 * @return bool
 */
function is_advanced(): bool
{
    return in_array(get_user_role(), ['admin', 'manager', 'assistant', 'staff'], true);
}

/**
 * Check whether the current user is an admin.
 *
 * @return bool
 */
function is_fitapp_admin(): bool
{
    return get_user_role() === 'admin';
}

/**
 * Check whether the current user is a manager.
 *
 * @return bool
 */
function is_manager(): bool
{
    return get_user_role() === 'manager';
}

/**
 * Check whether the current user is an assistant.
 *
 * @return bool
 */
function is_assistant(): bool
{
    return get_user_role() === 'assistant';
}

/**
 * Check whether the current user is staff.
 *
 * @return bool
 */
function is_staff(): bool
{
    return get_user_role() === 'staff';
}

/**
 * Check whether the current user can manage member accounts.
 *
 * @return bool
 */
function can_manage_members(): bool
{
    return in_array(get_user_role(), ['admin', 'manager', 'assistant'], true);
}

/**
 * Check whether the current user belongs to client-facing roles.
 *
 * @return bool
 */
function is_client_role(): bool
{
    return in_array(get_user_role(), ['client', 'user_online'], true);
}

/**
 * Resolve the default home page path for current role.
 * Staff-portal roles go to staff dashboard; client-facing roles go to client dashboard.
 *
 * @return string
 */
function get_role_home_path(): string
{
    return is_advanced()
        ? home_url('/?pagename=staff-dashboard')
        : home_url('/?pagename=client-dashboard');
}

// ─────────────────────────────────────────────────────────────────
// UI FEEDBACK HELPERS
// ─────────────────────────────────────────────────────────────────

/**
 * Render a styled error notice if the message is not null.
 *
 * @param string|null $msg Error message to display.
 * @return void
 */
function show_error(?string $msg): void
{
    if ($msg) {
        echo '<div class="fitapp-notice fitapp-notice--error"><strong>Error: </strong>'
            . htmlspecialchars($msg) . '</div>';
    }
}

/**
 * Render a styled success notice if the message is not null.
 *
 * @param string|null $msg Success message to display.
 * @return void
 */
function show_success(?string $msg): void
{
    if ($msg) {
        echo '<div class="fitapp-notice fitapp-notice--success">'
            . htmlspecialchars($msg) . '</div>';
    }
}

/**
 * Render a consistent in-theme placeholder page while sections are migrated.
 *
 * @param string $title
 * @param string $description
 * @param string $active
 * @param bool   $staffNav
 * @return void
 */
function render_wp_placeholder_page(string $title, string $description, string $active = '', bool $staffNav = false): void
{
    global $page_title;
    $page_title = $title;

    $GLOBALS['hide_global_header'] = true;
    $GLOBALS['hide_global_footer'] = true;
    voltgym_get_header();
    ?>
    <main class="pt-24 pb-32 px-6 max-w-5xl mx-auto min-h-screen">
        <section class="mb-10">
            <h1 class="font-headline text-5xl md:text-6xl font-black tracking-tighter uppercase"><?= h($title) ?></h1>
            <p class="text-on-surface-variant mt-3"><?= h($description) ?></p>
        </section>
        <section class="bg-surface-container rounded-3xl border border-outline-variant/20 p-8">
            <p class="text-sm text-on-surface-variant">
                Esta seccion ya pertenece al cliente WP y se esta migrando visualmente.
                La logica principal del flujo queda dentro de este cliente.
            </p>
            <div class="mt-6 flex gap-3">
                <a href="<?= esc_url(get_role_home_path()) ?>" class="kinetic-gradient text-on-primary-container px-5 py-3 rounded-full text-xs font-black uppercase tracking-widest">
                    Volver al panel
                </a>
                <a href="<?= esc_url(home_url('/')) ?>" class="px-5 py-3 rounded-full border border-outline-variant/40 text-xs font-black uppercase tracking-widest">
                    Inicio
                </a>
            </div>
        </section>
    </main>
    <?php
    voltgym_get_template_part('template-parts/nav', $staffNav ? 'staff' : 'client');
    voltgym_get_footer();
    unset($GLOBALS['hide_global_header'], $GLOBALS['hide_global_footer']);
}

/**
 * Start a consistent internal WP app page shell.
 *
 * @param string $title
 * @param bool   $staffNav
 * @return void
 */
function wp_app_page_start(string $title, bool $staffNav = false): void
{
    global $page_title;
    $page_title = $title;
    $GLOBALS['hide_global_header'] = true;
    $GLOBALS['hide_global_footer'] = true;
    voltgym_get_header();
    ?>
    <header class="fixed top-0 z-40 w-full bg-[#0d0f08] border-b border-outline-variant/20 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary-container">bolt</span>
            <span class="font-headline font-black uppercase tracking-tight text-primary-container">Volt Gym</span>
            <span class="text-[10px] px-2 py-1 rounded-full bg-surface-container text-on-surface-variant uppercase tracking-widest font-black"><?= h(get_user_role(), 'guest') ?></span>
        </div>
        <a href="<?= esc_url(home_url('/?pagename=logout')) ?>" class="text-xs font-black uppercase tracking-widest text-on-surface-variant hover:text-primary-container transition-colors">Logout</a>
    </header>
    <main class="pt-24 pb-32 px-6 max-w-6xl mx-auto min-h-screen">
        <nav class="mb-6 overflow-x-auto no-scrollbar">
            <div class="flex gap-2 min-w-max">
                <?php if ($staffNav): ?>
                    <a href="<?= esc_url(home_url('/?pagename=staff-dashboard')) ?>" class="px-4 py-2 rounded-full bg-surface-container-high text-xs font-black uppercase tracking-wider">Dashboard</a>
                    <a href="<?= esc_url(home_url('/?pagename=staff-attendance')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Attendance</a>
                    <a href="<?= esc_url(home_url('/?pagename=staff-manage-classes')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Classes</a>
                    <a href="<?= esc_url(home_url('/?pagename=staff-manage-routines')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Routines</a>
                    <a href="<?= esc_url(home_url('/?pagename=staff-rooms')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Rooms</a>
                    <?php if (can_manage_members()): ?>
                        <a href="<?= esc_url(home_url('/?pagename=staff-admin-users')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Users</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= esc_url(home_url('/?pagename=client-dashboard')) ?>" class="px-4 py-2 rounded-full bg-surface-container-high text-xs font-black uppercase tracking-wider">Dashboard</a>
                    <a href="<?= esc_url(home_url('/?pagename=client-classes')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Classes</a>
                    <a href="<?= esc_url(home_url('/?pagename=client-bookings')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Bookings</a>
                    <a href="<?= esc_url(home_url('/?pagename=client-routines')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Routines</a>
                    <a href="<?= esc_url(home_url('/?pagename=client-meal-schedule')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Meals</a>
                    <a href="<?= esc_url(home_url('/?pagename=client-settings')) ?>" class="px-4 py-2 rounded-full bg-surface-container text-xs font-black uppercase tracking-wider">Settings</a>
                <?php endif; ?>
            </div>
        </nav>
        <h1 class="font-headline text-5xl font-black uppercase tracking-tight mb-6"><?= h($title) ?></h1>
    <?php
}

/**
 * End a consistent internal WP app page shell.
 *
 * @param bool $staffNav
 * @return void
 */
function wp_app_page_end(bool $staffNav = false): void
{
    ?>
    </main>
    <?php
    voltgym_get_template_part('template-parts/nav', $staffNav ? 'staff' : 'client');
    voltgym_get_footer();
    unset($GLOBALS['hide_global_header'], $GLOBALS['hide_global_footer']);
}

/**
 * Extract the human-readable message from an API response.
 * Handles both flat strings and nested validation error arrays.
 *
 * @param array $response Full decoded API response.
 * @return string|null
 */
function api_message(array $response): ?string
{
    $msg = $response['message'] ?? null;

    if (is_string($msg)) {
        return $msg;
    }

    if (is_array($msg)) {
        if (isset($msg['general'])) {
            return $msg['general'];
        }

        $parts = [];
        foreach ($msg as $field => $value) {
            $parts[] = is_array($value)
                ? "$field: " . implode(', ', $value)
                : "$field: $value";
        }
        return implode(' | ', $parts);
    }

    return null;
}

/**
 * Safely escape and return a value for HTML output.
 * Equivalent to WordPress esc_html().
 *
 * @param mixed  $value   The value to sanitize.
 * @param string $default Fallback when value is empty.
 * @return string
 */
function h($value, string $default = '—'): string
{
    return htmlspecialchars((string)($value ?: $default));
}

/**
 * Return a sanitized URL for use in href attributes.
 * Equivalent to WordPress esc_url().
 *
 * @param string $url Raw URL.
 * @return string
 */
// function esc_url(string $url): string
// {
//     return htmlspecialchars(filter_var($url, FILTER_SANITIZE_URL));
// }

// // ─────────────────────────────────────────────────────────────────
// // CACHE FUNCTIONS (Transient-like for standalone)
// // ─────────────────────────────────────────────────────────────────

// /**
//  * Set a transient cache value.
//  * Mimics WordPress set_transient().
//  *
//  * @param string $key        Cache key.
//  * @param mixed  $value      Value to cache.
//  * @param int    $expiration Expiration time in seconds (default 1 hour).
//  * @return bool
//  */
// function set_transient(string $key, $value, int $expiration = 3600): bool
// {
//     $cache_dir = THEME_DIR . '/cache';
//     if (!is_dir($cache_dir)) {
//         mkdir($cache_dir, 0755, true);
//     }
//     $cache_file = $cache_dir . '/' . md5($key) . '.cache';
//     $data = [
//         'value' => $value,
//         'expires' => time() + $expiration,
//     ];
//     return file_put_contents($cache_file, serialize($data)) !== false;
// }

// /**
//  * Get a transient cache value.
//  * Mimics WordPress get_transient().
//  *
//  * @param string $key Cache key.
//  * @return mixed|false
//  */
// function get_transient(string $key)
// {
//     $cache_file = THEME_DIR . '/cache/' . md5($key) . '.cache';
//     if (file_exists($cache_file)) {
//         $data = unserialize(file_get_contents($cache_file));
//         if ($data['expires'] > time()) {
//             return $data['value'];
//         } else {
//             unlink($cache_file);
//         }
//     }
//     return false;
// }

// /**
//  * Delete a transient cache value.
//  * Mimics WordPress delete_transient().
//  *
//  * @param string $key Cache key.
//  * @return bool
//  */
// function delete_transient(string $key): bool
// {
//     $cache_file = THEME_DIR . '/cache/' . md5($key) . '.cache';
//     if (file_exists($cache_file)) {
//         return unlink($cache_file);
//     }
//     return true;
// }
