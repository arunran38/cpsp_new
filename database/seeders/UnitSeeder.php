<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run()
    {
        DB::table('units')->insert([
            ['unit_code' => 'DVACB', 'unit_name' => 'Vigilance Directorate'],
            ['unit_code' => 'SRT', 'unit_name' => 'Southern Range, Trivandrum'],
            ['unit_code' => 'CRE', 'unit_name' => 'Central Range, Ernakulam'],
            ['unit_code' => 'ERK', 'unit_name' => 'Eastern Range, Kottayam'],
            ['unit_code' => 'NRK', 'unit_name' => 'Northern Range, Kozhikkode'],
            ['unit_code' => 'SIU-I', 'unit_name' => 'Special Investigation Unit - I'],
            ['unit_code' => 'SIU-II', 'unit_name' => 'Special Investigation Unit - II'],
            ['unit_code' => 'SCT', 'unit_name' => 'Special Cell, Trivandrum'],
            ['unit_code' => 'SCE', 'unit_name' => 'Special Cell, Ernakulam'],
            ['unit_code' => 'SCK', 'unit_name' => 'Special Cell, Kozhikkode'],
            ['unit_code' => 'TVM', 'unit_name' => 'Thiruvananthapuram Unit'],
            ['unit_code' => 'KLM', 'unit_name' => 'Kollam Unit'],
            ['unit_code' => 'PTA', 'unit_name' => 'Pathanamthitta Unit'],
            ['unit_code' => 'EKM', 'unit_name' => 'Ernakulam Unit'],
            ['unit_code' => 'TSR', 'unit_name' => 'Thrissur Unit'],
            ['unit_code' => 'PKD', 'unit_name' => 'Palakkad Unit'],
            ['unit_code' => 'ALP', 'unit_name' => 'Alappuzha Unit'],
            ['unit_code' => 'KTM', 'unit_name' => 'Kottayam Unit'],
            ['unit_code' => 'IDK', 'unit_name' => 'Idukki Unit'],
            ['unit_code' => 'KKD', 'unit_name' => 'Kozhikkode Unit'],
            ['unit_code' => 'MPM', 'unit_name' => 'Malappuram Unit'],
            ['unit_code' => 'WYD', 'unit_name' => 'Wayanad Unit'],
            ['unit_code' => 'KNR', 'unit_name' => 'Kannur Unit'],
            ['unit_code' => 'KSD', 'unit_name' => 'Kasargode Unit'],
        ]);
    }
}
