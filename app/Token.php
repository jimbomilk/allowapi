<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{

    /**
     * Generate a token
     *
     */


    public static function generate($photoId,$owner,$personname,$personphone,$rhname, $rhphone)
    {
        return hash_hmac('sha256', $owner.$photoId.$personname.$personphone.$rhname.$rhphone, config('app.key'));

    }


}
