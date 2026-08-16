import json

json_path = '/home/faysal/.gemini/antigravity-ide/brain/b0ad5ee3-6852-461d-bf84-3718b32d7464/scratch/post_offices_final.json'
seeder_path = '/var/www/pkg/laravel-bangladesh-geocode/src/Seeders/PostOfficeSeeder.php'

with open(json_path, 'r', encoding='utf-8') as f:
    post_offices = json.load(f)

# Build items string
items_str = []
for i, po in enumerate(post_offices):
    # Escape quotes
    thana = po['thana'].replace("'", "\\'")
    thana_bn = po['thana_bn'].replace("'", "\\'")
    name = po['name'].replace("'", "\\'")
    bn_name = po['bn_name'].replace("'", "\\'")
    
    line = f"            ['id' => {i + 1}, 'district_id' => {po['district_id']}, 'thana' => '{thana}', 'thana_bn' => '{thana_bn}', 'name' => '{name}', 'bn_name' => '{bn_name}', 'post_code' => '{po['post_code']}'],"
    items_str.append(line)

seeder_template = """<?php

namespace Devfaysal\BangladeshGeocode\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostOfficeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $post_offices = [
{items}
        ];

        foreach (array_chunk($post_offices, 200) as $chunk) {
            DB::table('post_offices')->insert($chunk);
        }
    }
}
"""

formatted_content = seeder_template.replace('{items}', '\n'.join(items_str))

with open(seeder_path, 'w', encoding='utf-8') as f:
    f.write(formatted_content)

print(f"PostOfficeSeeder.php generated with {len(post_offices)} records.")
