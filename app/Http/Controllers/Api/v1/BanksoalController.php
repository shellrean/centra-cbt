<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Directory;
use App\Banksoal;
use App\Sekolah;
use App\Peserta;
use App\Server;
use App\File;

use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

use PDF;
use Excel;

class BanksoalController extends Controller
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
        $this->checkPermissions('banksoal');

        $user = request()->user('api'); 

        $banksoal = Banksoal::with(['matpel','user'])->orderBy('created_at', 'DESC');
        if (request()->q != '') {
            $banksoal = $banksoal->where('kode_banksoal', 'LIKE', '%'. request()->q.'%');
        }

        if ($user->role != 0) {
            $banksoal = $banksoal->where('author',$user->id);
        }

        $banksoal = $banksoal->paginate(10);
        return [ 'data' => $banksoal ];
    }

    /**
     * Get all data without pagination
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        $user = request()->user('api'); 

        $banksoal = Banksoal::with(['matpel'])->orderBy('created_at', 'DESC');

        if ($user->role != 0) {
            $banksoal = $banksoal->where('author',$user->id);
        }

        $banksoal = $banksoal->get();
        return [ 'data' => $banksoal ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->checkPermissions('create_banksoal');

        $validator = Validator::make($request->all(), [
            'kode_banksoal'     => 'required|unique:banksoals,kode_banksoal',
            'matpel_id'         => 'required|exists:matpels,id',
            'jumlah_soal'       => 'required|int',
            'jumlah_pilihan'    => 'required|int',
        ]); 

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()],422);
        }

        $direk = Directory::create([
            'name'      => $request->kode_banksoal,
            'slug'      => Str::slug($request->kode_banksoal, '-')
        ]);

        $data = [
            'kode_banksoal'     => $request->kode_banksoal,
            'matpel_id'         => $request->matpel_id,
            'author'            => auth()->user()->id,
            'jumlah_soal'       => $request->jumlah_soal,
            'jumlah_pilihan'    => $request->jumlah_pilihan,
            'jumlah_soal_esay'  => $request->jumlah_soal_esay,
            'directory_id'      => $direk->id
        ];

        $res = Banksoal::create($data);


        return response()->json(['data' => $res]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->checkPermissions('banksoal');

        $banksoal = Banksoal::where('id',$id)->with('matpel')->first();

        return response()->json(['data' => $banksoal]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->checkPermissions('edit_banksoal');

        $banksoal = Banksoal::find($id);
        $banksoal->kode_banksoal = $request->kode_banksoal;
        
        if(gettype($request->matpel_id) == 'array') {
            $banksoal->matpel_id = $request->matpel_id['id'];
        }

        $banksoal->jumlah_soal = $request->jumlah_soal;
        $banksoal->jumlah_soal_esay = $request->jumlah_soal_esay;
        $banksoal->save();

        return response()->json(['data' => $banksoal]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->checkPermissions('delete_banksoal');
        
        $banksoal = Banksoal::find($id);
    
        $banksoal->delete();

        File::where('directory_id', $banksoal->directory_id)->delete();
        Directory::find($banksoal->directory_id)->delete();

        return response()->json(['status' => 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function active()
    {
        $user = request()->user('api');
        if($user->sekolah_id == null) return [ 'message' => 'you have no school' ];

        $server = Server::where('sekolah_id', $user->sekolah_id)->pluck('server_name');
        $matpels = Peserta::whereIn('name_server', $server)->pluck('jurusan_id');

        $banksoal = Banksoal::whereHas('matpel',function($q) use($matpels) {
            $q->whereIn('jurusan_id',$matpels);
        })
        ->with('matpel');
        
        $banksoal = $banksoal->get()
        ->makeHidden('jumlah_soal')
        ->makeHidden('jumlah_pilihan')
        ->makeHidden('jumlah_soal_esay')
        ->makeHidden('matpel_id')
        ->makeHidden('author')
        ->makeHidden('created_at')
        ->makeHidden('updated_at')
        ->makeHidden('directory_id');

        return [ 'data' => $banksoal ];
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
