<?php

namespace App\Services;

use App\Models\EmailMessage;
use App\Repositories\EmailMessageSendingRepository;
use Illuminate\Http\Request;

class EmailMessageSendingService
{
    public function __construct(
        private EmailMessageSendingRepository $emailMessageSending,
        private ?Request $request = null
    ) {}

    public function setRequest($request): self
    {
        $this->request = $request;

        return $this;
    }

    public function createMessage()
    {
        return $this->emailMessageSending->create([
            'contact_id' => $this->request->contact_id,
            'from_email' => $this->request->from_email,
            'subject' => $this->request->subject,
            'message' => $this->request->message
        ]);
    }

    public function getAllMessage(?callable $closure = null)
    {
        $query = $this->emailMessageSending->with(['contact', 'sendBy']);

        if ($closure) $query = $closure($this->emailMessageSending);

        return $query->get();
    }

    public function deleteMessage(int $id)
    {
        $message = $this->emailMessageSending->find($id);

        if (!$message) throw new \Exception('Pesan tidak ditemukan.', 404);

        return $this->emailMessageSending->delete($id);
    }
}
