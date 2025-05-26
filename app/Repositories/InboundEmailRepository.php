<?php

namespace App\Repositories;

use App\Models\InboundEmail;

class InboundEmailRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        parent::setModel(InboundEmail::class);
        parent::__construct();
    }

    public function create(array $param = [])
    {
        return $this->model->create($param);
    }
}
