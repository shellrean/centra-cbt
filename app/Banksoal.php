<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Banksoal extends Model
{
    protected $fillable = [
		'kode_banksoal','kelas_id','author','matpel_id','jumlah_soal','jumlah_pilihan','jumlah_soal_esay','directory_id'
	];

    protected $appends = [
        'inputed','koreksi'
    ];

    protected $hidden = [
        'created_at','updated_at','author','koreksi'
    ];

    public function pertanyaans()
    {
    	return $this->hasMany('App\Soal', 'banksoal_id','id');
    }

    public function matpel()
    {
    	return $this->belongsTo('App\Matpel','matpel_id');
    }

    public function user()
    {
    	return $this->belongsTo('App\User','author');
    }

    public function ujian()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function getInputedAttribute()
    {
        $count = Soal::where('banksoal_id', $this->id)->count();
        return $count;
    }

    public function getKoreksiAttribute()
    {
        $exists = ResultEsay::where('banksoal_id', $this->id)
        ->get()
        ->pluck('jawab_id')
        ->unique();

        return JawabanPeserta::whereNotIn('id', $exists)->count();
    }
}
