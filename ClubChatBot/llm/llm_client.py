# llm/llm_client.py

from groq import Groq
from config.settings import settings


class GroqClient:
    def __init__(self):
        self.client = Groq(api_key=settings.GROQ_API_KEY)
        self.model = settings.MODEL_NAME

    async def ask(self, prompt: str) -> str:
        """
        Asenkron LLM çağrısı.
        """
        response = self.client.chat.completions.create(
            model=self.model,
            messages=[{"role": "user", "content": prompt}],
            temperature=0.7
        )

        return response.choices[0].message["content"]
