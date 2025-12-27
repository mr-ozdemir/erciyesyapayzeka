# agents/web_agent.py
"""
Web Agent - Main agent class for handling web-based queries.
Orchestrates scrapers, PromptBuilder, and LLM for intelligent responses.
"""

from typing import Dict, Optional, List
from datetime import datetime
from langchain_groq import ChatGroq
from langchain_core.messages import HumanMessage, SystemMessage
from config.settings import settings
from agents.scrapers.base_scraper import PageScraperBase
from agents.scrapers.event_scraper import EventScraper
from agents.scrapers.project_scraper import ProjectScraper
from llm.prompt_builder import PromptBuilder, PromptType


class WebAgent:    
    def __init__(self):
        self.scrapers: Dict[str, PageScraperBase] = {}
        self.prompt_builder = PromptBuilder()
        self.llm = ChatGroq(
            api_key=settings.GROQ_API_KEY,
            model=settings.MODEL_NAME
        )
        self._register_default_scrapers()
    
    def _register_default_scrapers(self):
        """Varsayılan scraper'ları kaydet."""
        self.register_scraper(EventScraper())
        self.register_scraper(ProjectScraper())
    
    def register_scraper(self, scraper: PageScraperBase):

        self.scrapers[scraper.page_type] = scraper
        print(f"[WebAgent] Registered scraper: {scraper.page_type} -> {scraper.full_url}")
    
    def get_registered_scrapers(self) -> List[str]:
        """Kayıtlı scraper tiplerini döndürür."""
        return list(self.scrapers.keys())
    
    def detect_query_type(self, query: str) -> str:

        query_lower = query.lower()
        
        # Event keywords
        event_keywords = [
            "etkinlik", "event", "seminer", "workshop", "kamp",
            "eğitim", "aktivite", "toplantı", "data camp", "datacamp"
        ]
        if any(kw in query_lower for kw in event_keywords):
            return "event"
        
        # Project keywords
        project_keywords = [
            "proje", "project", "yayın", "araştırma", "research",
            "makale", "tez", "çalışma", "publication", "paper"
        ]
        if any(kw in query_lower for kw in project_keywords):
            return "project"
        
        # Hem etkinlik hem proje isteniyor olabilir
        if "neler var" in query_lower or "ne var" in query_lower:
            return "all"
        
        return "general"
    
    def _get_prompt_type(self, query_type: str) -> PromptType:
        """Sorgu tipine göre PromptType döndür."""
        mapping = {
            "event": PromptType.EVENT,
            "project": PromptType.PROJECT,
            "all": PromptType.WEB_AGENT,
            "general": PromptType.GENERAL
        }
        return mapping.get(query_type, PromptType.GENERAL)
    
    def get_context(self, query_type: str) -> str:
        """
        Sorgu tipine göre context oluştur.
        
        Args:
            query_type: 'event', 'project', 'all', veya 'general'
            
        Returns:
            LLM için context string
        """
        context_parts = []
        
        if query_type == "event" and "event" in self.scrapers:
            context_parts.append(self.scrapers["event"].get_formatted_data())
        
        elif query_type == "project" and "project" in self.scrapers:
            context_parts.append(self.scrapers["project"].get_formatted_data())
        
        elif query_type == "all":
            # Tüm scraper'lardan veri al
            for scraper_type, scraper in self.scrapers.items():
                context_parts.append(scraper.get_formatted_data())
        
        return "\n\n".join(context_parts) if context_parts else ""
    
    def process_query(self, query: str, conversation_history: Optional[List] = None) -> str:
        """
        Kullanıcı sorgusunu işle ve LLM ile yanıt üret.
        
        Args:
            query: Kullanıcı sorusu
            conversation_history: Önceki konuşma geçmişi (opsiyonel)
            
        Returns:
            LLM tarafından üretilen yanıt
        """
        # Sorgu tipini belirle
        query_type = self.detect_query_type(query)
        
        # Context al
        context = self.get_context(query_type)
        
        # PromptBuilder ile mesajlar oluştur
        prompt_type = self._get_prompt_type(query_type)
        messages = self.prompt_builder.build(
            user_message=query,
            prompt_type=prompt_type,
            context=context,
            conversation_history=conversation_history
        )
        
        # LLM'den yanıt al
        try:
            # Mesajları LangChain formatına dönüştür
            langchain_messages = []
            for msg in messages:
                if msg["role"] == "system":
                    langchain_messages.append(SystemMessage(content=msg["content"]))
                elif msg["role"] == "user":
                    langchain_messages.append(HumanMessage(content=msg["content"]))
                elif msg["role"] == "assistant":
                    from langchain_core.messages import AIMessage
                    langchain_messages.append(AIMessage(content=msg["content"]))
            
            response = self.llm.invoke(langchain_messages)
            return response.content
        except Exception as e:
            return f"Bir hata oluştu: {str(e)}"
    
    def refresh_all_caches(self):
        """Tüm scraper cache'lerini yenile."""
        for scraper in self.scrapers.values():
            scraper.clear_cache()
        print("[WebAgent] All caches cleared")
    
    def get_scraper(self, page_type: str) -> Optional[PageScraperBase]:
        """Belirli bir scraper'ı döndür."""
        return self.scrapers.get(page_type)
    
    def get_event_data(self) -> str:
        """Etkinlik verilerini döndür (direkt erişim için)."""
        if "event" in self.scrapers:
            return self.scrapers["event"].get_formatted_data()
        return "Etkinlik verisi bulunamadı."
    
    def get_project_data(self) -> str:
        """Proje verilerini döndür (direkt erişim için)."""
        if "project" in self.scrapers:
            return self.scrapers["project"].get_formatted_data()
        return "Proje verisi bulunamadı."
