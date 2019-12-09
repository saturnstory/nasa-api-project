<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhotosData extends Model
{
    protected $table = "photos_data";

    public function photos(){
        return $this->belongsTo("Photos","id","photos_id");
    }

}
