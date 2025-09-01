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
            'Primary Email',
            'Primary Mobile Number',
            'Father Full Name',
            'Mother Full Name',
            'Student Name',
            'Student Email',
            'Student Mobile Number',
            'Address',
            'Total Payment',
            'Currency',
        ];
    }
}
