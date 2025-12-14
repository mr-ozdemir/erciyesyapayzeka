"""
Browser Agent Module
Intelligent agent for fetching and processing website content.
Handles events, projects, and active events queries.
"""

from typing import Optional, Dict
from scraping.browser_scraping import WebScraper
from links.website_links import WebsiteLinks


class BrowserAgent:
    """
    Intelligent browser agent for fetching website content.
    Determines what to scrape based on user query.
    """
    
    def __init__(self):
        self.scraper = WebScraper()
    
    def get_page_text_sync(self, url: str, max_chars: int = 1500) -> Optional[str]:
        """
        Synchronously fetch and return page text (for Streamlit compatibility).
        
        Args:
            url: URL to fetch
            max_chars: Maximum characters to return
            
        Returns:
            Cleaned and summarized page text
        """
        try:
            # Scrape the URL
            full_text = self.scraper.scrape_url(url)
            
            if not full_text:
                return None
            
            # Extract summary
            summary = self.scraper.extract_summary(full_text, max_chars)
            
            return summary
            
        except Exception as e:
            print(f"BrowserAgent error: {str(e)}")
            return None
    
    def fetch_events(self, events_url: Optional[str] = None) -> Optional[str]:
        """
        Fetch events information from the events page.
        
        Args:
            events_url: Optional URL override
            
        Returns:
            Events information text
        """
        url = events_url or WebsiteLinks.EVENTS_PAGE
        return self.get_page_text_sync(url, max_chars=2000)
    
    def fetch_projects(self, projects_url: Optional[str] = None) -> Optional[str]:
        """
        Fetch projects information.
        
        Args:
            projects_url: Optional URL override
            
        Returns:
            Projects information text
        """
        # Projects seem to be under research or similar in the current map, 
        # but if there was a separate projects page, we'd use it.
        # Based on determine_url_from_query in original code, projects -> research.
        # But let's check if WebsiteLinks has PROJECTS_PAGE. It does not.
        # So we default to RESEARCH_PAGE for now as per previous logic.
        url = projects_url or WebsiteLinks.RESEARCH_PAGE
        return self.get_page_text_sync(url, max_chars=2000)
    
    def fetch_research(self, research_url: Optional[str] = None) -> Optional[str]:
        """
        Fetch research information.
        
        Args:
            research_url: Optional URL override
            
        Returns:
            Research information text
        """
        url = research_url or WebsiteLinks.RESEARCH_PAGE
        return self.get_page_text_sync(url, max_chars=2000)
    
    def determine_url_from_query(self, query: str, website_links: Optional[Dict[str, str]] = None) -> str:
        """
        Determine which URL to scrape based on user query.
        
        Args:
            query: User's question
            website_links: Optional dictionary of available website links (deprecated, uses WebsiteLinks class)
            
        Returns:
            URL to scrape
        """
        query_lower = query.lower()
        
        # Check for specific keywords
        if any(word in query_lower for word in ["etkinlik", "event", "aktivite"]):
            return WebsiteLinks.EVENTS_PAGE
        
        elif any(word in query_lower for word in ["proje", "project"]):
            return WebsiteLinks.RESEARCH_PAGE
        
        elif any(word in query_lower for word in ["araştırma", "research"]):
            return WebsiteLinks.RESEARCH_PAGE
        
        elif any(word in query_lower for word in ["üye", "kayıt", "membership", "katıl"]):
            return WebsiteLinks.MEMBERSHIP_PAGE

        else:
            # Default to home page
            return WebsiteLinks.HOME_PAGE