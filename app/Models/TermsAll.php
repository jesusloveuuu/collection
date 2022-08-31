<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TermsAll extends Model
{
    protected $table = 'terms_all';

    protected $dates = ['created_at', 'updated_at'];

}
