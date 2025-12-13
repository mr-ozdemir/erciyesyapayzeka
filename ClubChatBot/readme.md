
<img width="487" height="633" alt="image" src="https://github.com/user-attachments/assets/de720789-94bb-4510-b3c4-5671ee5e4d86" />


Amaç: ChatHandler sadece sınıfları çağırsın; karar verme (intent) IntentRouter’da; moderation ayrı; LLM & prompt engineering ayrı; scraping ayrı — her birinin tek sorumluluğu olsun.

Aşağıdaki açıklama dosya adını → görev → hangi modüllerle konuşur → tipik iç iş akışı/örnek çağrı formatında. Okuması kolay olsun diye mantıksal akış (sequence) blokları da ekledim.

Genel çalışma mantığı 

main.py Chainlit event’i yakalar → app.py veya doğrudan ChatHandler'ı çağırır.

ChatHandler gelen mesajı alır, önce ModerationChain'e gönderir. (Güvenlik)

Moderasyon temizse IntentRouter çağrılır → hangi araç/servis kullanılacağı döner: info, links, scrape, llm vb.

ChatHandler ilgili servisi sınıfından çağırır (ör. InfoService, BrowserAgent, LLMClient).

Gerekirse PromptBuilder ile LLM için prompt hazırlanır; LangChain memory ConversationBufferWindowMemory burada/llm_client içinde kullanılır.

Sonuç veritabanına / konuşma geçmişine kaydedilir (opsiyonel) ve kullanıcıya dönülür.

Bu “tek sorumluluk” (SRP) prensibine uygun: her dosya tek işi yapar, test edilebilir.

Aşağıda dosya bazlı detaylar
config/settings.py

Ne yapar: Tüm uygulama genelindeki konfigürasyonları tutar (API key, feature flags, site URL, limitler).
İçerik (örnek):

GROQ_API_KEY = os.getenv("GROQ_API_KEY", "")
SITE_BASE = "https://erciyesyapayzeka.com.tr"
USE_BROWSER_AGENT = True
MAX_DAILY_MESSAGES = 50
MAX_TOKENS = 3000


Kim kullanır: llm/llm_client.py, scraping/browser_agent.py, chat_handler.py, database.py.
Neden ayrı: Ortam değişkenlerini ve sabitleri tek yerden yönetmek için.

info/club_info.py, info/community_info.py, info/membership_info.py

Ne yapar: Statik, doğrulanmış metinler döner — kısa açıklama, SSS, üyelik prosedürü.
İçerik: sadece CLUB_INFO = "..." veya def get_info(): return "...".
Kim kullanır: chat_handler.py (IntentRouter info dediğinde), prompt_builder.py (gerekirse prompt’a ekler).
Neden ayrı: Bilgiler sabit olduğu için LLM’e göndermeden önce temiz bir kaynaktan çekmek daha güvenli ve tutarlı.

links/website_links.py, links/social_links.py

Ne yapar: Tüm dış URL’leri (etkinlik sayfası, üyelik formu, sosyal medya) dictionary/konstant olarak tutar.
Kim kullanır: chat_handler.py (intent=links), prompt_builder.py (LLM’e kaynak gönderirken), browser_agent.py (default start url).
Neden ayrı: Linkleri merkezi tutmak, güncellemeyi kolaylaştırır.

llm/prompt_builder.py

Ne yapar: Prompt engineering — sistem promptları, assistant davranış kuralları, memory bağlama, retrieval sonuçlarını prompt’a ekleme.
Tipik fonksiyonlar:

build_for_info(user_msg, info_text, memory)

build_for_scrape(user_msg, page_snippet, memory)

build_conversational(user_msg, memory)
Kim kullanır: chat_handler.py (veya intent_router), llm/llm_client.py.
Neden ayrı: Prompt formatları sık değişir; test edilebilir, versiyonlanabilir.

llm/llm_client.py

Ne yapar: LLM ile konuşan tek katman. Burada:

PromptBuilder kullanılır,

LangChain (ConversationBufferWindowMemory) veya Groq client çağrılır,

Async wrapper (blocking Groq çağrısını asyncio.to_thread ile sar) konur,

Response parsing ve hata handling yapılır.
Örnek API:

class GroqClient:
    async def ask(self, user_message: str, memory_text: str = "") -> str:
        messages = PromptBuilder.build(user_message, memory_text)
        resp = await asyncio.to_thread(lambda: self.client.chat.completions.create(...))
        return resp.choices[0].message.content


Kim kullanır: chat_handler.py (default intent=llm), intent_router (karar için LLM tabanlı intent classification yapıyorsan).
Neden ayrı: Tüm model değişiklikleri (model adı, parametre, token limit) sadece buradan yapılır.

Memory notu (sen dedin “memory yok”):

Eğer LangChain kullanacaksan llm_client içinde from langchain.memory import ConversationBufferWindowMemory çağırıp memory = ConversationBufferWindowMemory(k=5, return_messages=True) oluşturup ask() çağrısına önce memory.load_memory_variables() veya memory.save_context() benzeri işlemler ekleyebilirsin.

Alternatif: ChatHandler memory’yi yönetir ve memory_text olarak llm_client.ask()'a verir. (Senin tercihine göre.)

moderation/message_validator.py, moderation/profanity_filter.py, moderation/spam_filter.py, moderation/toxicity_detector.py, moderation/safety_rules.py

Her biri ne yapar (tek cümle):

message_validator: format, boşluk, çok uzunluk, içerik tipi kontrolü.

profanity_filter: küfür listesi / regex kontrolü.

spam_filter: hız limitleri, link yoğunluğu, tekrar mesaj kontrolü.

toxicity_detector: daha geniş toksisite paterni (hakaret/nefret/tehdit).

safety_rules: kişisel bilgi, yasa dışı talimatlar engelleme.
Kim kullanır: moderation/moderation_chain.py toplar ve chat_handler sadece moderation_chain.check(text, user_id) çağırır.
Neden ayrı: Güvenlik kuralları değiştikçe kolayca genişleyebilirsin.

moderation/moderation_chain.py

Ne yapar: Sırayla tüm moderation adımlarını çalıştırır ve ilk ihlalde uyarı döner.
İmza: def check(self, text: str, user_id: str = "__anon__") -> Optional[str]
Kim kullanır: chat_handler.py (ilk adım olarak).
Neden ayrı: Orkestrasyon tek yerde; yeni kurallar eklendiğinde burada sıra değişir.

router/intent_router.py

Ne yapar: Mesajın hangi “iş”e (intent) ait olduğunu tespit eder ve ChatHandler’a hangi servisi çağırması gerektiğini söyler.
Çıkış örnekleri: "info", "links", "scrape", "llm", "unknown"
İçerik yaklaşımları (opsiyonel):

Basit kural tabanlı classifier (kelime tabanlı).

Veya LLM’e kısa bir sınıflandırma prompt’u gönderip intent döndürmek (daha doğru ama maliyetli).
Kim kullanır: chat_handler.py (diyalog akışı için).
Neden ayrı: Karar mantığı burada toplanır; ChatHandler sade kalır.

scraping/browser_agent.py ve scraping/browser_scraping.py

Ne yapar: Web sayfasını alır ve metni temizler.

browser_agent — Playwright ile dinamik sayfa yüklemeyi yapar (opsiyonel).

browser_scraping — requests + BeautifulSoup ile basit fetch.
Fonksiyon örnekleri:

async def fetch_with_playwright(url) -> str

def fetch_simple(url) -> str

async def get_page_text(url) -> str # returns cleaned text
Kim kullanır: chat_handler (intent=scrape ise), ayrıca llm/prompt_builder (eğer retrieval-augmented prompt gerekiyorsa).
Robots.txt kontrolü: browser_agent içinde kontrol et (ethics).

app.py

Ne yapar: (Sana göre app.py bir yardımcı modul) chainlit dışında yardımcı fonksiyonları barındırır: util fonksiyonlar, URL helper’lar, tarih parse fonksiyonları, fetch helpers vb. Ayrıca ortak imports burada.
Kim kullanır: Her modül gerektiğinde from app import <helper> ile.

chat_handler.py

Ne yapar (senin isteğine göre): Sadece sınıfları çağırır — yani orchestration logic’ini minimal tutar. Yönlendirme kararı (intent detection) IntentRouter tarafından verilir.
Adım adım:

session_id = get_or_create_session() (kısa helper)

mod_result = moderation.check(text, user_id) → eğer sorun var → hemen uyarı dön.

intent = intent_router.detect_intent(text)

if intent == "info": response = InfoService.get(text)
elif intent == "links": response = LinksService.get(text)
elif intent == "scrape": snippet = await BrowserAgent.get_page_text(url); response = await llm.ask(user_msg, memory=...)
elif intent == "llm": response = await llm.ask(user_msg, memory=...)

db.save_conversation(...)

send response to user

Kim çağırır: main.py / Chainlit event.

Neden burada karar yok: Çünkü karar IntentRouter’da. ChatHandler sadece çağrıları yaptığı için birim test yazması kolay, okunaklı olur.

database.py

Ne yapar: Konuşma kayıtları, günlük limitler, moderasyon logları saklanır.
Fonksiyon örnekleri: save_conversation(session_id, chat_id, user_msg, bot_response, category), check_daily_limit(session_id), increment_daily_count(session_id)
Kim kullanır: chat_handler.py, moderation (strike mantığı saklamak için).

main.py

Ne yapar: Chainlit entrypoint. @cl.on_message burada bağlanır ve ChatHandler'ın handle_message()'ını çağırır. Ayrıca @cl.set_starters vs burada tanımlanır.
Kim kullanır: Kullanıcı tarafından chainlit run main.py -w ile başlatılır.

requirements.txt

Ne yapar: Proje bağımlılıkları (chainlit, groq, playwright, requests, beautifulsoup4, langchain vb.)
Neden önemli: Projeyi kuran kişinin tek komutla ortamı oluşturmasına yardımcı olur.

Tipik Çağrı Sıralaması — örnek senaryo (adım adım)

Kullanıcı: “Bu ayki etkinlikler ne?” → main.py → ChatHandler.handle_message

ChatHandler alır metni.

ModerationChain.check("Bu ayki etkinlikler ne?", user_id) → None (ok).

IntentRouter.detect_intent("Bu ayki etkinlikler ne?") → returns "scrape" veya "info" (siteye bağlı).

Eğer "scrape": page_text = await BrowserAgent.get_page_text(WEBSITE_LINKS["events"]) → PromptBuilder.build_for_scrape(user_msg, page_text, memory) → llm.ask(prompt, memory) → LLM cevabını döndür.
Eğer "info": InfoService.get_info() doğrudan dönebilir (ve DB’ye kaydet).

db.save_conversation(session, chat, user_msg, bot_response, category)

Sonuç Chainlit ile kullanıcıya gönderilir.

Karar Mantığı: IntentRouter nasıl çalışmalı?

3 opsiyon:

Hafif (kural tabanlı): if "etkinlik" in text: etc. hızlı ve bedava.

Orta (önceden eğitilmiş classifier): küçük ML modeli (sklearn) kullanarak ifadeleri sınıflandır.

Akıllı (LLM tabanlı): küçük prompt ile LLM’e “classify intent” sor — daha doğru ama token maliyeti var.

Tavsiye: Önce kural tabanlı başla; gerekirse LLM ile hibritle (önce kural, kural belirsizse LLM).

Memory nerede tutulur? (senin söylediğin “memory yok” durumu)

Eğer LangChain kullanıyorsan llm/llm_client.py içinde ConversationBufferWindowMemory kullanabilirsin.

Alternatif olarak chat_handler database'i kullanarak son N mesajı çekip memory_text oluşturup llm.ask(user_msg, memory_text) çağırır. (Senin kurulumuna daha uygun olabilir — bağımsız memory modülü istemiyorsan bu yöntem sade ve kalıcıdır.)

Örnek:

# chat_handler içinde
prev = db.get_last_messages(session_id, limit=6)  # returns list of str
memory_text = "\n".join(prev)
answer = await llm.ask(user_msg, memory_text)

Test & geliştirme notları (pratik)

İlk önce kural-tabanlı IntentRouter yaz: basit, deterministik.

ModerationChain’ı aktif hale getir; tests yaz (unit tests) her modüle.

LLM çağrılarını bir mock ile test et (CI’de gerçek API çağrısı olmasın).

Scraper’ı ayrı dene; Playwright kurulumunu opsiyonel hale getir.

Kısa özet / talimat (adım adım ne yapacağın)

config/settings.py içini doldur.

moderation/ içindeki sınıfları import edilebilir yap.

router/intent_router.py kural-tabanlı implementasyon yaz.

chat_handler.py sadece sınıfları instantiate etsin, sıralamayı IntentRouter’dan gelen intent’e göre yapsın.

llm/llm_client.py PromptBuilder kullanarak LLM çağrısını yap; memory ya burada ya DB üzerinden sağlanır.

scraping/browser_agent.py basit fetch_simple() ile başla; sonra Playwright ekle.

main.py Chainlit entrypoint.

DB (database.py) ile konuşmaları kaydet.
