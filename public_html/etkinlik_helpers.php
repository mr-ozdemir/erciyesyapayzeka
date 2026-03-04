<?php
// etkinlik_helpers.php - Etkinlik Yardımcı Fonksiyonları

/**
 * Aktif (gelecek veya devam eden) etkinlikleri getir
 */
function get_aktif_etkinlikler($limit = null) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $query = "
            SELECT 
                e.*,
                (SELECT COUNT(*) FROM kayitlar WHERE etkinlik_id = e.id AND durum != 'iptal') as kayit_sayisi
            FROM etkinlikler e
            WHERE e.aktif = 1 
            AND (e.bitis_tarihi >= CURDATE() OR e.bitis_tarihi IS NULL)
            ORDER BY e.baslangic_tarihi ASC
        ";
        
        if ($limit) {
            $query .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("get_aktif_etkinlikler Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Tüm etkinlikleri getir
 */
function get_tum_etkinlikler() {
    try {
        $db = Database::getInstance()->getConnection();
        
        $query = "
            SELECT 
                e.*,
                (SELECT COUNT(*) FROM kayitlar WHERE etkinlik_id = e.id AND durum != 'iptal') as kayit_sayisi
            FROM etkinlikler e
            ORDER BY e.baslangic_tarihi DESC
        ";
        
        $stmt = $db->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("get_tum_etkinlikler Error: " . $e->getMessage());
        return [];
    }
}

/**
 * Slug'a göre etkinlik getir
 */
function get_etkinlik_by_slug($slug) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT 
                e.*,
                (SELECT COUNT(*) FROM kayitlar WHERE etkinlik_id = e.id AND durum != 'iptal') as kayit_sayisi,
                pk.ad_soyad as olusturan_adi
            FROM etkinlikler e
            LEFT JOIN panel_kullanicilar pk ON e.olusturan_kullanici_id = pk.id
            WHERE e.etkinlik_slug = ?
        ");
        
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch(PDOException $e) {
        error_log("get_etkinlik_by_slug Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Etkinlik aktif mi kontrol et
 */
function is_etkinlik_kayit_acik($etkinlik) {
    if (!$etkinlik['aktif']) {
        return false;
    }
    
    // Bitiş tarihi geçmişse kayıt kapalı
    if ($etkinlik['bitis_tarihi'] && strtotime($etkinlik['bitis_tarihi']) < time()) {
        return false;
    }
    
    // Kontenjan dolmuşsa kayıt kapalı
    if ($etkinlik['max_katilimci'] > 0 && $etkinlik['kayit_sayisi'] >= $etkinlik['max_katilimci']) {
        return false;
    }
    
    return true;
}

/**
 * Etkinlik durum badge'i
 */
function get_etkinlik_durum_badge($etkinlik) {
    if (is_etkinlik_kayit_acik($etkinlik)) {
        return '<span class="status-badge status-active">Aktif</span>';
    }
    
    if ($etkinlik['bitis_tarihi'] && strtotime($etkinlik['bitis_tarihi']) < time()) {
        return '<span class="status-badge status-completed">Tamamlandı</span>';
    }
    
    if ($etkinlik['max_katilimci'] > 0 && $etkinlik['kayit_sayisi'] >= $etkinlik['max_katilimci']) {
        return '<span class="status-badge status-full">Kontenjan Doldu</span>';
    }
    
    return '<span class="status-badge status-inactive">Pasif</span>';
}

/**
 * Etkinlik tarihi formatla
 */
function format_etkinlik_tarihi($baslangic, $bitis = null, $saat = null) {
    $output = '';
    
    if ($baslangic) {
        $baslangic_obj = new DateTime($baslangic);
        $output .= $baslangic_obj->format('d F');
        
        if ($bitis && $bitis != $baslangic) {
            $bitis_obj = new DateTime($bitis);
            $output .= ' - ' . $bitis_obj->format('d F');
        }
    }
    
    if ($saat) {
        $output .= ' | ' . $saat;
    }
    
    return $output;
}

/**
 * Türkçe ay adları
 */
function tr_ay_adi($tarih) {
    $aylar = [
        'January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart',
        'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran',
        'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül',
        'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'
    ];
    
    foreach ($aylar as $en => $tr) {
        $tarih = str_replace($en, $tr, $tarih);
    }
    
    return $tarih;
}

/**
 * Program takvimini decode et
 */
function get_program_takvimi($etkinlik) {
    if (empty($etkinlik['program_takvimi'])) {
        return [];
    }
    
    $program = json_decode($etkinlik['program_takvimi'], true);
    return is_array($program) ? $program : [];
}

/**
 * Galeri resimlerini decode et
 */
function get_galeri_resimleri($etkinlik) {
    if (empty($etkinlik['galeri_resimleri'])) {
        return [];
    }
    
    $galeri = json_decode($etkinlik['galeri_resimleri'], true);
    return is_array($galeri) ? $galeri : [];
}

/**
 * Konuşmacıları decode et
 */
function get_konusmacilar($etkinlik) {
    if (empty($etkinlik['konusmacilar'])) {
        return [];
    }
    
    $konusmacilar = json_decode($etkinlik['konusmacilar'], true);
    return is_array($konusmacilar) ? $konusmacilar : [];
}

/**
 * Etkinlik resminin yolunu al
 */
function get_etkinlik_resmi($etkinlik, $default = 'assets/img/blog/default.jpg') {
    if (!empty($etkinlik['etkinlik_resmi']) && file_exists($etkinlik['etkinlik_resmi'])) {
        return $etkinlik['etkinlik_resmi'];
    }
    
    // Slug'a göre resim ara
    $slug_image = 'assets/img/blog/' . $etkinlik['etkinlik_slug'] . '.jpg';
    if (file_exists($slug_image)) {
        return $slug_image;
    }
    
    return $default;
}

/**
 * YouTube video ID'sini çıkar
 */
function get_youtube_id($url) {
    if (empty($url)) {
        return null;
    }
    
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
    
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    
    return null;
}

/**
 * Vimeo video ID'sini çıkar
 */
function get_vimeo_id($url) {
    if (empty($url)) {
        return null;
    }
    
    $pattern = '/(?:vimeo\.com\/)([0-9]+)/i';
    
    if (preg_match($pattern, $url, $matches)) {
        return $matches[1];
    }
    
    return null;
}

/**
 * Video embed HTML'i oluştur
 */
function get_video_embed($url) {
    if (empty($url)) {
        return '';
    }
    
    // YouTube kontrolü
    $youtube_id = get_youtube_id($url);
    if ($youtube_id) {
        return '<div class="video-container">
                    <iframe src="https://www.youtube.com/embed/' . $youtube_id . '" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>';
    }
    
    // Vimeo kontrolü
    $vimeo_id = get_vimeo_id($url);
    if ($vimeo_id) {
        return '<div class="video-container">
                    <iframe src="https://player.vimeo.com/video/' . $vimeo_id . '" 
                            frameborder="0" 
                            allow="autoplay; fullscreen; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>';
    }
    
    return '';
}

/**
 * Dosya upload fonksiyonu
 */
function upload_etkinlik_dosyasi($file, $etkinlik_slug, $tip = 'resim') {
    // Upload dizinini oluştur
    $upload_dir = 'uploads/etkinlikler/' . $etkinlik_slug . '/';
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Dosya uzantısını al
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // İzin verilen uzantılar
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    if (!in_array($file_ext, $allowed_exts)) {
        return ['success' => false, 'message' => 'Sadece resim dosyaları yüklenebilir.'];
    }
    
    // Maksimum dosya boyutu (5MB)
    $max_size = 5 * 1024 * 1024;
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'Dosya boyutu 5MB\'dan büyük olamaz.'];
    }
    
    // Benzersiz dosya adı oluştur
    $new_filename = $tip . '-' . time() . '-' . uniqid() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_filename;
    
    // Dosyayı taşı
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'path' => $upload_path, 'filename' => $new_filename];
    }
    
    return ['success' => false, 'message' => 'Dosya yüklenemedi.'];
}

/**
 * Etkinlik resmini sil
 */
function delete_etkinlik_resmi($resim_yolu) {
    if (file_exists($resim_yolu)) {
        return unlink($resim_yolu);
    }
    return false;
}

/**
 * Tarih formatla (Türkçe)
 */
function format_tarih_tr($tarih) {
    if (empty($tarih)) {
        return '';
    }
    
    $date = new DateTime($tarih);
    $gun = $date->format('d');
    $ay = $date->format('F');
    $yil = $date->format('Y');
    
    $aylar = [
        'January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart',
        'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran',
        'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül',
        'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'
    ];
    
    $ay_tr = $aylar[$ay] ?? $ay;
    
    return $gun . ' ' . $ay_tr . ' ' . $yil;
}

/**
 * Gün sayısını hesapla
 */
function get_gun_farki($baslangic, $bitis) {
    if (empty($baslangic) || empty($bitis)) {
        return 0;
    }
    
    $date1 = new DateTime($baslangic);
    $date2 = new DateTime($bitis);
    $diff = $date1->diff($date2);
    
    return $diff->days + 1; // Başlangıç gününü de say
}
?>