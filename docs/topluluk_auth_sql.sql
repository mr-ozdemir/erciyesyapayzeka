-- Topluluk logini artık doğrudan erciyesy_eruai veritabanındaki
-- panel_kullanicilar + roller tablolarını kullanır.

-- 1) Rol tablosu (yoksa)
CREATE TABLE IF NOT EXISTS `roller` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `rol_adi` varchar(50) NOT NULL,
  `yetki_seviyesi` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `roller` (`id`, `rol_adi`, `yetki_seviyesi`) VALUES
(1, 'Üye', 1),
(2, 'Yönetici', 2),
(3, 'Admin', 3);

-- 2) Panel kullanıcı tablosu (yoksa)
CREATE TABLE IF NOT EXISTS `panel_kullanicilar` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kullanici_adi` varchar(50) NOT NULL,
  `sifre_hash` varchar(255) NOT NULL,
  `ad_soyad` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `rol_id` int(10) UNSIGNED NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `son_giris` datetime DEFAULT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `kullanici_adi` (`kullanici_adi`),
  KEY `idx_rol` (`rol_id`),
  CONSTRAINT `fk_pk_rol` FOREIGN KEY (`rol_id`) REFERENCES `roller` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Topluluk için yeni bir yönetici kullanıcı ekleme örneği
-- Hash üretimi:
-- php -r "echo password_hash('BURAYA_SIFRE', PASSWORD_BCRYPT), PHP_EOL;"

INSERT INTO `panel_kullanicilar`
(`kullanici_adi`, `sifre_hash`, `ad_soyad`, `email`, `rol_id`, `aktif`)
VALUES
('topluluk_admin', '$2y$10$REPLACE_WITH_BCRYPT_HASH', 'Topluluk Admin', 'admin@erciyesyapayzeka.com.tr', 3, 1);

-- 4) Kullanıcıyı pasife alma
-- UPDATE `panel_kullanicilar` SET `aktif` = 0 WHERE `kullanici_adi` = 'topluluk_admin';

-- 5) Şifre güncelleme
-- UPDATE `panel_kullanicilar` SET `sifre_hash` = '$2y$10$YENI_HASH' WHERE `kullanici_adi` = 'topluluk_admin';
