'''
Alınan kullanıcı girdisine göre uygun niyeti yönlendiren modül.
IntentRouter sınıfı ile yapacağız.
web_agent mı kullanılacak, yoksa direkt LLM mi cevap verecek?
veya link modüllerine mi yönlendirecek?
veya info modüllerine mi?
Bu kararları burada vereceğiz.
'''


class IntentRouter:
    """
    Kullanıcı mesajının niyetini belirler ve uygun işleme yönlendirir.
    """

    def detect_intent(self, user_message: str) -> str:
        """
        Kullanıcı mesajının niyetini belirler.
        
        Returns:
            'web_agent': Etkinlik ve proje sorguları (WebAgent kullanır)
            'info': Genel kulüp bilgisi
            'links': Link talepleri
            'llm': Genel soru-cevap
        """
        message_lower = user_message.lower()

        # Web Agent intent - etkinlik ve proje sorguları
        web_agent_keywords = [
            # Etkinlik anahtar kelimeleri
            "etkinlik", "event", "seminer", "workshop", "kamp", "eğitim",
            "data camp", "datacamp", "mutex", "cenglish", "aktivite",
            # Proje anahtar kelimeleri
            "proje", "project", "yayın", "araştırma", "research", "makale",
            # Durum anahtar kelimeleri
            "yaklaşan", "aktif", "güncel", "ne var", "neler var", "son",
            # Scraping anahtar kelimeleri (artık web_agent'a yönlendirilir)
            "tarayıcı", "web kazıma", "scrape"
        ]
        if any(kw in message_lower for kw in web_agent_keywords):
            return "web_agent"
        
        # Kulüp bilgisi
        if "bilgi" in message_lower or "hakkında" in message_lower:
            return "info"
        elif "link" in message_lower or "site" in message_lower:
            return "links"
        else:
            return "llm"