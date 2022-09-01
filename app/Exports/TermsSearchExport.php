<?php

namespace App\Exports;

use App\Models\Term;
use Maatwebsite\Excel\Concerns\FromCollection;

class TermsSearchExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Term::all();
    }
}
