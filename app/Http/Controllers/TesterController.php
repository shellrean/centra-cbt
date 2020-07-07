<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Services\DocService;

class TesterController extends Controller
{
    /**
     * 
     */
    public function index()
    {
        // $contents = Storage::get('storage/so.docx');
        $path = Storage::disk('public')->path('so.docx');

        // return $path;

        $docObj = new DocService($path);
        $docObj->extractImages();

        $txt = $docObj->displayImages();

        return $txt;
    }

    public function upload(Request $request)
    {
        // $dir = Directory::find($request->directory_id);
        $file = $request->file('image');
        $type = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $filename = date('Ymd').'-'.$file->getClientOriginalName();
        $path = $file->storeAs('public',$filename);

        // $data= [
        //     'directory_id'      => $request->directory_id,
        //     'filename'          => $filename,
        //     'path'              => $path,
        //     'exstension'        => $type,
        //     'dirname'           => $dir->slug,
        //     'size'              => $size,
        // ];

        // $logo = File::create($data);

        return response()->json(['src' => 'http://localhost/storage/'.$filename]);
    }
}
