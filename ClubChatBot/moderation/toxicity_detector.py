class ToxicityDetector:
    TOXIC_PATTERNS = [
        "seni döverim",
        "sen aptalsın",
        "herkes salak",
        "nefret ediyorum",
        "öldürürüm",
        
        # ve daha fazlası eklenmeli
    ]

    def check(self, text: str):
        lower = text.lower()
        if any(p in lower for p in self.TOXIC_PATTERNS):
            return "⚠️ Saldırgan veya toksik bir ifade algılandı."
        return None
