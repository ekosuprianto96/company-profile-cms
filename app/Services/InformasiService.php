<?php

namespace App\Services;

use App\Repositories\InformasiRepository;
use Illuminate\Http\Request;

class InformasiService
{
    public function __construct(
        protected InformasiRepository $informasi,
        protected $result = null,
        private bool $decode = false,
        protected ?Request $request = null
    ) {}

    public function find(int $id): self
    {
        $informasi = $this->informasi->findMany([$id]);
        $this->result = collect($informasi); // Pastikan jadi Collection
        return $this;
    }

    public function findByKey(string $key)
    {
        $informasi = $this->informasi->findKey($key);
        $this->result = collect($informasi); // Pastikan jadi Collection
        return $this;
    }

    public function getAllInformasi(): self
    {
        $informasi = $this->informasi->all();
        $this->result = collect($informasi); // Pastikan jadi Collection
        return $this;
    }

    public function getOptionsPhone(): array
    {
        $informasi = $this->findByKey('kontak')
            ->decode(false, false)
            ->get();

        return $informasi->value->phones ?? [];
    }

    public function getOptionsEmail(): array
    {
        $informasi = $this->findByKey('kontak')
            ->decode(false, false)
            ->get();

        return $informasi->value->emails ?? [];
    }

    public function decode(bool $assoc = false, bool $collect = true): self
    {
        if (!($this->result instanceof \Illuminate\Support\Collection)) {
            $this->result = collect($this->result);
        }

        if ($this->result->count() > 1) {
            $this->result = $this->result->map(function ($item) use ($assoc, $collect) {
                $value = $collect
                    ? collect(json_decode($item->value, $assoc))
                    : json_decode($item->value, $assoc);

                $item->value = $value;
                return $item;
            });
        }

        // if jika result hanya 1
        else {
            $this->result = $this->result->first();
            $decodeValue = $collect
                ? collect(json_decode($this->result->value, $assoc))
                : json_decode($this->result->value, $assoc);

            $this->result->value = $decodeValue;
        }

        return $this;
    }

    public function get()
    {
        return $this->result;
    }

    public function addResult(string $key, mixed $value): self
    {
        $this->result[$key] = $value;
        return $this;
    }

    public function setRequest($request): self
    {
        $this->request = $request;
        return $this;
    }

    public function update()
    {
        $informasi = $this->find($this->result['id'])
            ->decode(true, false)
            ->get();

        if ($informasi) {


            $datas = [];
            if (count($informasi->value) > 0) {
                foreach ($informasi->value as $key => $value) {
                    if (isset($value['type']) && $value['type'] == 'image') {
                        $this->removeOrUpdateImage($informasi, $key);
                        $datas[$key]['file'] = (
                            $this->request->get($key)
                            ? $this->request->get($key)
                            : $value['file']
                        );
                        $datas[$key]['type'] = 'image';
                    } else {
                        $datas[$key] = $this->request->get($key, is_array($value) ? [] : $value);
                    }
                }
            }

            $informasi->value = json_encode([
                ...$informasi->value,
                ...$datas,
            ]);
        }

        return $informasi->save() ?? false;
    }

    public function removeOrUpdateImage($informasi, $keyName)
    {
        if (isset($this->request->{$keyName})) {

            if (!empty($informasi->value[$keyName]['file'])) {
                $pathInformasi = 'assets/images/informasi/' . $informasi->value[$keyName]['file'];
                if (file_exists(public_path($pathInformasi))) {
                    unlink(public_path($pathInformasi));
                }
            }

            $pathTemps = 'assets/images/temps/' . $this->request->{$keyName};
            $pathInformasi = 'assets/images/informasi/' . $this->request->{$keyName};

            if (!is_dir(public_path('assets/images/informasi/'))) {
                mkdir(public_path('assets/images/informasi/'), 0777, true);
            }

            if (file_exists(public_path($pathTemps))) {
                rename(public_path($pathTemps), public_path($pathInformasi));
            }
        } else {
            $this->request->merge([
                'file_name' => $this->request->{$keyName} ?? ($informasi->value[$keyName]['file'] ?? ''),
            ]);
        }
    }
}
