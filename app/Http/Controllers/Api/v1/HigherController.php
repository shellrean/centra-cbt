<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Builder;

use Illuminate\Support\Facades\DB;

use App\Soal;
use App\Jadwal;
use App\Achieve;
use App\Banksoal;
use App\ResultEsay;
use App\HasilUjian;
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
                $salah = JawabanPeserta::whereHas('pertanyaan', function(Builder $query) {
                    $query->where('tipe_soal','!=',2);
                })
                ->where([
    				'iscorrect'	=> 0,
    				'jadwal_id' => $aktif,
    				'peserta_id'=> $not
    			])
                ->where('jawab', '!=', 0)
                ->get()
                ->pluck('jawab')->unique()
                ->count();

    			$benar = JawabanPeserta::whereHas('pertanyaan', function(Builder $query) {
                    $query->where('tipe_soal','!=',2);
                })
                ->where([
                    'iscorrect' => 1,
                    'jadwal_id' => $aktif,
                    'peserta_id'=> $not
                ])
                ->get()
                ->pluck('jawab')
                ->unique()
                ->count();

                $kosong = JawabanPeserta::whereHas('pertanyaan', function(Builder $query) {
                    $query->where('tipe_soal','!=',2);
                })
                ->where([
                    'jawab'     => 0,
                    'jadwal_id' => $aktif,
                    'peserta_id'=> $not
                ])
                ->get()
                ->count();

                $jmlh = JawabanPeserta::whereHas('pertanyaan', function(Builder $query) {
                    $query->where('tipe_soal','!=',2);
                })
                ->where([
                    'jadwal_id' => $aktif,
                    'peserta_id'=> $not
                ])
                ->get()
                ->pluck('jawab')
                ->unique()
                ->count();


    			if($benar == 0) {
    				$hasil_ganda = 0;
    			} else {
    				$hasil_ganda = ($benar/($jmlh+$kosong-1));
    			}

    			$hasil_esay = 0;
 
                $esays = JawabanPeserta::whereHas('pertanyaan', function(Builder $query) {
                    $query->where('tipe_soal',2);
                })
                ->where([
                    'jadwal_id' => $aktif,
                    'peserta_id'=> $not,
                ])
                ->get();

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

                $frs = $jawaban[0]->banksoal_id;
                $bks = Banksoal::find($frs);
                $jml_esay = $bks->jumlah_soal_esay;

                if($jml_esay != 0) {
        			$hasil = ($hasil_ganda*80)+(($hasil_esay/$jml_esay)*20);
                } else {
                    $hasil = $hasil_ganda*100;   
                }

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
     * Generate analys
     * create an analys for an question
     * @since 1.0.1 <wandinak17@gmail.com>
     * @return \Illuminate\Http\Response
     */
    public function generateAnalys()
    {
        $this->checkPermissions('setting');

        $activeJadwal = Jadwal::where('status_ujian',1)->get();
        
        $useBanksoal = $activeJadwal->flatMap(function ($item, $key) {
            return $item->banksoal_id;
        })->pluck('id');

        $soals = Soal::whereIn('banksoal_id', $useBanksoal)->get()
        ->makeVisible('salah')
        ->makeVisible('benar')
        ->makeVisible('penjawab');

        foreach ($soals as $value) {
            $analys = $value->analys;
            if(is_array($analys) && $analys['salah']) {
                $salah = $analys['salah'];
                $benar = $analys['benar'];
                $kosong = $analys['kosong'];
                $penjawab = $analys['penjawab'];
            } else {
                $salah = 0;
                $benar = 0;
                $kosong = 0;
                $penjawab = 0;
            }

            $new = [
                'salah'     => $salah+$value->salah,
                'benar'     => $benar+$value->benar,
                'kosong'    => $kosong+$value->kosong,
                'penjawab'  => $penjawab+$value->penjawab,
                'updated'   => now()
            ];

            $value->analys = $new;
            $value->save();
        }
        return response()->json(['message' => 'Analys success'],201);
    }

    /**
     * Arsip jawaban 
     * @since 1.0.1 <wandinak17@gmail.com>
     * @return \Illuminate\Http\Response
     */
    public function arsipJawaban()
    {
        DB::beginTransaction();

        try {
            $jadwals = JawabanPeserta::all()->pluck('jadwal_id')->unique();
            foreach ($jadwals as $jadwal) {
                $banksoals = JawabanPeserta::where('jadwal_id', $jadwal)->get()->pluck('banksoal_id')->unique();

                foreach ($banksoals as $banksoal) {
                    $pesertas = JawabanPeserta::where([
                        'jadwal_id'     => $jadwal,
                        'banksoal_id'   => $banksoal
                    ])->get()->pluck('peserta_id')->unique();

                    foreach ($pesertas as $peserta) {
                        $jawaban = JawabanPeserta::where([
                            'jadwal_id'    => $jadwal,
                            'banksoal_id'   => $banksoal,
                            'peserta_id'    => $peserta
                        ])->get()->except(['banksoal_id','jadwal_id','peserta_id']);

                        Achieve::create([
                            'banksoal_id'       => $banksoal,
                            'peserta_id'        => $peserta,
                            'jadwal_id'         => $jadwal,
                            'achieve'           => json_encode($jawaban)
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(['message' => 'Arsip sukses']);
            
        } catch (QueryException $e) {
            DB::rollback();
            return $e->getMessage();
        }
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
