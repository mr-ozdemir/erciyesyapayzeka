import streamlit as st
import time
from enum import Enum
from typing import List, Optional, Dict, Any
import os
import datetime
from llm.llm_client import GroqClient
from database import DatabaseManager
from config.settings import settings


# ====================================================
#                 ENUMS & CONSTANTS
# ====================================================
class MessageType(Enum):
    USER = "user"
    ASSISTANT = "assistant"


# ====================================================
#                 CHAT MANAGER CLASS
# ====================================================
class ChatManager:
    def __init__(self):
        self.db = DatabaseManager()
        
        # Tüm sohbetleri tutan ana liste
        if "all_chats" not in st.session_state:
            st.session_state.all_chats = []
        
        # Session ID (kullanıcı oturumu)
        if "session_id" not in st.session_state:
            st.session_state.session_id = datetime.datetime.now().strftime("%Y%m%d_%H%M%S")
        
        # Şu anki aktif sohbetin ID'si
        if "current_chat_id" not in st.session_state:
            self.create_new_chat()
        
        # İlk yüklemede veritabanından sohbetleri yükle
        if "chats_loaded" not in st.session_state:
            self.load_chats_from_db()
            st.session_state.chats_loaded = True

    def load_chats_from_db(self):
        """Veritabanından mevcut sohbetleri yükler."""
        chat_list = self.db.get_chat_list(st.session_state.session_id)
        
        for chat_data in chat_list:
            chat_id = chat_data['chat_id']
            messages = self.db.get_chat_messages(chat_id)
            
            # Mesajları dönüştür
            formatted_messages = []
            for msg in messages:
                formatted_messages.append({"role": "user", "content": msg['user_message']})
                formatted_messages.append({"role": "assistant", "content": msg['bot_response']})
            
            # Chat objesini oluştur
            chat_obj = {
                "id": chat_id,
                "title": chat_data['preview'][:30] + "..." if len(chat_data['preview']) > 30 else chat_data['preview'],
                "messages": formatted_messages,
                "timestamp": datetime.datetime.strptime(chat_data['start_time'], "%Y-%m-%d %H:%M:%S")
            }
            
            # Eğer bu chat zaten yüklenmemişse ekle
            if not any(c['id'] == chat_id for c in st.session_state.all_chats):
                st.session_state.all_chats.append(chat_obj)

    def create_new_chat(self):
        """Yeni bir boş sohbet oluşturur ve aktif yapar."""
        new_id = f"chat_{datetime.datetime.now().strftime('%Y%m%d_%H%M%S_%f')}"
        new_chat = {
            "id": new_id,
            "title": "Yeni Sohbet",
            "messages": [],
            "timestamp": datetime.datetime.now()
        }
        st.session_state.all_chats.append(new_chat)
        st.session_state.current_chat_id = new_id
        return new_id

    def get_current_chat(self):
        """Aktif sohbet objesini döndürür."""
        chat_id = st.session_state.current_chat_id
        for chat in st.session_state.all_chats:
            if chat["id"] == chat_id:
                return chat
        return None

    def add_message(self, role: str, content: str):
        """Aktif sohbete mesaj ekler."""
        current_chat = self.get_current_chat()
        if current_chat:
            current_chat["messages"].append({"role": role, "content": content})
            
            # İlk mesajsa, başlığı güncelle
            if len(current_chat["messages"]) == 1 and role == "user":
                title = content[:30] + "..." if len(content) > 30 else content
                current_chat["title"] = title

    def switch_chat(self, chat_id):
        """Başka bir sohbete geçiş yapar."""
        st.session_state.current_chat_id = chat_id
        
    def generate_response(self, user_message: str):
        """ChatHandler kullanarak cevap üretir (moderation, intent routing, LLM)."""
        from chat_handler import ChatHandler
        
        # ChatHandler'ı başlat
        handler = ChatHandler()
        
        # Mesajı işle
        result = handler.handle_message(
            text=user_message,
            session_id=st.session_state.session_id,
            chat_id=st.session_state.current_chat_id,
            user_id=st.session_state.session_id
        )
        
        # Sonucu döndür
        return result.get("content", "Üzgünüm, bir hata oluştu.")



# ====================================================
#                 STYLING
# ====================================================
class StyleManager:
    @staticmethod
    def apply_styles():
        st.markdown("""
        <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        /* Ana Renk Paleti - Logo Uyumlu */
        :root {
            --gold-primary: #D4AF37;
            --gold-light: #F4D03F;
            --gold-dark: #B8941E;
            --black-bg: #0A0A0A;
            --dark-bg: #1A1A1A;
            --darker-bg: #0F0F0F;
            --accent-blue: #4A90E2;
        }

        /* Genel Ayarlar */
        * {
            font-family: 'Inter', sans-serif;
        }
        
        /* Ana Arka Plan */
        .stApp {
            background: linear-gradient(135deg, var(--black-bg) 0%, var(--darker-bg) 100%);
        }
        
        h1 {
            font-family: 'Inter', sans-serif;
            font-size: 2rem !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            text-align: center;
            margin-bottom: 1rem !important;
        }

        /* Sidebar - Siyah & Altın Tema */
        [data-testid="stSidebar"] {
            background: linear-gradient(180deg, var(--black-bg) 0%, var(--dark-bg) 100%);
            border-right: 1px solid rgba(212, 175, 55, 0.2);
        }
        
        [data-testid="stSidebar"] img {
            border-radius: 15px;
            margin-bottom: 20px;
        }

        /* Hoşgeldin Ekranı - Altın Gradient */
        .welcome-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .welcome-container h1 {
            background: linear-gradient(135deg, var(--gold-light) 0%, var(--gold-primary) 50%, var(--gold-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Ana Butonlar - Altın Tema */
        .stButton > button {
            width: 100%;
            border-radius: 12px;
            border: 2px solid var(--gold-primary);
            background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold-primary) 100%);
            color: var(--black-bg);
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .stButton > button:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-light) 100%);
            border-color: var(--gold-light);
        }
        
        /* Chat Mesajları - Modern Kartlar */
        [data-testid="stChatMessage"] {
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.8) 0%, rgba(15, 15, 15, 0.9) 100%);
            border-radius: 16px;
            padding: 1.2rem;
            margin-bottom: 0.8rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.1);
        }
        
        /* Kullanıcı Mesajı */
        [data-testid="stChatMessage"][data-testid*="user"] {
            border-left: 3px solid var(--gold-primary);
        }
        
        /* Asistan Mesajı */
        [data-testid="stChatMessage"][data-testid*="assistant"] {
            border-left: 3px solid var(--accent-blue);
        }
        
        /* Chat Input - Altın Vurgu */
        [data-testid="stChatInput"] {
            border-radius: 16px;
            border: 2px solid var(--gold-primary);
            background: var(--dark-bg);
        }
        
        [data-testid="stChatInput"]:focus-within {
            border-color: var(--gold-light);
        }
        
        /* Sidebar Butonları */
        [data-testid="stSidebar"] .stButton > button {
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: var(--gold-light);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        [data-testid="stSidebar"] .stButton > button:hover {
            background: rgba(212, 175, 55, 0.2);
            border-color: var(--gold-primary);
            color: white;
        }
        
        /* Primary Button (Yeni Sohbet) */
        [data-testid="stSidebar"] button[kind="primary"] {
            background: linear-gradient(135deg, var(--gold-dark) 0%, var(--gold-primary) 100%) !important;
            border: 2px solid var(--gold-primary) !important;
            color: var(--black-bg) !important;
            font-weight: 600 !important;
        }
        
        [data-testid="stSidebar"] button[kind="primary"]:hover {
            background: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-light) 100%) !important;
        }
        
        /* Sidebar Caption */
        [data-testid="stSidebar"] .element-container p {
            color: var(--gold-primary);
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        /* Spinner - Altın Renk */
        .stSpinner > div {
            border-top-color: var(--gold-primary) !important;
        }
        
        /* Scrollbar - Altın Tema */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--dark-bg);
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--gold-dark) 0%, var(--gold-primary) 100%);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, var(--gold-primary) 0%, var(--gold-light) 100%);
        }
        </style>
        """, unsafe_allow_html=True)


# ====================================================
#                 MAIN VIEW
# ====================================================
class MainView:
    @staticmethod
    def render_welcome():
        logo_path = "assets/fav1.png"
        
        col_left, col_center, col_right = st.columns([1, 0.6, 1])
        with col_center:
            if os.path.exists(logo_path):
                st.image(logo_path, width=180) 
            else:
                st.image("https://cdn-icons-png.flaticon.com/512/4712/4712027.png", width=150)
        
        st.markdown("""
            <div class="welcome-container">
                <h1>Keşfedilmiş Kainatın En İyi Kulübüne Hoş Geldiniz 🚀</h1>
            </div>
        """, unsafe_allow_html=True)
        
        col1, col2 = st.columns(2)
        selection = None
        
        with col1:
            if st.button("🐍 Python'da liste nasıl oluşturulur?", use_container_width=True): 
                selection = "Python'da liste nasıl oluşturulur?"
            if st.button("✍️ Bana yaratıcı bir hikaye anlat", use_container_width=True): 
                selection = "Bana yaratıcı bir hikaye anlat"

        with col2:
            if st.button("🔌 API entegrasyonu nasıl yapılır?", use_container_width=True): 
                selection = "API entegrasyonu nasıl yapılır?"
            if st.button("📊 Veri analizi araçları nelerdir?", use_container_width=True): 
                selection = "Veri analizi için en iyi araçlar nelerdir?"
        
        return selection


# ====================================================
#                 MAIN APP FLOW
# ====================================================
def main():
    from PIL import Image
    im = Image.open("assets/fav1.png")
    st.set_page_config(
        page_title="Yapay Zeka Kulübü Chatbot",
        page_icon=im,
        layout="centered",
        initial_sidebar_state="expanded"
    )
    
    StyleManager.apply_styles()
    chat_manager = ChatManager()
    
    # --- AYARLAR ---
    ai_avatar_path = "assets/fav1.png"
    user_avatar_path = "assets/user_avatar.png"
    sidebar_logo_path = "assets/logo.png"

    # ================= SIDEBAR =================
    with st.sidebar:
        if os.path.exists(sidebar_logo_path):
            st.image(sidebar_logo_path, use_container_width=True)
        else:
            st.title("🤖 Yapay Zeka Kulübü")

        st.markdown("---")
        
        if st.button("➕ Yeni Sohbet Başlat", type="primary", use_container_width=True):
            chat_manager.create_new_chat()
            st.rerun()
            
        st.markdown("---")
        st.caption("GEÇMİŞ SOHBETLER")

        # Sohbetleri ters sırada göster (en yeni üstte)
        for chat in reversed(st.session_state.all_chats):
            # Aktif sohbeti vurgula
            is_active = chat['id'] == st.session_state.current_chat_id
            button_label = f"{'🟢' if is_active else '💬'} {chat['title']}"
            
            if st.button(button_label, key=f"chat_btn_{chat['id']}", use_container_width=True):
                chat_manager.switch_chat(chat['id'])
                st.rerun()

    # ================= ANA İÇERİK =================
    current_chat = chat_manager.get_current_chat()
    
    # --- 1. GEÇMİŞ MESAJLARI GÖSTER ---
    if not current_chat["messages"]:
        selected_prompt = MainView.render_welcome()
        if selected_prompt:
            chat_manager.add_message("user", selected_prompt)
            st.rerun()
    else:
        for msg in current_chat["messages"]:
            if msg["role"] == "assistant":
                current_avatar = ai_avatar_path if os.path.exists(ai_avatar_path) else "🤖"
            else:
                current_avatar = user_avatar_path if os.path.exists(user_avatar_path) else "👤"
            
            with st.chat_message(msg["role"], avatar=current_avatar):
                st.markdown(msg["content"])

    # --- 2. YENİ MESAJ VE CEVAP ---
    if prompt := st.chat_input("Mesajınızı buraya yazın..."):
        # Kullanıcı Mesajı
        user_avatar = user_avatar_path if os.path.exists(user_avatar_path) else "👤"
        with st.chat_message("user", avatar=user_avatar):
            st.markdown(prompt)
        chat_manager.add_message("user", prompt)

        # Asistan Cevabı
        ai_avatar = ai_avatar_path if os.path.exists(ai_avatar_path) else "🤖"
        with st.chat_message("assistant", avatar=ai_avatar):
            with st.spinner("Düşünüyorum..."):
                response = chat_manager.generate_response(prompt)
                st.markdown(response)
        chat_manager.add_message("assistant", response)
        
        st.rerun()


if __name__ == "__main__":
    main()
