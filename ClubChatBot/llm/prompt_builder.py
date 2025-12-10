# llm/prompt_builder.py

class PromptBuilder:
    """
    Kullanıcı mesajını alır ve LLM için daha uygun formatta bir prompt oluşturur.
    Tüm prompt engineering burada yapılır.
    """

    @staticmethod
    def build(user_message: str) -> str:
        system_prompt = """
You are ClubChatBot. 
You help users with club information, links, community details, and general Q&A.
Keep answers short, friendly, and accurate.
"""
        final_prompt = (
            f"{system_prompt}\n"
            f"User: {user_message}\n"
            f"Assistant:"
        )

        return final_prompt
