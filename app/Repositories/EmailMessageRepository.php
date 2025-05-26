<?php

namespace App\Repositories;

use App\Models\EmailMessage;

class EmailMessageRepository extends BaseRepositori
{
    protected $fillable = [];

    public function __construct()
    {
        parent::setModel(EmailMessage::class);
        parent::__construct();
    }

    public function create(array $data = [])
    {
        return $this->model->create($data);
    }

    public function setIsRead(int $id, int $status = 0)
    {
        $message = $this->find($id);
        return $message->update([
            'is_read' => $status
        ]);
    }
}
