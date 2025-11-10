<?php
// kayitlar.php - Kayıt Listesi ve Yönetimi

require_once '../config.php';
require_once '../auth.php';

check_login();

$page_title = 'Kayıt Yönetimi';
$breadcrumb = [
    ['title' => 'Kayıtlar']
];

// Filtreleme
$etkinlik_filtre = isset($_GET['etkinlik_id']) ? (int)$_GET['etkinlik_id'] : 0;
$durum_filtre = isset($_GET['durum']) ? $_GET['durum'] : 'all';

try {
    $db = Database::getInstance()->getConnection();
    
    // Tüm etkinlikleri getir (filtre için)
    $stmt = $db->query("SELECT id, etkinlik_adi FROM etkinlikler ORDER BY etkinlik_adi ASC");
    $etkinlikler = $stmt->fetchAll();
    
    // Kayıtları getir - ETKİNLİK ADINI DA GETİR
    $query = "
        SELECT 
            k.id,
            k.ad_soyad,
            k.email,
            k.bolum,
            k.kvkk_onay,
            k.ip_adresi,
            k.durum,
            k.kayit_tarihi,
            k.onaylayan_kullanici_id,
            e.id AS etkinlik_id,
            e.etkinlik_adi,
            e.etkinlik_slug
        FROM kayitlar k
        INNER JOIN etkinlikler e ON k.etkinlik_id = e.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($etkinlik_filtre > 0) {
        $query .= " AND k.etkinlik_id = ?";
        $params[] = $etkinlik_filtre;
    }
    
    if ($durum_filtre != 'all') {
        $query .= " AND k.durum = ?";
        $params[] = $durum_filtre;
    }
    
    $query .= " ORDER BY k.kayit_tarihi DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $kayitlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // İstatistikler
    $stats_query = "
        SELECT 
            COUNT(CASE WHEN durum = 'onaylandi' THEN 1 END) as onaylanan,
            COUNT(CASE WHEN durum = 'beklemede' THEN 1 END) as bekleyen,
            COUNT(CASE WHEN durum = 'iptal' THEN 1 END) as iptal,
            COUNT(CASE WHEN durum = 'katildi' THEN 1 END) as katildi,
            COUNT(*) as toplam
        FROM kayitlar
    ";
    
    if ($etkinlik_filtre > 0) {
        $stats_query .= " WHERE etkinlik_id = ?";
        $stmt = $db->prepare($stats_query);
        $stmt->execute([$etkinlik_filtre]);
    } else {
        $stmt = $db->query($stats_query);
    }
    
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Veritabanı Hatası: " . $e->getMessage() . "<br>Sorgu: " . $query);
}

// Kayıt durumu değiştirme
if (isset($_GET['action']) && $_GET['action'] == 'durum' && isset($_GET['id']) && isset($_GET['yeni_durum']) && is_yonetici_or_admin()) {
    $kayit_id = (int)$_GET['id'];
    $yeni_durum = $_GET['yeni_durum'];
    
    $allowed_durumlar = ['beklemede', 'onaylandi', 'iptal', 'katildi'];
    
    if (in_array($yeni_durum, $allowed_durumlar)) {
        try {
            $stmt = $db->prepare("UPDATE kayitlar SET durum = ?, onaylayan_kullanici_id = ? WHERE id = ?");
            $stmt->execute([$yeni_durum, $_SESSION['user_id'], $kayit_id]);
            
            log_aktivite($_SESSION['user_id'], 'update', 'kayitlar', $kayit_id, 'Kayıt durumu: ' . $yeni_durum);
            
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Kayıt durumu güncellendi!'
            ];
        } catch(PDOException $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'Durum güncellenirken hata oluştu!'
            ];
        }
    }
    
    redirect('kayitlar.php' . ($etkinlik_filtre > 0 ? '?etkinlik_id=' . $etkinlik_filtre : ''));
}

// Kayıt silme
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']) && is_admin()) {
    $kayit_id = (int)$_GET['id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM kayitlar WHERE id = ?");
        $stmt->execute([$kayit_id]);
        
        log_aktivite($_SESSION['user_id'], 'delete', 'kayitlar', $kayit_id, 'Kayıt silindi');
        
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => 'Kayıt başarıyla silindi!'
        ];
    } catch(PDOException $e) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Kayıt silinirken hata oluştu!'
        ];
    }
    
    redirect('kayitlar.php' . ($etkinlik_filtre > 0 ? '?etkinlik_id=' . $etkinlik_filtre : ''));
}

include 'includes/header.php';
?>

<!-- İstatistikler -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-list"></i>
        </div>
        <div class="stat-label">Toplam Kayıt</div>
        <div class="stat-value"><?= $stats['toplam'] ?></div>
    </div>

    <div class="stat-card success">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-label">Onaylanan</div>
        <div class="stat-value"><?= $stats['onaylanan'] ?></div>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-label">Bekleyen</div>
        <div class="stat-value"><?= $stats['bekleyen'] ?></div>
    </div>

    <div class="stat-card info">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-label">Katıldı</div>
        <div class="stat-value"><?= $stats['katildi'] ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users"></i>
            Tüm Kayıtlar (<?= count($kayitlar) ?>)
        </h3>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <!-- Etkinlik Filtresi -->
            <select onchange="updateFilter('etkinlik_id', this.value)" class="form-select" style="width: auto; min-width: 200px;">
                <option value="0">Tüm Etkinlikler</option>
                <?php foreach ($etkinlikler as $etkinlik): ?>
                    <option value="<?= $etkinlik['id'] ?>" <?= $etkinlik_filtre == $etkinlik['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($etkinlik['etkinlik_adi']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Durum Filtresi -->
            <select onchange="updateFilter('durum', this.value)" class="form-select" style="width: auto;">
                <option value="all" <?= $durum_filtre == 'all' ? 'selected' : '' ?>>Tüm Durumlar</option>
                <option value="onaylandi" <?= $durum_filtre == 'onaylandi' ? 'selected' : '' ?>>Onaylanan</option>
                <option value="beklemede" <?= $durum_filtre == 'beklemede' ? 'selected' : '' ?>>Bekleyen</option>
                <option value="katildi" <?= $durum_filtre == 'katildi' ? 'selected' : '' ?>>Katıldı</option>
                <option value="iptal" <?= $durum_filtre == 'iptal' ? 'selected' : '' ?>>İptal</option>
            </select>

            <button onclick="exportTableToExcel('kayitTable', 'kayitlar')" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Excel
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Arama -->
        <div style="margin-bottom: 20px;">
            <input 
                type="text" 
                id="searchInput" 
                class="form-control" 
                placeholder="🔍 Ad, email, bölüm veya etkinlik ara..."
                onkeyup="searchTable()"
            >
        </div>

        <?php if (count($kayitlar) > 0): ?>
        <div class="table-responsive">
            <table id="kayitTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Etkinlik</th>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Bölüm</th>
                        <th>Kayıt Tarihi</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kayitlar as $kayit): ?>
                        <tr>
                            <td><?= $kayit['id'] ?></td>
                            <td>
                                <strong style="color: #667eea;"><?= htmlspecialchars($kayit['etkinlik_adi']) ?></strong>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($kayit['ad_soyad']) ?></strong>
                            </td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($kayit['email']) ?>" style="color: #667eea;">
                                    <?= htmlspecialchars($kayit['email']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($kayit['bolum']) ?></td>
                            <td>
                                <small><?= format_tarih_saat($kayit['kayit_tarihi'], 'd.m.Y H:i') ?></small>
                            </td>
                            <td>
                                <?php
                                $badge_class = 'info';
                                $badge_text = ucfirst($kayit['durum']);
                                switch($kayit['durum']) {
                                    case 'onaylandi': 
                                        $badge_class = 'success'; 
                                        $badge_text = 'Onaylandı';
                                        break;
                                    case 'beklemede': 
                                        $badge_class = 'warning'; 
                                        $badge_text = 'Beklemede';
                                        break;
                                    case 'iptal': 
                                        $badge_class = 'danger'; 
                                        $badge_text = 'İptal';
                                        break;
                                    case 'katildi': 
                                        $badge_class = 'primary'; 
                                        $badge_text = 'Katıldı';
                                        break;
                                }
                                ?>
                                <span class="badge badge-<?= $badge_class ?>">
                                    <?= $badge_text ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <?php if (is_yonetici_or_admin()): ?>
                                    <div class="dropdown" style="position: relative;">
                                        <button class="btn btn-sm btn-primary" onclick="toggleDropdown(this)">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <div class="dropdown-menu" style="display: none;">
                                            <a href="?action=durum&id=<?= $kayit['id'] ?>&yeni_durum=onaylandi<?= $etkinlik_filtre > 0 ? '&etkinlik_id='.$etkinlik_filtre : '' ?>" class="dropdown-item">
                                                <i class="fas fa-check"></i> Onayla
                                            </a>
                                            <a href="?action=durum&id=<?= $kayit['id'] ?>&yeni_durum=katildi<?= $etkinlik_filtre > 0 ? '&etkinlik_id='.$etkinlik_filtre : '' ?>" class="dropdown-item">
                                                <i class="fas fa-user-check"></i> Katıldı
                                            </a>
                                            <a href="?action=durum&id=<?= $kayit['id'] ?>&yeni_durum=iptal<?= $etkinlik_filtre > 0 ? '&etkinlik_id='.$etkinlik_filtre : '' ?>" class="dropdown-item">
                                                <i class="fas fa-times"></i> İptal Et
                                            </a>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (is_admin()): ?>
                                    <a href="?action=delete&id=<?= $kayit['id'] ?><?= $etkinlik_filtre > 0 ? '&etkinlik_id='.$etkinlik_filtre : '' ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bu kaydı silmek istediğinizden emin misiniz?')"
                                       title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="text-center text-muted" style="padding: 50px;">
                <i class="fas fa-inbox fa-3x" style="opacity: 0.3; margin-bottom: 15px;"></i>
                <p style="font-size: 18px; margin: 0;">Kayıt bulunmuyor</p>
                <small>Seçilen filtrelere uygun kayıt bulunamadı.</small>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
    min-width: 180px;
    z-index: 1000;
    padding: 8px 0;
    margin-top: 4px;
}

.dropdown-item {
    display: block;
    padding: 10px 16px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.2s;
    font-size: 14px;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: #667eea;
}

.dropdown-item i {
    width: 18px;
    margin-right: 8px;
}
</style>

<script>
// Tablo arama
function searchTable() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('kayitTable');
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent || row.innerText;
        row.style.display = text.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
    }
}

// Filtre güncelleme
function updateFilter(param, value) {
    const url = new URL(window.location.href);
    if (value == '0' || value == 'all') {
        url.searchParams.delete(param);
    } else {
        url.searchParams.set(param, value);
    }
    window.location.href = url.toString();
}

// Dropdown toggle
function toggleDropdown(button) {
    const dropdown = button.nextElementSibling;
    const isVisible = dropdown.style.display === 'block';
    
    // Tüm dropdown'ları kapat
    document.querySelectorAll('.dropdown-menu').forEach(d => d.style.display = 'none');
    
    // Bu dropdown'ı aç/kapat
    dropdown.style.display = isVisible ? 'none' : 'block';
}

// Dropdown dışına tıklandığında kapat
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(d => d.style.display = 'none');
    }
});

// Excel export
function exportTableToExcel(tableId, filename = '') {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    let html = table.outerHTML;
    const url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    const downloadLink = document.createElement("a");
    
    downloadLink.href = url;
    downloadLink.download = filename + '_' + new Date().toISOString().slice(0,10) + '.xls';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

console.log('📊 Kayıtlar Sayfası Yüklendi');
console.log('Toplam Kayıt:', <?= count($kayitlar) ?>);
</script>

<?php include 'includes/footer.php'; ?>