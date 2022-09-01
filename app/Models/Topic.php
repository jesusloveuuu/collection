<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Topic extends Model
{
    protected $primaryKey = 'mid';

    public $incrementing = false;

    protected $dates = ['created_at', 'updated_at'];


}
