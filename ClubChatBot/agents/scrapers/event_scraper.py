# agents/scrapers/event_scraper.py
"""
Event Scraper - Etkinlikler sayfası için scraper.
Her etkinliğin detay sayfasına girerek tarih ve durum bilgisi çeker.
"""

import re
import requests
from typing import List, Optional, Tuple
from datetime import datetime
from bs4 import BeautifulSoup
from agents.scrapers.base_scraper import PageScraperBase
from agents.models import Event, parse_turkish_date, format_events_for_llm


class EventScraper(PageScraperBase):
    """
    Etkinlikler sayfasından etkinlik bilgilerini çeken scraper.
    Her etkinliğin detay sayfasından tarih ve durum çeker.
    """
    
    def __init__(self, base_url: str = "https://erciyesyapayzeka.com.tr"):
        super().__init__(base_url)
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        })
    
    @property
    def page_path(self) -> str:
        return "/etkinlikler"
    
    @property
    def page_type(self) -> str:
        return "event"
    
    def parse_content(self, content: str) -> List[Event]:
        """Ana etkinlik listesini parse et ve detay sayfalarından bilgi çek."""
        events = []
        
        # Ana sayfadan etkinlik linklerini çek
        event_links = self._extract_event_links(content)
        print(f"[event] 🔗 {len(event_links)} etkinlik linki bulundu")
        
        # Her etkinlik için detay sayfasından bilgi çek
        for title, detail_url in event_links[:15]:  # Max 15 etkinlik
            event = self._fetch_event_details(title, detail_url)
            if event:
                events.append(event)
        
        # Tarihe göre sırala (yeniden eskiye)
        events.sort(key=lambda e: e.date or datetime.min, reverse=True)
        
        print(f"[event] 📊 {len(events)} etkinlik detayı alındı")
        return events
    
    def _extract_event_links(self, content: str) -> List[Tuple[str, str]]:
        """Ana sayfadan etkinlik başlık ve linklerini çıkar."""
        links = []
        
        # Link pattern'leri
        patterns = [
            r'(https?://[^\s\)]+etkinlik-detay\.php\?slug=[^\s\)\"\']+)',
        ]
        
        for pattern in patterns:
            matches = re.findall(pattern, content)
            for url in matches:
                slug_match = re.search(r'slug=([^&\s\"\']+)', url)
                if slug_match:
                    slug = slug_match.group(1)
                    title = slug.replace('-', ' ').title()
                    if (title, url) not in links:
                        links.append((title, url))
        
        # Fallback: bilinen etkinlikler
        if not links:
            known_events = [
                "akademik-dusunme-ve-tartisma-gunu",
                "data-kamp-yapay-zeka-egitimi",
                "data-kamp-bahar-egitimi",
                "yapay-zeka-ekosistemi-tanisma-toplantisi",
                "veri-yapilari-ve-algoritmalar",
                "programlamaya-giris",
                "fikrim-geldi",
                "data-kamp-2-sezon",
                "python-ile-makine-ogrenmesi",
                "herkes-icin-yapay-zeka",
                "cenglish",
                "mutex-algoritma-kampi",
                "data-camp-1-sezon",
            ]
            for slug in known_events:
                url = f"{self.base_url}/etkinlik-detay.php?slug={slug}"
                title = slug.replace('-', ' ').title()
                links.append((title, url))
        
        return links
    
    def _fetch_event_details(self, title: str, url: str) -> Optional[Event]:
        """Etkinlik detay sayfasından bilgi çek."""
        try:
            response = self.session.get(url, timeout=10)
            if response.status_code != 200:
                return None
            
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # Başlık (h1'den al)
            h1 = soup.find('h1')
            if h1:
                title = h1.get_text(strip=True)
            
            # Tarih çıkar - sayfadaki tarih bilgisini bul
            text = soup.get_text(separator='\n', strip=True)
            date_pattern = r'(\d{1,2})\s+(Ocak|Şubat|Mart|Nisan|Mayıs|Haziran|Temmuz|Ağustos|Eylül|Ekim|Kasım|Aralık)\s+(\d{4})'
            date_match = re.search(date_pattern, text, re.IGNORECASE)
            
            event_date = None
            date_str = ""
            if date_match:
                date_str = date_match.group(0)
                event_date = parse_turkish_date(date_str)
            
            # Durum kontrolü
            is_completed = any(word in text.upper() for word in [
                'TAMAMLANMIŞTIR', 'TAMAMLANDI', 'İPTAL', 'SONA ERDİ'
            ])
            
            # Açıklama - "Etkinlik Hakkında" bölümünden al
            description = self._extract_description_from_soup(soup)
            
            # Aktiflik hesapla
            today = datetime.now()
            if is_completed:
                is_active = False
                status = "Tamamlandı"
            elif event_date and event_date.date() < today.date():
                is_active = False
                status = "Tamamlandı"
            elif event_date and event_date.date() == today.date():
                is_active = True
                status = "Bugün!"
            elif event_date and event_date.date() > today.date():
                is_active = True
                days_left = (event_date.date() - today.date()).days
                if days_left <= 7:
                    status = f"Bu hafta! ({days_left} gün kaldı)"
                else:
                    status = f"Yaklaşan ({days_left} gün kaldı)"
            else:
                is_active = not is_completed
                status = "Devam Ediyor" if is_active else "Tamamlandı"
            
            return Event(
                title=title,
                description=description,
                url=url,
                date=event_date,
                date_str=date_str,
                is_active=is_active,
                status=status
            )
            
        except Exception as e:
            print(f"[event] ⚠️ Detay çekilemedi {title}: {e}")
            return None
    
    def _extract_description_from_soup(self, soup: BeautifulSoup) -> str:
        """BeautifulSoup'dan açıklama çıkar."""
        # "Etkinlik Hakkında" bölümünü bul
        for heading in soup.find_all(['h2', 'h3', 'h4']):
            if 'hakkında' in heading.get_text().lower():
                # Başlıktan sonraki içeriği al
                next_elem = heading.find_next_sibling()
                if next_elem:
                    desc = next_elem.get_text(strip=True)
                    # Temizle
                    desc = re.sub(r'\s+', ' ', desc)
                    if len(desc) > 30:  # En az 30 karakter
                        return desc[:250] + "..." if len(desc) > 250 else desc
        
        # "Detaylı Bilgi" bölümünü dene
        for heading in soup.find_all(['h2', 'h3', 'h4']):
            if 'detay' in heading.get_text().lower() or 'bilgi' in heading.get_text().lower():
                next_elem = heading.find_next_sibling()
                if next_elem:
                    desc = next_elem.get_text(strip=True)
                    desc = re.sub(r'\s+', ' ', desc)
                    if len(desc) > 30:
                        return desc[:250] + "..." if len(desc) > 250 else desc
        
        # Paragraflardan al (header, nav, footer hariç)
        for p in soup.find_all('p'):
            # Parent'ı kontrol et
            parent = p.find_parent(['nav', 'header', 'footer'])
            if parent:
                continue
            
            text = p.get_text(strip=True)
            text = re.sub(r'\s+', ' ', text)
            
            # "Etkinlikler", "Ana Sayfa" gibi navigation metinlerini atla
            skip_words = ['etkinlikler', 'ana sayfa', 'projeler', 'üye ol', 'etkinlik takvimi']
            if any(word in text.lower()[:50] for word in skip_words):
                continue
            
            if len(text) > 50:
                return text[:250] + "..." if len(text) > 250 else text
        
        return "Detaylı bilgi için etkinlik sayfasını ziyaret edin."
    
    def format_for_llm(self, items: List[Event]) -> str:
        """Etkinlikleri LLM için formatla."""
        return format_events_for_llm(items)
