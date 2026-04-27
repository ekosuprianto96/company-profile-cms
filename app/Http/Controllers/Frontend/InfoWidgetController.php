<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\PageFacade;
use Illuminate\Http\Request;
use App\Services\PageService;
use App\Http\Controllers\Controller;
use App\Services\RekomendasiKavlingService;

class InfoWidgetController extends Controller
{
    public function redirectLink(Request $request, $slug) {
        
        $pageLayanan  = app(PageService::class)->page('detail-layanan');
        $phone = $pageLayanan->forms('list-rekomedasi-kavling')->get('phone');
        $message = $pageLayanan->forms('list-rekomedasi-kavling')->get('message_form_whatsapp');

        $getKavling = app(RekomendasiKavlingService::class)
            ->findKavlingBySlug($slug);

        if(!$getKavling) return abort('Halaman tidak ditemukan', 404);
    
        $message = merge_tags($message['value'], [
            'title' => $getKavling->title
        ]);

        return redirect()->away('https://wa.me/' . replacePhoneNumber($phone['value']) . '?text=' . urlencode($message));
    }
}
