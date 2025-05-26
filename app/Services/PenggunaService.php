<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Repositories\PenggunaRepository;
use Illuminate\Validation\Rule;

class PenggunaService
{
    private Request $request;
    private PenggunaRepository $pengguna;

    public function __construct(PenggunaRepository $pengguna)
    {
        $this->pengguna = $pengguna;
    }

    public function getPengguna($id)
    {
        return $this->pengguna->with(['account'])->find($id);
    }

    public function validate(bool $update = false, bool $isProfile = false): self
    {
        $this->request->validate([
            'nama' => 'required|string|min:3|max:50',
            'email' => ['required', 'email',  ($update ? Rule::unique('users', 'email')->ignore($this->request->get('id')) : 'unique:users,email')],
            'password' => $update ? (!empty($this->request->get('password', '')) ? ['min:6', 'max:150'] : '') : ['required', 'min:6', 'max:150'],
            'tgl_lahir' => 'required|date_format:Y-m-d',
            'no_telp' => 'required|numeric',
            'no_ktp' => 'required|numeric|digits:16',
            'no_nip' => 'required',
            'alamat' => 'required|string|min:3|max:255',
            'id_role' => $isProfile ? '' : 'required|exists:roles,id_role',
        ], [
            'password.required' => 'Password tidak boleh kosong.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.max' => 'Password maksimal 150 karakter.',
            'nama.required' => 'Nama tidak boleh kosong.',
            'nama.string' => 'Nama harus berupa string.',
            'nama.min' => 'Nama minimal 3 karakter.',
            'nama.max' => 'Nama maksimal 50 karakter.',
            'email.required' => 'Email tidak boleh kosong.',
            'email.email' => 'Email harus berupa email yang valid.',
            'email.unique' => 'Email sudah digunakan.',
            'tgl_lahir.required' => 'Tanggal lahir tidak boleh kosong.',
            'tgl_lahir.date_format' => 'Tanggal lahir harus berupa tanggal yang valid.',
            'no_telp.required' => 'Nomor telepon tidak boleh kosong.',
            'no_telp.numeric' => 'Nomor telepon harus berupa angka.',
            'no_ktp.required' => 'Nomor KTP tidak boleh kosong.',
            'no_ktp.numeric' => 'Nomor KTP harus berupa angka.',
            'no_ktp.digits' => 'Nomor KTP maksimal 16 angka.',
            'no_nip.required' => 'Nomor NIP tidak boleh kosong.',
            'alamat.required' => 'Alamat tidak boleh kosong.',
            'alamat.string' => 'Alamat harus berupa string.',
            'alamat.min' => 'Alamat minimal 3 karakter.',
            'alamat.max' => 'Alamat maksimal 255 karakter.',
            'id_role.required' => 'Role tidak boleh kosong.',
            'id_role.exists' => 'Role tidak ditemukan.'
        ]);

        return $this;
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function create()
    {
        return $this->pengguna->create($this->request->all());
    }

    public function update(int $id)
    {
        return $this->pengguna->update($id, $this->request->all());
    }

    public function updateProfile(int $id)
    {
        return $this->pengguna->updateProfile($id, $this->request->all());
    }

    public function delete()
    {
        $existsPengguna = $this->pengguna->find($this->request->id);

        if (!$existsPengguna) {
            throw new \Exception('Pengguna tidak ditemukan.', 404);
        }

        return $existsPengguna->delete();
    }

    public function uploadAvatar(): void
    {
        $pengguna = $this->pengguna->find($this->request->id);

        if (!$pengguna) {
            throw new \Exception('Pengguna tidak ditemukan.', 404);
        }

        try {

            if ($this->request->hasFile('avatar')) {

                if (!empty($pengguna->account->image ?? '')) {
                    unlink(public_path('assets/images/avatars/' . $pengguna->account->image));
                }

                $file = $this->request->file('avatar');
                $ext = $file->getClientOriginalExtension();
                $newName = now()->format('Y-m-d') . '-' . Str::uuid() . '.' . $ext;
                $path = "assets/images/avatars";

                if (!is_dir(public_path($path))) {
                    mkdir(public_path($path), 0777, true);
                }

                $file->move(public_path($path), $newName);

                $pengguna->account->update([
                    'image' => $newName
                ]);
            } else {
                throw new \Exception('Tidak ada file yang diupload.', 400);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}
