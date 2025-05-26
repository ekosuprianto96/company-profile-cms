<?php

namespace App\Services;

use App\Repositories\CustomerContactRepository;
use Illuminate\Http\Request;

class CustomerContactService
{
    public function __construct(
        private CustomerContactRepository $customerContact,
        private ?Request $request = null
    ) {}

    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    public function storeByMapping(array $header, array $rows, array $mapping)
    {
        // Mapping index
        $columnIndexMap = [];
        foreach ($mapping as $dbField => $columnName) {
            $columnIndexMap[$dbField] = array_search(strtolower($columnName), $header);
        }

        // Mulai dari row kedua
        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            $this->customerContact->create([
                'email' => $row[$columnIndexMap['email']] ?? '-',
                'name' => $row[$columnIndexMap['name']] ?? '-',
                'phone' => $row[$columnIndexMap['phone']] ?? null,
                'address' => $row[$columnIndexMap['address']] ?? null
            ]);
        }
    }

    public function getAllContact(?callable $closure = null)
    {
        return $this->customerContact->getAll($closure);
    }

    public function getContactByEmail(?string $email = null)
    {
        if ($email) {
            return $this->customerContact->findByEmail($email);
        }

        return $this->customerContact->findByEmail($this->request->email);
    }

    public function getContactById(int $id)
    {
        return $this->customerContact->findById($id);
    }

    public function createContact()
    {
        return $this->customerContact->create([
            'email' => $this->request->email,
            'name' => $this->request->name,
            'phone' => $this->request->phone ?? null,
            'address' => $this->request->address ?? null
        ]);
    }

    public function updateContact()
    {

        $contact = $this->customerContact->findById($this->request->id);

        if (!$contact) {
            throw new \Exception('Kontak tidak ditemukan.', 404);
        }

        return $this->customerContact->update($contact->id, $this->request->all());
    }

    public function destroy()
    {
        $contact = $this->customerContact->findById($this->request->id);

        if (!$contact) {
            throw new \Exception('Kontak tidak ditemukan.', 404);
        }

        return $this->customerContact->delete($contact->id);
    }
}
