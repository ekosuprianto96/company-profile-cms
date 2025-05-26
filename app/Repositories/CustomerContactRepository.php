<?php

namespace App\Repositories;

use App\Models\CustomerContact;

class CustomerContactRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        parent::setModel(CustomerContact::class);
        parent::__construct();
    }

    public function getAll(?callable $closure = null, string $order = 'asc')
    {
        $query = $this->model->orderBy('created_at', $order);
        if ($closure) $query = $closure($query);

        return $query->get();
    }

    public function create(array $data = [])
    {
        return $this->model->create($data);
    }

    public function update($id, array $data = [])
    {
        return $this->findById($id)->update($data);
    }

    public function findByEmail(string $email, ?callable $closure = null)
    {
        $query = $this->model->where('email', $email);
        if ($closure) $query = $closure($query);

        return $query->first();
    }

    public function findById(int $id, ?callable $closure = null)
    {
        if ($closure) $this->model = $closure($this->model);

        return $this->model->find($id);
    }

    public function destroy($id)
    {
        return $this->model->delete($id);
    }
}
