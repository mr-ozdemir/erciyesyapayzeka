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

        /* Sidebar - Flexbox Layout with Fixed Footer */
        [data-testid="stSidebar"] {
            padding-top: 0 !important;
        }
        
        [data-testid="stSidebar"] > div:first-child {
            padding: 1rem !important;
            height: 100vh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }
        
        /* Expander takes remaining space and scrolls */
        [data-testid="stSidebar"] [data-testid="stExpander"] {
            flex: 1 !important;
            overflow-y: auto !important;
            min-height: 0 !important;
            border: none !important;
            background: transparent !important;
        }
        
        [data-testid="stSidebar"] [data-testid="stExpander"] > details > summary {
            color: #6B6B6B !important;
            font-size: 0.8rem !important;
            font-weight: 500 !important;
        }
        
        [data-testid="stSidebar"] [data-testid="stExpander"] > details > summary:hover {
            color: #fff !important;
        }
        
        /* Footer stays at bottom */
        .sidebar-footer {
            margin-top: auto !important;
            padding-top: 0.5rem !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        
        [data-testid="stSidebar"] img {
            border-radius: 12px;
            margin-bottom: 0.5rem;
        }
        
        /* Section Titles */
        .section-title {
            font-size: 0.7rem !important;
            color: #6B6B6B !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            margin: 0.8rem 0 0.4rem 0 !important;
            font-weight: 500 !important;
        }
        
        /* Chat Item Active - Subtle Dark BG */
        .chat-item-active {
            background: rgba(255, 255, 255, 0.08);
            padding: 0.5rem 0.6rem;
            border-radius: 6px;
            margin-bottom: 0.3rem;
            font-size: 0.8rem;
            color: #fff;
        }
        
        /* User Profile */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            margin-top: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #C9A961;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #151515;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-size: 0.9rem;
            font-weight: 500;
            color: #fff;
            margin: 0 !important;
        }
        
        .user-plan {
            font-size: 0.75rem;
            color: #6B6B6B;
            margin: 0 !important;
        }
        
        /* Sidebar Logo/Başlık */
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
        
        /* Sidebar Chat Butonları */
        [data-testid="stSidebar"] .stButton > button {
            background: transparent;
            border: none;
            color: var(--text-gray);
            margin-bottom: 0.2rem;
            font-weight: 400;
            text-align: left;
            padding: 0.4rem 0.5rem;
            border-radius: 6px;
            font-size: 0.8rem;
            line-height: 1.3;
        }
        
        [data-testid="stSidebar"] .stButton > button:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-white);
        }
        
        /* Expander Stilleri */
        [data-testid="stSidebar"] [data-testid="stExpander"] {
            border: none !important;
            background: transparent !important;
        }
        
        [data-testid="stSidebar"] [data-testid="stExpander"] summary {
            color: var(--text-muted) !important;
            font-size: 0.75rem !important;
            font-weight: 500 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            padding: 0.5rem 0 !important;
        }
        
        [data-testid="stSidebar"] [data-testid="stExpander"] summary:hover {
            color: var(--text-white) !important;
        }
        
        /* Silme Butonu Küçük */
        [data-testid="stSidebar"] .stButton > button[data-testid*="del_"] {
            padding: 0.2rem !important;
            font-size: 0.7rem !important;
            opacity: 0.5;
        }
        
        [data-testid="stSidebar"] .stButton > button[data-testid*="del_"]:hover {
            opacity: 1;
            background: rgba(255, 100, 100, 0.2) !important;
        }
        
        /* Aktif Chat Vurgusu */
        [data-testid="stSidebar"] .stButton > button:active {
            background: rgba(201, 169, 97, 0.15);
            color: var(--text-white);
        }
        
        /* Yeni Sohbet Butonu - Minimal */
        [data-testid="stSidebar"] button[data-testid*="new_chat"] {
            background: transparent !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: var(--text-gray) !important;
            font-size: 1rem !important;
            font-weight: 400 !important;
            padding: 0.4rem 0.5rem !important;
            border-radius: 6px !important;
            transition: all 0.15s ease !important;
        }
        
        [data-testid="stSidebar"] button[data-testid*="new_chat"]:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: var(--text-white) !important;
            border-color: rgba(255, 255, 255, 0.3) !important;
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
        
        /* Popover Menü Stilleri */
        [data-testid="stPopover"] {
            background: var(--bg-medium) !important;
            border: 1px solid var(--bg-light) !important;
            border-radius: 8px !important;
        }
        
        [data-testid="stPopover"] button {
            background: transparent !important;
            color: var(--text-white) !important;
            font-size: 0.8rem !important;
        }
        
        [data-testid="stPopover"] button:hover {
            background: rgba(255, 255, 255, 0.1) !important;
        }
        
        /* 3 Nokta Menü Butonu */
        [data-testid="stSidebar"] [data-testid="stPopoverButton"] {
            background: transparent !important;
            border: none !important;
            color: var(--text-muted) !important;
            padding: 0.2rem !important;
            min-width: 24px !important;
            font-size: 1rem !important;
        }
        
        [data-testid="stSidebar"] [data-testid="stPopoverButton"]:hover {
            background: rgba(255, 255, 255, 0.1) !important;
            color: var(--text-white) !important;
        }
        
        /* Popover İçi Input */
        [data-testid="stPopover"] input {
            background: var(--bg-dark) !important;
            border: 1px solid var(--bg-light) !important;
            border-radius: 6px !important;
            color: var(--text-white) !important;
            font-size: 0.8rem !important;
            padding: 0.4rem 0.6rem !important;
        }
        
        /* Popover İçi Divider */
        [data-testid="stPopover"] hr {
            border-color: var(--bg-light) !important;
            margin: 0.5rem 0 !important;
        }
        
        </style>
        """, unsafe_allow_html=True)
