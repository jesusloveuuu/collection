<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ImportKeyword extends Model
{


    //protected $dates = ['created_at', 'updated_at'];

    public function addAll(Array $data)
    {
        return DB::table($this->getTable())->insert($data);
    }

}
