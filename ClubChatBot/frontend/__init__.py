# frontend/__init__.py
"""
Frontend package for ClubChatBot Streamlit application.
Contains UI components, styles, and chat management.
"""

from frontend.styles import StyleManager
from frontend.chat_manager import ChatManager, MessageType
from frontend.views import MainView

__all__ = ['StyleManager', 'ChatManager', 'MessageType', 'MainView']
