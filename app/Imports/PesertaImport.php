<?php

namespace App\Imports;

use App\Peserta;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PesertaImport implements ToModel, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Peserta([
            'name_server'     => $row[0],
            'sesi'            => $row[1],
            'no_ujian'        => $row[2],
            'nama'            => $row[3],
            'password'        => $row[4],
            'jurusan_id'      => $row[5],
            'agama_id'        => $row[6]
        ]);
    }

    public function startRow(): int
    {
        return 2;
    }
}