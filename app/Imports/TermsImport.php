<?php

namespace App\Imports;

use App\Models\Term;
use Maatwebsite\Excel\Concerns\ToModel;

class TermsImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Term([
            //
        ]);
    }
}
