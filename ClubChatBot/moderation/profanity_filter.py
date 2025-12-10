class ProfanityFilter:
    BAD_WORDS = [
        # Küfürlü kelimeler listesi
    ]

    def check(self, text: str):
        lower = text.lower()
        if any(word in lower for word in self.BAD_WORDS):
            return "⚠️ Küfür tespit edildi. Lütfen daha nazik konuşun."
        return None