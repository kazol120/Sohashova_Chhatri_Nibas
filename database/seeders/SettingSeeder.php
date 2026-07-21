<?php

namespace Database\Seeders;

use App\Models\Backend\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'title' => 'Web Setting',
            'slug' => 'web_setting',
            'contents' => json_encode([
                'title' => 'CMS',
                'phone' => '01891151713',
                'address' => 'Jahaj Company Mor',
                'email' => 'info@nsmlimited.com',
                'currency' => 'BDT',
            ]),
        ]);
    }
}
