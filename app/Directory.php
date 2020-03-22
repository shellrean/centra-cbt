<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    protected $guarded = [];

    protected $hidden = [
    	'created_at','updated_at'
    ];

    protected $appends = [
    	'size'
    ];

    public function file()
    {
        return $this->hasMany(File::class);
    }

    public function getSizeAttribute()
    {
    	return File::where('directory_id', $this->id)->get()->sum('size');
    }
}
