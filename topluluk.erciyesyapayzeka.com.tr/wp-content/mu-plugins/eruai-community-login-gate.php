<?php
/**
 * Plugin Name: ERU AI Community Login Gate
 * Description: Topluluk alt domaini için özel giriş ekranı ve erciyesy_eruai panel kullanıcılarıyla kimlik doğrulama.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('option_users_can_register', '__return_zero');
add_filter('pre_option_users_can_register', '__return_zero');

add_action('login_init', function () {
    $action = isset($_REQUEST['action']) ? sanitize_key((string) $_REQUEST['action']) : 'login';

    if ($action === 'logout') {
        return;
    }

    wp_safe_redirect(home_url('/giris/'));
    exit;
});

add_action('init', function () {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
}, 1);

/**
 * XAMPP/canlı için auth DB ayarları.
 * Öncelik: wp-config.php içinde tanımlı COMMUNITY_AUTH_DB_* sabitleri.
 */
function eruai_auth_db_config(): array
{
    $host = defined('COMMUNITY_AUTH_DB_HOST') ? COMMUNITY_AUTH_DB_HOST : '127.0.0.1';
    $port = defined('COMMUNITY_AUTH_DB_PORT') ? (int) COMMUNITY_AUTH_DB_PORT : 3306;
    $name = defined('COMMUNITY_AUTH_DB_NAME') ? COMMUNITY_AUTH_DB_NAME : 'erciyesy_eruai';
    $user = defined('COMMUNITY_AUTH_DB_USER') ? COMMUNITY_AUTH_DB_USER : 'root';
    $pass = defined('COMMUNITY_AUTH_DB_PASS') ? COMMUNITY_AUTH_DB_PASS : '';
    $charset = defined('COMMUNITY_AUTH_DB_CHARSET') ? COMMUNITY_AUTH_DB_CHARSET : 'utf8mb4';

    return [
        'host' => $host,
        'port' => $port,
        'name' => $name,
        'user' => $user,
        'pass' => $pass,
        'charset' => $charset,
    ];
}

function eruai_auth_pdo(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = eruai_auth_db_config();
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $cfg['host'],
        $cfg['port'],
        $cfg['name'],
        $cfg['charset']
    );

    $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function eruai_community_role_allowed(string $role): bool
{
    $role = mb_strtolower($role, 'UTF-8');
    $role = str_replace(['ö', 'ğ', 'ç', 'ş', 'ı', 'ü'], ['o', 'g', 'c', 's', 'i', 'u'], $role);

    $allowedRoles = ['admin', 'yonetici'];
    return in_array($role, $allowedRoles, true);
}

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
        'rol_adi' => (string) ($user['rol_adi'] ?? ''),
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
 * erciyesy_eruai.panel_kullanicilar + roller üzerinden kullanıcı çek.
 */
function eruai_find_panel_user(string $username): ?array
{
    $pdo = eruai_auth_pdo();

    $stmt = $pdo->prepare(
        'SELECT pk.id, pk.kullanici_adi, pk.sifre_hash, pk.ad_soyad, pk.email, pk.aktif, r.rol_adi
         FROM panel_kullanicilar pk
         INNER JOIN roller r ON r.id = pk.rol_id
         WHERE pk.kullanici_adi = :u OR pk.email = :e
         LIMIT 1'
    );
    $stmt->execute([':u' => $username, ':e' => $username]);

    $row = $stmt->fetch();
    return is_array($row) ? $row : null;
}

function eruai_mark_panel_last_login(int $userId): void
{
    $pdo = eruai_auth_pdo();
    $stmt = $pdo->prepare('UPDATE panel_kullanicilar SET son_giris = NOW() WHERE id = :id');
    $stmt->execute([':id' => $userId]);
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
            $username = isset($_POST['log']) ? sanitize_text_field(wp_unslash((string) $_POST['log'])) : '';
            $password = isset($_POST['pwd']) ? (string) $_POST['pwd'] : '';

            if ($username === '' || $password === '') {
                $errorMessage = 'Kullanıcı adı ve şifre zorunludur.';
            } else {
                try {
                    $user = eruai_find_panel_user($username);

                    if (!$user || empty($user['sifre_hash']) || !password_verify($password, (string) $user['sifre_hash'])) {
                        $errorMessage = 'Kullanıcı adı veya şifre hatalı.';
                    } elseif ((int) ($user['aktif'] ?? 0) !== 1) {
                        $errorMessage = 'Hesabınız pasif durumda. Yönetici ile iletişime geçin.';
                    } elseif (!eruai_community_role_allowed((string) ($user['rol_adi'] ?? ''))) {
                        $errorMessage = 'Bu alana giriş yetkiniz yok.';
                    } else {
                        eruai_set_session_user($user);
                        eruai_mark_panel_last_login((int) $user['id']);
                        wp_safe_redirect(home_url('/'));
                        exit;
                    }
                } catch (Throwable $e) {
                    $cfg = eruai_auth_db_config();
                    $errorMessage = sprintf(
                        'Veri tabanı bağlantısı sağlanamadı. DB: %s @ %s:%d',
                        $cfg['name'],
                        $cfg['host'],
                        $cfg['port']
                    );
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
            :root { --card: rgba(255,255,255,.95); --text: #0b1320; --muted: #5c6573; }
            * { box-sizing: border-box; }
            body {
                margin: 0; min-height: 100vh; display: grid; place-items: center;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; color: var(--text);
                background: linear-gradient(135deg, rgba(15,23,42,.88), rgba(12,20,48,.88)),
                            url('https://erciyesyapayzeka.com.tr/assets/img/banner/hero-bg-01.jpg') center/cover no-repeat;
                padding: 24px;
            }
            .card { width: min(430px, 100%); background: var(--card); border-radius: 20px; padding: 30px; box-shadow: 0 18px 45px rgba(0,0,0,.35); }
            .logo { text-align: center; margin-bottom: 14px; }
            .logo img { max-width: 90px; height: auto; }
            h1 { margin: 8px 0 4px; text-align: center; font-size: 22px; }
            p.sub { margin: 0 0 20px; text-align: center; color: var(--muted); font-size: 14px; }
            label { display:block; margin-bottom:6px; font-size:13px; font-weight:600; }
            input { width:100%; border:1px solid #d6dbe4; border-radius:12px; padding:12px 14px; margin-bottom:14px; font-size:14px; }
            input:focus { border-color:#3b82f6; outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.18); }
            .btn { width:100%; border:0; border-radius:12px; padding:12px; font-size:15px; font-weight:700; cursor:pointer; color:#fff; background: linear-gradient(135deg, #fb4f53 0%, #d93a6a 100%); }
            .error { margin-bottom:14px; background:#fee2e2; color:#991b1b; border:1px solid #fecaca; border-radius:12px; padding:10px 12px; font-size:13px; }
        </style>
    </head>
    <body>
    <main class="card" role="main">
        <div class="logo"><img src="<?php echo esc_url($logoUrl); ?>" alt="Erciyes Yapay Zeka Kulübü Logo"></div>
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
