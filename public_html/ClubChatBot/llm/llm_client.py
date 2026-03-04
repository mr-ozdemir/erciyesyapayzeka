# llm/llm_client.py

from groq import Groq
from config.settings import settings
from typing import List, Dict


class GroqClient:
    def __init__(self):
        self.client = Groq(api_key=settings.GROQ_API_KEY)
        self.model = settings.MODEL_NAME

    async def ask(self, prompt: str) -> str:
        """
        Asenkron LLM çağrısı (eski API - geriye dönük uyumluluk için).
        """
        response = self.client.chat.completions.create(
            model=self.model,
            messages=[{"role": "user", "content": prompt}],
            temperature=0.7
        )
        return response.choices[0].message.content

    def generate_response(self, messages: List[Dict[str, str]], temperature: float = 0.7, max_tokens: int = 2048) -> str:
        """
        Senkron LLM çağrısı - Streamlit için.
        
        Args:
            messages: Liste formatında mesaj geçmişi [{"role": "user/system/assistant", "content": "..."}]
            temperature: Yaratıcılık seviyesi (0.0-2.0)
            max_tokens: Maksimum token sayısı
            
        Returns:
            LLM'den gelen cevap metni
        """
        try:
            completion = self.client.chat.completions.create(
                model=self.model,
                messages=messages,
                temperature=temperature,
                max_tokens=max_tokens,
                top_p=1,
                stream=False
            )
            return completion.choices[0].message.content
        except Exception as e:
            return f"Hata oluştu: {str(e)}"
