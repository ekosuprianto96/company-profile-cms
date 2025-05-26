<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Pengguna;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PenggunaRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        parent::setModel(User::class);
        parent::__construct();
    }

    public function create(array $param = [])
    {
        $pengguna = $this->model->create([
            'email' => $param['email'],
            'name' => Str::title($param['nama']),
            'id_role' => $param['id_role'],
            'password' => Hash::make($param['password']),
        ]);

        $pengguna->createDetailAccount([
            'nama_lengkap' => Str::title($param['nama']),
            'alamat' => $param['alamat'],
            'user_id' => $pengguna->id,
            'no_ktp' => $param['no_ktp'],
            'no_nip' => strtoupper($param['no_nip']),
            'no_telpon' => $param['no_telp'],
            'tanggal_lahir' => $param['tgl_lahir']
        ]);

        return $pengguna;
    }

    public function update($id, array $param = [])
    {
        $pengguna = $this->model->find($id);

        $dataUpdate = [
            'email' => $param['email'],
            'name' => $param['nama'],
            'id_role' => $param['id_role']
        ];

        if (!empty($param['password'] ?? '')) {
            $dataUpdate['password'] = Hash::make($param['password']);
        }

        $pengguna->update($dataUpdate);

        $pengguna->updateDetailAccount([
            'nama_lengkap' => $param['nama'],
            'alamat' => $param['alamat'],
            'no_ktp' => $param['no_ktp'],
            'no_nip' => $param['no_nip'],
            'no_telpon' => $param['no_telp'],
            'tanggal_lahir' => $param['tgl_lahir']
        ]);

        return $pengguna;
    }

    public function updateProfile($id, array $param = [])
    {
        $pengguna = $this->model->find($id);

        $dataUpdate = [
            'email' => $param['email'],
            'name' => $param['nama']
        ];

        if (!empty($param['password'] ?? '')) {
            $dataUpdate['password'] = Hash::make($param['password']);
        }

        $pengguna->update($dataUpdate);

        $pengguna->updateDetailAccount([
            'nama_lengkap' => $param['nama'],
            'alamat' => $param['alamat'],
            'no_ktp' => $param['no_ktp'],
            'no_nip' => $param['no_nip'],
            'no_telpon' => $param['no_telp'],
            'tanggal_lahir' => $param['tgl_lahir']
        ]);

        return $pengguna;
    }
}
