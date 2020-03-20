<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    protected $guarded = [];

    protected $hidden = [
    	'created_at','updated_at'
    ];

    public function file()
    {
        return $this->hasMany(File::class);
    }
}
