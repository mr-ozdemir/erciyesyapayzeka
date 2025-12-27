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
        # Tıklanabilir Logo - Web sitesine gider
        if os.path.exists(sidebar_logo_path):
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