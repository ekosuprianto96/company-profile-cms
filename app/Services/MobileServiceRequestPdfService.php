<?php

namespace App\Services;

use App\Models\MobileServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class MobileServiceRequestPdfService
{
    public function generate(MobileServiceRequest $serviceRequest): string
    {
        $serviceRequest->loadMissing(['user', 'service', 'needType', 'budgetOption', 'handledBy']);

        return Pdf::loadView('admin.pdf.mobile-service-request-proposal', [
            'serviceRequest' => $serviceRequest,
            'proposalNumber' => $serviceRequest->transaction_code_label,
            'generatedAt' => now(),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->output();
    }
}
