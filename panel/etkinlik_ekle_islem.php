<?php
// panel/etkinlik_ekle_islem.php - Etkinlik Ekleme İşlemi

require_once '../config.php';
require_once '../auth.php';
require_once '../etkinlik_helpers.php';

check_login(); // Sadece giriş kontrolü

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('etkinlik_ekle.php');
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Form verilerini al
    $etkinlik_adi = sanitize_input($_POST['etkinlik_adi']);
    $etkinlik_slug = create_slug($etkinlik_adi);
    $baslangic_tarihi = sanitize_input($_POST['baslangic_tarihi']);
    $bitis_tarihi = !empty($_POST['bitis_tarihi']) ? sanitize_input($_POST['bitis_tarihi']) : null;
    $saat = sanitize_input($_POST['saat']);
    $konum = sanitize_input($_POST['konum']);
    $aciklama = sanitize_input($_POST['aciklama']);
    $detayli_aciklama = sanitize_input($_POST['detayli_aciklama']);
    $max_katilimci = (int)$_POST['max_katilimci'];
    $aktif = (int)$_POST['aktif'];
    $video_linki = sanitize_input($_POST['video_linki']);
    
    $user = get_current_user_data();
    $olusturan_id = $user['id'];
    
    // Etkinlik resmi upload
    $etkinlik_resmi = null;
    if (isset($_FILES['etkinlik_resmi']) && $_FILES['etkinlik_resmi']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_etkinlik_dosyasi($_FILES['etkinlik_resmi'], $etkinlik_slug, 'hero');
        if ($upload_result['success']) {
            $etkinlik_resmi = $upload_result['path'];
        }
    }
    
    // Program takvimini JSON'a çevir
    $program_takvimi = null;
    if (isset($_POST['program']) && is_array($_POST['program'])) {
        $program_array = [];
        foreach ($_POST['program'] as $item) {
            if (!empty($item['gun']) && !empty($item['tarih']) && !empty($item['baslik'])) {
                $program_array[] = [
                    'gun' => sanitize_input($item['gun']),
                    'tarih' => sanitize_input($item['tarih']),
                    'saat' => sanitize_input($item['saat'] ?? ''),
                    'baslik' => sanitize_input($item['baslik']),
                    'aciklama' => sanitize_input($item['aciklama'] ?? '')
                ];
            }
        }
        if (count($program_array) > 0) {
            $program_takvimi = json_encode($program_array, JSON_UNESCAPED_UNICODE);
        }
    }
    
    // Galeri resimlerini upload et
    $galeri_resimleri = null;
    if (isset($_FILES['galeri_resimleri']) && is_array($_FILES['galeri_resimleri']['name'])) {
        $galeri_array = [];
        $file_count = count($_FILES['galeri_resimleri']['name']);
        
        for ($i = 0; $i < $file_count && $i < 10; $i++) {
            if ($_FILES['galeri_resimleri']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['galeri_resimleri']['name'][$i],
                    'type' => $_FILES['galeri_resimleri']['type'][$i],
                    'tmp_name' => $_FILES['galeri_resimleri']['tmp_name'][$i],
                    'error' => $_FILES['galeri_resimleri']['error'][$i],
                    'size' => $_FILES['galeri_resimleri']['size'][$i]
                ];
                
                $upload_result = upload_etkinlik_dosyasi($file, $etkinlik_slug, 'galeri-' . ($i + 1));
                if ($upload_result['success']) {
                    $galeri_array[] = $upload_result['path'];
                }
            }
        }
        
        if (count($galeri_array) > 0) {
            $galeri_resimleri = json_encode($galeri_array, JSON_UNESCAPED_UNICODE);
        }
    }
    
    // Konuşmacıları işle
    $konusmacilar = null;
    if (isset($_POST['speakers']) && is_array($_POST['speakers'])) {
        $speakers_array = [];
        
        foreach ($_POST['speakers'] as $index => $speaker) {
            if (!empty($speaker['ad_soyad'])) {
                $speaker_data = [
                    'ad_soyad' => sanitize_input($speaker['ad_soyad']),
                    'unvan' => sanitize_input($speaker['unvan'] ?? ''),
                    'linkedin' => sanitize_input($speaker['linkedin'] ?? ''),
                    'fotograf' => ''
                ];
                
                // Konuşmacı fotoğrafı
                if (isset($_FILES['speakers']['name'][$index]['fotograf']) && 
                    $_FILES['speakers']['error'][$index]['fotograf'] === UPLOAD_ERR_OK) {
                    
                    $file = [
                        'name' => $_FILES['speakers']['name'][$index]['fotograf'],
                        'type' => $_FILES['speakers']['type'][$index]['fotograf'],
                        'tmp_name' => $_FILES['speakers']['tmp_name'][$index]['fotograf'],
                        'error' => $_FILES['speakers']['error'][$index]['fotograf'],
                        'size' => $_FILES['speakers']['size'][$index]['fotograf']
                    ];
                    
                    $upload_result = upload_etkinlik_dosyasi($file, $etkinlik_slug, 'speaker-' . $index);
                    if ($upload_result['success']) {
                        $speaker_data['fotograf'] = $upload_result['path'];
                    }
                }
                
                $speakers_array[] = $speaker_data;
            }
        }
        
        if (count($speakers_array) > 0) {
            $konusmacilar = json_encode($speakers_array, JSON_UNESCAPED_UNICODE);
        }
    }
    
    // Veritabanına ekle
    $stmt = $db->prepare("
        INSERT INTO etkinlikler (
            etkinlik_adi, 
            etkinlik_slug, 
            aciklama, 
            detayli_aciklama,
            etkinlik_resmi,
            program_takvimi,
            galeri_resimleri,
            video_linki,
            konusmacilar,
            baslangic_tarihi, 
            bitis_tarihi, 
            saat,
            konum,
            aktif, 
            max_katilimci,
            olusturan_kullanici_id,
            olusturma_tarihi
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )
    ");
    
    $stmt->execute([
        $etkinlik_adi,
        $etkinlik_slug,
        $aciklama,
        $detayli_aciklama,
        $etkinlik_resmi,
        $program_takvimi,
        $galeri_resimleri,
        $video_linki,
        $konusmacilar,
        $baslangic_tarihi,
        $bitis_tarihi,
        $saat,
        $konum,
        $aktif,
        $max_katilimci,
        $olusturan_id
    ]);
    
    $etkinlik_id = $db->lastInsertId();
    
    // Aktivite kaydı ekle
    log_activity($user['id'], 'etkinlik_eklendi', "Yeni etkinlik eklendi: {$etkinlik_adi}");
    
    set_flash_message('Etkinlik başarıyla eklendi!', 'success');
    redirect('etkinlikler.php');
    
} catch (Exception $e) {
    error_log("Etkinlik Ekleme Hatası: " . $e->getMessage());
    set_flash_message('Etkinlik eklenirken bir hata oluştu: ' . $e->getMessage(), 'danger');
    redirect('etkinlik_ekle.php');
}
?>