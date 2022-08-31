<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TermsTagsPivot extends Model
{

    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = ['term','tag'];

}
