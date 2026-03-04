<?php
// etkinlik-detay.php - Modern Showroom Etkinlik Detay Sayfası
// V2.0 - Resim, Takvim, Galeri, Video desteği

require_once 'config.php';
require_once 'etkinlik_helpers.php';

// Slug kontrolü
if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: etkinlikler.php');
    exit;
}

$slug = sanitize_input($_GET['slug']);
$etkinlik = get_etkinlik_by_slug($slug);

if (!$etkinlik) {
    header('Location: etkinlikler.php');
    exit;
}

$kayit_acik = is_etkinlik_kayit_acik($etkinlik);
$page_title = htmlspecialchars($etkinlik['etkinlik_adi']);

// Program, galeri ve konuşmacılar
$program = get_program_takvimi($etkinlik);
$galeri = get_galeri_resimleri($etkinlik);
$konusmacilar = get_konusmacilar($etkinlik);
$etkinlik_resmi = get_etkinlik_resmi($etkinlik);
?>
<!doctype html>
<html class="no-js" lang="tr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= $page_title ?> - Kayıt | ERÜ Yapay Zeka Kulübü</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= htmlspecialchars($etkinlik['aciklama']) ?>">
    
    <link rel="icon" href="assets/img/logo/fav1.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/font-awesome-pro.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #00b894;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #f8f9fa;
        }

        body {
            background: var(--light);
        }

        /* Hero Section */
        .event-hero {
            position: relative;
            height: 500px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            overflow: hidden;
        }

        .event-hero-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.3;
        }

        .event-hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .event-hero-content {
            text-align: center;
            color: white;
            z-index: 10;
            padding: 20px;
            max-width: 1200px;
        }

        .event-hero-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .event-hero-meta {
            display: flex;
            gap: 30px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .event-hero-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 18px;
        }

        .event-hero-meta-item i {
            font-size: 24px;
        }

        /* Back Link */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
            transition: all 0.3s;
        }

        .back-link:hover {
            color: var(--secondary);
            transform: translateX(-5px);
        }

        /* Content Area */
        .event-content-area {
            padding: 60px 0;
        }

        /* Info Cards */
        .info-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .info-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .info-card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 15px;
        }

        .info-card-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .info-card-value {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
        }

        /* Main Card */
        .main-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary);
        }

        .event-description {
            font-size: 16px;
            line-height: 1.8;
            color: #6c757d;
            margin-bottom: 30px;
        }

        /* Program Timeline */
        .program-timeline {
            position: relative;
            padding-left: 40px;
        }

        .program-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(180deg, var(--primary), var(--secondary));
        }

        .timeline-item {
            position: relative;
            margin-bottom: 30px;
            padding-left: 30px;
        }

        .timeline-dot {
            position: absolute;
            left: -25px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            border: 3px solid white;
            box-shadow: 0 0 0 3px var(--primary);
        }

        .timeline-date {
            font-size: 13px;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .timeline-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .timeline-description {
            font-size: 15px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .timeline-time {
            font-size: 14px;
            color: #95a5a6;
        }

        /* Video Section */
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
            overflow: hidden;
            border-radius: 12px;
            margin-bottom: 30px;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        /* Galeri */
        .event-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .gallery-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
            aspect-ratio: 1;
        }

        .gallery-item:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Konuşmacılar */
        .speakers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .speaker-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .speaker-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .speaker-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            border: 4px solid var(--primary);
        }

        .speaker-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .speaker-title {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .speaker-social {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .speaker-social a {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s;
        }

        .speaker-social a:hover {
            background: var(--secondary);
            transform: scale(1.1);
        }

        /* Kayıt Formu */
        .registration-sidebar {
            position: sticky;
            top: 20px;
        }

        .registration-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }

        .registration-card h3 {
            font-size: 22px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .kvkk-checkbox {
            display: flex;
            align-items: start;
            gap: 10px;
            margin: 20px 0;
        }

        .kvkk-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
        }

        .kvkk-checkbox label {
            font-size: 13px;
            color: #6c757d;
            line-height: 1.6;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert.show {
            display: block;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .registration-closed {
            background: #fff3cd;
            color: #856404;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            border-left: 4px solid #ffc107;
        }

        .registration-closed i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        /* Status Badge */
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-completed {
            background: #f8d7da;
            color: #721c24;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .event-hero {
                height: 350px;
            }

            .event-hero-title {
                font-size: 32px;
            }

            .event-hero-meta {
                flex-direction: column;
                gap: 15px;
            }

            .main-card {
                padding: 25px;
            }

            .section-title {
                font-size: 22px;
            }

            .registration-sidebar {
                position: static;
                margin-top: 30px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="event-hero">
        <img src="<?= $etkinlik_resmi ?>" alt="<?= $page_title ?>" class="event-hero-image">
        <div class="event-hero-overlay">
            <div class="event-hero-content">
                <h1 class="event-hero-title"><?= $page_title ?></h1>
                
                <div class="event-hero-meta">
                    <?php if ($etkinlik['baslangic_tarihi']): ?>
                    <div class="event-hero-meta-item">
                        <i class="fa-regular fa-calendar"></i>
                        <span><?= format_tarih_tr($etkinlik['baslangic_tarihi']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($etkinlik['saat']): ?>
                    <div class="event-hero-meta-item">
                        <i class="fa-regular fa-clock"></i>
                        <span><?= htmlspecialchars($etkinlik['saat']) ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($etkinlik['konum']): ?>
                    <div class="event-hero-meta-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span><?= htmlspecialchars($etkinlik['konum']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="event-content-area">
        <div class="container">
            <a href="etkinlikler.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Etkinliklere Dön
            </a>

            <!-- Info Cards -->
            <div class="info-cards">
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="info-card-label">Katılımcı</div>
                    <div class="info-card-value">
                        <?= $etkinlik['kayit_sayisi'] ?><?php if ($etkinlik['max_katilimci'] > 0): ?> / <?= $etkinlik['max_katilimci'] ?><?php endif; ?>
                    </div>
                </div>

                <?php if ($etkinlik['baslangic_tarihi'] && $etkinlik['bitis_tarihi']): ?>
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div class="info-card-label">Süre</div>
                    <div class="info-card-value">
                        <?= get_gun_farki($etkinlik['baslangic_tarihi'], $etkinlik['bitis_tarihi']) ?> Gün
                    </div>
                </div>
                <?php endif; ?>

                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fa-solid fa-signal"></i>
                    </div>
                    <div class="info-card-label">Seviye</div>
                    <div class="info-card-value">Tüm Seviyeler</div>
                </div>

                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                    <div class="info-card-label">Durum</div>
                    <div class="info-card-value">
                        <?= get_etkinlik_durum_badge($etkinlik) ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Açıklama -->
                    <?php if ($etkinlik['aciklama']): ?>
                    <div class="main-card">
                        <h2 class="section-title">Etkinlik Hakkında</h2>
                        <div class="event-description">
                            <?= nl2br(htmlspecialchars($etkinlik['aciklama'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Detaylı Açıklama -->
                    <?php if ($etkinlik['detayli_aciklama']): ?>
                    <div class="main-card">
                        <h2 class="section-title">Detaylı Bilgi</h2>
                        <div class="event-description">
                            <?= nl2br(htmlspecialchars($etkinlik['detayli_aciklama'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Program Takvimi -->
                    <?php if (count($program) > 0): ?>
                    <div class="main-card">
                        <h2 class="section-title">
                            <i class="fa-solid fa-calendar-week"></i> Program Takvimi
                        </h2>
                        <div class="program-timeline">
                            <?php foreach ($program as $gun): ?>
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date"><?= htmlspecialchars($gun['gun']) ?> - <?= format_tarih_tr($gun['tarih']) ?></div>
                                <div class="timeline-title"><?= htmlspecialchars($gun['baslik']) ?></div>
                                <div class="timeline-description"><?= htmlspecialchars($gun['aciklama']) ?></div>
                                <?php if (!empty($gun['saat'])): ?>
                                <div class="timeline-time">
                                    <i class="fa-regular fa-clock"></i> <?= htmlspecialchars($gun['saat']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Video -->
                    <?php if (!empty($etkinlik['video_linki'])): ?>
                    <div class="main-card">
                        <h2 class="section-title">
                            <i class="fa-solid fa-video"></i> Tanıtım Videosu
                        </h2>
                        <?= get_video_embed($etkinlik['video_linki']) ?>
                    </div>
                    <?php endif; ?>

                    <!-- Konuşmacılar -->
                    <?php if (count($konusmacilar) > 0): ?>
                    <div class="main-card">
                        <h2 class="section-title">
                            <i class="fa-solid fa-user-tie"></i> Konuşmacılar
                        </h2>
                        <div class="speakers-grid">
                            <?php foreach ($konusmacilar as $konusmaci): ?>
                            <div class="speaker-card">
                                <?php if (!empty($konusmaci['fotograf'])): ?>
                                <img src="<?= htmlspecialchars($konusmaci['fotograf']) ?>" 
                                     alt="<?= htmlspecialchars($konusmaci['ad_soyad']) ?>" 
                                     class="speaker-avatar">
                                <?php else: ?>
                                <div class="speaker-avatar" style="display: flex; align-items: center; justify-content: center; background: var(--primary); color: white; font-size: 32px; font-weight: 700;">
                                    <?= strtoupper(substr($konusmaci['ad_soyad'], 0, 1)) ?>
                                </div>
                                <?php endif; ?>
                                <div class="speaker-name"><?= htmlspecialchars($konusmaci['ad_soyad']) ?></div>
                                <div class="speaker-title"><?= htmlspecialchars($konusmaci['unvan']) ?></div>
                                <?php if (!empty($konusmaci['linkedin'])): ?>
                                <div class="speaker-social">
                                    <a href="<?= htmlspecialchars($konusmaci['linkedin']) ?>" target="_blank">
                                        <i class="fa-brands fa-linkedin-in"></i>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Galeri -->
                    <?php if (count($galeri) > 0): ?>
                    <div class="main-card">
                        <h2 class="section-title">
                            <i class="fa-solid fa-images"></i> Galeri
                        </h2>
                        <div class="event-gallery">
                            <?php foreach ($galeri as $resim): ?>
                            <div class="gallery-item">
                                <img src="<?= htmlspecialchars($resim) ?>" alt="Etkinlik Görseli">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="registration-sidebar">
                        <div class="registration-card">
                            <?php if ($kayit_acik): ?>
                                <h3>
                                    <i class="fa-solid fa-user-plus"></i> Etkinliğe Kayıt Ol
                                </h3>

                                <div id="alertBox" class="alert"></div>

                                <form id="registrationForm">
                                    <input type="hidden" name="etkinlik_slug" value="<?= htmlspecialchars($slug) ?>">

                                    <div class="form-group">
                                        <label for="ad_soyad">Ad Soyad *</label>
                                        <input type="text" id="ad_soyad" name="ad_soyad" class="form-control" required placeholder="Adınız ve Soyadınız">
                                    </div>

                                    <div class="form-group">
                                        <label for="email">E-posta *</label>
                                        <input type="email" id="email" name="email" class="form-control" required placeholder="ornek@email.com">
                                    </div>

                                    <div class="form-group">
                                        <label for="bolum">Bölüm *</label>
                                        <input type="text" id="bolum" name="bolum" class="form-control" required placeholder="Örn: Bilgisayar Mühendisliği">
                                    </div>

                                    <div class="kvkk-checkbox">
                                        <input type="checkbox" id="kvkk_onay" name="kvkk_onay" required>
                                        <label for="kvkk_onay">
                                            <a href="kvkk.html" target="_blank" style="color: var(--primary);">KVKK Aydınlatma Metni</a>'ni okudum ve kabul ediyorum. *
                                        </label>
                                    </div>

                                    <button type="submit" class="submit-btn" id="submitBtn">
                                        <i class="fa-solid fa-paper-plane"></i> Kayıt Ol
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="registration-closed">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                    <h4>Kayıtlar Kapalı</h4>
                                    <p>
                                        <?php if ($etkinlik['max_katilimci'] > 0 && $etkinlik['kayit_sayisi'] >= $etkinlik['max_katilimci']): ?>
                                            Bu etkinlik için kontenjan dolmuştur.
                                        <?php elseif ($etkinlik['bitis_tarihi'] && strtotime($etkinlik['bitis_tarihi']) < time()): ?>
                                            Bu etkinlik tamamlanmıştır.
                                        <?php else: ?>
                                            Bu etkinlik için kayıtlar şu anda kapalıdır.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('registrationForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const alertBox = document.getElementById('alertBox');
        const formData = new FormData(this);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Gönderiliyor...';

        fetch('kayit_islem.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alertBox.className = 'alert show alert-' + (data.success ? 'success' : 'danger');
            alertBox.textContent = data.message;

            if (data.success) { 
                this.reset();
                setTimeout(() => {
                    alertBox.classList.remove('show');
                }, 5000);
            }

            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kayıt Ol';
        })
        .catch(error => {
            alertBox.className = 'alert show alert-danger';
            alertBox.textContent = 'Bir hata oluştu. Lütfen tekrar deneyin.';

            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kayıt Ol';
        });
    });
    </script>
</body>
</html>