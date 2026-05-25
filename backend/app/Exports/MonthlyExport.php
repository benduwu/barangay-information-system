<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MonthlyExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = collect($data);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Report Category',
            'Activity / Stat Indicator',
            'Metric Value / Count'
        ];
    }

    /**
     * @param mixed $row
     */
    public function map($row): array
    {
        return [
            $row['category'],
            $row['indicator'],
            $row['value']
        ];
    }
}
