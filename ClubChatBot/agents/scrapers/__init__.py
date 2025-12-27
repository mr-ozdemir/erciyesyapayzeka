# agents/scrapers/__init__.py
"""
Scrapers submodule for web agent.
Contains base scraper and specific page scrapers.
"""

from agents.scrapers.base_scraper import PageScraperBase
from agents.scrapers.event_scraper import EventScraper
from agents.scrapers.project_scraper import ProjectScraper

__all__ = ["PageScraperBase", "EventScraper", "ProjectScraper"]
