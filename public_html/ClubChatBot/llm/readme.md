📌 3) llm/ → LLM Katmanı

Bu katmanda iki önemli dosya var:

🔹 llm_client.py

Groq API çağrılarını yapar

model seçimini yapar

conversation memory kullanır (LangChain)

Yani: LLM ile konuşan tek yer burası.

🔹 prompt_builder.py

Sistem message oluşturur

Rol ayarları

Tool kullanımı gerektiğinde prompt’u genişletir

Club info veya link modülleri gerekiyorsa bunu prompt'a ekler

Tüm prompt engineering buraya.