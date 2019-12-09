<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photos extends Model
{
    protected $table="photos";

    //OneToMany relation
    public function photosdata(){
        return $this->hasMany("App\PhotosData","photos_id","id");
    }

}
