'''
Alınan kullanıcı girdisine göre uygun niyeti yönlendiren modül.
IntentRouter sınıfı ile yapacağız.
agent mı kullanılacak, scraping mi, yoksa direkt LLM mi cevap verecek?
veya link modüllerine mi yönlendirecek?
veya info modüllerine mi?
Bu kararları burada vereceğiz.
'''
# Eğer proje, etkinlik, aktif etkinlik gibi güncel bilgiler istenirse tarayıcı ajanı kullanılacak. Bu mul sadece yönlendirme yapacak.
# Gelen mesajın niyetini belirleyecek ve uygun modülü çağıracak.

from scraping.browser_agent import BrowserAgent
from scraping.browser_scraping import WebScraper
# Direkt hangisi hakkında bilgi almak istiyorsa çalışacak
from info.club_info import ClubInfo
from info.community_info import CommunityInfo
from info.membership_info import MembershipInfo


# Link sorularında veya çoğu soruda bu linkleri vermeli
from links.website_links import WebsiteLinks
from links.social_links import SocialLinks




class IntentRouter:
    """
    Kullanıcı mesajının niyetini belirler ve uygun işleme yönlendirir.
    """

    def detect_intent(self, user_message: str) -> str:
        # Basit anahtar kelime tabanlı niyet tespiti
        message_lower = user_message.lower()

        if "bilgi" in message_lower or "etkinlik" in message_lower:
            return "info"
        elif "link" in message_lower or "site" in message_lower:
            return "links"
        elif "tarayıcı" in message_lower or "web kazıma" in message_lower:
            return "scrape"
        elif "yardımcı ajan" in message_lower or "agent" in message_lower:
            return "agent"
        else:
            return "llm"

    async def handle_with_agent(self, user_message: str) -> str:
        # Tarayıcı tabanlı ajan ile işleme
        from scraping.browser_agent import BrowserAgent
        agent = BrowserAgent()
        return await agent.interact(user_message)

    async def handle_with_scraping(self, user_message: str) -> str:
        # Web kazıma işlemi
        from scraping.browser_scraping import WebScraper
        scraper = WebScraper()
        return await scraper.scrape(user_message)

    async def handle_with_intent_router(self, user_message: str) -> str:
        # Diğer özel yönlendirmeler için
        return "Bu özellik yakında eklenecek."