# streamlit_app.py
"""
Main Streamlit application for ClubChatBot.
This is the entry point that orchestrates the chat interface.
"""

import streamlit as st
import os
import base64

# Import from frontend package
from frontend import StyleManager, ChatManager, MainView


def main():
    """Main application entry point."""
    from PIL import Image
    im = Image.open("assets/fav1.png")
    st.set_page_config(
        page_title="Yapay Zeka Kulübü Chatbot",
        page_icon=im,
        layout="centered",
        initial_sidebar_state="expanded"
    )
    
    # Apply styles and initialize chat manager
    StyleManager.apply_styles()
    chat_manager = ChatManager()
    
    # --- AYARLAR ---
    ai_avatar_path = "assets/fav1.png"
    user_avatar_path = "assets/avatar_user.png"
    sidebar_logo_path = "assets/logo.png"

    # ================= SIDEBAR =================
    with st.sidebar:
        # ===== ÜST BÖLÜM - Logo + Yeni Sohbet (aynı satır) =====
        col_logo, col_btn = st.columns([0.8, 0.2])
        
        with col_logo:
            if os.path.exists(sidebar_logo_path):
                with open(sidebar_logo_path, "rb") as f:
                    img_data = base64.b64encode(f.read()).decode()
                st.markdown(f'''
                    <a href="https://erciyesyapayzeka.com.tr" target="_blank">
                        <img src="data:image/png;base64,{img_data}" width="200" style="cursor: pointer; border-radius: 10px;">
                    </a>
                ''', unsafe_allow_html=True)
        
        with col_btn:
            if st.button("✚", key="new_chat_btn", help="Yeni Sohbet"):
                chat_manager.create_new_chat()
                st.rerun()
        
        # ===== ORTA BÖLÜM - SOHBETLER (SCROLLABLE AREA) =====
        # Scrollable container içinde expander
        with st.container(height=420):
            with st.expander("💬 Sohbetler", expanded=True):
                for chat in reversed(st.session_state.all_chats):
                    is_active = chat['id'] == st.session_state.current_chat_id
                    button_label = chat['title'][:28] + "..." if len(chat['title']) > 28 else chat['title']
                    
                    # Her sohbet için: [Sohbet Adı/Butonu] [⋮ Menü]
                    col_chat, col_menu = st.columns([0.85, 0.15])
                    
                    with col_chat:
                        if is_active:
                            st.markdown(f'''<div class="chat-item-active">📍 {button_label}</div>''', unsafe_allow_html=True)
                        else:
                            if st.button(button_label, key=f"chat_{chat['id']}", use_container_width=True):
                                chat_manager.switch_chat(chat['id'])
                                st.rerun()
                    
                    with col_menu:
                        with st.popover("⋮"):
                            st.markdown("**Düzenle**")
                            
                            # Yeniden Adlandır
                            new_name = st.text_input(
                                "Yeni ad",
                                value=chat['title'],
                                key=f"rename_{chat['id']}",
                                label_visibility="collapsed"
                            )
                            if st.button("✏️ Kaydet", key=f"save_{chat['id']}", use_container_width=True):
                                if new_name and new_name != chat['title']:
                                    chat_manager.rename_chat(chat['id'], new_name)
                                    st.rerun()
                            
                            st.divider()
                            
                            # Sil
                            if st.button("🗑️ Sil", key=f"del_{chat['id']}", use_container_width=True, type="secondary"):
                                chat_manager.delete_chat(chat['id'])
                                st.rerun()
        
        # ===== ALT BÖLÜM (SABİT) =====
        st.markdown('<div class="sidebar-footer">', unsafe_allow_html=True)
        
        # Model Seçici
        st.markdown("<p class='section-title'>🤖 Model</p>", unsafe_allow_html=True)
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
        st.markdown('''
            <div class="user-profile">
                <div class="user-avatar">K</div>
                <div class="user-info">
                    <p class="user-name">Kadir</p>
                    <p class="user-plan">Gözcü Arı Birimi</p>
                </div>
            </div>
        ''', unsafe_allow_html=True)
        
        st.markdown('</div>', unsafe_allow_html=True)

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