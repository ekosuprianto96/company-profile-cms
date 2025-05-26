<?php

namespace App\Repositories;

use App\Models\EmailMessageSending;

class EmailMessageSendingRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        parent::setModel(EmailMessageSending::class);
        parent::__construct();
    }

    public function create(array $param = [])
    {
        return $this->model->create($param);
    }

    public function delete($id)
    {
        $delete = $this->model->find($id);

        if (!$delete) throw new \Exception("Data tidak ditemukan", 404);

        return $delete->delete();
    }
}
