<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Models\MobileService;
use App\Models\Proposal;
use App\Services\ProposalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProposalController extends ApiController
{
    public function __construct(
        protected ProposalService $proposals
    ) {}

    /** Kirim isian form → tersimpan sebagai satu proposal. */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'mobile_service_id' => ['required', 'integer', 'exists:mobile_services,id'],
                'answers' => ['required', 'array'],
            ]);

            $service = MobileService::where('is_active', true)->findOrFail($validated['mobile_service_id']);
            $proposal = $this->proposals->submit($request->user(), $service, $validated['answers']);

            return $this->success(['proposal' => $this->payload($proposal)], 'Pengajuan berhasil dikirim.');
        } catch (ValidationException $error) {
            return response()->json([
                'success' => false,
                'message' => 'Ada isian yang belum sesuai.',
                'errors' => $error->errors(),
            ], 422);
        } catch (\Exception $error) {
            $code = (int) $error->getCode();

            return $this->error($error->getMessage(), $code >= 400 && $code < 600 ? $code : 500);
        }
    }

    public function index(Request $request)
    {
        $proposals = Proposal::with(['service:id,title,slug,icon', 'serviceRequest'])
            ->where('mobile_user_id', $request->user()->id)
            ->latest()
            ->get();

        return $this->success([
            'proposals' => $proposals->map(fn ($p) => $this->payload($p, false))->values()->all(),
        ], 'OK');
    }

    public function show(Request $request, int $id)
    {
        $proposal = Proposal::with(['service:id,title,slug,icon', 'serviceRequest'])
            ->where('mobile_user_id', $request->user()->id)
            ->find($id);

        if (! $proposal) {
            return $this->error('Proposal tidak ditemukan.', 404);
        }

        return $this->success(['proposal' => $this->payload($proposal)], 'OK');
    }

    /** Unggah berkas untuk field image/file sebelum submit. Mengembalikan path. */
    public function upload(Request $request)
    {
        try {
            $request->validate([
                'file' => ['required', 'file', 'max:20480'],
            ]);

            $file = $request->file('file');
            $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('proposals', $name, 'public');

            return $this->success([
                'path' => $path,
                'url' => storageUrl($path),
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ], 'Berkas terunggah.');
        } catch (ValidationException $error) {
            return response()->json(['success' => false, 'message' => 'Berkas tidak valid.', 'errors' => $error->errors()], 422);
        } catch (\Throwable $th) {
            Log::error('Proposal upload error: ' . $th->getMessage());

            return $this->error('Gagal mengunggah berkas.', 500);
        }
    }

    private function payload(Proposal $proposal, bool $withDetail = true): array
    {
        $data = [
            'id' => $proposal->id,
            'proposal_number' => $proposal->proposal_number,
            'status' => $proposal->status,
            'service' => $proposal->service ? [
                'id' => $proposal->service->id,
                'title' => $proposal->service->title,
                'slug' => $proposal->service->slug,
                'icon' => $proposal->service->icon,
            ] : null,
            'price_items' => $proposal->price_items ?? [],
            'total_amount' => (int) $proposal->total_amount,
            'submitted_at' => optional($proposal->submitted_at)?->toISOString(),
            // Order Layanan yang menaungi proposal (pemegang status & pembayaran).
            'service_request' => $proposal->serviceRequest ? [
                'id' => $proposal->serviceRequest->id,
                'status' => $proposal->serviceRequest->status,
                'payment_status' => $proposal->serviceRequest->payment_status,
                'total_amount' => (int) $proposal->serviceRequest->total_amount,
                'transaction_code' => $proposal->serviceRequest->transaction_code,
            ] : null,
        ];

        if ($withDetail) {
            // Jawaban siap-tampil (label + nilai + berkas) dari snapshot schema.
            $data['answers'] = collect($this->proposals->readableAnswers($proposal))
                ->map(fn ($row) => [
                    'label' => $row['label'],
                    'type' => $row['type'],
                    'value' => $row['value'],
                    'files' => $row['files'] ?? [],
                ])
                ->values()
                ->all();
            $data['admin_note'] = $proposal->admin_note;
        }

        return $data;
    }
}
