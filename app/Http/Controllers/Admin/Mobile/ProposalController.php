<?php

namespace App\Http\Controllers\Admin\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Services\ProposalPdfService;
use App\Services\ProposalService;
use App\Traits\AdminView;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    use AdminView;

    protected $statusCode = 500;

    public function __construct(
        protected ProposalService $proposals,
        protected ProposalPdfService $pdf,
    ) {
        $this->setView('admin.pages.mobile.proposals');
    }

    public function index()
    {
        return $this->view('index', [
            'proposals' => Proposal::with(['user', 'service', 'serviceRequest'])->latest()->paginate(25),
        ]);
    }

    public function show(int $id)
    {
        $proposal = Proposal::with(['user', 'service', 'serviceRequest'])->findOrFail($id);

        return $this->view('show', [
            'proposal' => $proposal,
            'answers' => $this->proposals->readableAnswers($proposal),
            'statuses' => config('form_builder.proposal_statuses', []),
        ]);
    }

    /** Tampilkan PDF di browser (preview). */
    public function pdf(int $id)
    {
        $proposal = Proposal::with(['user', 'service', 'serviceRequest'])->findOrFail($id);

        return response($this->pdf->generate($proposal), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $proposal->proposal_number . '.pdf"',
        ]);
    }

    public function download(int $id)
    {
        $proposal = Proposal::with(['user', 'service', 'serviceRequest'])->findOrFail($id);

        return response($this->pdf->generate($proposal), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $proposal->proposal_number . '.pdf"',
        ]);
    }

    public function updateStatus(Request $request, int $id)
    {
        try {
            $validated = $request->validate([
                'status' => ['required', 'in:submitted,in_review,approved,rejected,cancelled'],
                'admin_note' => ['nullable', 'string', 'max:2000'],
            ]);

            \Illuminate\Support\Facades\DB::transaction(function () use ($id, $validated) {
                $proposal = Proposal::with('serviceRequest')->findOrFail($id);
                $proposal->update($validated);

                // Propagasi keputusan ke Order Layanan (SR) yang menaungi proposal,
                // supaya badge & tombol "Bayar Sekarang" di mobile ikut benar.
                // Hanya untuk state terminal (rejected/cancelled) dan hanya bila SR
                // belum lanjut/terbayar — agar order yang sudah dibayar tak dibatalkan.
                $sr = $proposal->serviceRequest;
                if ($sr && in_array($validated['status'], ['rejected', 'cancelled'], true)
                    && $sr->payment_status !== 'paid'
                    && in_array($sr->status, ['draft', 'waiting_payment', 'waiting_transfer', 'payment_challenge', 'pending'], true)) {
                    $sr->update([
                        'status' => $validated['status'],
                        'rejection_reason' => $validated['status'] === 'rejected' ? ($validated['admin_note'] ?? $sr->rejection_reason) : $sr->rejection_reason,
                        'admin_note' => $validated['admin_note'] ?? $sr->admin_note,
                    ]);
                }
            });

            $this->statusCode = 200;

            return response()->json(['message' => 'Status proposal diperbarui.'], $this->statusCode);
        } catch (\Exception $error) {
            return response()->json(['message' => $error->getMessage()], $this->statusCode);
        }
    }
}
