<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HasilUjian extends Model
{
	protected $guarded = [];

	protected $hidden = [
		'created_at','updated_at','jawaban_peserta'
	];

	protected $casts = [
        'jawaban_peserta' => 'array'
    ];

    public function peserta() 
    {
    	return $this->hasOne('App\Peserta', 'id', 'peserta_id');
    }
}
