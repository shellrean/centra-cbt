<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    protected $fillable = [
    	'server_name','kosong','benar','salah','hasil','jadwal_id','peserta_id'
    ];

    public function peserta() 
    {
        return $this->hasOne('App\Peserta', 'id', 'peserta_id');
    }
}
