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
        
        # Model seçimi
        if "selected_model" not in st.session_state:
            st.session_state.selected_model = "llama-3.3-70b-versatile"
        
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
                "title": chat_data['preview'][:50] + "..." if len(chat_data['preview']) > 50 else chat_data['preview'],
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
                title = content[:50] + "..." if len(content) > 50 else content
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
#                 STYLING - MODERN & MINIMAL
# ====================================================
class StyleManager:
    @staticmethod
    def apply_styles():
        st.markdown("""
        <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        /* Modern Renk Paleti */
        :root {
            --honey-yellow: #C9A961;
            --honey-dark: #B39654;
            --bg-light: #2A2A2A;
            --bg-medium: #1F1F1F;
            --bg-dark: #151515;
            --text-white: #FFFFFF;
            --text-gray: #B0B0B0;
            --text-muted: #6B6B6B;
        }

        /* SADECE FOOTER'I GİZLE */
        footer {
            visibility: hidden;
        }

        /* Genel Ayarlar */
        * {
            font-family: 'Inter', sans-serif;
        }
        
        h1 {
            font-family: 'Inter', sans-serif;
            font-size: 2rem !important;
            font-weight: 600 !important;
            color: var(--text-white) !important;
            text-align: center;
            margin-bottom: 1rem !important;
        }

        /* Sidebar - Sabit Layout */
        [data-testid="stSidebar"] {
            padding-top: 0 !important;
            overflow: hidden !important;
        }
        
        [data-testid="stSidebar"] > div:first-child {
            padding: 1rem !important;
            height: 100vh !important;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
        }
        
        [data-testid="stSidebar"] img {
            border-radius: 12px;
            margin-bottom: 0.5rem;
        }
        
        /* Sidebar Logo/Başlık Küçült */
        [data-testid="stSidebar"] h1 {
            font-size: 1.2rem !important;
            font-weight: 600 !important;
            margin-bottom: 1rem !important;
        }

        /* Hoşgeldin Ekranı */
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
            color: var(--text-white);
        }
        
        /* Ana Butonlar - Kapalı Modern Sarı */
        .stButton > button {
            width: 100%;
            border-radius: 8px;
            border: none;
            background: var(--honey-yellow);
            color: var(--bg-dark);
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            transition: all 0.2s ease;
        }
        
        .stButton > button:hover {
            background: var(--honey-dark);
        }
        
        /* Chat Mesajları */
        [data-testid="stChatMessage"] {
            background: var(--bg-medium);
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 0.8rem;
            border: 1px solid var(--bg-light);
        }
        
        /* Kullanıcı Mesajı */
        [data-testid="stChatMessage"][data-testid*="user"] {
            border-left: 3px solid var(--honey-yellow);
        }
        
        /* Asistan Mesajı */
        [data-testid="stChatMessage"][data-testid*="assistant"] {
            border-left: 3px solid var(--text-gray);
        }
        
        /* Chat Input Container */
        [data-testid="stChatInput"] {
            border-radius: 12px;
            border: 1px solid var(--bg-light);
            background: var(--bg-medium);
        }
        
        [data-testid="stChatInput"]:focus-within {
            border-color: var(--honey-yellow);
        }
        
        /* Sidebar Chat Butonları - Compact */
        [data-testid="stSidebar"] .stButton > button {
            background: transparent;
            border: none;
            color: var(--text-gray);
            margin-bottom: 0.1rem;
            font-weight: 400;
            text-align: left;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            line-height: 1.3;
        }
        
        [data-testid="stSidebar"] .stButton > button:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-white);
        }
        
        /* Aktif Chat Vurgusu */
        [data-testid="stSidebar"] .stButton > button:active {
            background: rgba(201, 169, 97, 0.15);
            color: var(--text-white);
        }
        
        /* Primary Button (Yeni Sohbet) - Compact */
        [data-testid="stSidebar"] button[kind="primary"] {
            background: var(--honey-yellow) !important;
            border: none !important;
            color: var(--bg-dark) !important;
            font-weight: 600 !important;
            padding: 0.4rem 0.8rem !important;
            font-size: 0.8rem !important;
            margin-bottom: 0.5rem !important;
            width: auto !important;
        }
        
        [data-testid="stSidebar"] button[kind="primary"]:hover {
            background: var(--honey-dark) !important;
        }
        
        /* Sidebar Caption - Küçük */
        [data-testid="stSidebar"] .element-container p {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
        }
        
        /* Spinner */
        .stSpinner > div {
            border-top-color: var(--honey-yellow) !important;
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--bg-light);
            border-radius: 3px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--bg-medium);
        }
        
        /* Chat Input Gönder Butonu - Honey Yellow */
        [data-testid="stChatInput"] button[kind="primary"],
        [data-testid="stChatInput"] button:last-child {
            background: var(--honey-yellow) !important;
            color: var(--bg-dark) !important;
            border: none !important;
        }
        
        [data-testid="stChatInput"] button[kind="primary"]:hover,
        [data-testid="stChatInput"] button:last-child:hover {
            background: var(--honey-dark) !important;
        }
        
        /* Dosya Ekleme Butonu - Transparan/Gri */
        [data-testid="stChatInput"] button:first-child {
            background: transparent !important;
            color: var(--text-gray) !important;
            border: 1px solid var(--bg-light) !important;
        }
        
        [data-testid="stChatInput"] button:first-child:hover {
            background: var(--bg-light) !important;
            color: var(--text-white) !important;
        }
        
        /* Metin Renkleri */
        p, span, div {
            color: var(--text-white);
        }
        
        /* Input Metinleri */
        input, textarea {
            color: var(--text-white) !important;
        }
        
        /* Kullanıcı Profil Bölümü */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            margin-top: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .user-profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--honey-yellow);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bg-dark);
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-profile-info {
            flex: 1;
        }
        
        .user-profile-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-white);
            margin: 0;
        }
        
        .user-profile-plan {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin: 0;
        }
        
        /* Model Seçici */
        .stSelectbox {
            margin-bottom: 1rem;
        }
        
        .stSelectbox > div > div {
            background: var(--bg-medium);
            border: 1px solid var(--bg-light);
            border-radius: 8px;
            color: var(--text-white);
            font-size: 0.85rem;
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
                st.image(logo_path, width=3500) 
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
            if st.button("Aktif etkinlikleriniz neler?", use_container_width=True): 
                selection = "Aktif etkinlikleriniz neler?"
            if st.button("Projeleriniz neler?", use_container_width=True): 
                selection = "Projeleriniz neler?"

        with col2:
            if st.button("Kulüp hakkında bilgi ver", use_container_width=True): 
                selection = "Kulüp hakkında bilgi ver"
            if st.button("Vizyon ve misyonlarınız nedir?", use_container_width=True): 
                selection = "Vizyon ve misyonlarınız nedir?"
        
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
        # Tıklanabilir Logo - Web sitesine gider
        if os.path.exists(sidebar_logo_path):
            import base64
            
            # Görseli base64'e çevir
            with open(sidebar_logo_path, "rb") as f:
                img_data = base64.b64encode(f.read()).decode()
            
            st.markdown(f"""
                <a href="https://erciyesyapayzeka.com.tr" target="_blank" style="display: block; text-align: center;">
                    <img src="data:image/png;base64,{img_data}" width="300" style="cursor: pointer; border-radius: 12px;">
                </a>
            """, unsafe_allow_html=True)
        
        # Yeni Sohbet Butonu (+ Icon ile)
        if st.button("➕ Yeni Sohbet", type="primary", use_container_width=True, key="new_chat_btn"):
            chat_manager.create_new_chat()
            st.rerun()
        
        # Geçmiş Sohbetler Başlığı
        st.markdown("<p style='font-size: 0.65rem; color: #6B6B6B; text-transform: uppercase; letter-spacing: 1px; margin: 0.5rem 0 0.3rem 0;'>Geçmiş Sohbetler</p>", unsafe_allow_html=True)
        
        # Scrollable Chat Container (kalan alana sığacak şekilde)
        with st.container(height=350):
            for chat in reversed(st.session_state.all_chats):
                is_active = chat['id'] == st.session_state.current_chat_id
                button_label = chat['title'][:40] + "..." if len(chat['title']) > 40 else chat['title']
                
                if is_active:
                    st.markdown(f'''<div style="background: rgba(201, 169, 97, 0.2); border-left: 3px solid #C9A961; padding: 0.4rem 0.6rem; border-radius: 6px; margin-bottom: 0.2rem; font-size: 0.75rem; color: #fff;">{button_label}</div>''', unsafe_allow_html=True)
                else:
                    if st.button(button_label, key=f"chat_btn_{chat['id']}", use_container_width=True):
                        chat_manager.switch_chat(chat['id'])
                        st.rerun()
        
        # Sabit Alt Bölüm
        st.markdown("---")
        
        # Model Seçici
        st.markdown("<p style='font-size: 0.65rem; color: #6B6B6B; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.3rem;'>Model</p>", unsafe_allow_html=True)
        model_options = {
            "Llama 3.3 70B": "llama-3.3-70b-versatile",
            "Llama 3.1 8B": "llama-3.1-8b-instant",
            "Mixtral 8x7B": "mixtral-8x7b-32768"
        }
        
        selected_model_name = st.selectbox(
            "Model seçin",
            options=list(model_options.keys()),
            index=0,
            label_visibility="collapsed"
        )
        st.session_state.selected_model = model_options[selected_model_name]
        
        # Kullanıcı Profili
        st.markdown("""
            <div style="display: flex; align-items: center; gap: 0.7rem; padding: 0.8rem; background: rgba(255,255,255,0.03); border-radius: 10px; margin-top: 1rem;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #C9A961; display: flex; align-items: center; justify-content: center; color: #151515; font-weight: 600; font-size: 0.9rem;">K</div>
                <div>
                    <p style="font-size: 0.9rem; font-weight: 500; color: #fff; margin: 0;">Kadir</p>
                    <p style="font-size: 0.75rem; color: #6B6B6B; margin: 0;">Free plan</p>
                </div>
            </div>
        """, unsafe_allow_html=True)

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
    # Image yükleme destekli chat input
    user_input = st.chat_input(
        "Mesajınızı yazın veya görsel yükleyin...",
        accept_file=True,
        file_type=["jpg", "jpeg", "png", "gif", "webp"]
    )
    
    if user_input:
        # Text veya dict olabilir
        if isinstance(user_input, str):
            prompt = user_input
            uploaded_files = []
        else:
            prompt = user_input.text if user_input.text else "[Görsel yüklendi]"
            uploaded_files = user_input.files if hasattr(user_input, 'files') else []
        
        # Kullanıcı Mesajı
        user_avatar = user_avatar_path if os.path.exists(user_avatar_path) else "👤"
        with st.chat_message("user", avatar=user_avatar):
            st.markdown(prompt)
            # Yüklenen görselleri göster
            for file in uploaded_files:
                st.image(file, caption=file.name, width=300)
        
        chat_manager.add_message("user", prompt)

        # Asistan Cevabı
        ai_avatar = ai_avatar_path if os.path.exists(ai_avatar_path) else "🤖"
        with st.chat_message("assistant", avatar=ai_avatar):
            with st.spinner("Düşünüyorum...🐝🐝🐝"):
                response = chat_manager.generate_response(prompt)
                st.markdown(response)
        chat_manager.add_message("assistant", response)
        
        st.rerun()


if __name__ == "__main__":
    main()