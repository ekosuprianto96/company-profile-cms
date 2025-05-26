<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Repositories\InboundEmailRepository;

class InboundEmailService
{
    public function __construct(
        private InboundEmailRepository $inboundEmail,
        private ?Request $request = null
    ) {}

    public function setRequest($request): self
    {
        $this->request = $request;
        return $this;
    }

    public function createMessage()
    {
        return $this->inboundEmail->create([
            'message_id' => $this->request->message_id,
            'to_email' => $this->request->to_email,
            'name' => $this->request->name,
            'email' => $this->request->email,
            'subject' => $this->request->subject,
            'message' => $this->request->message
        ]);
    }
}
