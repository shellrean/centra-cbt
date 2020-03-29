<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

use App\Jadwal;
use App\HasilUjian;
use App\ResultEsay;
use App\JawabanPeserta;

class HigherController extends Controller
{
	protected $permissions = [];

    /**
     * Construction
     */
    public function __construct( )
    {
        $user = request()->user('api'); 
        $permissions = [];
        foreach (Permission::all() as $permission) {
            if (request()->user('api')->can($permission->name)) {
                $permissions[] = $permission->name;
            }
        }

        $this->permissions = $permissions;
    }

    /**
     * Generate hasil ujian
     * cumulate the result of esay and abc
     * @since 1.0.1 <wandinak17@gmail.com>
     * @return \Illuminate\Http\Response
     */
    public function generateHasilUjian()
    {
    	$this->checkPermissions('setting');

    	$ujian_aktif = Jadwal::where('status_ujian',1)
    	->get()->pluck('id');

    	foreach ($ujian_aktif as $aktif) {
    		$generated = HasilUjian::where('jadwal_id', $aktif)
    		->delete();

    		$hasnot = JawabanPeserta::where('jadwal_id', $aktif)
    		->get()->pluck('peserta_id')->unique();

    		foreach ($hasnot as $not) {
    			$salah = JawabanPeserta::where([
    				'iscorrect'	=> 0,
    				'jadwal_id' => $aktif,
    				'peserta_id'=> $not,
    				'esay'		=> null
    			])->count();

    			$benar = JawabanPeserta::where([
    				'iscorrect'	=> 1,
    				'jadwal_id' => $aktif,
    				'peserta_id'=> $not,
    				'esay'		=> null
    			])->count();

    			$kosong = JawabanPeserta::where([
    				'iscorrect'	=> 1,
    				'jadwal_id' => $aktif,
    				'peserta_id'=> $not,
    				'jawab'	    => 0,
    				'esay'		=> null
    			])->count();

    			$jmlh = JawabanPeserta::where([
    				'jadwal_id' => $aktif,
    				'peserta_id'=> $not,
    				'esay'		=> null
    			])->count();

    			if($benar == 0) {
    				$hasil_ganda = 0;
    			} else {
    				$hasil_ganda = ($benar/$jmlh)*100;
    			
    			}

    			$hasil_esay = 0;
    			$esays = JawabanPeserta::where([
    				'iscorrect'	=> 0,
    				'jadwal_id' => $aktif,
    				'peserta_id'=> $not,
    				'jawab'	    => 0
    			])
    			->whereNotNull('esay')->get();

    			foreach($esays as $esay) {
    				$res = ResultEsay::where('jawab_id', $esay->id)->first();
    				if(!$res){
    					continue;
    				}
    				$hasil_esay += $res->point;
    			}

    			$jawaban = JawabanPeserta::where([
    				'jadwal_id'		=> $aktif,
    				'peserta_id'	=> $not
    			])->get();

    			$hasil = ($hasil_ganda*80)+($hasil_esay*20);

    			HasilUjian::create([
                    'peserta_id'        => $not,
                    'jadwal_id'         => $aktif,
                    'jumlah_salah'      => $salah,
                    'jumlah_benar'      => $benar,
                    'point_esay'		=> $hasil_esay,
                    'tidak_diisi'       => $kosong,
                    'hasil'             => $hasil,
                    'jawaban_peserta'	=> $jawaban
    			]);
    		}
    	}

    	return response([],201);
    }

    /**
     * Response denied
     *
     * @param string $permission
     **/
    public function checkPermissions($permission)
    {
        if(in_array($permission, $this->permissions)) {
            return true;
        }
        return response()->json(['error' => 'forbidden'],403);
    }
}
