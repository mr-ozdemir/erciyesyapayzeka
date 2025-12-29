<?php
require_once "config.php";
require_once "etkinlik_helpers.php";
?>
<!doctype html>
<html class="no-js" lang="tr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Etkinlikler</title>
    <meta name="description" content="Erciyes Üniversitesi Yapay Zeka Kulübü Etkinlikleri Listesi">
    <meta name="keywords" content="ERÜ Yapay Zeka Kulübü, ERU AI CLUB, Yapay Zeka Kulübü, Erciyes Üniversitesi Yapay Zeka Kulübü, Erciyes Yapay Zeka, Yapay Zeka Kulübü, Erü Yapay Zeka">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="assets/img/logo/fav1.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/animate.css">
    <link rel="stylesheet" href="assets/css/font-awesome-pro.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        body {
            background: #f8f9fa;
        }

        /* Grid Layout */
        .events-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Event Card */
        .event-card {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-top: 4px solid #667eea;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

            .event-card.completed {
                border-top-color: #95a5a6;
            }

            .event-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            }

        .event-header {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 15px;
        }

        .event-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            min-width: 50px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            font-size: 22px;
        }

        .event-card.completed .event-icon {
            background: #95a5a6;
        }

        .event-title-wrapper {
            flex: 1;
        }

        .event-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .event-date {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e8eaf6;
            color: #667eea;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .event-card.completed .event-date {
            background: #e9ecef;
            color: #6c757d;
        }

        .event-description {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
            flex: 1;
        }

        .event-requirements {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 15px;
            border-left: 3px solid #667eea;
        }

        .event-card.completed .event-requirements {
            border-left-color: #95a5a6;
        }

        .event-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #d4edda;
            color: #28a745;
        }

            .status-active::before {
                content: "●";
                font-size: 14px;
            }

        .status-completed {
            background: #e2e3e5;
            color: #6c757d;
        }

            .status-completed::before {
                content: "✓";
                font-size: 12px;
            }

        /* Event Button */
        .event-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

            .event-btn:hover {
                background: #5a67d8;
                color: white;
                transform: translateX(5px);
            }

        .event-card.completed .event-btn {
            background: #95a5a6;
        }

            .event-card.completed .event-btn:hover {
                background: #7f8c8d;
            }

        /* Section Header */
        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

            .section-header h2 {
                font-size: 34px;
                font-weight: 700;
                color: #2c3e50;
                margin-bottom: 10px;
            }

            .section-header p {
                font-size: 15px;
                color: #6c757d;
            }

        /* Responsive */
        @media (max-width: 991px) {
            .events-grid {
                grid-template-columns: 1fr;
                gap: 25px;
            }
        }

        @media (max-width: 767px) {
            .event-card {
                padding: 20px;
            }

            .event-title {
                font-size: 16px;
            }

            .section-header h2 {
                font-size: 28px;
            }

            .event-header {
                gap: 12px;
            }

            .event-icon {
                width: 45px;
                height: 45px;
                min-width: 45px;
                font-size: 20px;
            }
        }

        /* Footer Küçültme CSS */
        .footer-area {
            font-size: 13px;
        }

        .footernewsletter__title {
            font-size: 20px !important;
        }

        .footercontact__icon {
            font-size: 28px !important;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

            .footercontact__icon i {
                font-size: 22px !important;
                color: #ffffff;
            }

        .fw-pink-icon i {
            color: #ff6b9d !important;
        }

        .fw-green-icon i {
            color: #00d9a0 !important;
        }

        .footercontact__title,
        .footercontact__content {
            font-size: 13px !important;
        }

            .footercontact__title a,
            .footercontact__content a,
            .footercontact__content span {
                font-size: 13px !important;
            }

        .footer-widget__copyright {
            font-size: 12px !important;
        }

            .footer-widget__copyright span,
            .footer-widget__copyright a {
                font-size: 12px !important;
            }

        .footercontact {
            margin-bottom: 25px !important;
        }

        .footer-area.pt-105 {
            padding-top: 70px !important;
        }

        .tp-footer-top.pb-25 {
            padding-bottom: 15px !important;
        }
    </style>
</head>
<body>
    <button class="scroll-top scroll-to-target" data-target="html">
        <i class="fas fa-angle-up"></i>
    </button>

    <div id="preloadertp">
        <img src="assets/img/preloader.png" alt="">
    </div>

    <header class="d-none d-xl-block">
        <div class="header-custom" id="header-sticky">
            <div class="header-logo-box">
                <a href="index"><img src="assets/img/logo/logo.png" alt="logo"></a>
            </div>
            <div class="header-menu-box">
                <div class="header-menu-bottom">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="main-menu main-menu-second">
                                <nav id="mobile-menu">
                                    <ul>
                                        <li><a href="index">Ana Sayfa</a></li>
                                        <li><a class="active" href="etkinlikler.html">Etkinlikler</a></li>
                                        <li><a href="research">Projeler</a></li>
                                        <li><a href="uyeol">Üye Ol</a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div id="header-mob-sticky" class="tp-mobile-header-area pt-15 pb-15 d-xl-none">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 col-10">
                    <div class="tp-mob-logo">
                        <a href="index"><img src="assets/img/logo/logo.png" alt="logo"></a>
                    </div>
                </div>
                <div class="col-md-8 col-2">
                    <div class="tp-mobile-bar d-flex align-items-center justify-content-end">
                        <button class="tp-menu-toggle"><i class="far fa-bars"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tpsideinfo tp-side-info-area">
        <button class="tpsideinfo__close"><i class="fal fa-times"></i></button>
        <div class="tpsideinfo__logo mb-40">
            <a href="index"><img src="assets/img/logo/logo.png" alt="logo"></a>
        </div>
        <div class="mobile-menu"></div>
    </div>

    <div class="body-overlay"></div>

    <main>
        <section class="breadcrumb__area pt-100 pb-140 breadcrumb__overlay" data-background="assets/img/banner/breadcrumb-01.jpg">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <div class="tp-breadcrumb text-center">
                            <h2 class="tp-breadcrumb__title">Etkinlikler</h2>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="events-area pt-130 pb-100">
            <div class="container">
                <div class="section-header">
                    </br>
                    <h2>Etkinlik Takvimi</h2>
                    <p>Yapay Zeka Kulübü tarafından düzenlenen eğitim ve etkinlikler</p>
                </div>

                <?php
                // Tüm etkinlikleri getir
                $tum_etkinlikler = get_tum_etkinlikler();
                ?>

                <div class="events-grid">
                    <?php if (count($tum_etkinlikler) > 0): ?>
                        <?php 
                        $delay = 0.1;
                        foreach ($tum_etkinlikler as $etkinlik): 
                            $kayit_acik = is_etkinlik_kayit_acik($etkinlik);
                            $is_completed = $etkinlik['bitis_tarihi'] && strtotime($etkinlik['bitis_tarihi']) < time();
                        ?>
                            <div class="event-card <?= $is_completed ? 'completed' : '' ?> wow fadeInUp" data-wow-delay=".<?= (int)($delay * 10) ?>s">
                                <div class="event-header">
                                    <div class="event-icon">
                                        <i class="fa-solid fa-graduation-cap"></i>
                                    </div>
                                    <div class="event-title-wrapper">
                                        <h3 class="event-title"><?= htmlspecialchars($etkinlik['etkinlik_adi']) ?></h3>
                                        <span class="event-date">
                                            <i class="fa-regular fa-calendar"></i>
                                            <?= tr_ay_adi(format_etkinlik_tarihi($etkinlik['baslangic_tarihi'], $etkinlik['bitis_tarihi'], $etkinlik['saat'])) ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <?php if ($etkinlik['aciklama']): ?>
                                <p class="event-description">
                                    <?= htmlspecialchars(mb_substr($etkinlik['aciklama'], 0, 200, 'UTF-8')) ?>
                                    <?= mb_strlen($etkinlik['aciklama'], 'UTF-8') > 200 ? '...' : '' ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php if ($etkinlik['konum']): ?>
                                <div class="event-requirements">
                                    📍 Konum: <?= htmlspecialchars($etkinlik['konum']) ?>
                                </div>
                                <?php endif; ?>
                                
                                <div class="event-footer">
                                    <?= get_etkinlik_durum_badge($etkinlik) ?>
                                    <a href="etkinlik-detay.php?slug=<?= urlencode($etkinlik['etkinlik_slug']) ?>" class="event-btn">
                                        Detaylar <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        <?php 
                            $delay += 0.1;
                            if ($delay > 0.9) $delay = 0.1;
                        endforeach; 
                        ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fa-light fa-calendar-xmark" style="font-size: 64px; color: #ccc;"></i>
                                <p class="mt-3" style="color: #6c757d; font-size: 18px;">Henüz etkinlik eklenmemiş.</p>
                                <small class="text-muted">Panel'den yeni etkinlik ekleyebilirsiniz.</small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                        </div>
    
                     
                      
                    </div>

                </div>
            </div>
            </br>
        </section>
    </main>

    <footer>
        <div class="footer-area tp-common-area footer-white-content theme-bg pt-105">
            <div class="tp-footer-top pb-25">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-7 col-md-6">
                            <div class="footernewsletter mb-40 wow fadeInUp" data-wow-delay=".2s">
                                <h4 class="footernewsletter__title">Bize Ulaşın</h4>
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-6">
                            <div class="footernewsletter__form p-relative wow fadeInUp" data-wow-delay=".4s">
                            </div>
                        </div>
                    </div>
                    <div class="row pb-40 pt-50">
                        <div class="col-lg-4 col-md-6">
                            <div class="footercontact mb-40 wow fadeInUp" data-wow-delay=".2s">
                                <div class="footercontact__icon">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div class="footercontact__content">
                                    <span class="footercontact__title"><a href="contact.html">Mühendislik Fakültesi - Bilgisayar Mühendisliği <br>Erciyes Üniversitesi</a></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="footercontact mb-40 wow fadeInUp" data-wow-delay=".4s">
                                <div class="footercontact__icon fw-pink-icon">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <div class="footercontact__content fw-pink-content">
                                    <a href="mailto:info@erciyesyapayzeka.com.tr">info@erciyesyapayzeka.com.tr</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="footercontact mb-40 wow fadeInUp" data-wow-delay=".6s">
                                <div class="footercontact__icon fw-green-icon">
                                    <i class="fa-solid fa-clock"></i>
                                </div>
                                <div class="footercontact__content">
                                    <span>Pazartesi - Cuma - 09:00 - 21:00</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="footercontact mb-40 wow fadeInUp" data-wow-delay=".6s">
                                <div class="tp-footer-widget__social fw-social">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-area-bottom-tp">
                <div class="container">
                    <div class="footer-bottom-border">
                        <div class="row">
                            <div class="col-xxl-6 col-xl-7 col-lg-6 col-md-12 col-12">
                                <div class="footer-widget__copyright cpy-white-content">
                                    <span>© Copyright ©202 <a href="index">Erciyes Üniversitesi Yapay Zeka Kulübü</a>. <i>All Rights Reserved Copyright</i></span>
                                </div>
                            </div>
                            <div class="col-xxl-6 col-xl-5 col-lg-6 col-md-12 col-12">
                                <div class="footer-widget__copyright-info info-direction cpy-white-content-info">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/waypoints.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/wow.js"></script>
    <script src="assets/js/aos.js"></script>
    <script src="assets/js/meanmenu.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>