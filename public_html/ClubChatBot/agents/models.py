# agents/models.py
"""
Data models for web agent scrapers.
Contains Event, Project dataclasses and utility functions.
"""

from dataclasses import dataclass, field
from typing import Optional, List
from datetime import datetime
import re


@dataclass
class PageItem:
    """Base class for all page items."""
    title: str
    description: str
    url: Optional[str] = None


@dataclass
class Event(PageItem):
    """Etkinlik modeli."""
    date: Optional[datetime] = None
    date_str: str = ""
    is_active: bool = True
    status: str = "Planlandı"  # "Aktif", "Tamamlandı", "Yaklaşıyor", "Planlandı"


@dataclass
class Project(PageItem):
    """Proje modeli."""
    category: str = ""  # "Yayın" veya "Proje"
    tags: List[str] = field(default_factory=list)


# Türkçe ay isimleri
MONTHS_TR = {
    'ocak': 1, 'şubat': 2, 'mart': 3, 'nisan': 4,
    'mayıs': 5, 'haziran': 6, 'temmuz': 7, 'ağustos': 8,
    'eylül': 9, 'ekim': 10, 'kasım': 11, 'aralık': 12
}


def parse_turkish_date(date_str: str) -> Optional[datetime]:

    if not date_str:
        return None
    
    date_str = date_str.lower().strip()
    
    # "15 Ocak 2025" formatı
    pattern = r'(\d{1,2})\s+(\w+)\s+(\d{4})'
    match = re.search(pattern, date_str)
    if match:
        day = int(match.group(1))
        month_name = match.group(2)
        year = int(match.group(3))
        month = MONTHS_TR.get(month_name, 1)
        
        # Saat bilgisi varsa ekle
        time_pattern = r'(\d{1,2}):(\d{2})'
        time_match = re.search(time_pattern, date_str)
        hour, minute = (int(time_match.group(1)), int(time_match.group(2))) if time_match else (0, 0)
        
        try:
            return datetime(year, month, day, hour, minute)
        except ValueError:
            return None
    
    # "2025-01-15" formatı
    iso_pattern = r'(\d{4})-(\d{2})-(\d{2})'
    iso_match = re.search(iso_pattern, date_str)
    if iso_match:
        try:
            return datetime(int(iso_match.group(1)), int(iso_match.group(2)), int(iso_match.group(3)))
        except ValueError:
            return None
    
    return None


def check_event_status(event_date: Optional[datetime]) -> tuple[bool, str]:
    """
    Etkinliğin aktif olup olmadığını kontrol eder.
    
    Returns:
        tuple: (is_active, status_string)
    """
    if event_date is None:
        return False, "Tarih belirsiz"
    
    now = datetime.now()
    
    if event_date < now:
        return False, "Tamamlandı"
    elif (event_date - now).days <= 7:
        return True, "Yaklaşıyor (Bu Hafta!)"
    elif (event_date - now).days <= 30:
        return True, "Yaklaşıyor"
    else:
        return True, "Planlandı"


def format_events_for_llm(events: List[Event]) -> str:
    """Etkinlikleri LLM için formatlı string'e çevirir."""
    if not events:
        return "Şu anda kayıtlı etkinlik bulunmamaktadır."
    
    now = datetime.now()
    output = f"📅 Bugün: {now.strftime('%d %B %Y').replace('January', 'Ocak').replace('February', 'Şubat').replace('March', 'Mart').replace('April', 'Nisan').replace('May', 'Mayıs').replace('June', 'Haziran').replace('July', 'Temmuz').replace('August', 'Ağustos').replace('September', 'Eylül').replace('October', 'Ekim').replace('November', 'Kasım').replace('December', 'Aralık')}\n\n"
    
    active_events = [e for e in events if e.is_active]
    past_events = [e for e in events if not e.is_active]
    
    # Aktif etkinlikler varsa önce onları göster
    if active_events:
        output += f"🟢 AKTİF ETKİNLİKLER ({len(active_events)} adet):\n"
        output += "=" * 40 + "\n"
        for e in active_events:
            output += f"\n📌 {e.title}\n"
            output += f"   📆 Tarih: {e.date_str}\n"
            output += f"   📊 Durum: {e.status}\n"
            if e.description:
                output += f"   📝 {e.description}\n"
            if e.url:
                output += f"   🔗 {e.url}\n"
    else:
        output += "ℹ️ Şu anda aktif etkinlik bulunmamaktadır.\n"
    
    # Son 5 etkinlik özeti
    output += f"\n\n📋 SON ETKİNLİKLER (Son {min(5, len(past_events))} tanesi):\n"
    output += "=" * 40 + "\n"
    
    for i, e in enumerate(past_events[:5], 1):
        output += f"\n{i}. {e.title}\n"
        if e.date_str:
            output += f"   📆 {e.date_str} | {e.status}\n"
        else:
            output += f"   📊 {e.status}\n"
        if e.description:
            output += f"   {e.description}\n"
    
    # Toplam istatistik
    output += f"\n📊 Toplam {len(events)} etkinlik kaydı bulunmaktadır."
    
    return output


def format_projects_for_llm(projects: List[Project]) -> str:
    """Projeleri LLM için formatlı string'e çevirir."""
    if not projects:
        return "Şu anda kayıtlı proje bulunmamaktadır."
    
    output = "📚 ERÜ YAPAY ZEKA KULÜBÜ PROJELERİ\n"
    output += "=" * 50 + "\n\n"
    
    # Kategorilere ayır
    publications = [p for p in projects if p.category == "Yayın"]
    research_projects = [p for p in projects if p.category == "Proje"]
    
    if publications:
        output += "📖 YAYINLAR:\n"
        output += "-" * 40 + "\n"
        for p in publications:
            output += f"• {p.title}\n"
            if p.description:
                output += f"  {p.description[:150]}\n"
            output += "\n"
    
    if research_projects:
        output += "\n🚀 PROJELER:\n"
        output += "-" * 40 + "\n"
        for p in research_projects:
            output += f"• {p.title}\n"
            if p.description:
                output += f"  {p.description[:150]}\n"
            if p.tags:
                output += f"  🏷️ Etiketler: {', '.join(p.tags)}\n"
            output += "\n"
    
    return output
