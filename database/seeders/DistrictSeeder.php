<?php

namespace Database\Seeders;
use \App\Models\District;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = [
            ['district_name' => 'Thiruvananthapuram', 'district_code' => 'TVM'],
            ['district_name' => 'Kollam',             'district_code' => 'KLM'],
            ['district_name' => 'Pathanamthitta',     'district_code' => 'PTA'],
            ['district_name' => 'Alappuzha',          'district_code' => 'ALP'],
            ['district_name' => 'Kottayam',           'district_code' => 'KTM'],
            ['district_name' => 'Idukki',             'district_code' => 'IDK'],
            ['district_name' => 'Ernakulam',          'district_code' => 'EKM'],
            ['district_name' => 'Thrissur',           'district_code' => 'TSR'],
            ['district_name' => 'Palakkad',           'district_code' => 'PKD'],
            ['district_name' => 'Malappuram',         'district_code' => 'MPM'],
            ['district_name' => 'Kozhikode',          'district_code' => 'KKD'],
            ['district_name' => 'Wayanad',            'district_code' => 'WYD'],
            ['district_name' => 'Kannur',             'district_code' => 'KNR'],
            ['district_name' => 'Kasaragod',          'district_code' => 'KSD'],
            ['district_name' => 'Other State',        'district_code' => 'OTH'],
        ];

        foreach ($districts as $district) {
            District::updateOrCreate(
                ['district_name' => $district['district_name']],
                ['district_code' => $district['district_code']]
            );
        }
    }
}
