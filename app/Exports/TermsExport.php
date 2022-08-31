<?php

namespace App\Exports;

use App\Models\Term;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TermsExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Term::all();
    }

    public function headings(): array
    {
        return [
            'term',
            'title',
            'type',
            'classification',
            'description',
            'created_at',
            'updated_at',
            'id',
        ];
    }
}
