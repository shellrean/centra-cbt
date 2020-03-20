<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Sekolah;
use PDF;
use DB;

class SekolahController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sekolah = Sekolah::orderBy('created_at');
        if(request()->q != '') {
            $sekolah = $sekolah->where('nama','LIKE', '%'.request()->q.'%');
        }

        $sekolah = $sekolah->paginate(10);
        return new AppCollection($sekolah);
    }

    public function allSekolah()
    {
        $sekolah = Sekolah::orderBy('nama')->get();
        return new AppCollection($sekolah);
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
            'nis'       => 'required',
            'nama'      => 'required',
            'alamat'    => 'required'
        ]);

        if($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [
            'nis'           => $request->nis,
            'nama'          => $request->nama,
            'alamat'        => $request->alamat
        ];

        $data = Sekolah::create($data);

        return response()->json(['data' => $data]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $sekolah = Sekolah::find($id);

        return response()->json(['data' => $sekolah]);
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
        $sekolah = Sekolah::find($id);
        $sekolah->update([
            'nis'       => $request->nis,
            'nama'      => $request->nama,
            'alamat'    => $request->alamat
        ]);

        return response()->json($sekolah);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $sekolah = Sekolah::find($id);

        $sekolah->delete();

        return response()->json([],200);
    }

    /**
     *
     */
    public function allJurusan()
    {
        $data = DB::table('jurusans')->get();

        return response()->json(['data' => $data]);
    }

    /**
     *
     *
     */
    public function preDataSekolah()
    {
        $sekolahs = Sekolah::orderBy('nama')->get();

        $pdf = PDF::loadview('prev.sekolah',compact('sekolahs'));
        return $pdf->stream('data-sekolah.pdf');
    }
}
