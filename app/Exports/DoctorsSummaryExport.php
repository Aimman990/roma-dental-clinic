<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DoctorsSummaryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return ['id','name','appointments_count','invoices_count','total_invoiced','total_collected','commission_earned','total_withdrawn','balance'];
    }
}
