<?php

namespace App\Repositories;

use App\Models\Informasi;
use App\Models\SocialMedia;

class SocialMediaRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        parent::setModel(Informasi::class);
        parent::__construct();
    }

    public function query()
    {
        return $this->model->where('key', 'social-media')->first();
    }

    public function getAll(?callable $closure = null)
    {
        $modelInit = $this->query();
        $decode = json_decode($modelInit->value ?? '[]', true);
        $collect = collect($decode);
        if ($closure) $collect = $closure($collect);
        return $collect;
    }

    public function find($id)
    {
        $query = $this->getAll();
        return $query->where('id', $id)->first();
    }

    public function create(array $param = [])
    {
        $query = $this->query();
        $decode = json_decode($query->value ?? '[]', true);
        $decode[] = $param;
        $query->update(['value' => json_encode($decode)]);

        return collect($param);
    }

    public function update($id, array $param = [])
    {
        $query = $this->query();
        $decode = json_decode($query->value ?? '[]', true);
        $collect = collect($decode);
        $collect->transform(function ($item) use ($id, $param) {
            if ($item['id'] === $id) {
                $item = [
                    ...$item,
                    ...$param
                ];
            }

            return $item;
        });

        $query->update(['value' => json_encode($collect->toArray())]);

        return $collect->where('id', $id)->first();
    }

    public function delete($id)
    {
        $query = $this->query();
        $decode = json_decode($query->value, true);
        $decode = collect($decode)->where('id', '!=', $id)->toArray();
        $query->update(['value' => json_encode($decode)]);

        return true;
    }
}
