<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gurukal;

class GurukalSeeder extends Seeder
{
    public function run(): void
    {
        Gurukal::insert([
            ['name' => 'Shiv Gurukul', 'is_active' => true],
            ['name' => 'Krishna Gurukul', 'is_active' => true],
            ['name' => 'Buddha Gurukul', 'is_active' => true],
            ['name' => 'Gandhi Gurukul', 'is_active' => true],
            ['name' => 'Tagore Gurukul', 'is_active' => true],
            ['name' => 'Ambedkar Gurukul', 'is_active' => true],
            ['name' => 'Ramakrishna Gurukul', 'is_active' => true],
            ['name' => 'Vivekananda Gurukul', 'is_active' => true],
        ]);
    }
}
