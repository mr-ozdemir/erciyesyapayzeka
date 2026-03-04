# agents/__init__.py
"""
Web Agent module for ClubChatBot.
Provides extensible scraping agents for events, projects, and future pages.
"""

from agents.web_agent import WebAgent
from agents.scrapers.event_scraper import EventScraper
from agents.scrapers.project_scraper import ProjectScraper

__all__ = ["WebAgent", "EventScraper", "ProjectScraper"]
