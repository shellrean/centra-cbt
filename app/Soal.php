<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
	protected $guarded = [];
    protected $hidden = [
		'analys','diagram'
	];

	protected $appends = [
		'diagram'
	];

	protected $casts = [
		'analys'	=> 'array'
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
}
