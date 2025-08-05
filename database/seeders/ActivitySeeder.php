<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        Activity::insert([
            ['name' => 'Dance', 'is_active' => true],
            ['name' => 'Music', 'is_active' => true],
            ['name' => 'Sports', 'is_active' => true],
            ['name' => 'Yoga', 'is_active' => true],
            ['name' => 'Art', 'is_active' => true],
            ['name' => 'Crafts', 'is_active' => true],
            ['name' => 'Drama', 'is_active' => true],
            ['name' => 'Coding', 'is_active' => true],
            ['name' => 'Cooking', 'is_active' => true],
            ['name' => 'Gardening', 'is_active' => true],
            ['name' => 'Photography', 'is_active' => true],
            ['name' => 'Writing', 'is_active' => true],

        ]);
    }
}
