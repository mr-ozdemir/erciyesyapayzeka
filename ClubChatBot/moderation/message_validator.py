class MessageValidator:
    def check(self, text: str):
        if len(text.strip()) == 0:
            return "⚠️ Boş mesaj gönderemezsiniz."
        if len(text) > 2000:
            return "⚠️ Mesaj çok uzun!"
        return None
