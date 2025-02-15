<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'politics',
            'tech',
            'business',
            'science',
            'health',
            'sport',
            'entertainment',
            'world',
            'finance',
            'education',
            'others'
        ];

        $now = now();

        $categoryRecords = array_map(function ($category) use ($now) {
            return [
                'name' => $category,
                'is_default' => $category === 'others',
                'created_at' => $now,
                'updated_at' => $now
            ];
        }, $categories);

        DB::table('categories')->insert($categoryRecords);
    }
}
