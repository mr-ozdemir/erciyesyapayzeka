
<?php
// includes/header.php - Sayfa Başlığı
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

// 🔒 Tüm panel sayfalarında oturum & timeout koruması
require_once __DIR__ . '/session_guard.php';

$user = get_current_user_data();
// Güvenli varsayılanlar
if (!is_array($user)) { $user = []; }
if (!isset($user['ad_soyad'])) { $user['ad_soyad'] = 'Kullanıcı'; }
if (!isset($user['rol_adi']))  { $user['rol_adi']  = 'Üye'; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Admin Panel - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/panel.css">

    <!-- İstemci tarafı idle-logout (tasarımı etkilemez) -->
    <script>
    (function () {
        // PHP sabitinden süre (ms). config.php'de SESSION_IDLE_TIMEOUT tanımlıysa onu kullanır.
        var IDLE_MS = <?=
            (int)((defined('SESSION_IDLE_TIMEOUT') ? SESSION_IDLE_TIMEOUT : 600) * 1000);
        ?>;

        // Login dışı sayfada (panelde) kullanıcı hiç etkileşim yapmazsa süre dolunca logout
        var timer;
        function resetTimer() {
            clearTimeout(timer);
            timer = setTimeout(function () {
                // Sunucu tarafı da zaten süre dolduysa düşürecek; bu sadece UX hızlandırma
                window.location.href = 'logout.php?timeout=1';
            }, IDLE_MS);
        }

        ['click','mousemove','keydown','scroll','touchstart'].forEach(function (ev) {
            document.addEventListener(ev, resetTimer, { passive: true });
        });

        // Sayfa yüklenince başlat
        resetTimer();
    })();
    </script>
</head>
<body>
    <div class="wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="page-header">
                <div class="page-title">
                    <h1><?= isset($page_title) ? $page_title : 'Dashboard' ?></h1>
                    <?php if (isset($breadcrumb)): ?>
                    <div class="breadcrumb">
                        <a href="dashboard.php"><i class="fas fa-home"></i> Ana Sayfa</a>
                        <?php foreach ($breadcrumb as $item): ?>
                            <?php if (isset($item['url'])): ?>
                                / <a href="<?= $item['url'] ?>"><?= $item['title'] ?></a>
                            <?php else: ?>
                                / <?= $item['title'] ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="page-actions">
                    <div class="header-user">
                        <div class="header-user-avatar">
                            <?= strtoupper(substr($user['ad_soyad'], 0, 1)) ?>
                        </div>
                        <div class="header-user-info">
                            <div class="header-user-name"><?= htmlspecialchars($user['ad_soyad']) ?></div>
                            <div class="header-user-role"><?= htmlspecialchars($user['rol_adi']) ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="content">
                <?php
                // Flash mesajları göster
                if (isset($_SESSION['flash_message'])) {
                    $flash = $_SESSION['flash_message'];
                    echo '<div class="alert alert-' . $flash['type'] . '">
                            <i class="fas fa-' . ($flash['type'] == 'success' ? 'check-circle' : 'exclamation-circle') . '"></i>
                            ' . htmlspecialchars($flash['message']) . '
                          </div>';
                    unset($_SESSION['flash_message']);
                }
                ?>
