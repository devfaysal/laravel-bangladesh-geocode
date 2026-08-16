import re

districts_path = '/var/www/pkg/laravel-bangladesh-geocode/src/Seeders/DistrictSeeder.php'
with open(districts_path, 'r', encoding='utf-8') as f:
    content = f.read()

line = "['id' => '1', 'division_id' => '1', 'name' => 'Comilla', 'bn_name' => 'কুমিল্লা', 'lat' => '23.4682747', 'lon' => '91.1788135', 'url' => 'www.comilla.gov.bd'],"

# Regex 1
pattern1 = re.compile(r"\[\s*'id'\s*=>\s*'(\d+)',\s*'division_id'\s*=>\s*'(\d+)',\s*'name'\s*=>\s*'([^']*)',\s*'bn_name'\s*=>\s*'([^']*)'")
match1 = pattern1.search(line)
print(f"Match 1: {match1 is not None}")

# Let's try matching globally
matches = pattern1.findall(content)
print(f"Global matches with pattern1: {len(matches)}")

# Let's try a simpler regex
pattern2 = re.compile(r"'id'\s*=>\s*'(\d+)',\s*'division_id'\s*=>\s*'(\d+)',\s*'name'\s*=>\s*'([^']*)',\s*'bn_name'\s*=>\s*'([^']*)'")
matches2 = pattern2.findall(content)
print(f"Global matches with pattern2: {len(matches2)}")
if matches2:
    print(f"First match: {matches2[0]}")
