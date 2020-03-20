<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $guarded = [];

    protected $hidden = [
    	'password', 'create_at', 'updated_at'
    ];
}
