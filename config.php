<?php
// config.php - Genel Ayarlar ve Ortak Fonksiyonlar

// Hata Raporlama (Lokal Geliştirme)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Istanbul');

// ==================== SESSION AYARLARI ====================
// Tüm session ini ayarları BURADA olacak (session_start'tan ÖNCE)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);      // Lokalde 0, HTTPS canlıda 1
ini_set('session.cookie_lifetime', 0);    // Tarayıcı kapanınca session bitsin

// Panel oturum süresi (saniye)
define('SESSION_TIMEOUT', 900);           // 15 dakika

// ==================== VERİTABANI AYARLARI ====================
define('DB_HOST', '127.0.0.1');          // veya 'localhost'
define('DB_NAME', 'erciyesy_eruai');     // phpMyAdmin'deki DB adı
define('DB_USER', 'root');               // XAMPP varsayılan kullanıcı
define('DB_PASS', '');                   // XAMPP varsayılan şifre BOŞ
define('DB_CHARSET', 'utf8mb4');

// ==================== SITE AYARLARI ====================
define('SITE_URL', 'http://localhost/erciyesyapayzeka');
define('SITE_NAME', 'Erciyes Üniversitesi Yapay Zeka Kulübü');
define('ADMIN_EMAIL', 'admin@localhost');

// Panel URL
define('PANEL_URL', SITE_URL . '/panel');
define('MAX_LOGIN_ATTEMPTS', 5);

// Rol Seviyeleri
define('ROL_UYE', 1);
define('ROL_YONETICI', 2);
define('ROL_ADMIN', 3);

// ==================== DATABASE CLASS ====================
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Veritabanı bağlantı hatası: " . $e->getMessage() . "<br>Host: " . DB_HOST . "<br>DB: " . DB_NAME);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
}

// ==================== HELPER FUNCTIONS ====================

// Güvenli Input
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Email doğrulama
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Tarih formatlama
function format_tarih($tarih, $format = 'd.m.Y') {
    if (!$tarih) return '';
    return date($format, strtotime($tarih));
}

// Tarih ve Saat Formatlama
function format_tarih_saat($datetime, $format = 'd.m.Y H:i') {
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

// Slug Oluştur (URL için)
function create_slug($text) {
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    
    // Türkçe karakterleri dönüştür
    $search = array('ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'İ');
    $replace = array('c', 'g', 'i', 'o', 's', 'u', 'i');
    $text = str_replace($search, $replace, $text);
    
    // Özel karakterleri temizle
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    
    return $text;
}

// IP adresi
function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Proxy arkasından ilk IP
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    }
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

// JSON response (API için)
function json_response($success, $message, $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Yönlendirme
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

// Aktivite Logu
function log_aktivite($kullanici_id, $aksiyon, $tablo_adi = null, $kayit_id = null, $detay = null) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO aktivite_loglari (
                kullanici_id, aksiyon, tablo_adi, kayit_id, detay, ip_adresi, olusturma_tarihi
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $kullanici_id,
            $aksiyon,
            $tablo_adi,
            $kayit_id,
            $detay,
            get_user_ip()
        ]);
    } catch (PDOException $e) {
        error_log('log_aktivite Hatası: ' . $e->getMessage());
    }
}

// ==================== SESSION BAŞLAT ====================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================== GLOBAL DB BAĞLANTISI ====================
$db = Database::getInstance()->getConnection();
?>