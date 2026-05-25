<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ResidentExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $residents;

    public function __construct($residents)
    {
        $this->residents = $residents;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->residents;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Last Name',
            'First Name',
            'Purok',
            'Date of Birth',
            'Gender',
            'Civil Status',
            'Occupation',
            'Voter Status',
            'Indigent Status',
            'PWD Status',
            'Senior Citizen Status',
            'Status'
        ];
    }

    /**
     * @param mixed $resident
     */
    public function map($resident): array
    {
        return [
            $resident->id,
            $resident->last_name,
            $resident->first_name,
            $resident->purok ? $resident->purok->purok_name : 'N/A',
            $resident->date_of_birth,
            ucfirst($resident->gender),
            ucfirst($resident->civil_status),
            $resident->occupation ?? 'N/A',
            $resident->is_voter ? 'Voter' : 'Non-Voter',
            $resident->is_indigent ? 'Indigent' : 'No',
            $resident->is_pwd ? 'PWD' : 'No',
            $resident->is_senior_citizen ? 'Senior' : 'No',
            $resident->is_active ? 'Active' : 'Inactive'
        ];
    }
}
