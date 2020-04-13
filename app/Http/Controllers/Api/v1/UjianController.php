<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;

use App\JawabanPeserta;
use App\EventUjian;
use App\SiswaUjian;
use App\HasilUjian;
use App\ResultEsay;
use App\Banksoal;
use App\Peserta;
use App\Jadwal;
use App\Result;
use App\Server;
use App\Sekolah;
use App\Soal;
use DB;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UjianController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	$this->checkPermissions('jadwal');

        $ujian = Jadwal::with('event')->orderBy('created_at', 'DESC');
        if (request()->q != '') {
            $ujian = $ujian->where('alias', 'LIKE', '%'. request()->q.'%');
        }
        if (request()->perPage != '') {
            $ujian = $ujian->paginate(request()->perPage);
        } else {
            $ujian = $ujian->paginate(20);
        }
        $ujian->makeHidden('banksoal_id');
        return [ 'data' => $ujian ];
    }

    /**
     * Get all ujian without pagination
     *
     * @return \Illuminate\http\Response
     */
    public function getAll()
    {
        $ujians = Jadwal::orderBy('id','desc')->get();

        return ['data' => $ujians ];
    }

    /**
     * Get jadwal by banksoal.
     *
     * @return \Illuminate\Http\Response
     */
    public function getByBanksoal($id)
    {
        $rest = DB::table('jawaban_pesertas')
        ->where('jawaban_pesertas.banksoal_id', $id)
        ->join('jadwals', function($j) {
            $j->on('jawaban_pesertas.soal_id', 'jadwals.id');
        })
        ->groupBy('jadwals.id')
        ->select('jadwals.id')
        ->pluck('id');

        $ujian = Jadwal::whereIn('id', $rest)->paginate(10);

        return response()->json(['data' => $ujian]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal'           => 'required',
            'mulai'             => 'required',
            'berakhir'          => 'required',
            'lama'              => 'required|int',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }
        $data = [
            'mulai'             => date('H:i:s', strtotime($request->mulai)),
            'berakhir'          => date('H:i:s',strtotime($request->berakhir)),
            'lama'              => $request->lama*60,
            'tanggal'           => date('Y-m-d',strtotime($request->tanggal)),
            'status_ujian'      => 0,
            'alias'             => $request->alias,
            'event_id'          => $request->event_id
        ];

        if($request->banksoal_id != '') {
            $fill = array();
            foreach($request->banksoal_id as $banksol) {
                $fush = [
                    'id' => $banksol['id'],
                    'jurusan' => $banksol['matpel']['jurusan_id']
                ];
                array_push($fill, $fush);
            }   

            $data['banksoal_id'] = $fill;
        }

        if($request->server_id != '') { 
            $fill = array();
            foreach($request->server_id as $server) {
                array_push($fill, $server['server_name']);
            }   

            $data['server_id'] = $fill;
        }

        Jadwal::create($data);

        return response()->json(['data' => 'success']);
    }

    /**
     * Store event ujian.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function storeEventUjian(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'           => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        EventUjian::create([
            'name'      => $request->name
        ]);

        return response()->json([], 201);
    }

    /**
     * Get ujian event
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response
     */
    public function getEventUjian()
    {
        $events = EventUjian::orderBy('created_at','DESC')->get();

        return ['data' => $events ];
    }

    /**
     * Set status ujian.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setStatus(Request $request)
    {
        $jadwal = Jadwal::find($request->id);
        $jadwal->status_ujian = $request->status;
        $jadwal->save();

        return response()->json(['status' => 'success']);
    }

    /**
     * Change token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function changeToken(Request $request)
    {
        $jadwal = Jadwal::find($request->id);
        $jadwal->token = strtoupper(Str::random(6));
        $jadwal->save();

        return response()->json(['data' => $jadwal]);
    }

    /**
     * Get all peserta by ujian
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function getPeserta($id)
    {
        $siswa = SiswaUjian::with('peserta')->where(['jadwal_id' => $id])->get();

        return response()->json(['data' => $siswa]);
    }

    public function getHasil($id)
    {
        $hasilPeserta = Result::with('peserta')->where(['jadwal_id' => $id])->get();

        return response()->json(['data' => $hasilPeserta]);
    }

    public function getEsay($id)
    {
        $has = ResultEsay::where('banksoal_id', $id)->get()->pluck('jawab_id');
        $exists = JawabanPeserta::whereNotIn('id', $has)
        ->with(['pertanyaan' => function($q) {
            $q->select(['id','rujukan','pertanyaan']);
        }])
        ->whereNotNull('esay')
        ->where('banksoal_id', $id)
        ->paginate(10);

        return [ 'data' => $exists ];
    }

    public function inputEsay(Request $request)
    {
        $jawab = JawabanPeserta::find($request->id);

        $user = request()->user('api'); 

        $has = ResultEsay::where('banksoal_id', $jawab->banksoal_id)->get()->pluck('jawab_id');
        $sames = JawabanPeserta::whereNotIn('id',$has)
        ->where(['esay' => $jawab->esay, 'banksoal_id' => $jawab->banksoal_id, 'soal_id' => $jawab->soal_id])
        ->get();

        if($sames) {
            foreach($sames as $same) {
                ResultEsay::create([
                    'banksoal_id'   => $same->banksoal_id,
                    'peserta_id'    => $same->peserta_id,
                    'jawab_id'      => $same->id,
                    'corrected_by'  => $user->id,
                    'point'         => $request->val
                ]);
            }

            return response()->json(['data' => 'OK1']);
        }


        ResultEsay::create([
            'banksoal_id'   => $jawab->banksoal_id,
            'peserta_id'    => $jawab->peserta_id,
            'jawab_id'      => $jawab->id,
            'corrected_by'  => $user->id,
            'point'         => $request->val
        ]);

        return response()->json(['data' => 'OK']);
    }

    public function getByFilter(Request $request)
    {
        $reslt = Result::count();
        if($reslt == 0) {
            $ujian = Jadwal::where('status_ujian', 1)->first();
            $peserta = Peserta::all();
            foreach($peserta as $p) {
                $resl = DB::table('jawaban_pesertas')
                ->where('peserta_id', $p->id)
                ->count();
                if($resl == 0) {
                    continue;
                }
                $salah = DB::table('jawaban_pesertas')
                ->where([
                    'iscorrect' => 0,
                    'jadwal_id' => $ujian->id,
                    'peserta_id' => $p->id
                ])->get()->count();

                $benar = DB::table('jawaban_pesertas')
                ->where([
                    'iscorrect' => 1,
                    'jadwal_id' => $ujian->id,
                    'peserta_id' => $p->id
                ])->get()->count();

                $kosong = DB::table('jawaban_pesertas')
                ->where([
                    'jadwal_id' => $ujian->id,
                    'peserta_id' => $p->id,
                    'jawab'     => 0
                ])->get()->count();

                $jml = DB::table('jawaban_pesertas')
                ->where([
                    'jadwal_id' => $ujian->id,
                    'peserta_id' => $p->id
                ])->get()->count();
                

                $hasil = ($benar/$jml)*100;

                DB::table('results')
                ->insert([
                    'server_name'       => $p->name_server,
                    'peserta_id'        => $p->id,
                    'jadwal_id'         => $ujian->id,
                    'salah'             => $salah,
                    'benar'             => $benar,
                    'kosong'            => $kosong,
                    'hasil'             => $hasil
                ]);
            }
        }
        $banksoal_id = $request->banksoal;
        $results = DB::table('results')
        ->join('pesertas', function($j) {
            $j->on('pesertas.id','results.peserta_id');
        })
        ->join('servers', function($j) {
            $j->on('servers.server_name','pesertas.name_server');
        })
        ->where('servers.sekolah_id', $request->sekolah)
        ->join('matpels', function($j) {
            $j->on('matpels.jurusan_id', 'pesertas.jurusan_id');
        })
        ->join('banksoals', function($j) use ($banksoal_id) {  
            $j->where('banksoals.id','=',$banksoal_id)
            ->on('banksoals.matpel_id', 'matpels.id');
        })
        ->select('pesertas.id','pesertas.nama','results.hasil','results.salah','results.benar','results.kosong')
        ->orderBy('pesertas.id')
        ->get();

        return response()->json(['data' => $results]);
    }

    /**
     *
     */
    public function getExistsEsay()
    {
        $has = ResultEsay::all()->pluck('jawab_id')->unique();

        $user = request()->user('api');

        $exists = JawabanPeserta::whereNotNull('esay')
        ->whereNotIn('id', $has)
        ->get()
        ->pluck('banksoal_id')
        ->unique();

        $banksoal = Banksoal::with('matpel')->whereIn('id', $exists)->get()
        ->makeHidden('jumlah_soal')
        ->makeHidden('jumlah_pilihan')
        ->makeHidden('matpel_id')
        ->makeHidden('directory_id')
        ->makeHidden('inputed')
        ->makeVisible('koreksi');

        $filtered = $banksoal->reject(function ($value, $key) use($user) {
            return !in_array($user->id, $value->matpel->correctors);
        });

        return ['data' => $filtered->values()->all()];
    }

    /**
     *
     */
    public function getSekolahByJadwal($jadwal_id)
    {
        $result = HasilUjian::where('jadwal_id', $jadwal_id)
        ->get()->pluck('peserta_id');
        
        $name_server = Peserta::whereIn('id', $result)
        ->get()->pluck('name_server');
        
        $sekolah_ids = Server::whereIn('server_name', $name_server)
        ->get()->pluck('sekolah_id');

        $sekolah = Sekolah::whereIn('id', $sekolah_ids)->get();

        return ['data' => $sekolah];
    }

    /**
     *
     */
    public function getHasilByJadwalAndSekolah(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'jadwal_id'           => 'required',
            'sekolah_id'             => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        $servers = Server::where('sekolah_id', $request->sekolah_id)
        ->get()->pluck('server_name');

        $pesertas = Peserta::whereIn('name_server', $servers)
        ->get()->pluck('id');

        $res = HasilUjian::with('peserta')
        ->whereIn('peserta_id', $pesertas)
        ->where('jadwal_id', $request->jadwal_id)
        ->orderBy('peserta_id');

        if(request()->perPage != '') {
            $res = $res->paginate(request()->perPage);
        } else {
            $res = $res->get();
        }

        return ['data' => $res ];

    }

    /**
     *
     */
    public function getBanksoalByJadwalAndSekolah(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jadwal_id'           => 'required',
            'sekolah_id'             => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        $servers = Server::where('sekolah_id', $request->sekolah_id)
        ->get()->pluck('server_name');

        $pesertas = Peserta::whereIn('name_server', $servers)
        ->get()->pluck('id');

        $res = HasilUjian::whereIn('peserta_id', $pesertas)
        ->where('jadwal_id', $request->jadwal_id)
        ->get()
        ->makeVisible('jawaban_peserta');

        $der = $res->flatMap(function($value) {
            return $value->jawaban_peserta;
        })->pluck('banksoal_id')->unique();

        $bankSoal = Banksoal::find($der);

        return ['data' => $bankSoal ];
    }

    /**
     *
     */
    public function getCapaianSiswa(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'jadwal_id'           => 'required',
            'sekolah_id'             => 'required',
            'banksoal_id'           => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        $soals = Soal::where('banksoal_id', $request->banksoal_id)->orderBy('tipe_soal')->get();

        $s = HasilUjian::with('peserta')
        ->where('jadwal_id', $request->jadwal_id)
        ->orderBy('peserta_id')
        ->get()
        ->makeVisible('jawaban_peserta');

        $filtered = $s->reject(function ($value, $key) use ($request){
            return $value->jawaban_peserta[0]['banksoal_id'] != $request->banksoal_id;
        });

        $der = $filtered->map(function($value) {
            return [$value->peserta, collect($value->jawaban_peserta)->sortBy('soal_id')];
        });

        return ['soals' => $soals, 'data' => $der->values()];
    }

    /**
     *
     */
    public function setRujukan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'soal_id'           => 'required',
            'rujukan'             => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        $soal = Soal::find($request->soal_id);
        if($soal) {
            $soal->rujukan = $request->rujukan;
            $soal->save();

            return response()->json(['message' => 'rujukan success to set'],200);
        }
    }

    /**
     * Response denied
     *
     *
     **/
    public function checkPermissions($permission)
    {
        if(in_array($permission, $this->permissions)) {
            return true;
        }

        return response()->json(['error' => 'forbidden'],403);
    }
}
