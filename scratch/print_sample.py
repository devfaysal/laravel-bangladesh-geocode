import json

post_offices_json = '/home/faysal/.gemini/antigravity-ide/brain/b0ad5ee3-6852-461d-bf84-3718b32d7464/scratch/post_offices_final.json'

with open(post_offices_json, 'r', encoding='utf-8') as f:
    data = json.load(f)

print(f"Total post offices: {len(data)}")
for i, item in enumerate(data[:5]):
    print(f"Item {i}: {item}")
