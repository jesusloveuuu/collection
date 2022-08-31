<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Term extends Model
{

    protected $primaryKey = 'term';
    public $incrementing = false;

    protected $dates = ['created_at', 'updated_at'];

    const TYPE_TERM = 0;
    const TYPE_TOPIC = 1;
    const TYPE_QUERY = 2;

}
