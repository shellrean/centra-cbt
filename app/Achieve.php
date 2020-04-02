<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Achieve extends Model
{
    protected $guarded = [];

    protected $casts = [
    	'achieve'	=> 'json'
    ];
}
