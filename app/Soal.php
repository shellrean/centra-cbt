<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
	protected $guarded = [];

    protected $hidden = [
		'analys','diagram','salah','benar', 'penjawab', 'kosong'
	];

	protected $appends = [
		'diagram','salah','benar', 'penjawab', 'kosong'
	];

	protected $casts = [
		'analys'	=> 'array',
        'created_at' => 'datetime:d/m/Y h:i:s A'
	];

	public function banksoal()
	{
		return $this->belongsTo('App\Banksoal','banksoal_id');
	}
    
    public function jawabans()
    {
    	return $this->hasMany('App\JawabanSoal', 'soal_id','id');
    }

    public function getDiagramAttribute()
    {
    	$array = array();
	    $array[0] = ['Task','Value'];
	    if(!is_array($this->analys)) {
	    	return $array;
	    }
	    foreach($this->analys as $key => $value)
	    {
	    	if($key == 'updated' || $key == 'penjawab') {
	    		continue;
	    	}
	    	$array[] = [
	    		$key, $value
	    	];
	    }
	    return $array;
    }

    public function getSalahAttribute()
    {
    	if($this->tipe_soal == 2) {
    		return 0;
    	}

    	$salah = JawabanPeserta::where([
    		'soal_id' => $this->id,
    		'iscorrect' => 0
    	])->count();

    	return $salah;
    }

    public function getBenarAttribute()
    {
    	$benar = JawabanPeserta::where([
    		'soal_id'	=> $this->id,
    		'iscorrect'	=> 1
    	])->count();

    	return $benar;
    }

    public function getPenjawabAttribute()
    {
    	$penjawab = JawabanPeserta::where([
    		'soal_id'	=> $this->id
    	])->count();

    	return $penjawab;
    }

    public function getKosongAttribute()
    {
    	if($this->tipe_soal == 2) {
    		return 0;
    	}

    	$kosong = JawabanPeserta::where([
    		'soal_id'		=> $this->id,
    		'jawab'			=> 0
    	])->count();

    	return $kosong;
    }
}
