<?php

namespace App;

use DB;

use Illuminate\Database\Eloquent\Model;

class Matpel extends Model
{
    protected $guarded = [];

    protected $appends = [ 'jurusans', 'correctors_name', 'agama' ];

    protected $hidden = [ 'created_at', 'updated_at' ];

    protected $casts = [
    	'jurusan_id'	=> 'array',
    	'correctors'	=> 'array'
    ];

    public function getJurusansAttribute() 
    {
        if($this->jurusan_id != 0) {
        	$jurusans = DB::table('jurusans')->whereIn('id', $this->jurusan_id)->get();
        	return $jurusans;
        }
        return 0;
    }

    public function getCorrectorsNameAttribute()
    {
        if($this->correctors != '') {
        	$correctors = User::whereIn('id', $this->correctors)->select('id','name')->get();
        	return $correctors;
        }
        return 0;
    }

    public function getAgamaAttribute()
    {
        if($this->agama_id != 0) {
            $agama = DB::table('agamas')->where('id', $this->agama_id)->first();
            return $agama->nama;
        }
        return 0;
    }
}
