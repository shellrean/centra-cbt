<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JawabanPeserta extends Model
{
    protected $guarded = [];

    protected $hidden = [
    	'created_at','udpated_at'
    ];

    protected $appends = [
    	'similiar'
    ];

    public function pertanyaan()
    {
    	return $this->belongsTo(Soal::class,'soal_id','id');
    }

    public function getSimiliarAttribute()
    {
    	$text = Soal::find($this->soal_id);
    	if($this->esay != null && $text->tipe_soal == 2) {
            $rujukan = strip_tags($text->rujukan);

            $jawab = strip_tags($this->esay);
    		similar_text($rujukan, $jawab, $percent);

    		return $percent;
    	}
    }
}
