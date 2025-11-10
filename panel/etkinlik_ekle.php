<?php
// panel/etkinlik_ekle.php - Etkinlik Ekleme Sayfası (Gelişmiş)

require_once '../config.php';
require_once '../auth.php';

check_login(); // Sadece giriş kontrolü yap, rol kontrolü yapma

// YENİ EKLENECEK KOD
if (!is_manager_or_admin()) {
    set_flash_message('Bu işlemi yapma yetkiniz bulunmuyor.', 'danger');
    redirect('dashboard.php');
}

$page_title = 'Yeni Etkinlik Ekle';
$breadcrumb = [
    ['title' => 'Etkinlikler', 'url' => 'etkinlikler.php'],
    ['title' => 'Yeni Etkinlik']
];

include 'includes/header.php';
?>

<style>
/* Özel Stiller */
.form-section {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.form-section-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid var(--primary-color);
}

.image-preview {
    width: 100%;
    max-width: 400px;
    margin-top: 10px;
    border-radius: 8px;
    display: none;
}

.image-preview img {
    width: 100%;
    height: auto;
    border-radius: 8px;
}

/* Program Takvimi */
.program-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 3px solid var(--primary-color);
}

.program-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.btn-remove {
    background: #dc3545;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
}

.btn-add {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    margin-top: 10px;
}

.btn-add:hover {
    background: #218838;
}

.btn-remove:hover {
    background: #c82333;
}

/* Galeri */
.gallery-preview {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.gallery-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-item-remove {
    position: absolute;
    top: 5px;
    right: 5px;
    background: #dc3545;
    color: white;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Konuşmacı */
.speaker-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}
</style>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-plus-circle"></i> Yeni Etkinlik Ekle
        </h3>
    </div>
    <div class="card-body">
        <form action="etkinlik_ekle_islem.php" method="POST" enctype="multipart/form-data" id="etkinlikForm">
            
            <!-- TEMEL BİLGİLER -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i class="fas fa-info-circle"></i> Temel Bilgiler
                </h4>
                
                <div class="form-group">
                    <label for="etkinlik_adi">Etkinlik Adı *</label>
                    <input type="text" class="form-control" id="etkinlik_adi" name="etkinlik_adi" required placeholder="Örn: Data Kamp 3. Sezon">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="baslangic_tarihi">Başlangıç Tarihi *</label>
                        <input type="date" class="form-control" id="baslangic_tarihi" name="baslangic_tarihi" required>
                    </div>

                    <div class="form-group">
                        <label for="bitis_tarihi">Bitiş Tarihi</label>
                        <input type="date" class="form-control" id="bitis_tarihi" name="bitis_tarihi">
                    </div>

                    <div class="form-group">
                        <label for="saat">Saat</label>
                        <input type="text" class="form-control" id="saat" name="saat" placeholder="Örn: Cuma 19:30 - 21:30">
                    </div>
                </div>

                <div class="form-group">
                    <label for="konum">Konum</label>
                    <input type="text" class="form-control" id="konum" name="konum" placeholder="Örn: Erciyes Üniversitesi - Bilgisayar Mühendisliği">
                </div>

                <div class="form-group">
                    <label for="aciklama">Kısa Açıklama</label>
                    <textarea class="form-control" id="aciklama" name="aciklama" rows="3" placeholder="Liste görünümünde gösterilecek kısa açıklama (200-300 karakter)"></textarea>
                </div>

                <div class="form-group">
                    <label for="detayli_aciklama">Detaylı Açıklama</label>
                    <textarea class="form-control" id="detayli_aciklama" name="detayli_aciklama" rows="6" placeholder="Detay sayfasında gösterilecek detaylı açıklama"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="max_katilimci">Maksimum Katılımcı</label>
                        <input type="number" class="form-control" id="max_katilimci" name="max_katilimci" value="100" min="0">
                        <small class="text-muted">0 = sınırsız</small>
                    </div>

                    <div class="form-group">
                        <label for="aktif">Durum *</label>
                        <select class="form-select" id="aktif" name="aktif" required>
                            <option value="1">Aktif (Kayıtlar Açık)</option>
                            <option value="0">Pasif (Kayıtlar Kapalı)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ETKİNLİK RESMİ -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i class="fas fa-image"></i> Etkinlik Görseli
                </h4>
                
                <div class="form-group">
                    <label for="etkinlik_resmi">Ana Görsel (Hero Image)</label>
                    <input type="file" class="form-control" id="etkinlik_resmi" name="etkinlik_resmi" accept="image/*">
                    <small class="text-muted">Önerilen boyut: 1920x1080px (16:9), Maksimum: 5MB</small>
                </div>

                <div id="imagePreview" class="image-preview">
                    <img src="" alt="Önizleme">
                </div>
            </div>

            <!-- PROGRAM TAKVİMİ -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i class="fas fa-calendar-week"></i> Program Takvimi
                </h4>
                
                <div id="programContainer">
                    <!-- Dinamik olarak program eklenecek -->
                </div>

                <button type="button" class="btn-add" onclick="addProgramItem()">
                    <i class="fas fa-plus"></i> Program Günü Ekle
                </button>
            </div>

            <!-- VİDEO LİNKİ -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i class="fas fa-video"></i> Video
                </h4>
                
                <div class="form-group">
                    <label for="video_linki">YouTube/Vimeo Video Linki</label>
                    <input type="url" class="form-control" id="video_linki" name="video_linki" placeholder="https://www.youtube.com/watch?v=...">
                    <small class="text-muted">Tanıtım videosu veya geçmiş etkinlik kaydı</small>
                </div>
            </div>

            <!-- GALERİ -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i class="fas fa-images"></i> Galeri
                </h4>
                
                <div class="form-group">
                    <label for="galeri_resimleri">Galeri Resimleri</label>
                    <input type="file" class="form-control" id="galeri_resimleri" name="galeri_resimleri[]" accept="image/*" multiple>
                    <small class="text-muted">Birden fazla resim seçebilirsiniz (Ctrl tuşu ile). Maksimum 10 resim, her biri 5MB'dan küçük olmalı.</small>
                </div>

                <div id="galleryPreview" class="gallery-preview"></div>
            </div>

            <!-- KONUŞMACILAR -->
            <div class="form-section">
                <h4 class="form-section-title">
                    <i class="fas fa-user-tie"></i> Konuşmacılar
                </h4>
                
                <div id="speakersContainer">
                    <!-- Dinamik olarak konuşmacı eklenecek -->
                </div>

                <button type="button" class="btn-add" onclick="addSpeakerItem()">
                    <i class="fas fa-plus"></i> Konuşmacı Ekle
                </button>
            </div>

            <!-- BUTONLAR -->
            <div class="form-section">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Etkinliği Kaydet
                </button>
                <a href="etkinlikler.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> İptal
                </a>
            </div>

        </form>
    </div>
</div>

<script>
// Resim önizleme
document.getElementById('etkinlik_resmi').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imagePreview');
            preview.querySelector('img').src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
});

// Galeri önizleme
document.getElementById('galeri_resimleri').addEventListener('change', function(e) {
    const container = document.getElementById('galleryPreview');
    container.innerHTML = '';
    
    Array.from(e.target.files).forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'gallery-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Galeri ${index + 1}">
            `;
            container.appendChild(div);
        }
        reader.readAsDataURL(file);
    });
});

// Program item sayacı
let programIndex = 0;

// Program günü ekle
function addProgramItem() {
    programIndex++;
    const container = document.getElementById('programContainer');
    const div = document.createElement('div');
    div.className = 'program-item';
    div.id = 'program-' + programIndex;
    div.innerHTML = `
        <div class="program-item-header">
            <strong>Gün ${programIndex}</strong>
            <button type="button" class="btn-remove" onclick="removeProgram(${programIndex})">
                <i class="fas fa-trash"></i> Sil
            </button>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Gün Adı</label>
                <input type="text" class="form-control" name="program[${programIndex}][gun]" placeholder="Örn: 1. Hafta" required>
            </div>
            <div class="form-group">
                <label>Tarih</label>
                <input type="date" class="form-control" name="program[${programIndex}][tarih]" required>
            </div>
            <div class="form-group">
                <label>Saat</label>
                <input type="text" class="form-control" name="program[${programIndex}][saat]" placeholder="19:30 - 21:30">
            </div>
        </div>
        <div class="form-group">
            <label>Başlık</label>
            <input type="text" class="form-control" name="program[${programIndex}][baslik]" placeholder="Örn: Python Temelleri" required>
        </div>
        <div class="form-group">
            <label>Açıklama</label>
            <textarea class="form-control" name="program[${programIndex}][aciklama]" rows="2" placeholder="Ders içeriği hakkında kısa bilgi"></textarea>
        </div>
    `;
    container.appendChild(div);
}

// Program sil
function removeProgram(index) {
    const elem = document.getElementById('program-' + index);
    if (elem) {
        elem.remove();
    }
}

// Konuşmacı sayacı
let speakerIndex = 0;

// Konuşmacı ekle
function addSpeakerItem() {
    speakerIndex++;
    const container = document.getElementById('speakersContainer');
    const div = document.createElement('div');
    div.className = 'speaker-item';
    div.id = 'speaker-' + speakerIndex;
    div.innerHTML = `
        <div class="program-item-header">
            <strong>Konuşmacı ${speakerIndex}</strong>
            <button type="button" class="btn-remove" onclick="removeSpeaker(${speakerIndex})">
                <i class="fas fa-trash"></i> Sil
            </button>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Ad Soyad</label>
                <input type="text" class="form-control" name="speakers[${speakerIndex}][ad_soyad]" placeholder="Örn: Muhammet Özdemir" required>
            </div>
            <div class="form-group">
                <label>Ünvan</label>
                <input type="text" class="form-control" name="speakers[${speakerIndex}][unvan]" placeholder="Örn: Veri Bilimci">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Fotoğraf</label>
                <input type="file" class="form-control" name="speakers[${speakerIndex}][fotograf]" accept="image/*">
            </div>
            <div class="form-group">
                <label>LinkedIn Profili</label>
                <input type="url" class="form-control" name="speakers[${speakerIndex}][linkedin]" placeholder="https://linkedin.com/in/...">
            </div>
        </div>
    `;
    container.appendChild(div);
}

// Konuşmacı sil
function removeSpeaker(index) {
    const elem = document.getElementById('speaker-' + index);
    if (elem) {
        elem.remove();
    }
}

// Sayfa yüklendiğinde 1 program günü ekle
window.addEventListener('DOMContentLoaded', function() {
    addProgramItem();
});
</script>

<?php include 'includes/footer.php'; ?>