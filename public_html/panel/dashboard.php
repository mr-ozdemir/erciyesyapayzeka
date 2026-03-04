<?php
// dashboard.php - Ana Dashboard


require_once '../config.php';
require_once '../auth.php';

check_login();

$page_title = 'Dashboard';
$user = get_current_user_data();

try {
    $db = Database::getInstance()->getConnection();
    
    // Etkinlik ve kayıt istatistikleri
$stats = [
    'aktif_etkinlik_sayisi'    => 0,
    'toplam_etkinlik_sayisi'   => 0,
    'onaylanan_kayit_sayisi'   => 0,
    'bekleyen_kayit_sayisi'    => 0,
    'toplam_kayit_sayisi'      => 0,
];

try {
    // Aktif ve toplam etkinlik
    $stmt = $db->query("SELECT 
        SUM(CASE WHEN aktif = 1 THEN 1 ELSE 0 END) AS aktif,
        COUNT(*) AS toplam
        FROM etkinlikler
    ");
    $row = $stmt->fetch();
    $stats['aktif_etkinlik_sayisi']  = (int)$row['aktif'];
    $stats['toplam_etkinlik_sayisi'] = (int)$row['toplam'];

    // Kayıt istatistikleri
    $stmt = $db->query("SELECT 
        SUM(CASE WHEN durum = 'onaylandi' THEN 1 ELSE 0 END) AS onaylanan,
        SUM(CASE WHEN durum = 'beklemede' THEN 1 ELSE 0 END) AS bekleyen,
        COUNT(*) AS toplam
        FROM kayitlar
    ");
    $row = $stmt->fetch();
    $stats['onaylanan_kayit_sayisi'] = (int)$row['onaylanan'];
    $stats['bekleyen_kayit_sayisi']  = (int)$row['bekleyen'];
    $stats['toplam_kayit_sayisi']    = (int)$row['toplam'];

} catch (PDOException $e) {
    // Geliştirme için görebilirsin, istersen yorum satırına al:
    // echo "Dashboard istatistik hatası: " . $e->getMessage();
}

    
    // Son kayıtlar
    $stmt = $db->query("
        SELECT k.*, e.etkinlik_adi 
        FROM kayitlar k
        JOIN etkinlikler e ON k.etkinlik_id = e.id
        ORDER BY k.kayit_tarihi DESC
        LIMIT 5
    ");
    $son_kayitlar = $stmt->fetchAll();
    
    // Yaklaşan etkinlikler
    $stmt = $db->query("
        SELECT * FROM etkinlikler 
        WHERE aktif = TRUE AND baslangic_tarihi >= CURDATE()
        ORDER BY baslangic_tarihi ASC
        LIMIT 5
    ");
    $yaklasan_etkinlikler = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("Hata: " . $e->getMessage());
}

include 'includes/header.php';
?>

<!-- İstatistik Kartları -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-label">Aktif Etkinlikler</div>
        <div class="stat-value"><?= $stats['aktif_etkinlik_sayisi'] ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            Toplam: <?= $stats['toplam_etkinlik_sayisi'] ?>
        </div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-label">Onaylanan Kayıtlar</div>
        <div class="stat-value"><?= $stats['onaylanan_kayit_sayisi'] ?></div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up"></i>
            Bu hafta
        </div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-label">Bekleyen Kayıtlar</div>
        <div class="stat-value"><?= $stats['bekleyen_kayit_sayisi'] ?></div>
        <div class="stat-change">
            <i class="fas fa-minus"></i>
            İşlem bekliyor
        </div>
    </div>

    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-label">Toplam Kayıt</div>
        <div class="stat-value"><?= $stats['toplam_kayit_sayisi'] ?></div>
        <div class="stat-change positive">
            <i class="fas fa-check"></i>
            Tüm zamanlar
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Yaklaşan Etkinlikler -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-calendar-alt"></i>
                Yaklaşan Etkinlikler
            </h3>
            <a href="etkinlikler.php" class="btn btn-sm btn-primary">Tümünü Gör</a>
        </div>
        <div class="card-body">
            <?php if (count($yaklasan_etkinlikler) > 0): ?>
                <?php foreach ($yaklasan_etkinlikler as $etkinlik): ?>
                    <div style="padding: 15px; border-bottom: 1px solid #e9ecef; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong><?= htmlspecialchars($etkinlik['etkinlik_adi']) ?></strong>
                            <div style="font-size: 13px; color: #6c757d; margin-top: 5px;">
                                <i class="fas fa-calendar"></i> <?= format_tarih($etkinlik['baslangic_tarihi']) ?>
                                <?php if ($etkinlik['saat']): ?>
                                    | <i class="fas fa-clock"></i> <?= htmlspecialchars($etkinlik['saat']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="badge badge-success">Aktif</span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center" style="padding: 40px 0;">Yaklaşan etkinlik bulunmuyor</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Son Kayıtlar -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-plus"></i>
                Son Kayıtlar
            </h3>
            <a href="kayitlar.php" class="btn btn-sm btn-primary">Tümünü Gör</a>
        </div>
        <div class="card-body">
            <?php if (count($son_kayitlar) > 0): ?>
                <?php foreach ($son_kayitlar as $kayit): ?>
                    <div style="padding: 15px; border-bottom: 1px solid #e9ecef;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 5px;">
                            <strong><?= htmlspecialchars($kayit['ad_soyad']) ?></strong>
                            <span class="badge badge-<?= $kayit['durum'] == 'onaylandi' ? 'success' : 'warning' ?>">
                                <?= ucfirst($kayit['durum']) ?>
                            </span>
                        </div>
                        <div style="font-size: 13px; color: #6c757d;">
                            <div><i class="fas fa-graduation-cap"></i> <?= htmlspecialchars($kayit['etkinlik_adi']) ?></div>
                            <div style="margin-top: 3px;">
                                <i class="fas fa-clock"></i> <?= format_tarih_saat($kayit['kayit_tarihi'], 'd.m.Y H:i') ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center" style="padding: 40px 0;">Henüz kayıt bulunmuyor</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Hızlı Aksiyonlar -->
<?php if (is_admin()): ?> 
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bolt"></i>
            Hızlı Aksiyonlar
        </h3>
    </div>
    <div class="card-body">
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="etkinlik_ekle.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Etkinlik Ekle
            </a>
            <a href="kullanicilar.php" class="btn btn-success">
                <i class="fas fa-user-plus"></i> Kullanıcı Ekle
            </a>
            <a href="kayitlar.php?durum=beklemede" class="btn btn-warning">
                <i class="fas fa-clock"></i> Bekleyen Kayıtlar (<?= $stats['bekleyen_kayit_sayisi'] ?>)
            </a>
            <a href="../index" target="_blank" class="btn btn-info">
                <i class="fas fa-external-link-alt"></i> Siteyi Görüntüle
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
