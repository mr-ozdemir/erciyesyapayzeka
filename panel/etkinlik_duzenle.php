<?php
// panel/etkinlik_duzenle.php - Etkinlik Düzenleme Sayfası

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../auth.php';

if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }

// YETKİ: Yönetici veya Yönetici (yonetici) erişebilsin
if (function_exists('check_permission')) {
    check_permission(['admin', 'yonetici']);
} else {
    // Eski projeler için minimum kontrol
    if (!function_exists('is_logged_in') || !is_logged_in()) {
        redirect('login.php');
    }
}

// Küçük yardımcılar (tasarıma dokunmaz)
function _safe($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function _val($name, $default=''){ return isset($_POST[$name]) ? $_POST[$name] : $default; }

// Slug üretim fallback (config.php'de create_slug yoksa)
if (!function_exists('create_slug')) {
    function create_slug($str) {
        $tr = ['ş'=>'s','Ş'=>'s','ı'=>'i','İ'=>'i','ğ'=>'g','Ğ'=>'g','ç'=>'c','Ç'=>'c','ö'=>'o','Ö'=>'o','ü'=>'u','Ü'=>'u'];
        $str = strtr($str, $tr);
        $str = preg_replace('~[^\\pL0-9_]+~u', '-', $str);
        $str = trim($str, '-');
        $str = strtolower($str);
        $str = preg_replace('~[^-a-z0-9_]+~', '', $str);
        return $str ?: 'etkinlik';
    }
}

// Temel giriş
$etkinlik = null;
$db = null;
try {
    $db = (class_exists('Database'))
        ? Database::getInstance()->getConnection()
        : (isset($pdo) && $pdo instanceof PDO ? $pdo : null);

    if (!$db) {
        throw new Exception('Veritabanı bağlantısı bulunamadı.');
    }

    // GET ile id veya slug yakala
    $id   = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $slug = isset($_GET['slug']) ? trim($_GET['slug']) : null;

    if ($id) {
        $st = $db->prepare("SELECT * FROM etkinlikler WHERE id = :id LIMIT 1");
        $st->execute([':id' => $id]);
        $etkinlik = $st->fetch(PDO::FETCH_ASSOC);
    } elseif ($slug) {
        $st = $db->prepare("SELECT * FROM etkinlikler WHERE etkinlik_slug = :s LIMIT 1");
        $st->execute([':s' => $slug]);
        $etkinlik = $st->fetch(PDO::FETCH_ASSOC);
    }

    if (!$etkinlik) {
        set_flash_message('Etkinlik bulunamadı.', 'danger');
        redirect('etkinlikler.php');
    }

    // POST işlemi (güncelle)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf = $_POST['csrf_token'] ?? '';
        if (function_exists('verify_csrf_token') && !verify_csrf_token($csrf)) {
            set_flash_message('Oturum doğrulama süresi doldu. Lütfen tekrar deneyin.', 'warning');
            redirect('etkinlik_duzenle.php?id=' . (int)$etkinlik['id']);
        }

        // Girdi al
        $etkinlik_adi   = function_exists('sanitize_input') ? sanitize_input($_POST['etkinlik_adi'] ?? '')   : trim($_POST['etkinlik_adi'] ?? '');
        $etkinlik_slug  = function_exists('sanitize_input') ? sanitize_input($_POST['etkinlik_slug'] ?? '')  : trim($_POST['etkinlik_slug'] ?? '');
        $aciklama       = function_exists('sanitize_input') ? sanitize_input($_POST['aciklama'] ?? '')       : trim($_POST['aciklama'] ?? '');
        $detayli        = $_POST['detayli_aciklama'] ?? ''; // HTML içerebilir, filtrelemeyi göstereceğiniz yerde yapın
        $program        = $_POST['program_takvimi'] ?? '';
        $galeri         = $_POST['galeri_resimleri'] ?? '';
        $video_linki    = function_exists('sanitize_input') ? sanitize_input($_POST['video_linki'] ?? '')    : trim($_POST['video_linki'] ?? '');
        $konusmacilar   = $_POST['konusmacilar'] ?? '';
        $baslangic      = function_exists('sanitize_input') ? sanitize_input($_POST['baslangic_tarihi'] ?? ''): trim($_POST['baslangic_tarihi'] ?? '');
        $bitis          = function_exists('sanitize_input') ? sanitize_input($_POST['bitis_tarihi'] ?? '')    : trim($_POST['bitis_tarihi'] ?? '');
        $saat           = function_exists('sanitize_input') ? sanitize_input($_POST['saat'] ?? '')           : trim($_POST['saat'] ?? '');
        $konum          = function_exists('sanitize_input') ? sanitize_input($_POST['konum'] ?? '')          : trim($_POST['konum'] ?? '');
        $max_katilimci  = (int)($_POST['max_katilimci'] ?? 0);
        $aktif          = isset($_POST['aktif']) ? 1 : 0;

        if ($etkinlik_slug === '') {
            $etkinlik_slug = create_slug($etkinlik_adi);
        }

        // Slug tekilliği (mevcut ID hariç)
        $chk = $db->prepare("SELECT id FROM etkinlikler WHERE etkinlik_slug = :slug AND id <> :id LIMIT 1");
        $chk->execute([':slug' => $etkinlik_slug, ':id' => (int)$etkinlik['id']]);
        if ($chk->fetch()) {
            $etkinlik_slug .= '-' . date('His');
        }

        // Resim işlemi
        $etkinlik_resmi = $_POST['etkinlik_resmi_old'] ?? $etkinlik['etkinlik_resmi'];
        $sil_resim      = isset($_POST['resmi_sil']);

        if ($sil_resim) {
            $etkinlik_resmi = null; // fiziksel silmeyi güvenlik ve yedekleme için burada yapmıyoruz
        }

        if (!empty($_FILES['etkinlik_resmi']['name'])) {
            $ext = strtolower(pathinfo($_FILES['etkinlik_resmi']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (in_array($ext, $allowed, true) && is_uploaded_file($_FILES['etkinlik_resmi']['tmp_name'])) {
                $uploadDir = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'etkinlikler';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0775, true);
                }
                $newName = $etkinlik_slug . '_' . time() . '.' . $ext;
                $target  = $uploadDir . DIRECTORY_SEPARATOR . $newName;
                if (@move_uploaded_file($_FILES['etkinlik_resmi']['tmp_name'], $target)) {
                    $etkinlik_resmi = 'uploads/etkinlikler/' . $newName; // web yolu
                }
            }
        }

        // Güncelle
        $upd = $db->prepare("
            UPDATE etkinlikler SET
                etkinlik_adi       = :adi,
                etkinlik_slug      = :slug,
                aciklama           = :aciklama,
                detayli_aciklama   = :detayli,
                etkinlik_resmi     = :resim,
                program_takvimi    = :program,
                galeri_resimleri   = :galeri,
                video_linki        = :video,
                konusmacilar       = :konusmacilar,
                baslangic_tarihi   = :baslangic,
                bitis_tarihi       = :bitis,
                saat               = :saat,
                konum              = :konum,
                max_katilimci      = :max_katilimci,
                aktif              = :aktif
            WHERE id = :id
        ");
        $upd->execute([
            ':adi'            => $etkinlik_adi,
            ':slug'           => $etkinlik_slug,
            ':aciklama'       => $aciklama,
            ':detayli'        => $detayli,
            ':resim'          => $etkinlik_resmi,
            ':program'        => ($program === '' ? null : $program),
            ':galeri'         => ($galeri === '' ? null : $galeri),
            ':video'          => ($video_linki === '' ? null : $video_linki),
            ':konusmacilar'   => ($konusmacilar === '' ? null : $konusmacilar),
            ':baslangic'      => ($baslangic === '' ? null : $baslangic),
            ':bitis'          => ($bitis === '' ? null : $bitis),
            ':saat'           => ($saat === '' ? null : $saat),
            ':konum'          => ($konum === '' ? null : $konum),
            ':max_katilimci'  => $max_katilimci,
            ':aktif'          => $aktif,
            ':id'             => (int)$etkinlik['id'],
        ]);

        if (function_exists('log_activity')) {
            log_activity(get_current_user_id(), 'update', 'Etkinlik güncellendi: ' . $etkinlik_adi, 'etkinlikler', (int)$etkinlik['id']);
        }

        set_flash_message('Etkinlik başarıyla güncellendi.', 'success');
        redirect('etkinlik_duzenle.php?id=' . (int)$etkinlik['id']);
    }

} catch (Exception $e) {
    set_flash_message('Hata: ' . $e->getMessage(), 'danger');
    redirect('etkinlikler.php');
}

// Sayfa başlığı ve breadcrumb
$page_title = 'Etkinlik Düzenle';
$breadcrumb = [
    ['title' => 'Etkinlikler', 'url' => 'etkinlikler.php'],
    ['title' => 'Düzenle'],
];

include __DIR__ . '/includes/header.php';

// Görsel önizleme yolu
$imgUrl = '';
if (!empty($etkinlik['etkinlik_resmi'])) {
    $imgUrl = (preg_match('~^https?://~i', $etkinlik['etkinlik_resmi']))
        ? $etkinlik['etkinlik_resmi']
        : '../' . ltrim($etkinlik['etkinlik_resmi'], '/');
}
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-edit"></i> Etkinlik Bilgilerini Düzenle</h3>
        <a href="etkinlikler.php" class="btn btn-sm btn-primary">Listeye Dön</a>
    </div>
    <div class="card-body">
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= function_exists('generate_csrf_token') ? _safe(generate_csrf_token()) : '' ?>">
            <input type="hidden" name="etkinlik_resmi_old" value="<?= _safe($etkinlik['etkinlik_resmi'] ?? '') ?>">

            <div class="form-group">
                <label>Etkinlik Adı</label>
                <input type="text" name="etkinlik_adi" class="form-control" required
                       value="<?= _safe(_val('etkinlik_adi', $etkinlik['etkinlik_adi'] ?? '')) ?>">
            </div>

            <div class="form-group">
                <label>Slug</label>
                <input type="text" name="etkinlik_slug" class="form-control"
                       value="<?= _safe(_val('etkinlik_slug', $etkinlik['etkinlik_slug'] ?? '')) ?>">
                <small class="text-muted">Boş bırakırsanız otomatik üretilecektir.</small>
            </div>

            <div class="form-group">
                <label>Kısa Açıklama</label>
                <textarea name="aciklama" class="form-control" rows="3"><?= _safe(_val('aciklama', $etkinlik['aciklama'] ?? '')) ?></textarea>
            </div>

            <div class="form-group">
                <label>Detaylı Açıklama (HTML desteklenir)</label>
                <textarea name="detayli_aciklama" class="form-control" rows="8"><?= _safe(_val('detayli_aciklama', $etkinlik['detayli_aciklama'] ?? '')) ?></textarea>
            </div>

            <div class="form-group">
                <label>Ana Görsel</label>
                <?php if ($imgUrl): ?>
                    <div style="margin-bottom:8px;">
                        <img src="<?= _safe($imgUrl) ?>" alt="Etkinlik görseli" style="max-width:260px;border:1px solid #e9ecef;border-radius:8px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="etkinlik_resmi" accept=".jpg,.jpeg,.png,.gif,.webp" class="form-control">
                <div style="margin-top:6px;">
                    <label><input type="checkbox" name="resmi_sil"> Mevcut görseli kaldır</label>
                </div>
            </div>

            <div class="form-group">
                <label>Program Takvimi (JSON)</label>
                <textarea name="program_takvimi" class="form-control" rows="6" placeholder='[{"gun":"1. Hafta","tarih":"YYYY-MM-DD","baslik":"...","aciklama":"...","saat":"..."}]'><?= _safe(_val('program_takvimi', $etkinlik['program_takvimi'] ?? '')) ?></textarea>
            </div>

            <div class="form-group">
                <label>Galeri Resimleri (JSON)</label>
                <textarea name="galeri_resimleri" class="form-control" rows="4" placeholder='["uploads/etkinlikler/img1.jpg","uploads/etkinlikler/img2.jpg"]'><?= _safe(_val('galeri_resimleri', $etkinlik['galeri_resimleri'] ?? '')) ?></textarea>
            </div>

            <div class="form-group">
                <label>Video Linki</label>
                <input type="text" name="video_linki" class="form-control" value="<?= _safe(_val('video_linki', $etkinlik['video_linki'] ?? '')) ?>">
            </div>

            <div class="form-group">
                <label>Konuşmacılar (JSON)</label>
                <textarea name="konusmacilar" class="form-control" rows="4" placeholder='[{"ad_soyad":"...","unvan":"...","fotograf":"...","linkedin":"..."}]'><?= _safe(_val('konusmacilar', $etkinlik['konusmacilar'] ?? '')) ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="form-group">
                    <label>Başlangıç Tarihi</label>
                    <input type="date" name="baslangic_tarihi" class="form-control"
                           value="<?= _safe(_val('baslangic_tarihi', $etkinlik['baslangic_tarihi'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label>Bitiş Tarihi</label>
                    <input type="date" name="bitis_tarihi" class="form-control"
                           value="<?= _safe(_val('bitis_tarihi', $etkinlik['bitis_tarihi'] ?? '')) ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="form-group">
                    <label>Saat</label>
                    <input type="text" name="saat" class="form-control" placeholder="19:30 - 21:30"
                           value="<?= _safe(_val('saat', $etkinlik['saat'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label>Konum</label>
                    <input type="text" name="konum" class="form-control"
                           value="<?= _safe(_val('konum', $etkinlik['konum'] ?? '')) ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                <div class="form-group">
                    <label>Maks. Katılımcı</label>
                    <input type="number" name="max_katilimci" min="0" class="form-control"
                           value="<?= _safe(_val('max_katilimci', (string)($etkinlik['max_katilimci'] ?? 0))) ?>">
                </div>
                <div class="form-group" style="display:flex; align-items:center; gap:10px; margin-top:28px;">
                    <label><input type="checkbox" name="aktif" <?= (isset($etkinlik['aktif']) && (int)$etkinlik['aktif'] === 1) ? 'checked' : '' ?>> Etkinlik aktif</label>
                </div>
            </div>

            <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
                <a href="etkinlikler.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Geri Dön</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
