
📌 2) moderation/ → GÜVENLİK KATMANI

Bu klasörün görevi:

Dosya	Görev
message_validator.py	Boş mesaj, çok uzun mesaj gibi kontroller
profanity_filter.py	Küfür tespiti
safety_rules.py	Etik kurallar (Self-harm, illegal advice engeli)
spam_filter.py	Flood / tekrar eden mesaj analizi
toxicity_detector.py	Toxic + hakaret + nefret söylemi tespiti
moderation_chain.py	Bunların HEPSİNİ sıraya koyup tek noktadan çağırma

ChatHandler bunları tek tek çağırmak yerine moderation_chain.check() der.
