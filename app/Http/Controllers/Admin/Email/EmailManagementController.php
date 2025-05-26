<?php

namespace App\Http\Controllers\Admin\Email;

use App\Mail\ReplayEmail;
use App\Traits\AdminView;
use App\Facades\PageFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Jobs\SendBulkEmailJob;
use App\Mail\SendBulkEmail;
use Illuminate\Support\Facades\Mail;
use App\Services\EmailMessageService;
use App\Services\CustomerContactService;
use App\Services\EmailMessageSendingService;
use App\Services\EmailSettingService;
use App\Services\InboundEmailService;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;

class EmailManagementController extends Controller
{
    use AdminView;
    public function __construct(
        private CustomerContactService $customerContact,
        private EmailMessageService $emailService,
        private EmailMessageSendingService $emailSendingservice
    ) {
        $this->setView('admin.pages.email-management');
    }

    public function index()
    {
        return $this->view('index');
    }

    public function emailSending()
    {
        return $this->view('list-email-sending');
    }

    public function createMessage()
    {
        return $this->view('create-message');
    }

    public function settingsEmail()
    {
        return $this->view('settings');
    }

    public function updateSettingsEmail(
        Request $request,
        EmailSettingService $emailSettingservice
    ) {

        $request->validate([
            'host' => 'required',
            'port' => 'required|numeric',
            'username' => 'required',
            'password' => 'required',
            'from_email' => 'required|email'
        ], [
            'host.required' => 'Host tidak boleh kosong.',
            'port.required' => 'Port tidak boleh kosong.',
            'port.numeric' => 'Port harus berupa angka.',
            'username.required' => 'Username tidak boleh kosong.',
            'password.required' => 'Password tidak boleh kosong.',
            'from_email.required' => 'Email tidak boleh kosong.',
            'from_email.email' => 'Email harus berupa email yang valid.'
        ]);

        try {

            DB::transaction(function () use ($request, $emailSettingservice) {
                $emailSettingservice
                    ->setRequest($request)
                    ->update();
            });

            Alert::success('Sukses', 'Pesan berhasil dikirim.');
            return redirect()->back();
        } catch (\Throwable $th) {
            Alert::error('Gagal', $th->getMessage());

            return redirect()->back();
        }
    }

    public function sendBulkMessage(
        Request $request
    ) {

        $request->validate([
            'emails' => 'required|array',
            'emails.*' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        ], [
            'emails.required' => 'Email tidak boleh kosong.',
            'emails.array' => 'Email harus berupa array.',
            'emails.*.required' => 'Email tidak boleh kosong.',
            'emails.*.email' => 'Email harus berupa email yang valid.',
            'subject.required' => 'Subject tidak boleh kosong.',
            'message.required' => 'Pesan tidak boleh kosong.'
        ]);

        try {

            $getContact = $this->customerContact->getAllContact(function ($query) use ($request) {
                return $query->whereIn('email', $request->get('emails', []));
            })->pluck('email')->toArray();

            if (count($getContact) > 0) {
                foreach ($getContact as $email) {

                    $contact = $this->customerContact->getContactByEmail($email);

                    DB::transaction(function () use ($request, $email, $contact) {
                        $request->merge([
                            'contact_id' => $contact->id,
                            'from_email' => config('mail.from.address')
                        ]);

                        $this->emailSendingservice->setRequest($request);

                        $message = $this->emailSendingservice->createMessage();
                        SendBulkEmailJob::dispatch($email, $message);
                    });
                }
            }

            Alert::success('Sukses', 'Pesan berhasil dikirim.');
            return redirect()->route('admin.email.email_sending');
        } catch (\Throwable $e) {
            Alert::error('Gagal', $e->getMessage());

            return redirect()->back();
        }
    }

    public function dataEmailSending(Request $request)
    {
        try {

            $messages = $this->emailSendingservice->getAllMessage();

            return DataTables::of($messages)
                ->addColumn('name', fn($message) => $message->contact->name ?? '-')
                ->addColumn('to_email', fn($message) => $message->contact->email ?? '-')
                ->addColumn('from_email', fn($message) => $message->from_email)
                ->addColumn('subject', function ($message) {
                    return '<span style="width: 130px;" class="text-truncate">' . $message->subject . '</span>';
                })
                ->addColumn('status', function ($message) {
                    $color = match ($message->status) {
                        0 => 'warning',
                        1 => 'success',
                        2 => 'danger',
                        default => 'warning'
                    };

                    $textStatus = match ($message->status) {
                        0 => 'Pending',
                        1 => 'Dikirim',
                        2 => 'Gagal',
                        default => 'Pending'
                    };

                    return '<span class="badge badge-sm badge-' . $color . '">' . $textStatus . '</span>';
                })
                ->addColumn('send_by', fn($message) => $message->sendBy->account->nama_lengkap ?? '-')
                ->addColumn('action', function ($message) {
                    return '
                            <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                                <a href="' . route('admin.email.show', $message->id) . '" class="btn btn-success btn-xs" title="Detail"><i class="ri-eye-line"></i></a>
                                <a href="javascript:void(0)" onclick="deleteMessage(' . $message->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                })
                ->rawColumns(['subject', 'status', 'action'])
                ->make(true);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function destroyMessage(
        Request $request
    ) {
        try {

            $this->emailService->deleteMessage($request->id);

            return response()->json([
                'status' => true,
                'message' => 'Berhasil menghapus pesan'
            ], 200);
        } catch (\Exception $err) {
            return response()->json([
                'status' => false,
                'message' => $err->getMessage()
            ], 500);
        }
    }

    public function listMessage(Request $request)
    {
        try {

            $messages = $this->emailService->getAllMessage();

            return DataTables::of($messages)
                ->addColumn('name', fn($message) => $message->name)
                ->addColumn('email', fn($message) => $message->email)
                ->addColumn('subject', function ($message) {
                    return '<span style="width: 130px;" class="text-truncate">' . $message->subject . '</span>';
                })
                ->addColumn('is_read', function ($message) {
                    $color = match ($message->is_read) {
                        0 => 'warning',
                        1 => 'success',
                        default => 'warning'
                    };

                    $textStatus = match ($message->is_read) {
                        0 => 'Belum Dibaca',
                        1 => 'Sudah Dibaca',
                        default => 'Belum Dibaca'
                    };

                    return '<span class="badge badge-sm badge-' . $color . '">' . $textStatus . '</span>';
                })
                ->addColumn('action', function ($message) {
                    return '
                            <div class="d-flex w-full justify-content-center align-items-center" style="gap: 10px">
                                <a href="' . route('admin.email.show', $message->id) . '" class="btn btn-success btn-xs" title="Detail"><i class="ri-eye-line"></i></a>
                                <a href="javascript:void(0)" onclick="deleteMessage(' . $message->id . ')" class="btn btn-danger btn-xs" title="Hapus"><i class="ri-delete-bin-5-line"></i></a>
                            </div>
                        ';
                })
                ->rawColumns(['subject', 'is_read', 'action'])
                ->make(true);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function listContactCustomer()
    {
        return $this->view('list-contact-customer');
    }

    public function show($id)
    {
        try {

            $message = $this->emailService->getMessage($id);

            $this->emailService->setIsRead($id, 1);

            return $this->view('show', compact('message'));
        } catch (\Throwable $e) {

            Alert::error('Gagal', $e->getMessage());

            return redirect()->back();
        }
    }

    public function replayMessage($message_id)
    {
        try {

            $page = PageFacade::page('kontak-kami');
            $email = $page->forms('form-kontak')->get('email');

            $message = $this->emailService->getMessageByMessageId($message_id);

            return $this->view('replay-message', [
                'message' => $message,
                'from_email' => $email['value']
            ]);
        } catch (\Throwable $e) {

            Alert::error('Gagal', $e->getMessage());

            return redirect()->back();
        }
    }

    public function sendReplayMessage(
        Request $request,
        $message_id,
        InboundEmailService $inboundEmail
    ) {
        try {

            $page = PageFacade::page('kontak-kami');
            $emailFrom = $page->forms('form-kontak')->get('email');
            $messageParent = $this->emailService->getMessageByMessageId($message_id);

            // Kirim email ke email kantor
            $test = Mail::to($messageParent->email)
                ->send(new ReplayEmail($messageParent, $emailFrom['value'], $request->message));

            $payload = [
                'to_email' => $messageParent->email,
                'message_id' => $messageParent->message_id,
                'email' => $messageParent->email,
                'name' => $messageParent->name,
                'subject' => 'Re: ' . $messageParent->subject,
                'message' => $request->message
            ];

            DB::transaction(function () use ($inboundEmail, $payload) {

                $inboundEmail->setRequest(new Request($payload));

                $inboundEmail->createMessage();
            });

            Alert::success('Sukses', 'Pesan berhasil dikirim.');
            return redirect()->back();
        } catch (\Throwable $e) {

            Alert::error('Gagal', $e->getMessage());

            return redirect()->back();
        }
    }
}
