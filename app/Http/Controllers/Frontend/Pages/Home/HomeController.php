<?php

namespace App\Http\Controllers\Frontend\Pages\Home;

use App\Facades\PageFacade;
use App\Http\Controllers\Controller;
use App\Services\BannerService;
use App\Services\GalleryService;
use App\Services\LayananService;
use App\Services\PackageService;
use App\Traits\FrontView;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use FrontView;

    public function __construct() {}

    public function index(): \Illuminate\Contracts\View\View
    {
        $data['page'] = PageFacade::page('home');
        return $this->view('home', $data);
    }

    public function packageRedirect(
        ?string $id = null,
        PackageService $packageService,
    ) {

        try {

            if (empty($id)) return abort(404);

            $package = $packageService->findPaket($id);

            if (empty($package)) return abort(404);

            $page = PageFacade::page('home');
            $forms = $page->forms('list-package');
            $phone = $forms->get('phone');
            $message = $forms->get('message_form_whatsapp');

            // check tag features
            $template = '';
            if (str_contains($message['value'], '{{ features }}')) {

                foreach ($package['features'] as $feature) {
                    $template .= '- ' . $feature . "\r\n";
                }
            }

            // check tag price
            $price = '';
            if (str_contains($message['value'], '{{ price }}')) {
                $price = 'Rp. ' . number_format($package['price'], 0, ',', '.');
            }

            $message = merge_tags($message['value'], [
                'name' => $package['name'],
                'price' =>  $price,
                'features' => $template,
                'description' => $package['description'],
                'id' => $package['id'],
            ]);

            return redirect()->away('https://wa.me/' . replacePhoneNumber($phone['value']) . '?text=' . urlencode($message));
        } catch (\Throwable $th) {
            return abort(500, $th->getMessage());
        }
    }
}
