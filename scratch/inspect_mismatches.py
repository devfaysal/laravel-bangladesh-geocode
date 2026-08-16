from bs4 import BeautifulSoup
import re

wiki_path = '/home/faysal/.gemini/antigravity-ide/brain/b0ad5ee3-6852-461d-bf84-3718b32d7464/.system_generated/steps/654/content.md'

with open(wiki_path, 'r', encoding='utf-8') as f:
    html = f.read()

soup = BeautifulSoup(html, 'html.parser')
headings = soup.find_all(['h3', 'h4'])

mismatches = ['মুন্সীগঞ্জ', 'নারায়ণগঞ্জ', 'রাজবাড়ী', 'ময়মনসিংহ', 'খাগড়াছড়ি', 'চুয়াডাঙ্গা', 'কুষ্টিয়া', 'পটুয়াখালী', 'বগুড়া', 'জয়পুরহাট', 'কুড়িগ্রাম']

for heading in headings:
    text = heading.get_text(strip=True).replace('[সম্পাদনা]', '').strip()
    if 'জেলা' not in text:
        continue
    name = text.replace('জেলা', '').replace(' ', '').strip()
    if any(m in name for m in mismatches):
        print(f"Wiki: '{name}' Chars: {[hex(ord(c)) for c in name]}")

print("\nReference mapping keys:")
districts_path = '/var/www/pkg/laravel-bangladesh-geocode/src/Seeders/DistrictSeeder.php'
with open(districts_path, 'r', encoding='utf-8') as f:
    dist_content = f.read()
dist_matches = re.findall(r"\[\s*'id'\s*=>\s*'(\d+)',\s*'division_id'\s*=>\s*'(\d+)',\s*'name'\s*=>\s*'([^']*)',\s*'bn_name'\s*=>\s*'([^']*)'", dist_content)

for dist_id, div_id, name, bn_name in dist_matches:
    bn_clean = bn_name.replace(' ', '').strip()
    if any(m in bn_clean for m in mismatches):
        print(f"Seeder: '{bn_clean}' Chars: {[hex(ord(c)) for c in bn_clean]}")
