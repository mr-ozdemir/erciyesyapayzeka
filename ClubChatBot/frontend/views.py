# frontend/views.py
"""
View components for the Streamlit application.
Contains UI components like welcome screen and quick action buttons.
"""

import streamlit as st
import os


class MainView:
    """Main view components for the chat interface."""
    
    @staticmethod
    def render_welcome():
        """Render the welcome screen with logo and quick action buttons."""
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
