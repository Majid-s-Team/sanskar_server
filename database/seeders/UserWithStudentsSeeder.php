<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Activity;
use Illuminate\Support\Facades\Hash;

class UserWithStudentsSeeder extends Seeder
{
    public function run(): void
    {
        $fatherActivities = Activity::factory()->count(2)->create(['is_active' => 1]);
        $motherActivities = Activity::factory()->count(2)->create(['is_active' => 1]);


        $user = User::create([
            'primary_email'           => 'parent@example.com',
            'secondary_email'         => 'parent2@example.com',
            'mobile_number'           => '1234567890',
            'secondary_mobile_number' => '0987654321',
            'father_name'             => 'John Doe',
            'mother_name'             => 'Jane Doe',
            'father_volunteering'     => true,
            'mother_volunteering'     => false,
            'is_hsnc_member'          => true,
            'address'                 => '123 Maple Street',
            'city'                    => 'New York',
            'state'                   => 'NY',
            'zip_code'                => '10001',
            'is_active'               => true,
            'is_payment_done'         => true,
            'password'                => Hash::make('password'),
        ]);

        $user->fatherActivities()->attach($fatherActivities->pluck('id')->toArray());
        $user->motherActivities()->attach($motherActivities->pluck('id')->toArray());


        Student::create([
            'user_id'               => $user->id,
            'first_name'           => 'Alice',
            'last_name'            => 'Doe',
            'dob'                  => '2010-04-12',
            'student_email'        => 'alice@example.com',
            'student_mobile_number'=> '1122334455',
            'join_the_club'        => true,
            'school_name'          => 'Sunrise School',
            'hobbies_interest'     => 'Drawing, Reading',
            'is_school_year_around'=> false,
            'last_year_class'      => '3rd Grade',
            'any_allergies'        => 'Peanuts',
            'teeshirt_size_id'     => 1, 
            'gurukal_id'           => 1,
            'school_grade_id'      => 1,
        ]);

        Student::create([
            'user_id'               => $user->id,
            'first_name'           => 'Bob',
            'last_name'            => 'Doe',
            'dob'                  => '2012-06-20',
            'student_email'        => 'bob@example.com',
            'student_mobile_number'=> '2233445566',
            'join_the_club'        => false,
            'school_name'          => 'Sunrise School',
            'hobbies_interest'     => 'Music, Chess',
            'is_school_year_around'=> true,
            'last_year_class'      => '2nd Grade',
            'any_allergies'        => 'None',
            'teeshirt_size_id'     => 1,
            'gurukal_id'           => 1,
            'school_grade_id'      => 1,
        ]);
    }
}
