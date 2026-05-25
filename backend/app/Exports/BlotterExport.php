<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BlotterExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $blotters;

    public function __construct($blotters)
    {
        $this->blotters = $blotters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->blotters;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Blotter Number',
            'Incident Type',
            'Incident Date & Time',
            'Incident Location',
            'Complainant(s)',
            'Respondent(s)',
            'Status',
            'Assigned Officer',
            'Settlement Details',
            'Filed By'
        ];
    }

    /**
     * @param mixed $blotter
     */
    public function map($blotter): array
    {
        $complainants = $blotter->parties->where('role', 'complainant')->pluck('full_name')->implode(', ');
        $respondents = $blotter->parties->where('role', 'respondent')->pluck('full_name')->implode(', ');

        return [
            $blotter->id,
            $blotter->blotter_number,
            $blotter->incident_type,
            $blotter->incident_date,
            $blotter->incident_location,
            $complainants ?: 'N/A',
            $respondents ?: 'N/A',
            ucwords(str_replace('_', ' ', $blotter->status)),
            $blotter->officer ? $blotter->officer->name : 'Unassigned',
            $blotter->settlement_details ?? 'N/A',
            $blotter->creator ? $blotter->creator->name : 'N/A'
        ];
    }
}
