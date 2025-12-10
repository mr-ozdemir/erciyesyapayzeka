# chat_handler.py
import chainlit as cl
from config.settings import SITE_BASE
from llm.llm_client import GroqClient
from memory.memory_manager import MemoryManager
from moderation.moderation_chain import ModerationChain
from scraping.browser_agent import BrowserAgent
from router.intent_router import IntentRouter

# info and links (these are simple string modules under info/ and links/)
from info.club_info import CLUB_INFO
from info.community_info import COMMUNITY_INFO
from info.membership_info import MEMBERSHIP_INFO
from links.website_links import WEBSITE_LINKS
from links.social_links import SOCIAL_LINKS

from database import DatabaseManager
import uuid

db = DatabaseManager()

class ChatHandler:
    """
    Orchestrator: this class does NOT decide intents -- it asks the IntentRouter.
    It CALLS moderation, memory, llm, scraping, and info modules as required.
    """

    def __init__(self):
        self.llm = GroqClient()
        self.memory = MemoryManager(k=5)
        self.moderation = ModerationChain()
        self.browser = BrowserAgent()
        self.router = IntentRouter()

    def _get_session_id(self, cl_user_session) -> str:
        sid = cl_user_session.get("session_id")
        if not sid:
            sid = str(uuid.uuid4())[:8]
            cl_user_session.set("session_id", sid)
        return sid

    def _get_chat_id(self, cl_user_session) -> str:
        cid = cl_user_session.get("chat_id")
        if not cid:
            cid = str(uuid.uuid4())[:12]
            cl_user_session.set("chat_id", cid)
        return cid

    async def handle_message(self, message: cl.Message):
        session_id = self._get_session_id(cl.user_session)
        chat_id = self._get_chat_id(cl.user_session)
        user_id = getattr(message, "author", session_id)
        text = (message.content or "").strip()

        # 1) Moderation
        mod = self.moderation.check(text, user_id)
        if mod:
            await cl.Message(content=mod).send()
            return

        # 2) Intent detection (delegated to IntentRouter)
        intent = self.router.detect_intent(text)

        # 3) Handle intents by calling relevant modules (ChatHandler orchestrates)
        if intent == "info":
            # check membership vs club vs community
            if any(k in text.lower() for k in ["üyelik", "uyelik", "nasıl üye"]):
                answer = MEMBERSHIP_INFO
                category = "membership"
            else:
                answer = CLUB_INFO
                category = "club_info"

            db.save_conversation(session_id, chat_id, text, answer, category)
            await cl.Message(content=answer).send()
            return

        if intent == "links":
            # choose best link to return (prioritize keywords)
            tl = text.lower()
            if "etkinlik" in tl or "events" in tl:
                link = WEBSITE_LINKS.get("events") or WEBSITE_LINKS.get("home")
            elif "üyelik" in tl or "uyelik" in tl:
                link = WEBSITE_LINKS.get("membership") or WEBSITE_LINKS.get("home")
            else:
                # general social link
                link = SOCIAL_LINKS.get("instagram") or WEBSITE_LINKS.get("home")
            answer = f"🔗 İşte bulduğum link: {link}"
            db.save_conversation(session_id, chat_id, text, answer, "links")
            await cl.Message(content=answer).send()
            return

        if intent == "scrape":
            # use browser agent to fetch relevant page (events by default)
            # simplistic: if text mentions 'etkinlik' fetch events, else home
            if "etkinlik" in text.lower() or "event" in text.lower():
                url = WEBSITE_LINKS.get("events")
            else:
                url = WEBSITE_LINKS.get("home")
            page_text = await self.browser.get_page_text(url)
            if page_text:
                snippet = page_text[:1200]
                answer = f"🔎 Site içeriğinden kısa not:\n{snippet}\n\nKaynak: web sitesi"
                db.save_conversation(session_id, chat_id, text, snippet, "scrape")
                await cl.Message(content=answer).send()
                return
            # fallback link
            answer = f"🔗 İçerik alınamadı, lütfen siteyi ziyaret edin: {url}"
            db.save_conversation(session_id, chat_id, text, answer, "scrape_fallback")
            await cl.Message(content=answer).send()
            return

        # default: use LLM
        prev_memory = self.memory.load(session_id)
        answer = await self.llm.ask(text, memory=prev_memory)
        # save memory and DB
        self.memory.save_user_message(session_id, text)
        self.memory.save_bot_message(session_id, answer)
        db.save_conversation(session_id, chat_id, text, answer, "general")
        await cl.Message(content=answer).send()
