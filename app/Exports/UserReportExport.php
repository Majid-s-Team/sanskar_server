<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserReportExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'S.No',
            'Father Name',
            'Mother Name',
            'Primary Email',
            'Secondary Email',
            'Mobile Number',
            'Secondary Mobile Number',
            'Address',
            'City',
            'State',
            'Zip Code',
            'Is HSNC Member',
            'Father Activities',
            'Mother Activities',
            'Student Image',
            'Student Name',
            'School Name',
            'Date Of Birth',
            'Student Email',
            'Student Number',
            'Hobbies/Interests',
            'Any Allergies',
            'Join The Club',
            'Teeshirt Size',
            'Gurukal',
            'School Grade',
        ];
    }
}
