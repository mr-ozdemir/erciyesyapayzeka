<?php
require_once "config.php";
require_once "etkinlik_helpers.php";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ERÜ YAPAY ZEKA KULÜBÜ</title>
  <meta name="description" content="ERÜ Yapay Zeka Kulübü ">
  <meta name="keywords" content="Yapay Zeka Kulübü, ERÜ Yapay Zeka, ERU AI CLUB, Erciyes Üniversitesi Yapay Zeka Kulübü, Erciyes Yapay Zeka, Yapay Zeka Kulübü, Erü Yapay Zeka">
  <meta name="google-site-verification" content="8MybSn_dUVt5PeYUSH69GpiKlEWYMUPmImmvS9m-XT0" />
  <link rel="canonical" href="https://erciyesyapayzeka.com.tr" />

  <link rel="icon" href="assets/img/logo/fav1.png" type="image/x-icon">
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/animate.css">
  <link rel="stylesheet" href="assets/css/swiper-bundle.css">
  <link rel="stylesheet" href="assets/css/slick.css">
  <link rel="stylesheet" href="assets/css/aos.css">
  <link rel="stylesheet" href="assets/css/magnific-popup.css">
  <link rel="stylesheet" href="assets/css/font-awesome-pro.css">
  <link rel="stylesheet" href="assets/css/flaticon.css">
  <link rel="stylesheet" href="assets/css/spacing.css">
  <link rel="stylesheet" href="assets/css/nice-select.css">
  <link rel="stylesheet" href="assets/css/meanmenu.css">
  <link rel="stylesheet" href="assets/css/style.css">

 <style>
  /* ====== GLOBAL RESET & CONTAINER SAFETY ====== */
  :root{ --sidebar-w: 248px }                              /* Desktop sol panel genişliği */
  *{ box-sizing: border-box }
  html,body{ overflow-x:hidden; max-width:100% }
  body{ position:relative; background:#fff }

  .container,.container-fluid{ overflow-x:hidden; max-width:100% }

  /* İçerik bölgelerinde sabit padding – slider hariç */
  .about-area,.statistics-area,.blog-area,.footer-area{ padding-left:50px; padding-right:50px }
  @media (max-width:1399px){
    .about-area,.statistics-area,.blog-area,.footer-area{ padding-left:40px; padding-right:40px }
  }
  @media (max-width:1199px){
    .about-area,.statistics-area,.blog-area,.footer-area{ padding-left:30px; padding-right:30px }
  }
  @media (max-width:991px){
    .about-area,.statistics-area,.blog-area,.footer-area{ padding-left:20px; padding-right:20px }
  }
  @media (max-width:767px){
    .about-area,.statistics-area,.blog-area,.footer-area{ padding-left:15px; padding-right:15px }
  }

  /* ====== SOL NAVBAR – SADE, SOLA YASLI ====== */
  .header-layout-left{
    position:fixed; top:0; left:0; height:100vh;
    overflow-y:auto; overflow-x:hidden;
    border:0 !important; box-shadow:none !important;
    z-index:1001; /* sidebar içerikten önde */
  }
  .header-customss{ display:flex; flex-direction:column; min-height:100vh; padding:20px 0 }
  .header-left-logo{ padding-bottom:12px !important }
  .header-left-menu{ flex:1; display:flex; flex-direction:column; justify-content:flex-start }
  .header-left-menu nav ul{ padding:0; margin:0; list-style:none; text-align:left }
  .header-left-menu nav ul li{ margin-bottom:12px }
  .header-left-menu nav ul li a{ 
    display:block; 
    padding:10px 0; 
    text-align:left;
    padding-left:0 !important;
    margin-left:0 !important;
  }

  /* Desktop: sidebar daralt, içerik sağa kaydır, çizgi/boşlukları temizle */
  @media (min-width:1200px){
    .header-layout-left{
      width:var(--sidebar-w) !important;
      min-width:var(--sidebar-w) !important;
      max-width:var(--sidebar-w) !important;
    }
    .content-layout-right{
      margin-left:var(--sidebar-w) !important; width:auto; position:relative; z-index:1;
    }
    .header-left-menu,
    .header-left-menu::before,
    .header-left-menu::after,
    .header-layout-left::before,
    .header-layout-left::after{
      border:0 !important; box-shadow:none !important; content:none !important;
    }
    .header-layout-left .header-customss{ padding-left:20px !important; padding-right:14px !important }
    .header-left-menu #mobile-menu>ul{ padding-left:0 !important; margin-left:0 !important }
    .header-left-menu #mobile-menu>ul>li{ margin-left:0 !important }
    .header-left-menu #mobile-menu>ul>li>a{ padding:10px 0 !important }
  }

  /* Kısa yükseklik ekranlar için sıkılaştırma */
  @media (min-width:1200px) and (max-height:800px){
    .header-left-menu nav ul li{ margin-bottom:10px }
  }
  @media (min-width:1200px) and (max-height:720px){
    .header-customss{ padding:15px 0 }
    .header-left-menu nav ul li{ margin-bottom:8px }
    .header-left-menu nav ul li a{ font-size:15px; padding:8px 0 }
  }
  @media (min-width:1500px){
    .header-left-menu nav ul li{ margin-bottom:18px !important }
  }

  /* ====== SOSYAL İKON + ÜYE GİRİŞ BLOĞU ====== */
  .sidebar-social{
    padding:12px 0; 
    margin-top:16px;
  }
  
  .social-icons{
    display:flex;
    gap:16px; 
    justify-content:flex-start; 
    align-items:center;
    margin-bottom:16px;
  }
  
.social-icons {
  margin-top: 10px;
  display: flex;
  gap: 15px;
}

.social-icons a {
  display: inline-block;
  font-size: 24px;
  transition: color 0.3s ease;
}

.social-icons a:hover {
  transform: translateY(-2px);
}

/* Her ikon için ayrı renk */
.fa-linkedin {
  color: #0077b5; /* LinkedIn mavi */
}

.fa-instagram {
  color: #e4405f; /* Instagram pembe */
}

.fa-github {
  color: white; /* GitHub beyaz */
}
  
  @media (min-width:1200px) and (max-width:1350px){
    .social-icons{ gap:12px }
    .social-icons a{ width:36px; height:36px; font-size:18px }
  }

  .menu-login-wrapper{ 
    display:flex; 
    justify-content:flex-start;
  }
  
  .btn-panel{
    background:#070337;
    color:#fff; 
    border:2px solid rgba(255,255,255,0.15);
    border-radius:8px;
    padding:10px 20px; 
    font-weight:600; 
    font-size:14px;
    text-decoration:none;
    display:inline-flex; 
    align-items:center; 
    gap:8px;
    transition:all .3s ease; 
    white-space:nowrap;
  }
  
  .btn-panel:hover{ 
    color:#fff; 
    background:#fb4f53;
    border-color:#fb4f53;
    transform:translateY(-2px);
    box-shadow:0 4px 12px rgba(251,79,83,0.3);
  }
  
  .btn-panel i{
    font-size:16px;
  }

  /* ====== CAROUSEL KONTROLLERİ ====== */
  .carousel-control-prev,.carousel-control-next{
    display:flex !important; align-items:center; justify-content:center; width:5%;
  }
  .carousel-control-prev-icon,.carousel-control-next-icon{
    background-size:100% 100%; background-image:none;
  }
  .carousel-control-prev-icon::after,.carousel-control-next-icon::after{
    content:''; display:inline-block; width:30px; height:30px;
    background:transparent no-repeat center/100% 100%;
  }
  .carousel-control-prev-icon::after{ background-image:url('assets/img/icons/prev-icon.png') }
  .carousel-control-next-icon::after{ background-image:url('assets/img/icons/next-icon.png') }
  @media (max-width:767.98px){
    .carousel-control-prev,.carousel-control-next{ width:10% }
    .carousel-control-prev-icon::after,.carousel-control-next-icon::after{ width:20px; height:20px }
  }

  /* Başvur butonu */
  .carousel-caption{ position:absolute; bottom:20%; left:50%; transform:translateX(-50%); z-index:10 }
  .basvur-btn{
    display:inline-block; padding:15px 40px;
    background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);
    color:#fff; font-size:18px; font-weight:600; text-decoration:none;
    border-radius:50px; border:2px solid transparent; transition:.3s;
    box-shadow:0 4px 15px rgba(102,126,234,.4); text-transform:uppercase; letter-spacing:1px;
  }
  .basvur-btn:hover{
    background:linear-gradient(135deg,#764ba2 0%,#667eea 100%);
    transform:translate(-50%,-3px);
    box-shadow:0 6px 20px rgba(102,126,234,.6); color:#fff; border-color:#fff;
  }
  @media (max-width:768px){
    .carousel-caption{ bottom:10% }
    .basvur-btn{ padding:12px 30px; font-size:16px }
  }

  /* ====== İSTATİSTİK BÖLÜMÜ ====== */
  .statistics-area{ background:#fff; position:relative; overflow:hidden }
  .statistics-area::before{
    content:''; position:absolute; inset:0;
    background:linear-gradient(135deg,#f8f9fa 0%,#e9ecef 100%); opacity:.4; z-index:0;
  }
  .statistics-area .container{ position:relative; z-index:1 }
  .statistics-area .tp-section__sub-title{ color:#6c757d; font-weight:400; font-size:13px }
  .statistics-area .tp-section__title{ color:#2c3e50; font-weight:600; font-size:22px }

  .stats-card{
    background:#fff; border-radius:10px; padding:25px 15px; text-align:center;
    transition:.3s; box-shadow:0 2px 8px rgba(0,0,0,.05);
    height:100%; display:flex; flex-direction:column; justify-content:center; align-items:center;
    border:1px solid #f0f0f0;
  }
  .stats-card:hover{ transform:translateY(-5px); box-shadow:0 6px 16px rgba(0,0,0,.1); border-color:#667eea }
  .stats-icon{ font-size:32px; color:#667eea; margin-bottom:12px; transition:.3s }
  .stats-card:hover .stats-icon{ color:#5a67d8; transform:scale(1.05) }
  .stats-number{ font-size:30px; font-weight:600; color:#2c3e50; margin-bottom:6px; line-height:1 }
  .stats-title{ font-size:13px; color:#6c757d; font-weight:400; margin:0; line-height:1.3 }

  @media (max-width:1199px){
    .stats-card{ padding:22px 12px }
    .stats-number{ font-size:28px }
    .stats-icon{ font-size:30px }
  }
  @media (max-width:991px){
    .stats-number{ font-size:26px }
    .stats-icon{ font-size:28px }
    .stats-card{ padding:20px 12px }
    .statistics-area .tp-section__title{ font-size:20px }
  }
  @media (max-width:767px){
    .statistics-area{ padding-top:50px !important; padding-bottom:25px !important }
    .statistics-area .tp-section__sub-title{ font-size:12px }
    .statistics-area .tp-section__title{ font-size:19px }
    .stats-card{ padding:20px 12px; margin-bottom:12px }
    .stats-number{ font-size:24px }
    .stats-icon{ font-size:26px; margin-bottom:10px }
    .stats-title{ font-size:12px }
  }
  @media (max-width:575px){
    .statistics-area{ padding-top:40px !important; padding-bottom:20px !important }
    .statistics-area .tp-section__sub-title{ font-size:11px; margin-bottom:10px !important }
    .statistics-area .tp-section__title{ font-size:18px; margin-bottom:20px !important }
    .stats-card{ padding:18px 10px; margin-bottom:10px }
    .stats-number{ font-size:22px }
    .stats-icon{ font-size:24px; margin-bottom:8px }
    .stats-title{ font-size:11px }
    .tp-mob-logo img{ max-height:50px; width:auto }
  }
  @media (max-width:390px){
    .stats-card{ padding:16px 8px }
    .stats-number{ font-size:20px }
    .stats-icon{ font-size:22px }
    .stats-title{ font-size:10px }
    .tp-mob-logo img{ max-height:40px }

  }

  

  /* ====== FOOTER COMPACT STYLING (etkinlikler.php ile aynı) ====== */
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
  <div id="preloadertp"><img src="assets/img/preloader.png" alt=""></div>

  <div class="layout-left-right">
    <!-- SOL SABİT MENÜ (XL ve üzeri) -->
    <div class="header-layout-left theme-bg d-none d-xl-block">
      <header>
        <div class="header-customss">
          <div class="header-left-logo pb-80">
            <!-- <a href="index"><img src="assets/img/logo/Adsız3.png" alt="logo"></a> -->
          </div>

          <div class="header-left-menu">
            <nav id="mobile-menu">
              <ul>
                <li><a class="active" href="index">Ana Sayfa</a></li>
                <li><a href="etkinlikler">Etkinlikler</a></li>
                <!-- <li><a href="fikrimgeldi.html">Fikrim Geldi</a></li> -->
                <li><a href="research">Projeler</a></li>
                <li><a href="uyeol">Üye Ol</a></li>

                <!-- Sosyal medya ikonları -->
                <li>
                  <div class="social-icons">
                    <a href="https://www.linkedin.com/company/erciyes-yapay-zeka/" target="_blank">
                      <i class="fa-brands fa-linkedin"></i>
                    </a>
                    <a href="https://www.instagram.com/eruaiclub/" target="_blank">
                      <i class="fa-brands fa-instagram"></i>
                    </a>
                    <a href="https://github.com/Yapay-Zeka-Kulubu/" target="_blank">
                      <i class="fa-brands fa-github"></i>
                    </a>
                  </div>
                </li>
              </ul>
            </nav>
          </div>

        </div>
      </header>
    </div>

    <!-- MOBİL HEADER (XL altı) -->
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

    <!-- OFF-CANVAS -->
    <div class="tpsideinfo tp-side-info-area">
      <button class="tpsideinfo__close"><i class="fal fa-times"></i></button>
      <div class="tpsideinfo__logo mb-40">
        <a href="index"><img src="assets/img/logo/logo.png" alt="logo"></a>
      </div>
      <div class="mobile-menu"></div>
    </div>
    <div class="body-overlay"></div>

    <!-- İÇERİK -->
    <div class="content-layout-right">
      <!-- HERO / CAROUSEL -->
      <section class="hero-area">
        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <img src="assets/img/banner/b1.png" class="d-block w-100" alt="First slide">
              <div class="carousel-caption d-md-block">
                <a href="datacamp.html" class="basvur-btn">Başvur</a>
              </div>
            </div>
          </div>
          <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
          </button>
        </div>
      </section>

      <!-- İSTATİSTİKLER -->
      <section class="statistics-area tp-common-area pt-80 pb-60">
        <div class="container">
          <div class="row text-center">
            <div class="col-12 mb-35">
              <div class="tp-section">
                <span class="tp-section__sub-title mb-15">Başarılarımız</span>
                <h3 class="tp-section__title">Rakamlarla Biz</h3>
              </div>
            </div>
          </div>
          <div class="row justify-content-center">
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
              <div class="stats-card mb-25 wow fadeInUp" data-wow-delay=".2s">
                <div class="stats-icon"><i class="fa-solid fa-book"></i></div>
                <div class="stats-content">
                  <h2 class="stats-number">5</h2>
                  <p class="stats-title">SCI Yayını</p>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
              <div class="stats-card mb-25 wow fadeInUp" data-wow-delay=".4s">
                <div class="stats-icon"><i class="fa-solid fa-flask"></i></div>
                <div class="stats-content">
                  <h2 class="stats-number">7</h2>
                  <p class="stats-title">TÜBİTAK Proje Desteği</p>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-4">
              <div class="stats-card mb-25 wow fadeInUp" data-wow-delay=".6s">
                <div class="stats-icon"><i class="fa-solid fa-trophy"></i></div>
                <div class="stats-content">
                  <h2 class="stats-number">2</h2>
                  <p class="stats-title">Teknofest Finalistliği</p>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
              <div class="stats-card mb-25 wow fadeInUp" data-wow-delay=".8s">
                <div class="stats-icon"><i class="fa-solid fa-calendar-days"></i></div>
                <div class="stats-content">
                  <h2 class="stats-number">12</h2>
                  <p class="stats-title">Düzenlenen Etkinlik</p>
                </div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6">
              <div class="stats-card mb-25 wow fadeInUp" data-wow-delay="1s">
                <div class="stats-icon"><i class="fa-solid fa-users"></i></div>
                <div class="stats-content">
                  <h2 class="stats-number">846</h2>
                  <p class="stats-title">Etkinlik Katılımcısı</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- HAKKIMIZDA -->
      <section class="about-area tp-common-area pt-130 pb-70">
        <div class="container">
          <div class="row">
            <div class="col-xl-6 col-lg-6 col-12">
              <div class="tp-about__content mb-50 wow fadeInLeft" data-wow-delay=".3s">
                <div class="tp-section">
                  <span class="tp-section__sub-title left-line mb-25">Hakkımızda</span>
                  <h3 class="tp-section__title about-title mb-30"><br> Yapay Zeka Ekosistemi</h3>
                  <p class="mr-20 mb-45">
                    Erciyes Üniversitesi Yapay Zeka Kulübü, yüksek kalitede yapay zeka bilimi üreten Erciyes Üniversitesi
                    Bilgisayar Mühendisliği bünyesinde, Yapay Zeka Anabilim Dalı Başkanı ve yapay zeka alanında yaptığı özgün çalışmalar ile
                    Türkiye Bilimler Akademisi ödüllü Prof. Dr. Bahriye Akay danışmanlığında kuruldu. Yapay zeka teknolojilerinin alt
                    sistemlerinde gerçekleştirilecek araştırmalarla, geleceğin teknolojilerine yön vermek ve inovasyonu teşvik etmek
                    bu alanda eğitimler ve ödüllü yarışmalar düzenlemeyi hedeflemekteyiz. Araştırma grubumuzda yürütülen ve yürütülecek
                    projeler, teknolojide sürdürülebilir bir değişim sağlama amacını taşır. Amacımız, lisans düzeyinde bilimsel içerik üreterek,
                    teknolojik gelişmelerin önünü açmak ve bu alanda lider bir topluluk olmaktır.
                  </p>
                </div>
                <div class="tp-about__info-list mb-55">
                  <ul>
                    <li><i class="fa-solid fa-check"></i>Sağlıkta Yapay Zeka</li>
                    <li><i class="fa-solid fa-check"></i>Veri Bilimi</li>
                    <li><i class="fa-solid fa-check"></i>Bilgisayarlı Görü</li>
                    <li><i class="fa-solid fa-check"></i>Doğal Dil İşleme</li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-12">
              <div class="tp-about-thumb tp-3-thumb mb-60 wow fadeInRight" data-wow-delay=".3s">
                <div class="tp-ab-img">
                  <div class="tp-ab-main-img p-relative">
                    <img src="assets/img/about/b.png" alt="about-thumb">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- BLOG / AKTİF ETKİNLİKLER -->
      <section class="blog-area tp-common-area grey-bg pt-125 pb-100" data-background="assets/img/shape/shape-bg-09.png">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-md-8 col-12">
              <div class="tp-section mb-20">
                <span class="tp-section__sub-title left-line mb-25">Erciyes Üniversitesi Yapay Zeka Kulübü</span>
                <h3 class="tp-section__title mb-60">Aktif Etkinlikler</h3>
              </div>
            </div>
            <div class="col-md-4 col-12">
              <div class="tp-blog-btn mb-30">
                <a href="etkinlikler.php" class="tp-btn-second">Etkinlik Takvimi</a>
              </div>
            </div>
          </div>

          <?php $aktif_etkinlikler = get_aktif_etkinlikler(3); ?>

          <div class="row">
            <?php if (count($aktif_etkinlikler) > 0): ?>
              <?php foreach ($aktif_etkinlikler as $etkinlik): ?>
                <div class="col-xxl-4 col-lg-6 col-md-6">
                  <div class="blogthumb mb-30 green-blog wow fadeInUp" data-background="assets/img/blog/default.jpg">
                    <div class="blogitem">
                      <div class="fix inner-blog-wrap">
                        <div class="blogitem__avata-part">
                          <div class="blogitem__avata">
                            <div class="blogitem__avata-icon"></div>
                            <div class="blogitem__avata-content">
                              <span>Koordinatör</span>
                              <a>YZ Kulübü</a>
                            </div>
                          </div>
                          <div class="blogitem__medi"><a>Yapay Zeka Kulübü</a></div>
                        </div>
                        <div class="">
                          <div class="blog-item__date-info mb-15">
                            <ul class="d-flex align-items-center">
                              <li>
                                <i class="fa-light fa-clock"></i>
                                <?= tr_ay_adi(format_etkinlik_tarihi($etkinlik['baslangic_tarihi'], $etkinlik['bitis_tarihi'], $etkinlik['saat'])) ?>
                              </li>
                            </ul>
                          </div>
                          <h5 class="blogitem__title mb-20">
                            <a href="etkinlik-detay.php?slug=<?= urlencode($etkinlik['etkinlik_slug']) ?>">
                              <?= htmlspecialchars($etkinlik['etkinlik_adi']) ?>
                            </a>
                          </h5>
                          <div class="tp-blog__btn blog-bg-btn">
                            <a href="etkinlik-detay.php?slug=<?= urlencode($etkinlik['etkinlik_slug']) ?>">Etkinlik İçeriği</a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="col-12">
                <div class="text-center py-5">
                  <i class="fa-light fa-calendar-xmark" style="font-size:48px;color:#ccc"></i>
                  <p class="mt-3" style="color:#6c757d">Şu anda aktif etkinlik bulunmamaktadır.</p>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </section>

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
                                    <span>© Copyright ©2025 <a href="index">Erciyes Üniversitesi Yapay Zeka Kulübü</a>. <i>All Rights Reserved Copyright</i></span>
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
    </div>
  </div>

  <!-- JS -->
  <script src="assets/js/jquery.js"></script>
  <script src="assets/js/waypoints.js"></script>
  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/swiper-bundle.js"></script>
  <script src="assets/js/slick.js"></script>
  <script src="assets/js/magnific-popup.js"></script>
  <script src="assets/js/counterup.js"></script>
  <script src="assets/js/wow.js"></script>
  <script src="assets/js/isotope-pkgd.js"></script>
  <script src="assets/js/imagesloaded-pkgd.js"></script>
  <script src="assets/js/ajax-form.js"></script>
  <script src="assets/js/aos.js"></script>
  <script src="assets/js/nice-select.js"></script>
  <script src="assets/js/meanmenu.js"></script>
  <script src="assets/js/jquery.appear.js"></script>
  <script src="assets/js/jquery.knob.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>