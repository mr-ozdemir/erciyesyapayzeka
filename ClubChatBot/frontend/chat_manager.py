# frontend/chat_manager.py
"""
ChatManager class for managing chat sessions and messages.
Handles chat creation, switching, message storage, and response generation.
"""

import streamlit as st
import datetime
from enum import Enum
from database import DatabaseManager


class MessageType(Enum):
    """Enum for message types in chat."""
    USER = "user"
    ASSISTANT = "assistant"


class ChatManager:
    """Manages chat sessions, messages, and response generation."""
    
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
