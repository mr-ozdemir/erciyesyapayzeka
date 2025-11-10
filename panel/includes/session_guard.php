<?php
// panel/includes/session_guard.php
// Tüm panel sayfalarında oturum (login) kontrolü, idle/absolute timeout ve güvenli sonlandırma

if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

// Varsayılanlar (config.php içinde tanımlıysa onları kullanır)
if (!defined('SESSION_IDLE_TIMEOUT'))      define('SESSION_IDLE_TIMEOUT', 600);   // 10 dk hareketsizlikte düşür
if (!defined('SESSION_ABSOLUTE_TIMEOUT'))  define('SESSION_ABSOLUTE_TIMEOUT', 28800); // 8 saat mutlak süre (opsiyon)
if (!defined('SESSION_REGEN_INTERVAL'))    define('SESSION_REGEN_INTERVAL', 300); // 5 dk’da bir session id yenile

// Yardımcı: güvenli yönlendirme (redirect() yoksa header() kullan)
function _guard_redirect_login_timeout() {
    if (function_exists('set_flash_message')) {
        set_flash_message('Oturumunuz zaman aşımına uğradı. Lütfen tekrar giriş yapın.', 'warning');
    }
    if (function_exists('redirect')) {
        redirect('login.php?timeout=1');
    } else {
        header('Location: login.php?timeout=1');
    }
    exit;
}

// 1) Zorunlu: giriş yapılmış mı?
if (empty($_SESSION['user_id'])) {
    // login değilse login sayfasına
    if (function_exists('set_flash_message')) {
        set_flash_message('Bu sayfayı görüntülemek için giriş yapmalısınız.', 'warning');
    }
    if (function_exists('redirect')) {
        redirect('login.php');
    } else {
        header('Location: login.php');
    }
    exit;
}

$now = time();

// 2) İlk giriş / aktivite damgalarını hazırla
$_SESSION['login_time']     = $_SESSION['login_time']     ?? $now;
$_SESSION['last_activity']  = $_SESSION['last_activity']  ?? $now;
$_SESSION['last_regen']     = $_SESSION['last_regen']     ?? $now;

// 3) MUTLAK SÜRE (istiyorsan kapatmak için 0 yap)
if (SESSION_ABSOLUTE_TIMEOUT > 0 && ($now - $_SESSION['login_time']) > SESSION_ABSOLUTE_TIMEOUT) {
    $uid = $_SESSION['user_id'] ?? null;
    if (function_exists('log_activity') && $uid) { @log_activity($uid, 'logout', 'Oturum süresi (absolute) doldu'); }
    session_unset();
    session_destroy();
    _guard_redirect_login_timeout();
}

// 4) HAREKETSİZLİK SÜRESİ (idle timeout)
if (SESSION_IDLE_TIMEOUT > 0 && ($now - $_SESSION['last_activity']) > SESSION_IDLE_TIMEOUT) {
    $uid = $_SESSION['user_id'] ?? null;
    if (function_exists('log_activity') && $uid) { @log_activity($uid, 'logout', 'Zaman aşımı ile çıkış'); }
    session_unset();
    session_destroy();
    _guard_redirect_login_timeout();
}

// 5) Güvenlik: periyodik session id yenile (fixation önlemi)
if (($now - $_SESSION['last_regen']) > SESSION_REGEN_INTERVAL) {
    @session_regenerate_id(true);
    $_SESSION['last_regen'] = $now;
}

// 6) Aktivite damgasını güncelle (HER istekte)
$_SESSION['last_activity'] = $now;
