# test_agents.py
"""
Test script for Web Agent system.
Shows which agents are running and what data they return.
"""

print("=" * 60)
print("🧪 WEB AGENT TEST SCRIPT")
print("=" * 60)

# 1. Test imports
print("\n📦 1. IMPORT TESTİ:")
try:
    from agents.web_agent import WebAgent
    print("   ✅ WebAgent import OK")
except Exception as e:
    print(f"   ❌ WebAgent import HATA: {e}")

try:
    from agents.scrapers.event_scraper import EventScraper
    print("   ✅ EventScraper import OK")
except Exception as e:
    print(f"   ❌ EventScraper import HATA: {e}")

try:
    from agents.scrapers.project_scraper import ProjectScraper
    print("   ✅ ProjectScraper import OK")
except Exception as e:
    print(f"   ❌ ProjectScraper import HATA: {e}")

try:
    from llm.prompt_builder import PromptBuilder, PromptType
    print("   ✅ PromptBuilder import OK")
except Exception as e:
    print(f"   ❌ PromptBuilder import HATA: {e}")

try:
    from router.intent_router import IntentRouter
    print("   ✅ IntentRouter import OK")
except Exception as e:
    print(f"   ❌ IntentRouter import HATA: {e}")

# 2. Test Intent Router
print("\n🎯 2. INTENT ROUTER TESTİ:")
router = IntentRouter()
test_queries = [
    "Etkinlikler neler?",
    "Projeler hakkında bilgi ver",
    "Kulüp hakkında bilgi ver",
    "Python nedir?",
]
for query in test_queries:
    intent = router.detect_intent(query)
    print(f"   '{query}' → {intent}")

# 3. Test WebAgent initialization
print("\n🤖 3. WEB AGENT BAŞLATMA:")
try:
    agent = WebAgent()
    print(f"   ✅ WebAgent başlatıldı")
    print(f"   📋 Kayıtlı scraperlar: {agent.get_registered_scrapers()}")
except Exception as e:
    print(f"   ❌ WebAgent başlatma HATA: {e}")
    exit(1)

# 4. Test Event Scraper
print("\n📅 4. ETKİNLİK SCRAPER TESTİ:")
try:
    event_scraper = agent.get_scraper("event")
    print(f"   URL: {event_scraper.full_url}")
    
    # Scrape dene
    events = event_scraper.scrape()
    print(f"   ✅ {len(events)} etkinlik bulundu")
    
    # İlk 3 etkinliği göster
    for i, event in enumerate(events[:3]):
        status = "🟢 Aktif" if event.is_active else "🔴 Tamamlandı"
        print(f"   {i+1}. {event.title} - {status}")
    
    if len(events) > 3:
        print(f"   ... ve {len(events) - 3} etkinlik daha")
        
except Exception as e:
    print(f"   ❌ Event scraper HATA: {e}")

# 5. Test Project Scraper
print("\n🚀 5. PROJE SCRAPER TESTİ:")
try:
    project_scraper = agent.get_scraper("project")
    print(f"   URL: {project_scraper.full_url}")
    
    # Scrape dene
    projects = project_scraper.scrape()
    print(f"   ✅ {len(projects)} proje/yayın bulundu")
    
    # Kategorilere ayır
    pubs = [p for p in projects if p.category == "Yayın"]
    projs = [p for p in projects if p.category == "Proje"]
    print(f"   📖 Yayınlar: {len(pubs)}")
    print(f"   🔬 Projeler: {len(projs)}")
    
    # İlk 2 tanesini göster
    for i, proj in enumerate(projects[:2]):
        print(f"   {i+1}. [{proj.category}] {proj.title[:50]}...")
        
except Exception as e:
    print(f"   ❌ Project scraper HATA: {e}")

# 6. Test LLM Query (etkinlik)
print("\n💬 6. LLM SORGU TESTİ (Etkinlik):")
try:
    query = "Aktif etkinlikler neler?"
    print(f"   Sorgu: '{query}'")
    
    query_type = agent.detect_query_type(query)
    print(f"   Tespit edilen tip: {query_type}")
    
    context = agent.get_context(query_type)
    print(f"   Context uzunluğu: {len(context)} karakter")
    
    # LLM'e gönder
    print("   LLM'e gönderiliyor...")
    response = agent.process_query(query)
    print(f"   ✅ Yanıt alındı ({len(response)} karakter)")
    print(f"\n   📝 Yanıt:\n   {response[:500]}...")
    
except Exception as e:
    print(f"   ❌ LLM sorgu HATA: {e}")

# 7. Test LLM Query (proje)
print("\n💬 7. LLM SORGU TESTİ (Proje):")
try:
    query = "Kulübün projeleri neler?"
    print(f"   Sorgu: '{query}'")
    
    query_type = agent.detect_query_type(query)
    print(f"   Tespit edilen tip: {query_type}")
    
    # LLM'e gönder
    print("   LLM'e gönderiliyor...")
    response = agent.process_query(query)
    print(f"   ✅ Yanıt alındı ({len(response)} karakter)")
    print(f"\n   📝 Yanıt:\n   {response[:500]}...")
    
except Exception as e:
    print(f"   ❌ LLM sorgu HATA: {e}")

print("\n" + "=" * 60)
print("✅ TEST TAMAMLANDI")
print("=" * 60)
