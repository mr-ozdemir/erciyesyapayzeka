-- Topluluk giriş sistemi için özel kullanıcı tablosu
-- Not: wp_ prefix'i sizde farklı olabilir (ör: wpey_). Gerekirse tablo adını güncelleyin.

CREATE TABLE IF NOT EXISTS `wpey_community_users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kullanici_adi` varchar(64) NOT NULL,
  `sifre_hash` varchar(255) NOT NULL,
  `ad_soyad` varchar(120) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `rol` enum('admin','yonetici','editor','uye') NOT NULL DEFAULT 'yonetici',
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `son_giris` datetime DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_kullanici_adi` (`kullanici_adi`),
  KEY `idx_rol` (`rol`),
  KEY `idx_aktif` (`aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opsiyonel: login denemeleri / audit log tablosu
CREATE TABLE IF NOT EXISTS `wpey_community_auth_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `kullanici_adi` varchar(64) DEFAULT NULL,
  `durum` enum('success','failed') NOT NULL,
  `detay` varchar(255) DEFAULT NULL,
  `ip_adresi` varchar(45) DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_kullanici_adi` (`kullanici_adi`),
  KEY `idx_durum` (`durum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- İlk yönetici kullanıcı örneği
-- sifre_hash için PHP'de üretin:
--   php -r "echo password_hash('BURAYA_SIFRE', PASSWORD_BCRYPT), PHP_EOL;"

INSERT INTO `wpey_community_users` (`kullanici_adi`, `sifre_hash`, `ad_soyad`, `email`, `rol`, `aktif`)
VALUES ('topluluk_admin', '$2y$10$REPLACE_WITH_BCRYPT_HASH', 'Topluluk Admin', 'admin@erciyesyapayzeka.com.tr', 'admin', 1);

-- Kullanıcı ekleme şablonu (panelden de aynı mantıkla eklenebilir)
-- INSERT INTO `wpey_community_users`
-- (`kullanici_adi`, `sifre_hash`, `ad_soyad`, `email`, `rol`, `aktif`)
-- VALUES
-- ('kullanici_adi', '$2y$10$HASH', 'Ad Soyad', 'mail@example.com', 'yonetici', 1);

-- Kullanıcıyı pasife alma
-- UPDATE `wpey_community_users` SET aktif = 0 WHERE id = 5;

-- Şifre güncelleme
-- UPDATE `wpey_community_users` SET sifre_hash = '$2y$10$YENI_HASH' WHERE kullanici_adi = 'topluluk_admin';
