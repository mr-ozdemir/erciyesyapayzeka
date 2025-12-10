class SafetyRules:
    BLOCKED_KEYWORDS = [
        "tc no", "kimlik", "adres", 
        "card number", "credit card"
        # yasal problem açacak her şey
    ]

    def check(self, text: str):
        lower = text.lower()
        if any(word in lower for word in self.BLOCKED_KEYWORDS):
            return "⚠️ Güvenlik nedeniyle kişisel bilgi paylaşamazsınız."
        return None
