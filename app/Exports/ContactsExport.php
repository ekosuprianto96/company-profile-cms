<?php

namespace App\Exports;

use App\Models\CustomerContact;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ContactsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return CustomerContact::all();
    }

    public function headings(): array
    {
        return ['ID', 'Nama', 'Email', 'No Telpon', 'Alamat', 'Dibuat Pada', 'Diubah Pada'];
    }
}
