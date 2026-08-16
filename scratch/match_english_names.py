import json
import re

# Files
upazilas_path = '/var/www/pkg/laravel-bangladesh-geocode/src/Seeders/UpazilaSeeder.php'
unions_path = '/var/www/pkg/laravel-bangladesh-geocode/src/Seeders/UnionSeeder.php'
post_offices_json = '/home/faysal/.gemini/antigravity-ide/brain/b0ad5ee3-6852-461d-bf84-3718b32d7464/scratch/post_offices.json'

# Parse Upazilas
with open(upazilas_path, 'r', encoding='utf-8') as f:
    up_content = f.read()
up_matches = re.findall(r"\[\s*'id'\s*=>\s*'(\d+)',\s*'district_id'\s*=>\s*'(\d+)',\s*'name'\s*=>\s*'([^']*)',\s*'bn_name'\s*=>\s*'([^']*)'", up_content)

# Parse Unions
with open(unions_path, 'r', encoding='utf-8') as f:
    union_content = f.read()
union_matches = re.findall(r"\[\s*'id'\s*=>\s*'(\d+)',\s*'upazila_id'\s*=>\s*'(\d+)',\s*'name'\s*=>\s*'([^']*)',\s*'bn_name'\s*=>\s*'([^']*)'", union_content)

def norm_bn(s):
    # Normalize unicode nuktas
    s = s.replace('\u09af\u09bc', '\u09df') # য়
    s = s.replace('\u09a1\u09bc', '\u09dc') # ড়
    s = s.replace('\u09a2\u09bc', '\u09dd') # ঢ়
    s = s.replace(' ', '').replace('ী', 'ি').replace('ূ', 'ু').strip()
    return s

# Create mapping dictionary of Bengali name -> English name
bn_to_en = {}
for up_id, dist_id, name, bn_name in up_matches:
    bn_to_en[norm_bn(bn_name)] = name
for u_id, up_id, name, bn_name in union_matches:
    bn_to_en[norm_bn(bn_name)] = name

# Fallback phonetic transliterator for Bengali to English
translit_rules = [
    # Conjuncts
    ('ক্ষ', 'Ksh'), ('জ্ঞ', 'Gya'), ('ঞ্চ', 'Nch'), ('ঞ্জ', 'Nj'), ('ন্ধ', 'Ndh'), ('ন্দ', 'Nd'), ('ন্ত', 'Nt'), ('ম্প', 'Mp'),
    # Vowels
    ('আ', 'A'), ('অ', 'O'), ('ই', 'I'), ('ঈ', 'I'), ('উ', 'U'), ('ঊ', 'U'), ('ঋ', 'Ri'), ('এ', 'E'), ('ঐ', 'Oi'), ('ও', 'O'), ('ঔ', 'Ou'),
    ('া', 'a'), ('ি', 'i'), ('ী', 'i'), ('ু', 'u'), ('ূ', 'u'), ('ৃ', 'ri'), ('ে', 'e'), ('ৈ', 'oi'), ('ো', 'o'), ('ৌ', 'ou'),
    # Consonants
    ('ক', 'k'), ('খ', 'kh'), ('গ', 'g'), ('ঘ', 'gh'), ('ঙ', 'ng'),
    ('চ', 'ch'), ('ছ', 'chh'), ('জ', 'j'), ('ঝ', 'jh'), ('ঞ', 'n'),
    ('ট', 't'), ('ঠ', 'th'), ('ড', 'd'), ('ঢ', 'dh'), ('ণ', 'n'),
    ('ত', 't'), ('থ', 'th'), ('দ', 'd'), ('ধ', 'dh'), ('ন', 'n'),
    ('প', 'p'), ('ফ', 'f'), ('ব', 'b'), ('ভ', 'bh'), ('ম', 'm'),
    ('য', 'j'), ('র', 'r'), ('ল', 'l'), ('শ', 'sh'), ('ষ', 'sh'), ('স', 's'), ('হ', 'h'),
    ('ড়', 'r'), ('ঢ়', 'rh'), ('য়', 'y'), ('ৎ', 't'), ('ং', 'ng'), ('ঃ', 'h'), ('ঁ', 'n'),
    ('ৗ', 'o'), ('্য', 'y'), ('্র', 'r'), ('্ল', 'l'), ('্ব', 'w'), ('্ম', 'm'),
    ('্', '') # Hasanta
]

def transliterate(bn_text):
    text = norm_bn(bn_text)
    res = ""
    i = 0
    while i < len(text):
        matched = False
        for rule_bn, rule_en in translit_rules:
            if text[i:].startswith(rule_bn):
                res += rule_en
                i += len(rule_bn)
                matched = True
                break
        if not matched:
            res += text[i]
            i += 1
    return ' '.join(w.capitalize() for w in res.split())

# Test transliteration
print(f"Transliterate 'ডেমরা': {transliterate('ডেমরা')}")
print(f"Transliterate 'যাত্রাবাড়ী': {transliterate('যাত্রাবাড়ী')}")

# Load parsed post offices
with open(post_offices_json, 'r', encoding='utf-8') as f:
    post_offices = json.load(f)

enhanced_post_offices = []
for po in post_offices:
    thana_bn = po['thana']
    po_bn = po['name']
    
    # 1. Thana English Name
    norm_thana = norm_bn(thana_bn)
    thana_en = bn_to_en.get(norm_thana)
    if not thana_en:
        thana_en = transliterate(thana_bn)
        
    # 2. Post Office English Name
    norm_po = norm_bn(po_bn)
    po_en = bn_to_en.get(norm_po)
    if not po_en:
        po_en = transliterate(po_bn)
        
    enhanced_post_offices.append({
        'district_id': po['district_id'],
        'thana': thana_en,
        'thana_bn': thana_bn,
        'name': po_en,
        'bn_name': po_bn,
        'post_code': po['post_code']
    })

# Save the final JSON list
with open('/home/faysal/.gemini/antigravity-ide/brain/b0ad5ee3-6852-461d-bf84-3718b32d7464/scratch/post_offices_final.json', 'w', encoding='utf-8') as jf:
    json.dump(enhanced_post_offices, jf, ensure_ascii=False, indent=4)
