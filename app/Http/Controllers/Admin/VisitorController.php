<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\VisitorService;
use App\Http\Controllers\Controller;

class VisitorController extends Controller
{
    public function __construct(
        private VisitorService $visitor
    ) {}

    public function data(Request $request)
    {
        return datatables()
            ->of($this->visitor->getAllVisitor())
            ->addColumn('tanggal', fn($item) => $item->created_at->format('d F Y'))
            ->addColumn('waktu', fn($item) => Str::limit($item->created_at->diffForHumans(), 30))
            ->addColumn('user_agent', function ($item) {
                $userAgent = Str::limit($item->user_agent, 30);
                return '<span title="' . $item->user_agent . '">' . $userAgent . '</span>';
            })
            ->addColumn('url', function ($item) {
                $url = Str::limit($item->url, 40);
                return '<a title="' . $item->url . '" href="' . ($item->url) . '" target="_blank">' . $url . '</a>';
            })
            ->addColumn('page', function ($item) {
                $page = Str::limit($item->page, 40);
                return '<span title="' . $item->page . '">' . $page . '</span>';
            })
            ->rawColumns(['url', 'page', 'user_agent'])
            ->make(true);
    }
}
