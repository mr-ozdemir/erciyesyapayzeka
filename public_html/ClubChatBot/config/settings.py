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
    GROQ_API_KEY: str = os.getenv("GROQ_API_KEY")

    # LLM Settings
    MODEL_NAME: str = "meta-llama/llama-4-maverick-17b-128e-instruct"

    # Logging
    DEBUG: bool = True

settings = Settings()
