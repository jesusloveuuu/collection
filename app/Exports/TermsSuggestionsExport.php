<?php

namespace App\Exports;

use App\Models\TermsSuggestion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TermsSuggestionsExport implements FromCollection,WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return TermsSuggestion::all();
    }

    public function headings(): array
    {
        return [
            'id',
            'term',
            'json_suggestion',
            'data_source',
            'created_at',
            'updated_at',
            'term_id',
        ];
    }
}
