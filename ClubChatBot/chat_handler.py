# chat_handler.py
"""
Streamlit-compatible ChatHandler
Orchestrates moderation, memory, LLM, scraping, and info modules.
"""

from config.settings import settings
from llm.llm_client import GroqClient
from moderation.moderation_chain import ModerationChain
from router.intent_router import IntentRouter
from database import DatabaseManager
import uuid
from typing import Optional, Dict, Any

# Try to import BrowserAgent (may not be implemented)
try:
    from scraping.browser_agent import BrowserAgent
    BROWSER_AVAILABLE = True
except (ImportError, AttributeError):
    BROWSER_AVAILABLE = False
    BrowserAgent = None


# Import info and links modules
def load_club_info():
    """Load club info from info folder."""
    try:
        from info.club_info import ClubInfo
        return ClubInfo.get_info()
    except Exception as e:
        return "Erciyes Üniversitesi Yapay Zeka Kulübü hakkında bilgi almak için lütfen web sitemizi ziyaret edin."

def load_community_info():
    """Load community info from info folder."""
    try:
        from info.community_info import CommunityInfo
        return CommunityInfo.get_info()
    except Exception as e:
        return "Kulüp topluluğu hakkında bilgi almak için lütfen web sitemizi ziyaret edin."

def load_membership_info():
    """Load membership info from info folder."""
    try:
        from info.membership_info import MembershipInfo
        return MembershipInfo.get_info()
    except Exception as e:
        return "Üyelik için lütfen web sitemizi ziyaret edin veya sosyal medya hesaplarımızdan bize ulaşın."

try:
    from links.website_links import WebsiteLinks
    WEBSITE_LINKS = {
        "home": WebsiteLinks.HOME_PAGE,
        "events": WebsiteLinks.EVENTS_PAGE,
        "membership": WebsiteLinks.MEMBERSHIP_PAGE,
        "research": WebsiteLinks.RESEARCH_PAGE,
    }
except Exception:
    WEBSITE_LINKS = {
        "home": "https://erciyesyapayzeka.com.tr",
        "events": "https://erciyesyapayzeka.com.tr/etkinlikler",
        "membership": "https://erciyesyapayzeka.com.tr/uyeol"
    }

try:
    from links.social_links import SocialLinks
    SOCIAL_LINKS = SocialLinks.SOCIAL_LINKS
except Exception:
    SOCIAL_LINKS = {
        "instagram": "https://instagram.com/eruaiclub",
        "linkedin": "https://linkedin.com/company/erciyes-yapay-zeka",
        "github": "https://github.com/Yapay-Zeka-Kulubu"
    }


class ChatHandler:
    """
    Streamlit-compatible orchestrator for chat functionality.
    Handles moderation, intent routing, and response generation.
    """

    def __init__(self):
        self.llm = GroqClient()
        self.moderation = ModerationChain()
        self.browser = BrowserAgent() if BROWSER_AVAILABLE else None
        self.router = IntentRouter()
        self.db = DatabaseManager()

    def handle_message(
        self, 
        text: str, 
        session_id: str, 
        chat_id: str,
        user_id: Optional[str] = None
    ) -> Dict[str, Any]:
        """
        Handle a user message and return the response.
        
        Args:
            text: User message text
            session_id: Session identifier
            chat_id: Chat identifier
            user_id: Optional user identifier
            
        Returns:
            Dict with 'content', 'category', and optional 'error' keys
        """
        if not user_id:
            user_id = session_id
            
        text = text.strip()
        
        # 1) Moderation
        mod_result = self.moderation.check(text, user_id)
        if mod_result:
            return {
                "content": mod_result,
                "category": "moderation",
                "error": True
            }

        # 2) Intent detection
        intent = self.router.detect_intent(text)

        # 3) Handle intents
        if intent == "info":
            return self._handle_info_intent(text, session_id, chat_id)
        
        if intent == "links":
            return self._handle_links_intent(text, session_id, chat_id)
        
        if intent == "scrape":
            return self._handle_scrape_intent(text, session_id, chat_id)

        # Default: use LLM
        return self._handle_llm_intent(text, session_id, chat_id)

    def _handle_info_intent(self, text: str, session_id: str, chat_id: str) -> Dict[str, Any]:
        """Handle info-related queries."""
        text_lower = text.lower()
        
        if any(k in text_lower for k in ["üyelik", "uyelik", "nasıl üye"]):
            answer = load_membership_info()
            category = "membership"
        elif any(k in text_lower for k in ["topluluk", "community"]):
            answer = load_community_info()
            category = "community"
        else:
            answer = load_club_info()
            category = "club_info"

        self.db.save_conversation(session_id, chat_id, text, answer, category)
        
        return {
            "content": answer,
            "category": category,
            "error": False
        }

    def _handle_links_intent(self, text: str, session_id: str, chat_id: str) -> Dict[str, Any]:
        """Handle link requests."""
        text_lower = text.lower()
        
        if "etkinlik" in text_lower or "events" in text_lower:
            link = WEBSITE_LINKS.get("events") or WEBSITE_LINKS.get("home")
        elif "üyelik" in text_lower or "uyelik" in text_lower:
            link = WEBSITE_LINKS.get("membership") or WEBSITE_LINKS.get("home")
        else:
            # General social link
            link = SOCIAL_LINKS.get("instagram") or WEBSITE_LINKS.get("home")
        
        answer = f"🔗 İşte bulduğum link: {link}"
        self.db.save_conversation(session_id, chat_id, text, answer, "links")
        
        return {
            "content": answer,
            "category": "links",
            "error": False
        }

    def _handle_scrape_intent(self, text: str, session_id: str, chat_id: str) -> Dict[str, Any]:
        """Handle web scraping requests."""
        text_lower = text.lower()
        
        if "etkinlik" in text_lower or "event" in text_lower:
            url = WEBSITE_LINKS.get("events")
        else:
            url = WEBSITE_LINKS.get("home")
        
        # Check if browser is available
        if self.browser and BROWSER_AVAILABLE:
            try:
                # Note: browser.get_page_text is async, but we'll handle it synchronously for Streamlit
                # You may need to adjust this based on your BrowserAgent implementation
                page_text = self.browser.get_page_text_sync(url) if hasattr(self.browser, 'get_page_text_sync') else None
                
                if page_text:
                    snippet = page_text[:1200]
                    answer = f"🔎 Site içeriğinden kısa not:\n{snippet}\n\nKaynak: web sitesi"
                    self.db.save_conversation(session_id, chat_id, text, snippet, "scrape")
                    
                    return {
                        "content": answer,
                        "category": "scrape",
                        "error": False
                    }
            except Exception as e:
                pass
        
        # Fallback - browser not available or scraping failed
        answer = f"🔗 İçerik alınamadı, lütfen siteyi ziyaret edin: {url}"
        self.db.save_conversation(session_id, chat_id, text, answer, "scrape_fallback")
        
        return {
            "content": answer,
            "category": "scrape_fallback",
            "error": False
        }

    def _handle_llm_intent(self, text: str, session_id: str, chat_id: str) -> Dict[str, Any]:
        """Handle general queries with LLM."""
        # Build system prompt
        system_prompt = "Sen Erciyes Üniversitesi Yapay Zeka Kulübü'nün yardımsever AI asistanısın. Türkçe cevap ver ve kullanıcılara yapay zeka, programlama ve kulüp etkinlikleri hakkında yardımcı ol."
        
        messages = [
            {"role": "system", "content": system_prompt},
            {"role": "user", "content": text}
        ]
        
        # Get LLM response
        answer = self.llm.generate_response(messages, temperature=0.7, max_tokens=2048)
        
        # Save to database
        self.db.save_conversation(session_id, chat_id, text, answer, "general")
        
        return {
            "content": answer,
            "category": "general",
            "error": False
        }
