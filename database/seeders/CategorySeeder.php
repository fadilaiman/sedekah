<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'value' => 'mosque',
                'label' => 'Masjid',
                'icon' => 'mosque',
                'color' => 'green',
                'order' => 1,
                'active' => true,
            ],
            [
                'value' => 'surau',
                'label' => 'Surau',
                'icon' => 'home',
                'color' => 'blue',
                'order' => 2,
                'active' => true,
            ],
            [
                'value' => 'other',
                'label' => 'Lain-lain',
                'icon' => 'account_balance',
                'color' => 'gray',
                'order' => 3,
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['value' => $category['value']],
                $category
            );
        }
    }
}
