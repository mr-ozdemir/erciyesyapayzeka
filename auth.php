<?php
// auth.php - Kullanıcı Yetkilendirme ve Oturum Yönetimi (UYUMLU SÜRÜM)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------------------------------------------------------
   Yardımcılar
--------------------------------------------------------- */

/**
 * Veritabanı bağlantısını döndürür (PDO).
 */
function _db()
{
    if (class_exists('Database')) {
        return Database::getInstance()->getConnection();
    }
    // config.php içinde $pdo tanımlı olabilir
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }
    throw new Exception('Veritabanı bağlantısı bulunamadı (Database/PDO).');
}

/**
 * Rol adını (TR) kanonik koda çevirir: Admin/Yönetici/Üye → admin/yonetici/uye
 */
function _role_canonical(?string $rolAdi): string
{
    if ($rolAdi === null) return 'uye';
    $t = mb_strtolower($rolAdi, 'UTF-8');
    $t = str_replace(['ö','ğ','ç','ş','ı','ü','Ö','Ğ','Ç','Ş','İ','Ü'], ['o','g','c','s','i','u','o','g','c','s','i','u'], $t);
    if ($t === 'admin') return 'admin';
    if ($t === 'yonetici') return 'yonetici';
    if ($t === 'uye') return 'uye';
    if (strpos($t, 'admin') !== false) return 'admin';
    if (strpos($t, 'yonet') !== false) return 'yonetici';
    return 'uye';
}

/**
 * Oturum açık mı?
 */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/* ---------------------------------------------------------
   Giriş / Çıkış ve Yetki
--------------------------------------------------------- */

/**
 * Kullanıcı giriş yapmış mı kontrol et
 */
function check_login()
{
    if (!is_logged_in()) {
        set_flash_message('Bu sayfayı görüntülemek için giriş yapmalısınız.', 'warning');
        redirect('login.php');
    }
}

/**
 * Kullanıcının yetkisini kontrol et (rol bazlı)
 * $allowed_roles: ['admin','yonetici','uye'] gibi kanonik kodlar ya da TR adlar gelebilir
 */
function check_permission($allowed_roles = [])
{
    if (!is_logged_in()) {
        set_flash_message('Bu sayfaya erişim yetkiniz yok.', 'danger');
        redirect('login.php');
    }

    if (!empty($allowed_roles)) {
        if (!is_array($allowed_roles)) $allowed_roles = [$allowed_roles];
        // Hepsini kanonik koda çevir
        $allowed_canon = array_map('_role_canonical', $allowed_roles);
        $user_role = get_current_user_role(); // kanonik
        if (!in_array($user_role, $allowed_canon, true)) {
            set_flash_message('Bu işlem için yetkiniz bulunmuyor.', 'danger');
            redirect('dashboard.php');
        }
    }
    return true;
}

/**
 * Kullanıcı giriş yap
 * - Eski sürümle uyumlu: login_user($kullanici_adi, $sifre)
 * - Yeni sürümle uyumlu: login_user($user_array)
 * Her iki kullanım da ['success'=>bool, 'message'=>string] döndürür (login.php beklentisine uygun).
 */
function login_user($user_or_username, $password = null): array
{
    try {
        // 1) Dizi geldiyse doğrudan oturumu kur
        if (is_array($user_or_username)) {
            $u = $user_or_username;
            $_SESSION['user_id']    = $u['id'];
            $_SESSION['user_name']  = $u['ad_soyad'] ?? '';
            $_SESSION['user_email'] = $u['email']    ?? '';
            // rol_adi TR olabilir → kanonik koda çevir
            $_SESSION['user_role']  = _role_canonical($u['rol_adi'] ?? 'uye');
            $_SESSION['login_time'] = time();

            log_activity($u['id'], 'login', 'Panele giriş yapıldı');
            return ['success' => true, 'message' => 'Giriş başarılı'];
        }

        // 2) Eski akış: kullanıcı adı/e-posta + şifre
        $usernameOrEmail = (string)$user_or_username;
        $passPlain       = (string)$password;

        if ($usernameOrEmail === '' || $passPlain === '') {
            return ['success' => false, 'message' => 'Kullanıcı adı ve şifre gereklidir.'];
        }

        $db = _db();
        $stmt = $db->prepare("
            SELECT 
                pk.id,
                pk.kullanici_adi,
                pk.sifre_hash,
                pk.ad_soyad,
                pk.email,
                pk.rol_id,
                r.rol_adi
            FROM panel_kullanicilar pk
            INNER JOIN roller r ON r.id = pk.rol_id
            WHERE pk.kullanici_adi = :u OR pk.email = :e
            LIMIT 1
        ");
        $stmt->execute([':u' => $usernameOrEmail, ':e' => $usernameOrEmail]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['sifre_hash']) || !password_verify($passPlain, $user['sifre_hash'])) {
            log_activity(null, 'failed_login', 'Başarısız giriş denemesi: ' . $usernameOrEmail);
            return ['success' => false, 'message' => 'Geçersiz kullanıcı adı veya şifre.'];
        }

        // Oturumu kur
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['user_name']  = $user['ad_soyad'] ?? '';
        $_SESSION['user_email'] = $user['email']    ?? '';
        $_SESSION['user_role']  = _role_canonical($user['rol_adi'] ?? 'uye');
        $_SESSION['login_time'] = time();

        // son_giris güncelle (opsiyonel)
        try {
            $up = $db->prepare("UPDATE panel_kullanicilar SET son_giris = NOW() WHERE id = :id");
            $up->execute([':id' => $user['id']]);
        } catch (Throwable $e) {
            // sessiz geç
        }

        log_activity($user['id'], 'login', 'Panele giriş yapıldı');
        return ['success' => true, 'message' => 'Giriş başarılı'];
    } catch (Exception $e) {
        error_log('login_user hata: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()];
    }
}

/**
 * Kullanıcı çıkış yap
 */
function logout_user()
{
    $user_id = $_SESSION['user_id'] ?? null;

    if ($user_id) {
        log_activity($user_id, 'logout', 'Kullanıcı çıkış yaptı');
    }

    session_unset();
    session_destroy();

    redirect('login.php');
}

/* ---------------------------------------------------------
   Kullanıcı verisi ve izinler
--------------------------------------------------------- */

/**
 * Mevcut kullanıcının verilerini al
 */
function get_current_user_data()
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    try {
        $db = _db();
        $stmt = $db->prepare("
            SELECT 
                pk.id,
                pk.ad_soyad,
                pk.email,
                pk.rol_id,
                r.rol_adi,
                pk.olusturma_tarihi
            FROM panel_kullanicilar pk
            INNER JOIN roller r ON r.id = pk.rol_id
            WHERE pk.id = ?
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['rol_kod'] = _role_canonical($row['rol_adi'] ?? null);
        }
        return $row ?: null;
    } catch (Exception $e) {
        error_log("Kullanıcı verisi alma hatası: " . $e->getMessage());
        return null;
    }
}

/**
 * Kullanıcının belirli bir yetkisi var mı? (örnek)
 */
function has_permission($permission)
{
    if (!isset($_SESSION['user_role'])) {
        return false;
    }

    $role = $_SESSION['user_role']; // kanonik: admin/yonetici/uye

    // Admin her şeyi yapabilir
    if ($role === 'admin') {
        return true;
    }

    // Yönetici çoğu şeyi yapabilir
    if ($role === 'yonetici') {
        $restricted = ['kullanici_sil', 'sistem_ayarlari'];
        return !in_array($permission, $restricted, true);
    }

    // Üye sadece okuma yapabilir
    if ($role === 'uye') {
        $allowed = ['etkinlik_goruntule', 'katilimci_goruntule'];
        return in_array($permission, $allowed, true);
    }

    return false;
}

/* ---------------------------------------------------------
   Flash mesajlar
--------------------------------------------------------- */

function set_flash_message($message, $type = 'info')
{
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type'    => $type
    ];
}

function get_flash_message()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/* ---------------------------------------------------------
   Aktivite log
   Şema: aktivite_loglari(id, kullanici_id, aksiyon, tablo_adi, kayit_id, detay, ip_adresi, olusturma_tarihi)
--------------------------------------------------------- */

function log_activity($user_id, $action, $description = '', $table = null, $record_id = null)
{
    try {
        $db = _db();
        $stmt = $db->prepare("
            INSERT INTO aktivite_loglari (
                kullanici_id,
                aksiyon,
                tablo_adi,
                kayit_id,
                detay,
                ip_adresi
            ) VALUES (:uid, :act, :tablo, :kid, :detay, :ip)
        ");

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $stmt->execute([
            ':uid'   => $user_id,
            ':act'   => $action,
            ':tablo' => $table,
            ':kid'   => $record_id,
            ':detay' => $description,
            ':ip'    => $ip
        ]);
    } catch (Exception $e) {
        // Hata logla ama akışı bozma
        error_log("Aktivite log hatası: " . $e->getMessage());
    }
}

/* ---------------------------------------------------------
   CSRF
--------------------------------------------------------- */

function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/* ---------------------------------------------------------
   Kısa yardımcılar
--------------------------------------------------------- */

function get_current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

function get_current_user_role()
{
    return $_SESSION['user_role'] ?? 'uye'; // her zaman kanonik
}

function is_admin()
{
    return get_current_user_role() === 'admin';
}

function is_manager_or_admin()
{
    return in_array(get_current_user_role(), ['admin','yonetici'], true);
}

/* Bazı sayfalarda bu isim kullanılıyorsa geriye dönük uyum için alias */
if (!function_exists('is_yonetici_or_admin')) {
    function is_yonetici_or_admin() {
        return is_manager_or_admin();
    }
}
