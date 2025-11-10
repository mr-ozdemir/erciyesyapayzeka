<?php
// includes/sidebar.php - Sol Menü (Rol Bazlı)

// DOĞRU YOLLAR (iki klasör yukarı)
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';

if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

/* --- Yalnızca yoksa tanımlanan küçük yardımcılar (tasarıma dokunmaz) --- */
if (!function_exists('_canon_role')) {
    function _canon_role(string $s): string {
        $s = mb_strtolower($s, 'UTF-8');
        $map = ['ö'=>'o','ğ'=>'g','ç'=>'c','ş'=>'s','ı'=>'i','ü'=>'u','Ö'=>'o','Ğ'=>'g','Ç'=>'c','Ş'=>'s','İ'=>'i','Ü'=>'u'];
        $s = strtr($s, $map);
        if (in_array($s, ['admin','yonetici','uye'], true)) return $s;
        if (strpos($s, 'admin') !== false) return 'admin';
        if (strpos($s, 'yonet') !== false) return 'yonetici';
        return 'uye';
    }
}
if (!function_exists('is_admin')) {
    function is_admin(): bool {
        $u = function_exists('get_current_user_data') ? get_current_user_data() : ($_SESSION['user'] ?? []);
        $rk = _canon_role($u['rol_adi'] ?? '');
        return $rk === 'admin';
    }
}
if (!function_exists('is_uye')) {
    function is_uye(): bool {
        $u = function_exists('get_current_user_data') ? get_current_user_data() : ($_SESSION['user'] ?? []);
        $rk = _canon_role($u['rol_adi'] ?? '');
        return in_array($rk, ['admin','yonetici','uye'], true);
    }
}
/* --- /yardımcılar --- */

$current_page = basename($_SERVER['PHP_SELF']);
$user = function_exists('get_current_user_data') ? get_current_user_data() : ($_SESSION['user'] ?? []);
if (!is_array($user)) { $user = []; }
$user['ad_soyad'] = $user['ad_soyad'] ?? 'Kullanıcı';
?>

<div class="sidebar">
    <div class="sidebar-logo">
        <i class="fas fa-robot"></i>
        <h2>YZ Kulübü Panel</h2>
        <p>Yönetim Sistemi</p>
    </div>

    <nav class="sidebar-nav">
        <a href="dashboard.php" class="nav-item <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <?php if (is_uye()): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Etkinlikler</div>
        </div>

        <a href="etkinlikler.php" class="nav-item <?= $current_page == 'etkinlikler.php' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt"></i>
            <span>Tüm Etkinlikler</span>
        </a>

        <?php if (is_admin()): ?>
        <a href="etkinlik_ekle.php" class="nav-item <?= $current_page == 'etkinlik_ekle.php' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i>
            <span>Yeni Etkinlik</span>
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <?php if (is_uye()): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Katılımcılar</div>
        </div>

        <a href="kayitlar.php" class="nav-item <?= $current_page == 'kayitlar.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Kayıtlar</span>
        </a>
        <?php endif; ?>

        <?php if (is_admin()): ?>
        <div class="sidebar-section">
            <div class="sidebar-section-title">Yönetim</div>
        </div>

        <a href="kullanicilar.php" class="nav-item <?= $current_page == 'kullanicilar.php' ? 'active' : '' ?>">
            <i class="fas fa-user-cog"></i>
            <span>Kullanıcılar</span>
        </a>

        <a href="aktivite.php" class="nav-item <?= $current_page == 'aktivite.php' ? 'active' : '' ?>">
            <i class="fas fa-history"></i>
            <span>Aktivite Geçmişi</span>
        </a>
        <?php endif; ?>

        <div class="sidebar-section">
            <div class="sidebar-section-title">Sistem</div>
        </div>

        <a href="logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Çıkış Yap</span>
        </a>
    </nav>

    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <?= strtoupper(substr($user['ad_soyad'], 0, 1)) ?>
        </div>
        <div class="sidebar-user-name"><?= htmlspecialchars($user['ad_soyad']) ?></div>
        <div class="sidebar-user-role">
            <?php
            switch(_canon_role($user['rol_adi'] ?? '')) {
                case 'admin':
                    echo '👑 Yönetici';
                    break;
                case 'yonetici':
                    echo '👨‍💼 Etkinlik Yöneticisi';
                    break;
                case 'uye':
                    echo '👤 Kulüp Üyesi';
                    break;
                default:
                    echo htmlspecialchars($user['rol_adi'] ?? '');
            }
            ?>
        </div>
    </div>
</div>
