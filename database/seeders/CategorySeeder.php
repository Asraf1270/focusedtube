<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Programming', 'icon' => '💻'],
            ['name' => 'Education',   'icon' => '🎓'],
            ['name' => 'Mathematics', 'icon' => '➗'],
            ['name' => 'Science',     'icon' => '🔬'],
            ['name' => 'English',     'icon' => '📖'],
            ['name' => 'Technology',  'icon' => '⚙️'],
            ['name' => 'Design',      'icon' => '🎨'],
        ];

        foreach ($categories as $i => $row) {
            Category::updateOrCreate(
                ['slug' => Str::slug($row['name'])],
                [
                    'name'       => $row['name'],
                    'icon'       => $row['icon'],
                    'status'     => Category::STATUS_ACTIVE,
                    'sort_order' => $i,
                ]
            );
        }
    }
}