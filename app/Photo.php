<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = ['data'];

    public function rightholders()
    {
        return $this->hasMany('App\RightholderPhoto');
    }
}
