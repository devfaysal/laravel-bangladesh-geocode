<?php

namespace Devfaysal\BangladeshGeocode\Commands;

use Devfaysal\BangladeshGeocode\Models\Union;
use Devfaysal\BangladeshGeocode\Models\Upazila;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddNewRecordsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BangladeshGeocode:addnew';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Updating Laravel Bangladesh Geocode..');
        $this->info('Adding new Upazila..');
        $newUpazilas = [
            ['id' => '492', 'district_id' => '9', 'name' => 'Eidgaon', 'bn_name' => 'ঈদগাঁও', 'url' => 'eidgaon.coxsbazar.gov.bd'],
            ['id' => '493', 'district_id' => '39', 'name' => 'Madhyanagar', 'bn_name' => 'মধ্যনগর', 'url' => 'madhyanagar.sunamganj.gov.bd'],
            ['id' => '494', 'district_id' => '50', 'name' => 'Dasar', 'bn_name' => 'ডাসার', 'url' => 'dasar.madaripur.gov.bd'],
            ['id' => '495', 'district_id' => '38', 'name' => 'Shayestaganj', 'bn_name' => 'শায়েস্তাগঞ্জ', 'url' => 'shayestaganj.habiganj.gov.bd'],
        ];
        foreach ($newUpazilas as $upazila) {
            Upazila::updateOrCreate(
                ['id' => $upazila['id']],
                [
                    'district_id' => $upazila['district_id'],
                    'name' => $upazila['name'],
                    'bn_name' => $upazila['bn_name'],
                    'url' => $upazila['url'],
                ]
            );
        }
        $this->info('New Upazila added successsfully');

        $this->info('Updating exising Unions parent Upazila..');
            $unions = [
                ['id' => '815', 'upazila_id' => '492', 'name' => 'Islamabad', 'bn_name' => 'ইসলামাবাদ', 'url' => 'islamabadup.coxsbazar.gov.bd'],
                ['id' => '816', 'upazila_id' => '492', 'name' => 'Islampur', 'bn_name' => 'ইসলামপুর', 'url' => 'islampurup.coxsbazar.gov.bd'],
                ['id' => '817', 'upazila_id' => '492', 'name' => 'Pokkhali', 'bn_name' => 'পোকখালী', 'url' => 'pokkhaliup.coxsbazar.gov.bd'],
                ['id' => '818', 'upazila_id' => '492', 'name' => 'Eidgaon', 'bn_name' => 'ঈদগাঁও', 'url' => 'eidgaonup.coxsbazar.gov.bd'],
                ['id' => '819', 'upazila_id' => '492', 'name' => 'Jalalabad', 'bn_name' => 'জালালাবাদ', 'url' => 'jalalabadup.coxsbazar.gov.bd'],

                ['id' => '2744', 'upazila_id' => '493', 'name' => 'Bongshikunda North', 'bn_name' => 'বংশীকুন্ডা উত্তর', 'url' => 'bongshikundanorthup.sunamganj.gov.bd'],
                ['id' => '2745', 'upazila_id' => '493', 'name' => 'Bongshikunda South', 'bn_name' => 'বংশীকুন্ডা দক্ষিণ', 'url' => 'bongshikundasouthup.sunamganj.gov.bd'],
                ['id' => '2746', 'upazila_id' => '493', 'name' => 'Chamordani', 'bn_name' => 'চামরদানী', 'url' => 'chamordaniup.sunamganj.gov.bd'],
                ['id' => '2747', 'upazila_id' => '493', 'name' => 'Madhyanagar', 'bn_name' => 'মধ্যনগর', 'url' => 'madhyanagarup.sunamganj.gov.bd'],

                ['id' => '3478', 'upazila_id' => '494', 'name' => 'Baligram', 'bn_name' => 'বালীগ্রাম', 'url' => 'baligramup.madaripur.gov.bd'],
                ['id' => '3481', 'upazila_id' => '494', 'name' => 'Dashar', 'bn_name' => 'ডাসার', 'url' => 'dasharup.madaripur.gov.bd'],
                ['id' => '3483', 'upazila_id' => '494', 'name' => 'Gopalpur', 'bn_name' => 'গোপালপুর', 'url' => 'gopalpurup.madaripur.gov.bd'],
                ['id' => '3485', 'upazila_id' => '494', 'name' => 'Kazibakai', 'bn_name' => 'কাজীবাকাই', 'url' => 'kazibakaiup.madaripur.gov.bd'],
                ['id' => '3487', 'upazila_id' => '494', 'name' => 'Nabogram', 'bn_name' => 'নবগ্রাম', 'url' => 'nabogramup.madaripur.gov.bd'],

                ['id' => '2670', 'upazila_id' => '495', 'name' => 'Nurpur', 'bn_name' => 'নুরপুর', 'url' => 'nurpurup.habiganj.gov.bd'],
                ['id' => '2671', 'upazila_id' => '495', 'name' => 'Shayestaganj', 'bn_name' => 'শায়েস্তাগঞ্জ', 'url' => 'shayestaganjup.habiganj.gov.bd'],
                ['id' => '4541', 'upazila_id' => '495', 'name' => 'Brahmandura', 'bn_name' => 'ব্রাহ্মণডুরা', 'url' => 'brahmanduraup.habiganj.gov.bd'],

                ['id' => '911', 'upazila_id' => '96', 'name' => 'Hafchari', 'bn_name' => 'হাফছড়ি', 'url' => 'hafchariup.khagrachhari.gov.bd'],
                ['id' => '918', 'upazila_id' => '96', 'name' => 'Guimara', 'bn_name' => 'গুইমারা', 'url' => 'guimaraup.khagrachhari.gov.bd'],
                ['id' => '4542', 'upazila_id' => '96', 'name' => 'Sindukchari', 'bn_name' => 'সিন্দুকছড়ি', 'url' => 'sindukchariup.khagrachhari.gov.bd'],

                ['id' => '4543', 'upazila_id' => '112', 'name' => 'Koyra', 'bn_name' => 'কয়রা', 'url' => 'koyraup.sirajganj.gov.bd'],

                ['id' => '1285', 'upazila_id' => '149', 'name' => 'Brahmapur', 'bn_name' => 'ব্রহ্মপুর', 'url' => 'brahmapurup.natore.gov.bd'],
                ['id' => '1286', 'upazila_id' => '149', 'name' => 'Madhnagar', 'bn_name' => 'মাধনগর', 'url' => 'madhnagarup.natore.gov.bd'],
                ['id' => '1287', 'upazila_id' => '149', 'name' => 'Khajura', 'bn_name' => 'খাজুরা', 'url' => 'khajuraup.natore.gov.bd'],
                ['id' => '1288', 'upazila_id' => '149', 'name' => 'Piprul', 'bn_name' => 'পিপরুল', 'url' => 'piprulup.natore.gov.bd'],
                ['id' => '1289', 'upazila_id' => '149', 'name' => 'Biprobelghoria', 'bn_name' => 'বিপ্রবেলঘড়িয়া', 'url' => 'biprobelghoriaup.natore.gov.bd'],

                ['id' => '4544', 'upazila_id' => '46', 'name' => 'Horoni', 'bn_name' => 'হরণী', 'url' => 'horoniup.noakhali.gov.bd'],
                ['id' => '4545', 'upazila_id' => '46', 'name' => 'Chanondi', 'bn_name' => 'চানন্দী', 'url' => 'chanondiup.noakhali.gov.bd'],
            ];
        foreach($unions as $union){
            Union::updateOrCreate(
                ['id' => $union['id']],
                [
                    'upazila_id' => $union['upazila_id'],
                    'name' => $union['name'],
                    'bn_name' => $union['bn_name'],
                    'url' => $union['url'],
                ]
            );
        }
        $this->info('Exising Unions parent Upazila updated successsfully');

    }
}
