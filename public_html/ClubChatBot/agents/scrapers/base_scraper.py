# agents/scrapers/base_scraper.py
"""
Abstract Base Class for all page scrapers.
Provides common interface and caching mechanism.
"""

from abc import ABC, abstractmethod
from typing import List, Any, Optional
from datetime import datetime, timedelta
import asyncio


class PageScraperBase(ABC):
    """
    Abstract base class for all page scrapers.
    
    Subclasses must implement:
    - page_path: Property returning the URL path (e.g., '/etkinlikler')
    - page_type: Property returning the page type identifier (e.g., 'event')
    - parse_content: Method to parse raw content into model objects
    - format_for_llm: Method to format parsed items for LLM consumption
    """
    
    def __init__(self, base_url: str = "https://erciyesyapayzeka.com.tr"):
        self.base_url = base_url
        self._cache: Optional[List[Any]] = None
        self._last_fetch: Optional[datetime] = None
        self._cache_duration = timedelta(minutes=10)  # 10 dakika cache
    
    @property
    @abstractmethod
    def page_path(self) -> str:
        """Sayfa path'i (örn: '/etkinlikler')"""
        pass
    
    @property
    @abstractmethod
    def page_type(self) -> str:
        """Sayfa tipi (örn: 'event', 'project')"""
        pass
    
    @property
    def full_url(self) -> str:
        """Tam URL'i döndürür."""
        return f"{self.base_url}{self.page_path}"
    
    @abstractmethod
    def parse_content(self, content: str) -> List[Any]:
        """
        HTML içeriğini parse et ve model listesi döndür.
        
        Args:
            content: Raw text content from the page
            
        Returns:
            List of parsed model objects (Event, Project, etc.)
        """
        pass
    
    @abstractmethod
    def format_for_llm(self, items: List[Any]) -> str:
        """
        Öğeleri LLM için formatla.
        
        Args:
            items: List of model objects
            
        Returns:
            Formatted string for LLM context
        """
        pass
    
    def _is_cache_valid(self) -> bool:
        """Cache'in geçerli olup olmadığını kontrol et."""
        if self._cache is None or self._last_fetch is None:
            return False
        return datetime.now() - self._last_fetch < self._cache_duration
    
    def scrape(self) -> List[Any]:
        """
        Sayfayı scrape et ve cache'le.
        Senkron versiyon - Streamlit için.
        
        Returns:
            List of parsed model objects
        """
        # Cache kontrolü
        if self._is_cache_valid():
            print(f"[{self.page_type}] ✅ Cache kullanılıyor ({len(self._cache)} öğe)")
            return self._cache
        
        print(f"[{self.page_type}] 🔄 Veri çekiliyor: {self.full_url}")
        
        try:
            # PlaywrightURLLoader kullan
            from langchain_community.document_loaders import PlaywrightURLLoader
            
            print(f"[{self.page_type}] Playwright ile deneniyor...")
            loader = PlaywrightURLLoader(
                urls=[self.full_url],
                remove_selectors=["nav", "footer", "header", "script", "style"],
                headless=True
            )
            
            docs = loader.load()
            
            if docs:
                content = docs[0].page_content
                print(f"[{self.page_type}] ✅ Playwright başarılı - {len(content)} karakter")
                self._cache = self.parse_content(content)
                self._last_fetch = datetime.now()
                return self._cache
            
            print(f"[{self.page_type}] ⚠️ Playwright boş döndü")
            return self._scrape_with_requests()
            
        except ImportError:
            print(f"[{self.page_type}] ⚠️ Playwright yok, requests ile deneniyor...")
            return self._scrape_with_requests()
        except Exception as e:
            print(f"[{self.page_type}] ❌ Playwright hatası: {e}")
            return self._scrape_with_requests()
    
    def _scrape_with_requests(self) -> List[Any]:
        """Fallback: requests ile scraping."""
        print(f"[{self.page_type}] 🔄 Requests ile deneniyor...")
        try:
            import requests
            from bs4 import BeautifulSoup
            
            response = requests.get(self.full_url, timeout=10)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # Script ve style taglarını kaldır
            for tag in soup(['script', 'style', 'nav', 'footer', 'header']):
                tag.decompose()
            
            content = soup.get_text(separator='\n', strip=True)
            print(f"[{self.page_type}] ✅ İnternet'ten veri alındı - {len(content)} karakter")
            self._cache = self.parse_content(content)
            self._last_fetch = datetime.now()
            print(f"[{self.page_type}] 📊 Parse sonucu: {len(self._cache)} öğe")
            return self._cache
            
        except Exception as e:
            print(f"[{self.page_type}] ❌ İnternet hatası: {e}")
            print(f"[{self.page_type}] 📦 FALLBACK veri kullanılıyor...")
            # Ağ hatası durumunda parse_content'e boş string gönder
            # Bu sayede subclass'ların fallback verileri çalışır
            if self._cache:
                return self._cache
            else:
                # Fallback verileri kullan
                self._cache = self.parse_content("")
                self._last_fetch = datetime.now()
                print(f"[{self.page_type}] 📊 Fallback sonucu: {len(self._cache)} öğe")
                return self._cache
    
    async def scrape_async(self) -> List[Any]:
        """
        Asenkron scraping - async context için.
        
        Returns:
            List of parsed model objects
        """
        # Cache kontrolü
        if self._is_cache_valid():
            return self._cache
        
        try:
            from playwright.async_api import async_playwright
            
            async with async_playwright() as p:
                browser = await p.chromium.launch(headless=True)
                page = await browser.new_page()
                
                await page.goto(self.full_url)
                await asyncio.sleep(2)  # JS render için bekle
                
                content = await page.inner_text("body")
                await browser.close()
                
                self._cache = self.parse_content(content)
                self._last_fetch = datetime.now()
                return self._cache
                
        except Exception as e:
            print(f"[{self.page_type}] Async scraping error: {e}")
            return self._cache if self._cache else []
    
    def clear_cache(self):
        """Cache'i temizle."""
        self._cache = None
        self._last_fetch = None
    
    def get_formatted_data(self) -> str:
        """Scrape et ve formatlanmış veri döndür."""
        items = self.scrape()
        return self.format_for_llm(items)
