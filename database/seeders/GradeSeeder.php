<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grade;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        Grade::insert([
            ['name' => '1st Grade', 'is_active' => true],
            ['name' => '2nd Grade', 'is_active' => true],
            ['name' => '3rd Grade', 'is_active' => true],
            ['name' => '4th Grade', 'is_active' => true],
            ['name' => '5th Grade', 'is_active' => true],
            ['name' => '6th Grade', 'is_active' => true],
            ['name' => '7th Grade', 'is_active' => true],
            ['name' => '8th Grade', 'is_active' => true],

        ]);
    }
}
