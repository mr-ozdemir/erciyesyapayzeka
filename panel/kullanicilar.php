<?php
// kullanicilar.php - Kullanıcı Yönetimi (Sadece Admin)

require_once '../config.php';
require_once '../auth.php';

// check_permission(ROL_ADMIN); // Bu satırı silin veya yorum satırı yapın
if (!is_admin()) {
    set_flash_message('Bu sayfaya erişim yetkiniz yok.', 'danger');
    redirect('dashboard.php');
}

$page_title = 'Kullanıcı Yönetimi';
$breadcrumb = [
    ['title' => 'Kullanıcılar']
];

try {
    $db = Database::getInstance()->getConnection();
    
    // Kullanıcıları getir
    $stmt = $db->query("
        SELECT 
            pk.*,
            r.rol_adi,
            r.yetki_seviyesi
        FROM panel_kullanicilar pk
        JOIN roller r ON pk.rol_id = r.id
        ORDER BY pk.olusturma_tarihi DESC
    ");
    $kullanicilar = $stmt->fetchAll();
    
    // Rolleri getir
    $stmt = $db->query("SELECT * FROM roller ORDER BY yetki_seviyesi DESC");
    $roller = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("Hata: " . $e->getMessage());
}

// Kullanıcı ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $result = create_user($_POST);
    
    $_SESSION['flash_message'] = [
        'type' => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ];
    
    if ($result['success']) {
        redirect('kullanicilar.php');
    }
}

// Kullanıcı silme
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    $result = delete_user($user_id);
    
    $_SESSION['flash_message'] = [
        'type' => $result['success'] ? 'success' : 'error',
        'message' => $result['message']
    ];
    
    redirect('kullanicilar.php');
}

// Kullanıcı durumu değiştirme
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];
    
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['flash_message'] = [
            'type' => 'error',
            'message' => 'Kendi hesabınızın durumunu değiştiremezsiniz!'
        ];
    } else {
        try {
            $stmt = $db->prepare("UPDATE panel_kullanicilar SET aktif = NOT aktif WHERE id = ?");
            $stmt->execute([$user_id]);
            
            log_aktivite($_SESSION['user_id'], 'update', 'panel_kullanicilar', $user_id, 'Kullanıcı durumu değiştirildi');
            
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Kullanıcı durumu güncellendi!'
            ];
        } catch(PDOException $e) {
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => 'Bir hata oluştu!'
            ];
        }
    }
    
    redirect('kullanicilar.php');
}

include 'includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users-cog"></i>
            Panel Kullanıcıları (<?= count($kullanicilar) ?>)
        </h3>
        <button onclick="openModal('addUserModal')" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Yeni Kullanıcı
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kullanıcı Adı</th>
                        <th>Ad Soyad</th>
                        <th>E-posta</th>
                        <th>Rol</th>
                        <th>Son Giriş</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kullanicilar as $kullanici): ?>
                        <tr>
                            <td><?= $kullanici['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($kullanici['kullanici_adi']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($kullanici['ad_soyad']) ?></td>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($kullanici['email']) ?>">
                                    <?= htmlspecialchars($kullanici['email']) ?>
                                </a>
                            </td>
                            <td>
                                <?php
                                $rol_badge = 'info';
                                $rol_icon = 'user';
                                switch($kullanici['rol_adi']) {
                                    case 'admin':
                                        $rol_badge = 'danger';
                                        $rol_icon = 'crown';
                                        break;
                                    case 'yonetici':
                                        $rol_badge = 'warning';
                                        $rol_icon = 'user-tie';
                                        break;
                                    case 'uye':
                                        $rol_badge = 'info';
                                        $rol_icon = 'user';
                                        break;
                                }
                                ?>
                                <span class="badge badge-<?= $rol_badge ?>">
                                    <i class="fas fa-<?= $rol_icon ?>"></i>
                                    <?= ucfirst($kullanici['rol_adi']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($kullanici['son_giris']): ?>
                                    <small><?= format_tarih_saat($kullanici['son_giris'], 'd.m.Y H:i') ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Hiç giriş yapmadı</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($kullanici['aktif']): ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <?php if ($kullanici['id'] != $_SESSION['user_id']): ?>
                                        <a href="?action=toggle&id=<?= $kullanici['id'] ?>" 
                                           class="btn btn-sm btn-warning"
                                           title="Durumu Değiştir">
                                            <i class="fas fa-power-off"></i>
                                        </a>
                                        
                                        <a href="?action=delete&id=<?= $kullanici['id'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           data-delete
                                           data-message="Bu kullanıcıyı silmek istediğinizden emin misiniz?"
                                           title="Sil">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge badge-primary">Siz</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Yeni Kullanıcı Modal -->
<div id="addUserModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle
            </h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group">
                    <label for="kullanici_adi">
                        Kullanıcı Adı <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="kullanici_adi" 
                        name="kullanici_adi"
                        placeholder="Benzersiz kullanıcı adı"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="ad_soyad">
                        Ad Soyad <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="ad_soyad" 
                        name="ad_soyad"
                        placeholder="Tam adı"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">
                        E-posta <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        name="email"
                        placeholder="ornek@email.com"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="sifre">
                        Şifre <span style="color: red;">*</span>
                    </label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="sifre" 
                        name="sifre"
                        placeholder="Güçlü bir şifre"
                        required
                        minlength="6"
                    >
                    <small class="text-muted">En az 6 karakter</small>
                </div>

                <div class="form-group">
                    <label for="rol_id">
                        Rol <span style="color: red;">*</span>
                    </label>
                    <select class="form-select" id="rol_id" name="rol_id" required>
                        <?php foreach ($roller as $rol): ?>
                            <option value="<?= $rol['id'] ?>">
                                <?= ucfirst($rol['rol_adi']) ?> - <?= htmlspecialchars($rol['aciklama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="aktif" id="aktif" checked>
                        <span>Kullanıcı aktif (Giriş yapabilir)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">
                    <i class="fas fa-times"></i> İptal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Kullanıcıyı Ekle
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
