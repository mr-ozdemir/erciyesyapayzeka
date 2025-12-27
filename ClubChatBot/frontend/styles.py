# frontend/styles.py
"""
StyleManager class for custom CSS styling.
Contains all the visual styles for the Streamlit application.
"""

import streamlit as st


class StyleManager:
    """Manages custom CSS styles for the application."""
    
    @staticmethod
    def apply_styles():
        """Apply custom CSS styles to the Streamlit app."""
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
