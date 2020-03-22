<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultEsay extends Model
{
	protected $table = 'jawaban_esays';
	
    protected $guarded = [];
    
    public function pertanyaan()
    {
        return $this->hasOne('App\Soal','id','soal_id');
    }
}
