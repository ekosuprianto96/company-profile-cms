<?php

namespace App\Http\Controllers\Frontend\Pages;

use App\Traits\FrontView;
use App\Facades\PageFacade;
use Illuminate\Http\Request;
use App\Mail\ContactFormMail;
use App\Services\InformasiService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Services\CustomerContactService;
use App\Services\EmailMessageService;
use RealRashid\SweetAlert\Facades\Alert;

class KontakController extends Controller
{
    use FrontView;

    public function index()
    {
        $data['page'] = PageFacade::page('kontak-kami');
        return $this->view('kontak-kami', $data);
    }

    public function sendingInquiry(
        Request $request,
        CustomerContactService $customerContactService
    ) {
        try {

            $request->validate([
                'name' => 'required',
                'email' => 'required|email',
                'phone' => 'numeric|digits_between:9,13',
                'paket' => 'required',
                'luas_tanah' => 'required'
            ], [
                'name.required' => 'Nama tidak boleh kosong.',
                'email.required' => 'Email tidak boleh kosong.',
                'email.email' => 'Email harus berupa email yang valid.',
                'phone.numeric' => 'Nomor telepon harus berupa angka.',
                'phone.digits_between' => 'Nomor telepon minimal 9 angka dan maksimal 13 angka.',
                'paket.required' => 'Paket tidak boleh kosong.',
                'luas_tanah.required' => 'Luas tanah tidak boleh kosong.'
            ]);

            $page = PageFacade::page('kontak-kami');
            $phone = $page->forms('form-kontak')->get('phone');
            $message = $page->forms('form-kontak')->get('message_form_whatsapp');

            $message = merge_tags($message['value'], [
                'name' => $request->name,
                'email' => $request->email ?? '-',
                'luas_tanah' => $request->luas_tanah,
                'paket' => $request->paket,
                'alamat' => $request->address
            ]);

            DB::transaction(function () use ($request, $customerContactService) {
                // set request
                $customerContactService->setRequest($request);

                // check exists contact
                $existsContact = $customerContactService->getContactByEmail();

                if (!$existsContact) {
                    // create contact
                    $customerContactService->createContact();
                }
            });

            return redirect()->away('https://wa.me/' . replacePhoneNumber($phone['value']) . '?text=' . urlencode($message));
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }
    }

    public function sendingEmail(
        Request $request,
        CustomerContactService $customerContactService,
        EmailMessageService $emailService
    ) {
        try {

            // Validasi input dari form
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'numeric|digits_between:9,13',
                'subject' => 'required|string|max:255',
                'message' => 'required|string',
            ], [
                'name.required' => 'Nama tidak boleh kosong.',
                'email.required' => 'Email tidak boleh kosong.',
                'email.email' => 'Email harus berupa email yang valid.',
                'phone.numeric' => 'Nomor telepon harus berupa angka.',
                'phone.digits_between' => 'Nomor telepon minimal 9 angka dan maksimal 13 angka.',
                'subject.required' => 'Subjek tidak boleh kosong.',
                'message.required' => 'Pesan tidak boleh kosong.',
            ]);

            $page = PageFacade::page('kontak-kami');
            $email = $page->forms('form-kontak')->get('email');
            $messageID = time() . '@' . gethostname();

            // Kirim email ke email kantor
            $test = Mail::to($email['value'])
                ->send(new ContactFormMail($validatedData, $messageID));

            DB::transaction(function () use ($request, $customerContactService, $emailService, $email, $messageID) {

                $request->merge([
                    'to_email' => $email['value'],
                    'message_id' => $messageID
                ]);

                // proses insert message
                $emailService->setRequest($request);

                // create message
                $emailService->createMessage();

                // set request
                $customerContactService->setRequest($request);

                // check exists contact
                $existsContact = $customerContactService->getContactByEmail();

                if (!$existsContact) {
                    // create contact
                    $customerContactService->createContact();
                }
            });

            Alert::success('Sukses', 'Terimakasih sudah mengirimkan pesan kepada kami, Pesan anda akan segera kami balas secepatnya.');
            return redirect()->back();
        } catch (\Exception $e) {
            Alert::error('Error', $e->getMessage());

            return redirect()->back();
        }
    }
}
