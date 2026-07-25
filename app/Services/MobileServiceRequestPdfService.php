<?php

namespace App\Services;

use App\Models\MobileServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class MobileServiceRequestPdfService
{
    public function __construct(
        protected ProposalPdfService $proposalPdf
    ) {}

    /**
     * Dokumen proposal untuk sebuah pengajuan. Bila pengajuan berasal dari form
     * builder (punya proposal), pakai data proposal; bila pengajuan lama, data
     * dibentuk dari kolom pengajuan. Keduanya memakai format dokumen yang sama.
     */
    public function generate(MobileServiceRequest $serviceRequest): string
    {
        $serviceRequest->loadMissing(['user', 'service', 'proposal']);

        $data = $serviceRequest->proposal
            ? $this->proposalPdf->data($serviceRequest->proposal)
            : $this->proposalPdf->dataFromServiceRequest($serviceRequest);

        return Pdf::loadView('admin.pdf.proposal', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->output();
    }
}
