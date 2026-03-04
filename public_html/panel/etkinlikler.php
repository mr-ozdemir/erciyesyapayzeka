<?php
// etkinlikler.php - Etkinlik Listesi ve Yönetimi

require_once '../config.php';
require_once '../auth.php';
require_once __DIR__ . '/includes/roles.php'; // is_yonetici_or_admin, is_admin, is_uye

// Artık şu kontrol çalışır:
if (!is_yonetici_or_admin()) {
    http_response_code(403);
    echo 'Bu işlemi yapmak için yetkiniz yok.';
    exit;
}

check_login();

$page_title = 'Etkinlik Yönetimi';
$breadcrumb = [
    ['title' => 'Etkinlikler']
];

// Durum filtreleme
$durum_filtre = isset($_GET['durum']) ? $_GET['durum'] : 'all';

try {
    $db = Database::getInstance()->getConnection();
    
    // Etkinlikleri getir
    $query = "
        SELECT 
            e.*,
            pk.ad_soyad as olusturan_adi,
            (SELECT COUNT(*) FROM kayitlar WHERE etkinlik_id = e.id) as kayit_sayisi
        FROM etkinlikler e
        LEFT JOIN panel_kullanicilar pk ON e.olusturan_kullanici_id = pk.id
    ";
    
    if ($durum_filtre == 'aktif') {
        $query .= " WHERE e.aktif = TRUE";
    } elseif ($durum_filtre == 'pasif') {
        $query .= " WHERE e.aktif = FALSE";
    }
    
    $query .= " ORDER BY e.olusturma_tarihi DESC";
    
    $stmt = $db->query($query);
    $etkinlikler = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("Hata: " . $e->getMessage());
}

// Etkinlik silme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && is_admin()) {
    $etkinlik_id = (int)$_GET['id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM etkinlikler WHERE id = ?");
        $stmt->execute([$etkinlik_id]);
        
        log_aktivite($_SESSION['user_id'], 'delete', 'etkinlikler', $etkinlik_id, 'Etkinlik silindi');
        
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Etkinlik başarıyla silindi!'
        ];
        
        redirect('etkinlikler.php');
    } catch(PDOException $e) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Etkinlik silinirken hata oluştu!'
        ];
    }
}

// Etkinlik durumu değiştirme
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id']) && is_yonetici_or_admin()) {
    $etkinlik_id = (int)$_GET['id'];
    
    try {
        $stmt = $db->prepare("UPDATE etkinlikler SET aktif = NOT aktif WHERE id = ?");
        $stmt->execute([$etkinlik_id]);
        
        log_aktivite($_SESSION['user_id'], 'update', 'etkinlikler', $etkinlik_id, 'Etkinlik durumu değiştirildi');
        
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Etkinlik durumu güncellendi!'
        ];
        
        redirect('etkinlikler.php');
    } catch(PDOException $e) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Durum güncellenirken hata oluştu!'
        ];
    }
}

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-calendar-alt"></i>
            Tüm Etkinlikler (<?= count($etkinlikler) ?>)
        </h3>
        <div style="display: flex; gap: 10px;">
            <!-- Durum Filtreleme -->
            <select onchange="window.location.href='etkinlikler.php?durum=' + this.value" class="form-select" style="width: auto;">
                <option value="all" <?= $durum_filtre == 'all' ? 'selected' : '' ?>>Tüm Etkinlikler</option>
                <option value="aktif" <?= $durum_filtre == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="pasif" <?= $durum_filtre == 'pasif' ? 'selected' : '' ?>>Pasif</option>
            </select>
            
            <?php if (is_admin()): ?>
            <a href="etkinlik_ekle.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Etkinlik
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Arama -->
        <div style="margin-bottom: 20px;">
            <input 
                type="text" 
                id="searchInput" 
                class="form-control" 
                placeholder="🔍 Etkinlik ara..."
                onkeyup="searchTable()"
            >
        </div>

        <div class="table-responsive">
            <table id="etkinlikTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Etkinlik Adı</th>
                        <th>Tarih</th>
                        <th>Kayıt Sayısı</th>
                        <th>Durum</th>
                        <th>Oluşturan</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($etkinlikler) > 0): ?>
                        <?php foreach ($etkinlikler as $etkinlik): ?>
                            <tr>
                                <td><?= $etkinlik['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($etkinlik['etkinlik_adi']) ?></strong>
                                    <div style="font-size: 12px; color: #6c757d;">
                                        <?= htmlspecialchars($etkinlik['etkinlik_slug']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($etkinlik['baslangic_tarihi']): ?>
                                        <div><?= format_tarih($etkinlik['baslangic_tarihi']) ?></div>
                                        <?php if ($etkinlik['saat']): ?>
                                            <small class="text-muted"><?= htmlspecialchars($etkinlik['saat']) ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= $etkinlik['kayit_sayisi'] ?></strong>
                                    <?php if ($etkinlik['max_katilimci'] > 0): ?>
                                        / <?= $etkinlik['max_katilimci'] ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($etkinlik['aktif']): ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Pasif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= htmlspecialchars($etkinlik['olusturan_adi'] ?? 'Sistem') ?></small>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <a href="kayitlar.php?etkinlik_id=<?= $etkinlik['id'] ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Kayıtları Görüntüle">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        
                                        <?php if (is_yonetici_or_admin()): ?>
                                        <a href="?action=toggle&id=<?= $etkinlik['id'] ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Durumu Değiştir">
                                            <i class="fas fa-power-off"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (is_admin()): ?>
                                        <a href="etkinlik_duzenle.php?id=<?= $etkinlik['id'] ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <a href="?action=delete&id=<?= $etkinlik['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           data-delete
                                           data-message="Bu etkinliği ve tüm kayıtlarını silmek istediğinizden emin misiniz?"
                                           title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted" style="padding: 50px;">
                                <i class="fas fa-inbox fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                                <p>Henüz etkinlik bulunmuyor</p>
                                <?php if (is_admin()): ?>
                                <a href="etkinlik_ekle.php" class="btn btn-primary mt-10">
                                    <i class="fas fa-plus"></i> İlk Etkinliği Ekle
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Tablo arama fonksiyonu
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('etkinlikTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent || row.innerText;
        
        if (text.toLowerCase().indexOf(filter) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>