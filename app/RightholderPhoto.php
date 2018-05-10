<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RightholderPhoto extends Model
{
    protected $fillable = ['owner','name','phone','rhphone','rhname','link','sharing'];

    public function photo()
    {
        return $this->belongsTo('App\Photo','photo_id','id');
    }
}
