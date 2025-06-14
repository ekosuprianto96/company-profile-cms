<?php

namespace App\Repositories;

use App\Models\Visitor;

class VisitorRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        parent::setModel(Visitor::class);
        parent::__construct();
    }

    public function create(array $param = [])
    {
        return $this->model->create($param);
    }

    public function getVisitorByIp($ip)
    {
        return $this->model->where('ip_address', $ip)->first();
    }

    public function getAllVisitor()
    {
        return $this->model->latest()->get();
    }
}
