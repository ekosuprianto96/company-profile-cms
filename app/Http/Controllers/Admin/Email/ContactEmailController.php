<?php

namespace App\Http\Controllers\Admin\Email;

use App\Traits\AdminView;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Exports\ContactsExport;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\CustomerContactService;
use Yajra\DataTables\Facades\DataTables;

class ContactEmailController extends Controller
{
    use AdminView;
    public function __construct(
        private CustomerContactService $customerContact
    ) {
        $this->setView('admin.pages.email-management');
    }

    public function index()
    {
        return $this->view('list-contact');
    }

    public function export(Request $request)
    {
        return Excel::download(new ContactsExport, 'contacts.xlsx');
    }

    public function importWithMapping(Request $request)
    {
        try {
            $mapping = $request->input('mapping'); // contoh: ['nama' => 'Name', 'no_telpon' => 'Phone', 'alamat' => 'Address']
            $filePath = public_path('temps/' . $request->input('file_path'));

            $rows = Excel::toArray([], $filePath)[0]; // Ambil semua data sheet 0
            $header = array_map('strtolower', $rows[0]);

            DB::transaction(function () use ($header, $rows, $mapping) {
                $this->customerContact
                    ->storeByMapping($header, $rows, $mapping);
            });

            return response()->json(['message' => 'Kontak berhasil diimport.'], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function readFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        // Simpan file ke temporary
        $file = $request->file('file');
        $newName = now('Asia/Jakarta')->format('Y-m-d') . '-' . Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = public_path('temps');

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $file->move($path, $newName);

        // Ambil header row
        $headings = Excel::toArray([], $path . '/' . $newName)[0][0]; // Row pertama

        return view('admin.components.forms.contact-mapping', [
            'file' => $newName,
            'headings' => $headings
        ]);
    }

    public function data(Request $request)
    {
        try {

            $contacts = $this->customerContact->getAllContact();

            return DataTables::of($contacts)
                ->addColumn('name', fn($item) => $item->name ?? '-')
                ->addColumn('email', fn($item) => $item->email ?? '-')
                ->addColumn('phone', fn($item) => $item->phone ?? '-')
                ->addColumn('address', function ($item) {
                    return '<span style="width: 130px;" class="text-truncate">' . ($item->address ?? '-') . '</span>';
                })
                ->addColumn('action', function ($item) {
                    return '
                            <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                                <a href="javascript:void(0)" data-bind-contact="' . $item->id . '" class="btn btn-success btn-xs editContact" title="Edit"><i class="ri-pencil-line"></i></a>
                                <a href="javascript:void(0)" onclick="deleteContact(' . $item->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                })
                ->rawColumns(['address', 'action'])
                ->make(true);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {

            $contact = $this->customerContact
                ->setRequest($request)
                ->updateContact();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil mengubah kontak.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        try {

            $this->customerContact
                ->setRequest($request)
                ->destroy();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil menghapus kontak.'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function forms(Request $request)
    {
        $contact = null;

        try {

            if (key_exists('contact_id', $request->all())) {
                $contact = $this->customerContact->getContactById($request->contact_id);
            }

            return $this->setView('admin.components.forms.')->view($request->view, compact('contact'));
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'phone' => !empty($request->input('phone', null)) ? 'numeric|digits_between:9,13' : 'nullable'
        ], [
            'email.required' => 'Email tidak boleh kosong.',
            'email.email' => 'Email harus berupa email yang valid.',
            'name.required' => 'Nama tidak boleh kosong.',
            'phone.numeric' => 'Nomor telepon harus berupa angka.',
            'phone.digits_between' => 'Nomor telepon minimal 9 angka dan maksimal 13 angka.'
        ]);

        try {

            $contact = $this->customerContact->setRequest($request)->createContact();

            return response()->json([
                'message' => 'Kontak berhasil ditambahkan.',
                'data' => [
                    'id' => $contact->email,
                    'text' => $contact->email
                ]
            ], 200);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], 500);
        }
    }
}
