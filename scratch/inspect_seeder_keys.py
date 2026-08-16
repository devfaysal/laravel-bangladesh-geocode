import re

districts_path = '/var/www/pkg/laravel-bangladesh-geocode/src/Seeders/DistrictSeeder.php'
with open(districts_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Let's find all 'bn_name' values
bn_names = re.findall(r"'bn_name' => '([^']*)'", content)
print(f"Parsed {len(bn_names)} bn_names.")
for bn in bn_names[:10]:
    print(f"'{bn}' Chars: {[hex(ord(c)) for c in bn]}")
