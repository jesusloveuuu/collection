<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tag extends Model
{
    protected $primaryKey = 'tag';
    public $incrementing = false;

    protected $dates = ['created_at', 'updated_at'];

}
