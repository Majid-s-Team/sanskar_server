<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TeeshirtSize;

class TeeshirtSizeSeeder extends Seeder
{
    public function run(): void
    {
        TeeshirtSize::insert([
            ['name' => 'XS', 'is_active' => true],
            ['name' => 'S', 'is_active' => true],
            ['name' => 'M', 'is_active' => true],
            ['name' => 'L', 'is_active' => true],
            ['name' => 'XL', 'is_active' => true],
            ['name' => 'XXL', 'is_active' => true],
            ['name' => 'XXXL', 'is_active' => true],
            ['name' => 'Custom Size', 'is_active' => true],
            ['name' => 'Kids Size', 'is_active' => true],
            
        ]);

    }
}
