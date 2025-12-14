"""
Web Scraping Module
Fetches content from the club website for events, projects, and active events.
"""

import requests
from bs4 import BeautifulSoup
from typing import Optional, Dict


class WebScraper:
    """
    Web scraper for fetching content from club website.
    Uses requests and BeautifulSoup for HTML parsing.
    """
    
    def __init__(self, timeout: int = 10):
        self.timeout = timeout
        self.headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
    
    def scrape_url(self, url: str) -> Optional[str]:
        """
        Scrape content from a URL and return cleaned text.
        
        Args:
            url: URL to scrape
            
        Returns:
            Cleaned text content or None if failed
        """
        try:
            response = requests.get(url, headers=self.headers, timeout=self.timeout)
            response.raise_for_status()
            
            # Parse HTML
            soup = BeautifulSoup(response.content, 'html.parser')
            
            # Remove script and style elements
            for script in soup(["script", "style", "nav", "footer", "header"]):
                script.decompose()
            
            # Get text
            text = soup.get_text()
            
            # Clean up text
            lines = (line.strip() for line in text.splitlines())
            chunks = (phrase.strip() for line in lines for phrase in line.split("  "))
            text = '\n'.join(chunk for chunk in chunks if chunk)
            
            return text
            
        except Exception as e:
            print(f"Scraping error for {url}: {str(e)}")
            return None
    
    def scrape_events(self, events_url: str) -> Optional[str]:
        """Scrape events page."""
        return self.scrape_url(events_url)
    
    def scrape_projects(self, projects_url: str) -> Optional[str]:
        """Scrape projects page."""
        return self.scrape_url(projects_url)
    
    def extract_summary(self, text: str, max_chars: int = 1500) -> str:
        """
        Extract a summary from scraped text.
        
        Args:
            text: Full scraped text
            max_chars: Maximum characters to return
            
        Returns:
            Summarized text
        """
        if not text:
            return ""
        
        # Take first max_chars characters
        summary = text[:max_chars]
        
        # Try to end at a sentence
        last_period = summary.rfind('.')
        if last_period > max_chars * 0.7:  # If we have at least 70% of content
            summary = summary[:last_period + 1]
        
        return summary.strip()
