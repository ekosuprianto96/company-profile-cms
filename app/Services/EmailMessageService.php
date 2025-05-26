<?php

namespace App\Services;

use App\Repositories\EmailMessageRepository;
use Illuminate\Http\Request;

class EmailMessageService
{
    public function __construct(
        private EmailMessageRepository $emailMessage,
        private ?Request $request = null
    ) {}

    public function setRequest($request): self
    {
        $this->request = $request;

        return $this;
    }

    public function createMessage()
    {
        return $this->emailMessage->create([
            'message_id' => $this->request->message_id,
            'to_email' => $this->request->to_email,
            'name' => $this->request->name,
            'email' => $this->request->email,
            'subject' => $this->request->subject,
            'message' => $this->request->message
        ]);
    }

    public function getMessageByMessageId($message_id)
    {
        return $this->emailMessage->where('message_id', $message_id)->first();
    }

    public function getMessage($id)
    {
        $message = $this->emailMessage->with(['inBoundmail'])->find($id);

        if (!$message) throw new \Exception('Pesan tidak ditemukan', 404);

        return $message;
    }

    public function getAllMessage(?callable $callback = null)
    {
        if ($callback) $this->emailMessage = $callback($this->emailMessage);
        return $this->emailMessage->all();
    }

    public function setIsRead(int $id, int $status = 0)
    {
        $message = $this->emailMessage->find($id);

        if (!$message) throw new \Exception('Pesan tidak ditemukan.', 404);

        return $this->emailMessage->setIsRead($id, $status);
    }

    public function deleteMessage(int $id)
    {
        $message = $this->emailMessage->find($id);

        if (!$message) throw new \Exception('Pesan tidak ditemukan.', 404);

        return $this->emailMessage->delete($id);
    }
}
