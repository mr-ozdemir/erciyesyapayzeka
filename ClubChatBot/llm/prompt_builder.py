# llm/prompt_builder.py
"""
Prompt Builder Module
Intent tipine göre dinamik prompt oluşturur.
WebAgent, info, links ve genel sorgular için farklı system promptları kullanır.
"""

from typing import Optional, Dict, Any
from datetime import datetime
from enum import Enum


class PromptType(Enum):
    """Prompt tipleri."""
    GENERAL = "general"
    WEB_AGENT = "web_agent"
    INFO = "info"
    LINKS = "links"
    EVENT = "event"
    PROJECT = "project"


class PromptBuilder:
    """
    Intent tipine göre dinamik prompt oluşturan sınıf.
    OOP prensiplerine uygun, genişletilebilir yapı.
    """
    
    # Temel sistem promptları
    BASE_SYSTEM_PROMPT = """Sen Erciyes Üniversitesi Yapay Zeka Kulübü'nün yardımsever AI asistanısın.
Kullanıcılara Türkçe yanıt ver. Samimi, bilgili ve yardımsever ol.
Detaylı ve kapsamlı cevaplar ver.

ÖNEMLİ FORMAT KURALI:
- Her yanıtının EN SONUNA 🍯 emojisi koy
Örnek: " Merhaba! Kulübümüz hakkında... 🍯" """

    EVENT_CONTEXT_PROMPT = """
Etkinlikler hakkında bilgi verirken:
- HER ETKİNLİK İÇİN AÇIKLAMASINI DA YAZ (context'te 📝 ile verilen bilgi)
- Etkinliğin ne olduğunu ve neler yapıldığını açıkla
- Aktif/yaklaşan etkinlikleri önce ve detaylı belirt
- Tarih bilgisini yaz
- Detaylı ve bilgilendirici ol, sadece başlık listesi verme

ÖRNEK FORMAT:
1. DATA KAMP (03 Ekim 2025 - Tamamlandı)
   Temel yapay zeka kavramlarıyla başlayıp Bilgisayarlı Görü ve NLP atölyelerine ilerleyen eğitim programı.
   
2. Python ile Makine Öğrenmesi (20 Eylül 2024 - Tamamlandı)
   Scikit-learn, Pandas ve NumPy ile uygulamalı makine öğrenmesi eğitimi.

ÖNEMLİ: Her yanıtının başına 🐝, sonuna 🍯 koy."""

    PROJECT_CONTEXT_PROMPT = """
Projeler hakkında bilgi verirken İKİ AYRI BÖLÜM olarak sun:

📖 YAYINLAR:
- Akademik yayınları listele
- Her yayının ne hakkında olduğunu açıkla

🚀 PROJELER:
- Araştırma projelerini listele  
- Her projenin amacını ve kullanılan teknolojileri açıkla
- TÜBİTAK, TÜSEB, TEKNOFEST gibi destekleri belirt
- Etiketleri (NLP, CV, Derin Öğrenme vb.) yaz

ÖRNEK:
📖 YAYINLAR:
1. Derin Sinir Ağları Aktivasyon Fonksiyonlarının Genetik Programlama ile Otomatik Tasarımı
   Aktivasyon fonksiyonlarının evrimsel arama ile otomatik keşfi.

🚀 PROJELER:
1. İşaret Dili Tespiti (Bilgisayarlı Görü, Derin Öğrenme)
   CNN-GRU modeli ile gerçek zamanlı işaret dili tanıma sistemi.

ÖNEMLİ: Her yanıtının başına 🐝, sonuna 🍯 koy."""

    INFO_CONTEXT_PROMPT = """
Kulüp hakkında bilgi verirken:
- Yapay Zeka Kulübü'nün misyonunu açıkla
- Çalışma alanlarını (Sağlıkta AI, NLP, CV, Veri Bilimi) belirt
- 5 SCI Yayını, 7 TÜBİTAK Projesi, 2 Teknofest Finalistliği gibi başarıları vurgula

ÖNEMLİ FORMAT KURALI:
- Her yanıtının EN BAŞINA 🐝 emojisi koy
- Her yanıtının EN SONUNA 🍯 emojisi koy
Örnek: '🐝 Merhaba! Kulübümüz hakkında... 🍯' """

    LINKS_CONTEXT_PROMPT = """
Link paylaşırken:
- Linki açık ve tıklanabilir olarak ver
- Linkin ne işe yarayacağını kısaca açıkla

ÖNEMLİ FORMAT KURALI:
- Her yanıtının EN BAŞINA 🐝 emojisi koy
- Her yanıtının EN SONUNA 🍯 emojisi koy
Örnek: '🐝 Merhaba! Kulübümüz hakkında... 🍯' """

    INFO_CONTEXT_PROMPT = """
Kulüp hakkında bilgi verirken:
- Yapay Zeka Kulübü'nün misyonunu açıkla
- Çalışma alanlarını (Sağlıkta AI, NLP, CV, Veri Bilimi) belirt
- 5 SCI Yayını, 7 TÜBİTAK Projesi, 2 Teknofest Finalistliği gibi başarıları vurgula

ÖNEMLİ FORMAT KURALI:
- Her yanıtının E   N BAŞINA 🐝 emojisi koy
- Her yanıtının EN SONUNA 🍯 emojisi koy
Örnek: '🐝 Merhaba! Kulübümüz hakkında... 🍯'"""

    LINKS_CONTEXT_PROMPT = """
Link paylaşırken:
- Linki açık ve tıklanabilir olarak ver
- Linkin ne işe yarayacağını kısaca açıkla

ÖNEMLİ FORMAT KURALI:
- Her yanıtının EN BAŞINA 🐝 emojisi koy
- Her yanıtının EN SONUNA 🍯 emojisi koy
Örnek: '🐝 Merhaba! Kulübümüz hakkında... 🍯'"""

    def __init__(self):
        self._custom_prompts: Dict[str, str] = {}
    
    @classmethod
    def get_current_datetime(cls) -> str:
        """Şu anki tarih ve saati döndürür."""
        now = datetime.now()
        return now.strftime('%d %B %Y, %A, %H:%M')
    
    def register_custom_prompt(self, prompt_type: str, prompt: str):

        self._custom_prompts[prompt_type] = prompt
    
    def build(
        self,
        user_message: str,
        prompt_type: PromptType = PromptType.GENERAL,
        context: Optional[str] = None,
        conversation_history: Optional[list] = None
    ) -> list:

        system_prompt = self._build_system_prompt(prompt_type, context)
        
        messages = [{"role": "system", "content": system_prompt}]
        
        # Konuşma geçmişi varsa ekle
        if conversation_history:
            for msg in conversation_history[-6:]:  # Son 6 mesaj
                messages.append(msg)
        
        # Kullanıcı mesajını ekle
        messages.append({"role": "user", "content": user_message})
        
        return messages
    
    def _build_system_prompt(self, prompt_type: PromptType, context: Optional[str] = None) -> str:
        """Intent tipine göre sistem promptu oluşturur."""
        
        # Temel prompt ile başla
        system_prompt = self.BASE_SYSTEM_PROMPT
        
        # Tarih bilgisi ekle
        system_prompt += f"\n\nŞu anki tarih ve saat: {self.get_current_datetime()}"
        
        # Prompt tipine göre ek context ekle
        if prompt_type == PromptType.EVENT:
            system_prompt += self.EVENT_CONTEXT_PROMPT
        elif prompt_type == PromptType.PROJECT:
            system_prompt += self.PROJECT_CONTEXT_PROMPT
        elif prompt_type == PromptType.INFO:
            system_prompt += self.INFO_CONTEXT_PROMPT
        elif prompt_type == PromptType.LINKS:
            system_prompt += self.LINKS_CONTEXT_PROMPT
        elif prompt_type == PromptType.WEB_AGENT:
            system_prompt += self.EVENT_CONTEXT_PROMPT + self.PROJECT_CONTEXT_PROMPT
        
        # Özel prompt varsa kullan
        if prompt_type.value in self._custom_prompts:
            system_prompt += f"\n\n{self._custom_prompts[prompt_type.value]}"
        
        # Context bilgisi varsa ekle
        if context:
            system_prompt += f"\n\n--- GÜNCEL BİLGİLER ---\n{context}"
        
        return system_prompt
    
    @staticmethod
    def build_simple(user_message: str) -> str:

        system_prompt = """Sen ClubChatBot'sun. 
Kullanıcılara kulüp bilgileri, bağlantılar, topluluk ayrıntıları ve genel soru-cevaplar konusunda yardımcı olursun.
Cevaplarını samimi ve doğru tut.

ÖNEMLİ FORMAT KURALI:
- Her yanıtının EN BAŞINA 🐝 emojisi koy
- Her yanıtının EN SONUNA 🍯 emojisi koy
Örnek: '🐝 Merhaba! Kulübümüz hakkında... 🍯'
"""
        final_prompt = (
            f"{system_prompt}\n"
            f"User: {user_message}\n"
            f"Assistant:"
        )
        return final_prompt
    
    @classmethod
    def for_web_agent(cls, user_message: str, scraped_data: str) -> list:
        builder = cls()
        return builder.build(
            user_message=user_message,
            prompt_type=PromptType.WEB_AGENT,
            context=scraped_data
        )
    
    @classmethod
    def for_event_query(cls, user_message: str, event_data: str) -> list:
        """Etkinlik sorguları için prompt."""
        builder = cls()
        return builder.build(
            user_message=user_message,
            prompt_type=PromptType.EVENT,
            context=event_data
        )
    
    @classmethod
    def for_project_query(cls, user_message: str, project_data: str) -> list:
        """Proje sorguları için prompt."""
        builder = cls()
        return builder.build(
            user_message=user_message,
            prompt_type=PromptType.PROJECT,
            context=project_data
        )
