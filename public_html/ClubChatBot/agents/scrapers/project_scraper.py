# agents/scrapers/project_scraper.py
"""
Project Scraper - Projeler/Araştırma sayfası için scraper.
Site: https://erciyesyapayzeka.com.tr/research
"""

import re
from typing import List
from agents.scrapers.base_scraper import PageScraperBase
from agents.models import Project, format_projects_for_llm


class ProjectScraper(PageScraperBase):
    """
    Projeler sayfasından yayın ve proje bilgilerini çeken scraper.
    """
    
    # Bilinen projeler ve yayınlar (fallback için)
    KNOWN_PUBLICATIONS = [
        ("Derin Sinir Ağları Aktivasyon Fonksiyonlarının Genetik Programlama ile Otomatik Tasarımı",
         "Aktivasyon fonksiyonlarının evrimsel arama ile otomatik keşfi.",
         ["Yapay Zeka", "Derin Öğrenme"]),
        ("Doğal Dil Modelleri ile Yazılım Test Senaryolarının Üretilmesi",
         "Büyük dil modelleri ile yazılım projeleri için otomatik yazılım testlerinin üretilmesi.",
         ["NLP", "Yazılım Test"]),
        ("A Comparative Review of EA & Mutation Types in Automatic Activation Function Design",
         "Evrimsel algoritmalarla aktivasyon tasarımında kullanılan EA ve mutasyon türlerinin karşılaştırmalı incelemesi.",
         ["Yapay Zeka"]),
        ("Semantic Search for Automatic Activation Function with Artificial Bee Colony Programming",
         "ABC programlama ile semantik yönlendirmeli aktivasyon keşfi.",
         ["Yapay Zeka"]),
        ("Neural Architecture Search with Artificial Bee Colony Coding",
         "Yapay arı kolonisi ile NAS araması için yeni kodlama yaklaşımı.",
         ["Yapay Zeka", "NAS"]),
        ("Unit Test Generation Using a Large Language Model",
         "Birim testlerinin büyük dil modelleri ile üretilmesi.",
         ["NLP", "Yazılım Test"]),
    ]
    
    KNOWN_PROJECTS = [
        ("Gerçek Zamanlı İşaret Dili Tespiti ve Metinden Sese Çevirme Sistemi",
         "CNN-GRU derin öğrenme modeli ile işaret dili tespiti ve HMM tabanlı metinden sese çevirme.",
         ["Bilgisayarlı Görü", "Derin Öğrenme"]),
        ("Kolektif Derin Öğrenme ile Meme Mamografilerinde Kanser Risk Sınıflandırması",
         "Negatif Tabanlı Kolektif Öğrenme modeli ile meme mamografilerinde BI-RADS sınıflandırması.",
         ["Sağlık", "Derin Öğrenme"]),
        ("Derin Öğrenme ile Meme Mamografilerinde Lezyon Tespiti",
         "YOLOv8 ve Yapay Arı Kolonisi algoritması ile meme yoğunluklarına göre kitle ve kalsifikasyon tespiti.",
         ["Sağlık", "YOLO", "Bilgisayarlı Görü"]),
        ("HMM-N-gram Hibrit Modeli ile Türkçe Konuşma Tanıma Sistemi",
         "Düşük maliyetli mikrodenetleyiciler üzerinde gerçek zamanlı Türkçe konuşma tanıma sistemi.",
         ["NLP", "HMM"]),
        ("Yapay Zeka Destekli Portföy Yönetimi ve Risk Analizi Sistemi",
         "Bi-LSTM ile finansal tahmin, FinBERT ile sosyal medya analizi ve Monte Carlo simülasyonu.",
         ["Finans", "LSTM", "NLP"]),
        ("Makine Öğrenme Tabanlı Müşteri Eşleştirme ve Ürün Öneri Sistemi",
         "Kafe ve restoran işletmeleri için geliştirilmektedir. TÜBİTAK BİDEB desteği.",
         ["Makine Öğrenmesi", "TÜBİTAK"]),
        ("LSTM-CNN Hibrit Modeli ile Uyku Evrelerinin Analizi",
         "Uyku hastalıkları teşhisi için geliştirilmiş entegre alarm sistemi. TÜBİTAK BİDEB desteği.",
         ["Sağlık", "Derin Öğrenme", "TÜBİTAK"]),
        ("EEG Verilerinden Duygu Durumu Analizi ve Müzik Öneri Sistemi",
         "Derin öğrenme yöntemleri ile EEG verilerinden duygu analizi. TÜBİTAK BİDEB desteği.",
         ["Sağlık", "Derin Öğrenme", "TÜBİTAK"]),
        ("Hibrit Mamografi Analizi ve Rapor Varlık İsmi Çıkarımı",
         "Bilgisayarlı görü ve doğal dil işleme teknikleriyle mamografi analizi. TÜSEB/TEKNOFEST desteği.",
         ["Sağlık", "NLP", "Bilgisayarlı Görü", "TEKNOFEST"]),
    ]
    
    @property
    def page_path(self) -> str:
        return "/research"
    
    @property
    def page_type(self) -> str:
        return "project"
    
    def parse_content(self, content: str) -> List[Project]:
        """Projeler sayfasının içeriğini parse eder."""
        projects = []
        
        # Önce regex ile dene
        projects = self._parse_with_regex(content)
        
        # Eğer hiç proje bulunamadıysa, fallback kullan
        if not projects:
            projects = self._get_fallback_projects()
        
        return projects
    
    def _parse_with_regex(self, content: str) -> List[Project]:
        """Regex ile projeleri parse et."""
        projects = []
        
        # ##### Başlık\nAçıklama formatı
        pattern = r'#{4,5}\s*([^\n]+)\n([^#]+?)(?=#{4,5}|\Z)'
        matches = re.findall(pattern, content, re.MULTILINE)
        
        current_category = "Proje"
        
        for match in matches:
            title = match[0].strip()
            description = match[1].strip()
            
            # Başlığı temizle
            title = re.sub(r'^[📚🚀📖•#\s]+', '', title).strip()
            
            # Kategori güncelle
            if "Yayın" in title or "Akademik" in title:
                current_category = "Yayın"
                continue
            elif "Proje" in title:
                current_category = "Proje"
                continue
            
            # Skip irrelevant sections
            if any(skip in title.lower() for skip in ['bize ulaş', 'contact', 'footer']):
                continue
            
            if not title or len(title) < 5:
                continue
            
            # Açıklamayı temizle
            description = re.sub(r'\[.*?\]\(.*?\)', '', description).strip()
            description = description[:250] if len(description) > 250 else description
            
            # Etiketleri çıkar
            tags = self._extract_tags(description)
            
            projects.append(Project(
                title=title,
                description=description,
                category=current_category,
                tags=tags
            ))
        
        return projects
    
    def _get_fallback_projects(self) -> List[Project]:
        """Bilinen projeleri döndür (fallback)."""
        projects = []
        
        # Yayınları ekle
        for title, description, tags in self.KNOWN_PUBLICATIONS:
            projects.append(Project(
                title=title,
                description=description,
                category="Yayın",
                tags=tags
            ))
        
        # Projeleri ekle
        for title, description, tags in self.KNOWN_PROJECTS:
            projects.append(Project(
                title=title,
                description=description,
                category="Proje",
                tags=tags
            ))
        
        return projects
    
    def _extract_tags(self, text: str) -> List[str]:
        """Metinden etiketleri çıkarır."""
        tags = []
        
        known_tags = [
            "TÜBİTAK", "TÜSEB", "TEKNOFEST", "BİDEB",
            "Yapay Zeka", "Derin Öğrenme", "Makine Öğrenmesi",
            "Doğal Dil İşleme", "NLP", "Bilgisayarlı Görü", "CV",
            "CNN", "LSTM", "GRU", "YOLO", "HMM",
            "Sağlık", "Finans", "Ses", "Görüntü"
        ]
        
        text_lower = text.lower()
        for tag in known_tags:
            if tag.lower() in text_lower:
                tags.append(tag)
        
        return tags[:5]
    
    def format_for_llm(self, items: List[Project]) -> str:
        """Projeleri LLM için formatla."""
        return format_projects_for_llm(items)
