import time

class SpamFilter:
    last_message_time = 0

    def check(self, text: str, user_id: str = None):
        now = time.time()
        diff = now - SpamFilter.last_message_time

        SpamFilter.last_message_time = now

        # 2 saniyeden kısa sürede 2. mesaj
        if diff < 2:
            return "⚠️ Çok hızlı mesaj gönderiyorsunuz. Lütfen biraz bekleyin."
        return None
