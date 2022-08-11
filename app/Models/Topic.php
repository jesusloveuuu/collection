<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Topic extends Model
{


    protected $dates = ['created_at', 'updated_at'];

    public function createTopic($array_attribute){
        foreach ($array_attribute as $key=>$value){
            $this->$key = $value;
        }
        $this->save();

        return $this;
    }

}
