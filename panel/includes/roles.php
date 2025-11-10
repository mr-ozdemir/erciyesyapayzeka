<?php
// panel/includes/roles.php (REDECLARE-SAFE)
// Bu dosya get_current_user_data() FONKSİYONUNU TANIMLAMAZ.
// Sadece rol yardımcılarını sağlar ve mevcut fonksiyonları kullanır.

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

/**
 * Dahili yardımcı: mevcut kullanıcıyı güvenli al.
 * Burada get_current_user_data() varsa onu kullanır; yoksa $_SESSION['user']'a bakar.
 */
if (!function_exists('_roles_current_user')) {
    function _roles_current_user(): array {
        if (function_exists('get_current_user_data')) {
            $u = get_current_user_data();
            if (!is_array($u)) $u = [];
        } else {
            $u = (isset($_SESSION['user']) && is_array($_SESSION['user'])) ? $_SESSION['user'] : [];
        }
        // Güvenli varsayılanlar
        $u['ad_soyad'] = $u['ad_soyad'] ?? 'Kullanıcı';
        $u['rol_adi']  = $u['rol_adi']  ?? 'Üye';
        // Kanonik rol kodu oluştur
        $u['rol_kod']  = $u['rol_kod']  ?? (function_exists('role_canonical') ? role_canonical($u['rol_adi']) : (function () use ($u) {
            $t = mb_strtolower((string)$u['rol_adi'], 'UTF-8');
            $t = str_replace(['ö','ğ','ç','ş','ı','ü'], ['o','g','c','s','i','u'], $t);
            if ($t === 'admin') return 'admin';
            if ($t === 'yonetici') return 'yonetici';
            return 'uye';
        })());
        return $u;
    }
}

/**
 * Veritabanı rol adını kanonik koda çevirir: Admin/Yönetici/Üye → admin/yonetici/uye
 */
if (!function_exists('role_canonical')) {
    function role_canonical(?string $rolAdi): string {
        if ($rolAdi === null) return 'uye';
        $t = mb_strtolower($rolAdi, 'UTF-8');
        $t = str_replace(['ö','ğ','ç','ş','ı','ü'], ['o','g','c','s','i','u'], $t);
        if ($t === 'admin') return 'admin';
        if ($t === 'yonetici') return 'yonetici';
        if ($t === 'uye') return 'uye';
        if (strpos($t, 'admin') !== false) return 'admin';
        if (strpos($t, 'yonet') !== false) return 'yonetici';
        return 'uye';
    }
}

if (!function_exists('is_admin')) {
    function is_admin(): bool {
        $u = _roles_current_user();
        return $u['rol_kod'] === 'admin';
    }
}

if (!function_exists('is_yonetici_or_admin')) {
    function is_yonetici_or_admin(): bool {
        $u = _roles_current_user();
        return in_array($u['rol_kod'], ['admin','yonetici'], true);
    }
}

if (!function_exists('is_uye')) {
    function is_uye(): bool {
        $u = _roles_current_user();
        return in_array($u['rol_kod'], ['admin','yonetici','uye'], true);
    }
}
