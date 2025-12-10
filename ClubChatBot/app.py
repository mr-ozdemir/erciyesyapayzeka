# app.py
# small helpers used by project (not the chainlit entry)
from urllib.parse import urlparse, urljoin
import requests
from bs4 import BeautifulSoup

def is_same_domain(url: str, base: str) -> bool:
    try:
        return urlparse(url).netloc.endswith(urlparse(base).netloc)
    except Exception:
        return False

def fetch_page_text_simple(url: str, max_items: int = 80) -> str:
    try:
        r = requests.get(url, timeout=8, headers={"User-Agent":"Mozilla/5.0"})
        r.raise_for_status()
        soup = BeautifulSoup(r.text, "html.parser")
        for tag in soup(["script","style","nav","header","footer","form","noscript"]):
            tag.decompose()
        texts = []
        for tag in soup.find_all(['h1','h2','h3','p','li']):
            t = tag.get_text(" ", strip=True)
            if t and len(t) > 20:
                texts.append(t)
            if len(texts) >= max_items:
                break
        return "\n".join(texts)
    except Exception:
        return ""
