<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'site_name',        'value' => 'FocusedTube',    'group' => 'general'],
            ['key' => 'site_description', 'value' => 'A focused, distraction-free video learning platform.', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0',              'group' => 'general'],
            ['key' => 'pagination_size',  'value' => '24',             'group' => 'general'],
        ];

        foreach ($defaults as $row) {
            Setting::updateOrCreate(['key' => $row['key']], $row);
        }
    }
}