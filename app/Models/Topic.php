<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Topic extends Model
{


    protected $dates = ['created_at', 'updated_at'];


    public function createTopic($array_attribute){
        $array_fields = ['mid','title','type'];
        foreach ($array_attribute as $key=>$value){
            if(in_array($key,$array_fields)){
                $this->$key = $value;
            }
        }
        $this->save();

        return $this;
    }

}
