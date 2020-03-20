<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SiswaUjian extends Model
{
    public function peserta() {
    	return $this->hasOne('App\Peserta','id','peserta_id');
    }
}
