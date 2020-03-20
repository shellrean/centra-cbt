<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Matpel extends Model
{
    protected $guarded = [];

    protected $hidden = [ 'created_at', 'updated_at' ];

    protected $casts = [
    	'jurusan_id'	=> 'array'
    ];
}
