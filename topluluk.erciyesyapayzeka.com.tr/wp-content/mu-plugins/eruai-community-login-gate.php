<?php
/**
 * Plugin Name: ERU AI Community Login Gate
 * Description: Topluluk alt domaini için özel giriş ekranı ve özel kullanıcı tablosu ile kimlik doğrulama.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('option_users_can_register', '__return_zero');
add_filter('pre_option_users_can_register', '__return_zero');

/**
 * WP core login ekranını dışarıdan kullandırtmayalım.
 */
add_action('login_init', function () {
    $action = isset($_REQUEST['action']) ? sanitize_key((string) $_REQUEST['action']) : 'login';

    if ($action === 'logout') {
        return;
    }

    wp_safe_redirect(home_url('/giris/'));
    exit;
});

/**
 * Session başlat (özel auth için).
 */
add_action('init', function () {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
}, 1);

/**
 * Özel tablo adı.
 */
function eruai_community_users_table(): string
{
    global $wpdb;
    return $wpdb->prefix . 'community_users';
}

/**
 * Sadece yönetim rollerini içeri al.
 */
function eruai_community_role_allowed(string $role): bool
{
    $allowedRoles = ['admin', 'yonetici', 'editor'];
    return in_array(mb_strtolower($role, 'UTF-8'), $allowedRoles, true);
}

/**
 * Oturumdaki kullanıcıyı döndür.
 */
function eruai_get_session_user(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return null;
    }

    $user = $_SESSION['eruai_community_user'] ?? null;
    return is_array($user) ? $user : null;
}

function eruai_set_session_user(array $user): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }

    $_SESSION['eruai_community_user'] = [
        'id' => (int) ($user['id'] ?? 0),
        'kullanici_adi' => (string) ($user['kullanici_adi'] ?? ''),
        'ad_soyad' => (string) ($user['ad_soyad'] ?? ''),
        'rol' => (string) ($user['rol'] ?? ''),
        'login_time' => time(),
    ];
}

function eruai_logout_session_user(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    unset($_SESSION['eruai_community_user']);
}

/**
 * Login denetimi ve route kontrolü.
 */
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $requestPath = is_string($requestPath) ? trim($requestPath, '/') : '';

    $homePath = trim((string) parse_url(home_url('/'), PHP_URL_PATH), '/');
    if ($homePath !== '' && str_starts_with($requestPath, $homePath . '/')) {
        $requestPath = substr($requestPath, strlen($homePath) + 1);
    } elseif ($homePath !== '' && $requestPath === $homePath) {
        $requestPath = '';
    }

    if ($requestPath === 'giris') {
        eruai_render_login_page();
    }

    if ($requestPath === 'cikis') {
        eruai_logout_session_user();
        wp_safe_redirect(home_url('/giris/'));
        exit;
    }

    if (!eruai_get_session_user()) {
        wp_safe_redirect(home_url('/giris/'));
        exit;
    }
});

/**
 * Kullanıcıyı özel tablodan username ile çek.
 */
function eruai_find_user_by_username(string $username): ?array
{
    global $wpdb;

    $table = eruai_community_users_table();
    $sql = $wpdb->prepare(
        "SELECT id, kullanici_adi, sifre_hash, ad_soyad, email, rol, aktif FROM {$table} WHERE kullanici_adi = %s LIMIT 1",
        $username
    );

    $row = $wpdb->get_row($sql, ARRAY_A);

    return is_array($row) ? $row : null;
}

/**
 * Login sonrası son giriş güncelle.
 */
function eruai_mark_last_login(int $userId): void
{
    global $wpdb;
    $table = eruai_community_users_table();

    $wpdb->update(
        $table,
        ['son_giris' => current_time('mysql')],
        ['id' => $userId],
        ['%s'],
        ['%d']
    );
}

function eruai_render_login_page(): void
{
    if (eruai_get_session_user()) {
        wp_safe_redirect(home_url('/'));
        exit;
    }

    $errorMessage = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nonce = isset($_POST['eruai_login_nonce']) ? (string) $_POST['eruai_login_nonce'] : '';

        if (!wp_verify_nonce($nonce, 'eruai_login')) {
            $errorMessage = 'Güvenlik doğrulaması başarısız oldu. Lütfen tekrar deneyin.';
        } else {
            $username = isset($_POST['log']) ? sanitize_user(wp_unslash((string) $_POST['log'])) : '';
            $password = isset($_POST['pwd']) ? (string) $_POST['pwd'] : '';

            if ($username === '' || $password === '') {
                $errorMessage = 'Kullanıcı adı ve şifre zorunludur.';
            } else {
                $user = eruai_find_user_by_username($username);

                if (!$user || empty($user['sifre_hash']) || !password_verify($password, (string) $user['sifre_hash'])) {
                    $errorMessage = 'Kullanıcı adı veya şifre hatalı.';
                } elseif ((int) ($user['aktif'] ?? 0) !== 1) {
                    $errorMessage = 'Hesabınız pasif durumda. Yönetici ile iletişime geçin.';
                } elseif (!eruai_community_role_allowed((string) ($user['rol'] ?? ''))) {
                    $errorMessage = 'Bu alana giriş yetkiniz yok.';
                } else {
                    eruai_set_session_user($user);
                    eruai_mark_last_login((int) $user['id']);
                    wp_safe_redirect(home_url('/'));
                    exit;
                }
            }
        }
    }

    status_header(200);
    nocache_headers();

    $logoUrl = 'https://erciyesyapayzeka.com.tr/assets/img/logo/logo.png';
    ?>
    <!doctype html>
    <html lang="tr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Topluluk Girişi | Erciyes Yapay Zeka</title>
        <style>
            :root {
                --card: rgba(255,255,255,.95);
                --text: #0b1320;
                --muted: #5c6573;
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                color: var(--text);
                background:
                    linear-gradient(135deg, rgba(15,23,42,.88), rgba(12,20,48,.88)),
                    url('https://erciyesyapayzeka.com.tr/assets/img/banner/hero-bg-01.jpg') center/cover no-repeat;
                padding: 24px;
            }
            .card {
                width: min(430px, 100%);
                background: var(--card);
                border-radius: 20px;
                padding: 30px;
                box-shadow: 0 18px 45px rgba(0,0,0,.35);
                backdrop-filter: blur(4px);
            }
            .logo { text-align: center; margin-bottom: 14px; }
            .logo img { max-width: 90px; height: auto; }
            h1 { margin: 8px 0 4px; text-align: center; font-size: 22px; }
            p.sub { margin: 0 0 20px; text-align: center; color: var(--muted); font-size: 14px; }
            label { display:block; margin-bottom:6px; font-size:13px; font-weight:600; }
            input {
                width:100%; border:1px solid #d6dbe4; border-radius:12px;
                padding:12px 14px; margin-bottom:14px; font-size:14px;
            }
            input:focus { border-color:#3b82f6; outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.18); }
            .btn {
                width:100%; border:0; border-radius:12px; padding:12px;
                font-size:15px; font-weight:700; cursor:pointer; color:#fff;
                background: linear-gradient(135deg, #fb4f53 0%, #d93a6a 100%);
            }
            .error {
                margin-bottom:14px; background:#fee2e2; color:#991b1b;
                border:1px solid #fecaca; border-radius:12px; padding:10px 12px; font-size:13px;
            }
        </style>
    </head>
    <body>
    <main class="card" role="main">
        <div class="logo">
            <img src="<?php echo esc_url($logoUrl); ?>" alt="Erciyes Yapay Zeka Kulübü Logo">
        </div>
        <h1>Topluluk Yönetim Girişi</h1>
        <p class="sub">Kullanıcılar yalnızca yönetim tarafından eklenir.</p>

        <?php if ($errorMessage !== '') : ?>
            <div class="error"><?php echo esc_html($errorMessage); ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="on">
            <?php wp_nonce_field('eruai_login', 'eruai_login_nonce'); ?>
            <label for="log">Kullanıcı Adı</label>
            <input id="log" name="log" type="text" required>

            <label for="pwd">Şifre</label>
            <input id="pwd" name="pwd" type="password" required>

            <button class="btn" type="submit">Giriş Yap</button>
        </form>
    </main>
    </body>
    </html>
    <?php
    exit;
}
