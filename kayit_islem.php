<?php


require_once __DIR__ . '/config.php'; // DİKKAT: ../ DEĞİL
header('Content-Type: application/json; charset=utf-8');

// CORS (gerekirse)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Sadece POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Geçersiz istek metodu');
}


// POST verilerini al
$etkinlik_slug = isset($_POST['etkinlik_slug']) ? sanitize_input($_POST['etkinlik_slug']) : '';
$ad_soyad = isset($_POST['ad_soyad']) ? sanitize_input($_POST['ad_soyad']) : '';
$email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
$bolum = isset($_POST['bolum']) ? sanitize_input($_POST['bolum']) : '';
$kvkk_onay = isset($_POST['kvkk_onay']) ? true : false;

// Validasyon
$errors = [];

if (empty($ad_soyad) || strlen($ad_soyad) < 3) {
    $errors[] = 'Ad Soyad en az 3 karakter olmalıdır.';
}

if (empty($email) || !validate_email($email)) {
    $errors[] = 'Geçerli bir e-posta adresi giriniz.';
}

if (empty($bolum)) {
    $errors[] = 'Bölüm alanı zorunludur.';
}

if (!$kvkk_onay) {
    $errors[] = 'KVKK metnini onaylamanız gerekmektedir.';
}

if (empty($etkinlik_slug)) {
    $errors[] = 'Etkinlik seçimi zorunludur.';
}

// Hata varsa döndür
if (!empty($errors)) {
    json_response(false, implode(' ', $errors));
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Etkinliği kontrol et
    $stmt = $db->prepare("SELECT id, etkinlik_adi, aktif, max_katilimci FROM etkinlikler WHERE etkinlik_slug = ?");
    $stmt->execute([$etkinlik_slug]);
    $etkinlik = $stmt->fetch();
    
    if (!$etkinlik) {
        json_response(false, 'Etkinlik bulunamadı.');
    }
    
    if (!$etkinlik['aktif']) {
        json_response(false, 'Bu etkinlik için kayıtlar kapalıdır.');
    }
    
    // Kayıt sayısını kontrol et (max_katilimci 0 ise sınırsız)
    if ($etkinlik['max_katilimci'] > 0) {
        $stmt = $db->prepare("SELECT COUNT(*) as toplam FROM kayitlar WHERE etkinlik_id = ? AND durum != 'iptal'");
        $stmt->execute([$etkinlik['id']]);
        $kayit_sayisi = $stmt->fetch()['toplam'];
        
        if ($kayit_sayisi >= $etkinlik['max_katilimci']) {
            json_response(false, 'Bu etkinlik için kontenjan dolmuştur.');
        }
    }
    
    // Aynı email ile kayıt var mı kontrol et
    $stmt = $db->prepare("SELECT id FROM kayitlar WHERE etkinlik_id = ? AND email = ?");
    $stmt->execute([$etkinlik['id'], $email]);
    
    if ($stmt->fetch()) {
        json_response(false, 'Bu e-posta adresi ile zaten kayıt yapılmış.');
    }
    
    // Kayıt ekle
    $ip_adresi = get_user_ip();
    $stmt = $db->prepare("
        INSERT INTO kayitlar (etkinlik_id, ad_soyad, email, bolum, kvkk_onay, ip_adresi, durum) 
        VALUES (?, ?, ?, ?, ?, ?, 'onaylandi')
    ");
    
    $stmt->execute([
        $etkinlik['id'],
        $ad_soyad,
        $email,
        $bolum,
        $kvkk_onay ? 1 : 0,
        $ip_adresi
    ]);
    
    // Başarılı kayıt
    // Email gönderimi eklenebilir (mail() fonksiyonu ile)
    
    json_response(true, 'Kaydınız başarıyla alınmıştır! Katılım bilgileri e-posta adresinize gönderilecektir.', [
        'kayit_id' => $db->lastInsertId(),
        'etkinlik' => $etkinlik['etkinlik_adi']
    ]);
    
} catch(PDOException $e) {
    // Üretim ortamında detaylı hata mesajı göstermeyin
    json_response(false, 'Bir hata oluştu. Lütfen tekrar deneyin.');
    // Loglama yapılabilir: error_log($e->getMessage());
}
?>
