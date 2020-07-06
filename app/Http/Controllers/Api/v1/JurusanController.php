<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jurusan;

class JurusanController extends Controller
{
    /**
     *  Display list of the source
     *
     *  @return  \Illuminate\Http\Response
     */
    public function index()
    {
        $jurusans = Jurusan::orderBy('nama');
        if (request()->q != '') {
            $jurusans = $jurusans->where('nama', 'LIKE', '%'. request()->q.'%');
        }

        if(request()->perPage != '') {
            $jurusans = $jurusans->paginate(request()->perPage);
        } else {
            $jurusans = $jurusans->get();
        }

        return [ 'data' => $jurusans ];
    }

    /**
     * Store jurusan new
     *
     * 
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
        ]);

        Jurusan::create([
            'nama' => $request->nama
        ]);

        return response()->json([],201);
    }

    /**
     * Get jurusan by id
     *
     * @return  \Illuminate\Http\Response
     */
    public function show(Jurusan $jurusan)
    {
        return response()->json([
            'error' => false,
            'data' => $jurusan
        ]);
    }

    /**
     * Update jurusan by id
     *
     * @return  \Illuminate\Http\Response
     * 
     */
    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'nama' => 'required',
        ]);

        $jurusan->nama = $request->nama;
        $jurusan->save();

        return response()->json(['data' => $jurusan ]);
    }

    /**
     * Delete Jurusan by id
     *
     * @return  \Illuminate\Http\Response
     */
    public function destroy(Jurusan $jurusan)
    {
        $jurusan->delete();
        
        return response()->json([
            'message' => 'jurusan deleted'
        ]);
    }
}
