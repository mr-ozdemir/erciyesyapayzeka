# config/settings.py

#------------------------------
# gerekli ayarları ve yapılandırmaları tutan modül.
# Ortak ayarlar burada tanımlanır.
# Örneğin, API anahtarları, model isimleri vb.
#------------------------------


import os
from dotenv import load_dotenv

load_dotenv()

class Settings:
    APP_NAME: str = "ClubChatBot"
    VERSION: str = "1.0"

    # Groq API Key
    api_key = os.getenv("GROQ_API_KEY")

    # LLM Settings
    MODEL_NAME: str = "mixtral-8x7b-32768"

    # Logging
    DEBUG: bool = True

settings = Settings()
