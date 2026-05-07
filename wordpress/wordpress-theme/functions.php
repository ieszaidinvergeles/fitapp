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

/**
 * Enable support for Post Thumbnails on posts and pages.
 * Necessary for Custom Post Types to show the Featured Image field.
 */
add_action('after_setup_theme', function() {
    add_theme_support('post-thumbnails');
});

// ─────────────────────────────────────────────────────────────────
// WORDPRESS PAGE AUTO-PROVISIONING
// Creates all required pages in the WP database if they don't exist.
// ─────────────────────────────────────────────────────────────────

/**
 * All slugs that need a WordPress page entry.
 * Each slug maps to a page-{slug}.php template automatically.
 */
function voltgym_required_pages(): array
{
    return [
        'register'               => 'Register',
        'forgot-password'        => 'Forgot Password',
        'logout'                 => 'Logout',
        'client-dashboard'       => 'Client Dashboard',
        'client-memberships'     => 'Memberships',
        'client-classes'         => 'Classes',
        'client-bookings'        => 'My Bookings',
        'client-routines'        => 'My Routines',
        'client-routine'         => 'Routine Detail',
        'client-meal-schedule'   => 'Meal Schedule',
        'client-settings'        => 'Settings',
        'client-metrics'         => 'My Metrics',
        'client-notifications'   => 'Notifications',
        'client-favorites'       => 'My Favorites',
        'client-diet-plans'      => 'Diet Plans',
        'client-exercises'       => 'Exercises',
        'client-recipes'         => 'Recipes',
        'client-catalog'         => 'Catalog',
        'client-equipment'       => 'Equipment Vault',
        'staff-dashboard'        => 'Staff Dashboard',
        'staff-attendance'       => 'Attendance',
        'staff-manage-classes'   => 'Manage Classes',
        'staff-create-class'     => 'Create Class',
        'staff-edit-class'       => 'Edit Class',
        'staff-cancel-class'     => 'Cancel Class',
        'staff-class-bookings'   => 'Class Bookings',
        'staff-manage-routines'  => 'Manage Routines',
        'staff-create-routine'   => 'Create Routine',
        'staff-rooms'            => 'Rooms',
        'staff-notifications'    => 'Notifications',
        'staff-admin-users'      => 'Users',
        'staff-admin-user-create'=> 'Create User',
        'staff-admin-user-edit'  => 'Edit User',
    ];
}

/**
 * Create any missing required pages in the WordPress database.
 */
function voltgym_create_required_pages(): void
{
    foreach (voltgym_required_pages() as $slug => $title) {
        $existing = get_page_by_path($slug, OBJECT, 'page');
        if (!$existing) {
            wp_insert_post([
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => '',
            ]);
        }
    }
}

// Run automatically when the theme is activated.
add_action('after_switch_theme', 'voltgym_create_required_pages');

// Also allow a manual trigger via ?voltgym_setup=1.
add_action('init', function () {
    if (
        isset($_GET['voltgym_setup']) &&
        $_GET['voltgym_setup'] === '1'
    ) {
        voltgym_create_required_pages();
        // Redirect to home if not in admin
        if (!is_admin()) {
            wp_redirect(home_url('/?voltgym_pages_created=1'));
        } else {
            wp_redirect(admin_url('?voltgym_pages_created=1'));
        }
        exit;
    }
});

// Show an admin notice if pages are missing.
add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['voltgym_pages_created'])) {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Volt Gym:</strong> Todas las páginas del tema han sido creadas correctamente.</p></div>';
        return;
    }

    $missing = false;
    foreach (array_keys(voltgym_required_pages()) as $slug) {
        if (!get_page_by_path($slug, OBJECT, 'page')) {
            $missing = true;
            break;
        }
    }

    if ($missing) {
        $url = admin_url('?voltgym_setup=1');
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Volt Gym:</strong> Hay páginas del tema que no existen aún en WordPress. ';
        echo '<a href="' . esc_url($url) . '" class="button button-primary">Crear páginas ahora</a></p>';
        echo '</div>';
    }
});


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
 * Send a POST request with a file (multipart/form-data).
 */
function api_post_file(string $endpoint, string $fieldName, string $filePath, string $fileName, bool $auth = false): array
{
    $url = API_BASE . $endpoint;
    $headers = ['Accept: application/json'];

    if ($auth && isset($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return ['result' => false, 'message' => ['general' => 'Could not initialize HTTP client (cURL).']];
    }

    $cfile = new CURLFile($filePath, mime_content_type($filePath), $fileName);
    $data = [$fieldName => $cfile];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST,           true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS,     $data);
    curl_setopt($ch, CURLOPT_TIMEOUT,        15);

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return ['result' => false, 'message' => ['general' => 'Could not connect to the API.']];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return ['result' => false, 'message' => ['general' => "API returned non-JSON (HTTP $httpCode)."]];
    }

    return $decoded;
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

function fitapp_has_uploaded_file(?array $file): bool
{
    return $file
        && !empty($file['tmp_name'])
        && is_uploaded_file($file['tmp_name'])
        && (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
}

function fitapp_upload_error_message(?array $file): ?string
{
    if (!$file) {
        return null;
    }

    $error = (int)($file['error'] ?? UPLOAD_ERR_NO_FILE);

    if ($error === UPLOAD_ERR_OK || $error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    return match ($error) {
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE => 'La imagen es demasiado grande. Sube una imagen de maximo 2 MB.',
        UPLOAD_ERR_PARTIAL => 'La imagen no se subio completa. Intentalo otra vez.',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal de subida en PHP.',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo guardar la imagen temporalmente.',
        UPLOAD_ERR_EXTENSION => 'Una extension de PHP bloqueo la subida de la imagen.',
        default => 'No se pudo subir la imagen. Intentalo con otro archivo.',
    };
}

function fitapp_image_proxy_url(string $folder, int $id): string
{
    return home_url('/?fitapp_image_proxy=1&folder=' . rawurlencode($folder) . '&id=' . $id);
}

function fitapp_image_proxy_endpoint(string $folder, int $id): ?string
{
    $map = [
        'activities'       => '/activities/' . $id . '/image',
        'diet_plans'       => '/diet-plans/' . $id . '/image',
        'equipment'        => '/equipment/' . $id . '/image',
        'exercises'        => '/exercises/' . $id . '/image',
        'gyms'             => '/gyms/' . $id . '/logo',
        'membership_plans' => '/membership-plans/' . $id . '/image',
        'recipes'          => '/recipes/' . $id . '/image',
        'rooms'            => '/rooms/' . $id . '/image',
        'routines'         => '/routines/' . $id . '/image',
        'users'            => '/users/' . $id . '/photo',
    ];

    return $map[$folder] ?? null;
}

function fitapp_public_asset_url($url): string
{
    $url = trim((string)$url);

    if (
        $url === ''
        || $url === '-'
        || $url === '&mdash;'
        || $url === '&#8212;'
        || $url === '—'
        || $url === 'â€”'
        || $url === 'Ã¢â‚¬â€'
        || strtoupper($url) === 'NULL'
    ) {
        return '';
    }

    $path = parse_url($url, PHP_URL_PATH) ?: $url;
    if (preg_match('~^/?images/([^/]+)/(\d+)\.[a-z0-9]+$~i', $path, $matches)) {
        return fitapp_image_proxy_url(str_replace('-', '_', (string)$matches[1]), (int)$matches[2]);
    }

    if (preg_match('~/uploads/images/([^/]+)/(\d+)\.[a-z0-9]+$~i', $path, $matches)) {
        return fitapp_image_proxy_url(str_replace('-', '_', (string)$matches[1]), (int)$matches[2]);
    }

    if (preg_match('~/api/v1/([^/]+)/(\d+)/(image|logo|photo)$~i', $path, $matches)) {
        $resource = str_replace('-', '_', (string)$matches[1]);
        $folderMap = [
            'activities' => 'activities',
            'diet_plans' => 'diet_plans',
            'equipment' => 'equipment',
            'exercises' => 'exercises',
            'gyms' => 'gyms',
            'membership_plans' => 'membership_plans',
            'recipes' => 'recipes',
            'rooms' => 'rooms',
            'routines' => 'routines',
            'users' => 'users',
        ];

        if (isset($folderMap[$resource])) {
            return fitapp_image_proxy_url($folderMap[$resource], (int)$matches[2]);
        }
    }

    if (str_starts_with($url, 'http://nginx:8000')) {
        return 'http://localhost:8000' . substr($url, strlen('http://nginx:8000'));
    }

    if (preg_match('~^https?://(localhost|127\.0\.0\.1)/uploads/~i', $url)) {
        return preg_replace('~^https?://(localhost|127\.0\.0\.1)~i', 'http://localhost:8000', $url);
    }

    if (str_starts_with($url, '/uploads/')) {
        return 'http://localhost:8000' . $url;
    }

    if (str_starts_with($url, 'uploads/')) {
        return 'http://localhost:8000/' . $url;
    }

    return $url;
}

function fitapp_handle_image_proxy(): void
{
    if (empty($_GET['fitapp_image_proxy'])) {
        return;
    }

    $folder = preg_replace('/[^a-z0-9_]/i', '', (string)($_GET['folder'] ?? ''));
    $id = (int)($_GET['id'] ?? 0);
    $endpoint = $id > 0 ? fitapp_image_proxy_endpoint($folder, $id) : null;

    if (!$endpoint) {
        http_response_code(404);
        exit;
    }

    $headers = ['Accept: image/*'];
    if (!empty($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    }

    $ch = curl_init(API_BASE . $endpoint);
    if ($ch === false) {
        http_response_code(500);
        exit;
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = (string)(curl_getinfo($ch, CURLINFO_CONTENT_TYPE) ?: 'application/octet-stream');
    curl_close($ch);

    if ($body === false || $status < 200 || $status >= 300) {
        http_response_code($status >= 400 ? $status : 404);
        exit;
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: ' . $contentType);
    header('Cache-Control: private, max-age=300');
    echo $body;
    exit;
}

add_action('init', 'fitapp_handle_image_proxy');

function fitapp_render_image_or_placeholder(
    string $url,
    string $alt,
    string $imageClass = 'h-full w-full object-cover',
    string $placeholderClass = 'h-full w-full flex-col items-center justify-center text-center text-on-surface-variant',
    string $icon = 'image',
    string $label = 'No image'
): void {
    $visibleState = $url !== '' ? 'hidden' : 'flex';
    ?>
    <?php if ($url !== ''): ?>
        <img
            src="<?= esc_url($url) ?>"
            alt="<?= h($alt) ?>"
            class="<?= h($imageClass) ?>"
            onerror="this.classList.add('hidden'); if (this.nextElementSibling) this.nextElementSibling.classList.remove('hidden');"
        >
    <?php endif; ?>

    <div class="<?= h($visibleState . ' ' . $placeholderClass) ?>">
        <span class="material-symbols-outlined mb-1 text-3xl text-primary-container/70"><?= h($icon) ?></span>
        <span class="text-[10px] font-black uppercase tracking-wide text-on-surface-variant"><?= h($label) ?></span>
    </div>
    <?php
}

function fitapp_api_multipart_request(
    string $endpoint,
    array $fields = [],
    ?array $file = null,
    string $fileField = 'image',
    bool $auth = false,
    string $method = 'POST'
): array {
    $url = API_BASE . $endpoint;
    $headers = ['Accept: application/json'];

    if ($auth && isset($_SESSION['token'])) {
        $headers[] = 'Authorization: Bearer ' . $_SESSION['token'];
    }

    $postFields = $fields;
    $method = strtoupper($method);

    $uploadError = fitapp_upload_error_message($file);
    if ($uploadError !== null) {
        return ['result' => false, 'message' => ['general' => $uploadError]];
    }

    if ($method !== 'POST') {
        $postFields['_method'] = $method;
    }

    if (fitapp_has_uploaded_file($file)) {
        $postFields[$fileField] = new CURLFile(
            $file['tmp_name'],
            ($file['type'] ?? '') ?: (mime_content_type($file['tmp_name']) ?: 'application/octet-stream'),
            ($file['name'] ?? '') ?: $fileField
        );
    }

    $ch = curl_init($url);
    if ($ch === false) {
        return ['result' => false, 'message' => ['general' => 'Could not initialize HTTP client (cURL).']];
    }

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $raw = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
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

function fitapp_api_multipart_post(string $endpoint, array $fields = [], ?array $file = null, string $fileField = 'image', bool $auth = false): array
{
    return fitapp_api_multipart_request($endpoint, $fields, $file, $fileField, $auth, 'POST');
}

function fitapp_api_multipart_update(string $endpoint, array $fields = [], ?array $file = null, string $fileField = 'image', bool $auth = false): array
{
    return fitapp_api_multipart_request($endpoint, $fields, $file, $fileField, $auth, 'PUT');
}

function fitapp_render_image_dropzone(
    string $label,
    string $uploadText,
    string $inputId,
    string $dropzoneId,
    string $inputName = 'image',
    string $currentImage = '',
    string $previewAlt = 'Image preview',
    string $icon = 'image'
): void {
    $previewId = $inputId . 'Preview';
    $placeholderId = $inputId . 'Placeholder';
    $removeId = $inputId . 'Remove';
    ?>
    <div>
        <label class="mb-1.5 block text-sm font-medium text-on-surface-variant"><?= h($label) ?></label>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_180px]">
            <label
                id="<?= h($dropzoneId) ?>"
                class="flex min-h-[150px] cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high px-4 py-6 text-center transition hover:border-primary-container hover:bg-surface-container-highest"
            >
                <span class="material-symbols-outlined mb-2 text-4xl text-primary-container">upload</span>
                <span class="text-sm font-bold text-on-surface"><?= h($uploadText) ?></span>
                <span class="mt-1 text-xs text-on-surface-variant">JPG, PNG, WEBP or GIF</span>
                <span class="mt-1 text-[11px] text-on-surface-variant/70">Click or drag and drop</span>

                <input
                    id="<?= h($inputId) ?>"
                    type="file"
                    name="<?= h($inputName) ?>"
                    accept="image/*"
                    class="hidden"
                >
            </label>

            <div
                class="group relative flex h-[150px] items-center justify-center overflow-hidden rounded-2xl border border-dashed border-outline-variant/30 bg-surface-container-high transition hover:border-primary-container hover:bg-surface-container-highest"
            >
                <div id="<?= h($placeholderId) ?>" class="<?= $currentImage ? 'hidden' : 'flex' ?> flex-col items-center justify-center text-center text-on-surface-variant">
                    <span class="material-symbols-outlined mb-2 text-4xl text-on-surface-variant/60"><?= h($icon) ?></span>
                    <span class="text-xs font-bold">No image selected</span>
                </div>

                <img
                    id="<?= h($previewId) ?>"
                    src="<?= esc_url($currentImage) ?>"
                    alt="<?= h($previewAlt) ?>"
                    class="<?= $currentImage ? '' : 'hidden' ?> h-full w-full object-cover"
                >

                <button
                    id="<?= h($removeId) ?>"
                    type="button"
                    class="absolute inset-0 <?= $currentImage ? 'flex' : 'hidden' ?> items-center justify-center bg-black/55 opacity-0 transition group-hover:opacity-100"
                >
                    <span class="material-symbols-outlined rounded-full bg-error p-2 text-white">close</span>
                </button>
            </div>
        </div>
    </div>
    <?php
}

function fitapp_render_image_dropzone_script(string $inputId, string $dropzoneId): void
{
    $previewId = $inputId . 'Preview';
    $placeholderId = $inputId . 'Placeholder';
    $removeId = $inputId . 'Remove';
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('<?= h($inputId) ?>');
        const dropzone = document.getElementById('<?= h($dropzoneId) ?>');
        const preview = document.getElementById('<?= h($previewId) ?>');
        const placeholder = document.getElementById('<?= h($placeholderId) ?>');
        const removeBtn = document.getElementById('<?= h($removeId) ?>');

        if (!input || !dropzone || !preview || !placeholder || !removeBtn) return;

        function clearPreview() {
            input.value = '';
            preview.removeAttribute('src');
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            removeBtn.classList.add('hidden');
            removeBtn.classList.remove('flex');
        }

        function setPreview(file) {
            if (!file) {
                clearPreview();
                return;
            }

            if (!file.type.startsWith('image/')) {
                clearPreview();
                alert('Please select a valid image file.');
                return;
            }

            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
            removeBtn.classList.remove('hidden');
            removeBtn.classList.add('flex');
        }

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            setPreview(file);
        });

        dropzone.addEventListener('dragover', function (event) {
            event.preventDefault();
            dropzone.classList.add('border-primary-container', 'bg-surface-container-highest');
        });

        dropzone.addEventListener('dragleave', function () {
            dropzone.classList.remove('border-primary-container', 'bg-surface-container-highest');
        });

        dropzone.addEventListener('drop', function (event) {
            event.preventDefault();
            dropzone.classList.remove('border-primary-container', 'bg-surface-container-highest');

            const file = event.dataTransfer.files && event.dataTransfer.files[0];
            if (!file) return;

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            input.files = dataTransfer.files;
            setPreview(file);
        });

        removeBtn.addEventListener('click', clearPreview);
    });
    </script>
    <?php
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
    
    // Fetch fresh user data and notifications from dashboard (most reliable source)
    if (is_logged_in()) {
        $dashResponse = api_get('/dashboard', auth: true);
        $dashResult = $dashResponse['result'] ?? [];
        $user = !empty($dashResult['user']) ? $dashResult['user'] : ($_SESSION['user'] ?? []);
        $GLOBALS['unread_notifications_count'] = $dashResult['unread_notifications_count'] ?? 0;
    } else {
        $user = $_SESSION['user'] ?? [];
    }
    
    voltgym_get_header();
    ?>
    <header class="fixed top-0 z-40 w-full bg-[#0d0f08]/80 backdrop-blur-md flex justify-between items-center px-6 py-4 border-b border-white/5">
        <div class="flex items-center gap-4">
            <button id="open-client-menu" type="button" class="text-[#d4fb00] hover:bg-white/5 transition-colors p-2 rounded-xl active:scale-95 duration-150">
                <span class="material-symbols-outlined text-2xl">menu</span>
            </button>
            <h1 class="text-3xl font-black italic text-[#d4fb00] tracking-tighter font-headline uppercase">VOLT</h1>
        </div>
        <div class="flex items-center gap-4 relative">
            <!-- Profile Dropdown Trigger -->
            <button id="profile-dropdown-btn" class="flex items-center gap-3 p-1 pr-3 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 transition-all active:scale-95 relative">
                <div class="w-9 h-9 rounded-full overflow-hidden flex items-center justify-center border-2 border-[#d4fb00]/30 bg-surface-container-high text-[#d4fb00] font-headline font-black text-sm relative">
                    <?php if (!empty($user['profile_photo_url'])): ?>
                        <img src="<?= esc_url($user['profile_photo_url']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <?= strtoupper(substr(h($user['username'] ?? 'U'), 0, 1)) ?>
                    <?php endif; ?>
                    
                    <?php if (($GLOBALS['unread_notifications_count'] ?? 0) > 0): ?>
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-error rounded-full border-2 border-[#0d0f08] animate-pulse shadow-[0_0_10px_rgba(255,69,58,0.5)]"></span>
                    <?php endif; ?>
                </div>
                <span class="material-symbols-outlined text-zinc-500 text-sm transition-transform duration-300" id="profile-arrow">expand_more</span>
            </button>

            <!-- Dropdown Menu -->
            <div id="profile-dropdown-menu" class="hidden absolute top-full right-0 mt-3 w-56 bg-[#1a1c14] border border-white/10 rounded-[1.5rem] shadow-2xl overflow-hidden z-50 animate-in fade-in slide-in-from-top-2 duration-200">
                <div class="p-4 border-b border-white/5 bg-white/5">
                    <p class="text-[10px] text-[#d4fb00] font-black uppercase tracking-widest mb-0.5">@<?= h($user['username'] ?? 'athlete') ?></p>
                    <p class="text-sm font-bold text-white truncate"><?= h($user['full_name'] ?? 'Athlete Profile') ?></p>
                </div>
                <div class="p-2">
                    <a href="<?= esc_url(home_url('/?pagename=client-memberships')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-zinc-400 hover:bg-[#d4fb00] hover:text-black transition-all group">
                        <span class="material-symbols-outlined text-lg opacity-50 group-hover:opacity-100">workspace_premium</span>
                        My Membership
                    </a>
                    <a href="<?= esc_url(home_url('/?pagename=client-metrics')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-zinc-400 hover:bg-[#d4fb00] hover:text-black transition-all group">
                        <span class="material-symbols-outlined text-lg opacity-50 group-hover:opacity-100">straighten</span>
                        Body Metrics
                    </a>
                    <a href="<?= esc_url(home_url('/?pagename=client-favorites')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-zinc-400 hover:bg-[#d4fb00] hover:text-black transition-all group">
                        <span class="material-symbols-outlined text-lg opacity-50 group-hover:opacity-100">star</span>
                        My Favorites
                    </a>
                    <a href="<?= esc_url(home_url('/?pagename=client-friends')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-zinc-400 hover:bg-[#d4fb00] hover:text-black transition-all group">
                        <span class="material-symbols-outlined text-lg opacity-50 group-hover:opacity-100">group</span>
                        My Friends
                    </a>
                    <a href="<?= esc_url(home_url('/?pagename=client-notifications')); ?>" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold text-zinc-400 hover:bg-[#d4fb00] hover:text-black transition-all group">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-lg opacity-50 group-hover:opacity-100">notifications</span>
                            Notifications
                        </div>
                        <?php 
                        // Try to get unread count from global data if available
                        $globalNotifCount = $GLOBALS['unread_notifications_count'] ?? 0;
                        if ($globalNotifCount > 0): ?>
                            <span class="bg-error text-white text-[10px] px-1.5 py-0.5 rounded-full"><?= (int)$globalNotifCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= esc_url(home_url('/?pagename=client-settings')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-zinc-400 hover:bg-[#d4fb00] hover:text-black transition-all group">
                        <span class="material-symbols-outlined text-lg opacity-50 group-hover:opacity-100">settings</span>
                        Account Settings
                    </a>
                </div>
                <div class="p-2 border-t border-white/5">
                    <a href="<?= esc_url(home_url('/?pagename=logout')); ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold text-error/70 hover:bg-error/10 hover:text-error transition-all group">
                        <span class="material-symbols-outlined text-lg opacity-50 group-hover:opacity-100">logout</span>
                        Sign Out
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div id="client-menu-overlay" class="hidden fixed inset-0 z-[70] bg-black/70 backdrop-blur-sm"></div>
    <aside id="client-menu-drawer" class="fixed top-0 left-0 h-full w-80 max-w-[85vw] z-[80] -translate-x-full transition-transform duration-300 bg-surface-container p-6 border-r border-outline-variant/30 flex flex-col">
        <div class="flex items-center justify-between mb-8">
            <h3 class="font-headline font-black uppercase text-primary-container tracking-tight">Volt Gym</h3>
            <button id="close-client-menu" type="button" class="text-on-surface-variant hover:text-primary-container">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <nav class="space-y-2 flex-1">
            <?php
            $activePage = $GLOBALS['active'] ?? '';
            $linkClass = "block px-4 py-3 rounded-xl font-bold uppercase tracking-wider text-xs transition-colors ";
            $inactiveCls = $linkClass . "text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface";
            $activeCls = $linkClass . "bg-primary-container text-on-primary-container shadow-lg shadow-primary-container/20";
            ?>
            <?php if ($staffNav): ?>
                <a href="<?= esc_url(home_url('/?pagename=staff-dashboard')) ?>" class="<?= $activePage === 'staff-dashboard' ? $activeCls : $inactiveCls ?>">Dashboard</a>
                <a href="<?= esc_url(home_url('/?pagename=staff-attendance')) ?>" class="<?= $activePage === 'staff-attendance' ? $activeCls : $inactiveCls ?>">Attendance</a>
                <a href="<?= esc_url(home_url('/?pagename=staff-manage-classes')) ?>" class="<?= $activePage === 'staff-manage-classes' ? $activeCls : $inactiveCls ?>">Classes</a>
                <a href="<?= esc_url(home_url('/?pagename=staff-manage-routines')) ?>" class="<?= $activePage === 'staff-manage-routines' ? $activeCls : $inactiveCls ?>">Routines</a>
                <a href="<?= esc_url(home_url('/?pagename=staff-rooms')) ?>" class="<?= $activePage === 'staff-rooms' ? $activeCls : $inactiveCls ?>">Rooms</a>
                <?php if (can_manage_members()): ?>
                    <a href="<?= esc_url(home_url('/?pagename=staff-admin-users')) ?>" class="<?= $activePage === 'staff-admin-users' ? $activeCls : $inactiveCls ?>">Users</a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= esc_url(home_url('/?pagename=client-dashboard')) ?>" class="<?= $activePage === 'dashboard' ? $activeCls : $inactiveCls ?>">Dashboard</a>

                <a href="<?= esc_url(home_url('/?pagename=client-memberships')) ?>" class="<?= $activePage === 'client-memberships' ? $activeCls : $inactiveCls ?>">Memberships</a>
                <a href="<?= esc_url(home_url('/?pagename=client-classes')) ?>" class="<?= $activePage === 'classes' ? $activeCls : $inactiveCls ?>">Classes</a>
                <a href="<?= esc_url(home_url('/?pagename=client-bookings')) ?>" class="<?= $activePage === 'bookings' ? $activeCls : $inactiveCls ?>">Bookings</a>
                <a href="<?= esc_url(home_url('/?pagename=client-routines')) ?>" class="<?= $activePage === 'routines' ? $activeCls : $inactiveCls ?>">Routines</a>
                <a href="<?= esc_url(home_url('/?pagename=client-catalog')) ?>" class="<?= $activePage === 'client-catalog' ? $activeCls : $inactiveCls ?>">Catalog</a>
                <a href="<?= esc_url(home_url('/?pagename=client-equipment')) ?>" class="<?= $activePage === 'equipment' ? $activeCls : $inactiveCls ?>">Equipment</a>
                <a href="<?= esc_url(home_url('/?pagename=client-recipes')) ?>" class="<?= $activePage === 'recipes' ? $activeCls : $inactiveCls ?>">Recipes</a>
                <a href="<?= esc_url(home_url('/?pagename=client-diet-plans')) ?>" class="<?= $activePage === 'diet-plans' ? $activeCls : $inactiveCls ?>">Diet Plans</a>
                <a href="<?= esc_url(home_url('/?pagename=client-meal-schedule')) ?>" class="<?= $activePage === 'meals' ? $activeCls : $inactiveCls ?>">Meals</a>
            <?php endif; ?>
        </nav>

    </aside>

    <main class="pt-24 pb-32 px-6 max-w-7xl mx-auto min-h-screen">
        <?php if ($title !== 'Member Dashboard'): ?>
            <div class="mb-8">
                <h1 class="font-headline font-black text-6xl md:text-8xl uppercase tracking-tighter italic"><?= h($title) ?></h1>
                <div class="h-1 w-12 bg-primary-container mt-2"></div>
            </div>
        <?php endif; ?>
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
    ?>
    <script>
    (() => {
        // Sidebar logic
        const overlay = document.getElementById('client-menu-overlay');
        const drawer = document.getElementById('client-menu-drawer');
        const openBtn = document.getElementById('open-client-menu');
        const closeBtn = document.getElementById('close-client-menu');
        if (overlay && drawer && openBtn && closeBtn) {
            const open = () => {
                overlay.classList.remove('hidden');
                setTimeout(() => drawer.classList.remove('-translate-x-full'), 10);
            };
            const close = () => {
                drawer.classList.add('-translate-x-full');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            };
            openBtn.addEventListener('click', open);
            closeBtn.addEventListener('click', close);
            overlay.addEventListener('click', close);
        }

        // Profile Dropdown logic
        const profileBtn = document.getElementById('profile-dropdown-btn');
        const profileMenu = document.getElementById('profile-dropdown-menu');
        const profileArrow = document.getElementById('profile-arrow');

        if (profileBtn && profileMenu) {
            const toggleMenu = (e) => {
                e.stopPropagation();
                const isHidden = profileMenu.classList.contains('hidden');
                if (isHidden) {
                    profileMenu.classList.remove('hidden');
                    profileArrow.style.transform = 'rotate(180deg)';
                } else {
                    profileMenu.classList.add('hidden');
                    profileArrow.style.transform = 'rotate(0deg)';
                }
            };

            const closeMenu = () => {
                profileMenu.classList.add('hidden');
                profileArrow.style.transform = 'rotate(0deg)';
            };

            profileBtn.addEventListener('click', toggleMenu);
            document.addEventListener('click', closeMenu);
            profileMenu.addEventListener('click', (e) => e.stopPropagation());
        }
    })();
    </script>
    <?php
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
function h($value, string $default = ''): string
{
    if ($value === null) {
        return htmlspecialchars($default);
    }

    $text = trim((string)$value);

    if ($text === '' || $text === '-' || $text === '—' || $text === 'â€”' || strtoupper($text) === 'NULL') {
        return htmlspecialchars($default);
    }

    return htmlspecialchars((string)$value);
}

/**
 * Return a sanitized URL for use in href attributes.
 * Equivalent to WordPress esc_url().
 * Guard: only defined when running outside WordPress (standalone PHP server).
 *
 * @param string $url Raw URL.
 * @return string
 */
if (!function_exists('esc_url')) {
    function esc_url(string $url): string
    {
        return htmlspecialchars(filter_var($url, FILTER_SANITIZE_URL));
    }
}

/**
 * Return the base URL of the standalone PHP client.
 * Equivalent to WordPress home_url().
 * Guard: only defined when running outside WordPress (standalone PHP server).
 *
 * @param string $path Optional path to append.
 * @return string
 */
if (!function_exists('home_url')) {
    function home_url(string $path = ''): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

        if ($path !== '' && $path !== '/') {
            $parsed = parse_url($path);
            $qs     = [];
            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $qs);
            }

            if (!empty($qs['pagename'])) {
                $slug     = $qs['pagename'];
                $filename = 'page-' . $slug . '.php';
                unset($qs['pagename']);
                $extra = !empty($qs) ? '?' . http_build_query($qs) : '';
                return $scheme . '://' . $host . $base . '/' . $filename . $extra;
            }

            return $scheme . '://' . $host . $base . $path;
        }

        return $scheme . '://' . $host . $base . '/';
    }
}

/**
 * Perform an HTTP redirect and stop execution.
 * Equivalent to WordPress wp_redirect() + exit.
 * Guard: only defined when running outside WordPress (standalone PHP server).
 *
 * @param string $location Target URL.
 * @param int    $status   HTTP status code (default 302).
 * @return void
 */
if (!function_exists('wp_redirect')) {
    function wp_redirect(string $location, int $status = 302): void
    {
        header('Location: ' . $location, true, $status);
        exit;
    }
}

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

/**
 * Convert a date string into a human-readable "time ago" format.
 *
 * @param string $datetime Date string compatible with strtotime().
 * @return string
 */
function time_ago(string $datetime): string
{
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 1) return 'just now';
    
    $intervals = [
        31536000 => 'year',
        2592000  => 'month',
        604800   => 'week',
        86400    => 'day',
        3600     => 'hour',
        60       => 'minute',
        1        => 'second',
    ];

    foreach ($intervals as $secs => $label) {
        $div = $diff / $secs;
        if ($div >= 1) {
            $round = round($div);
            return $round . ' ' . $label . ($round > 1 ? 's' : '') . ' ago';
        }
    }
    
    return $datetime;
}
