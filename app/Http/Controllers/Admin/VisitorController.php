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
        // Builder (bukan Collection) → DataTables pagination/search jadi SQL server-side,
        // tak lagi memuat seluruh tabel visitors ke memori.
        return datatables()
            ->of(\App\Models\Visitor::query()->latest())
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
            // Kolom virtual tanggal/waktu berasal dari created_at → petakan search & order
            // ke kolom nyata agar tak error di mode server-side (Builder).
            ->filterColumn('tanggal', fn ($q, $k) => $q->whereRaw("DATE_FORMAT(created_at, '%d %M %Y') LIKE ?", ["%{$k}%"]))
            ->filterColumn('waktu', fn ($q) => $q)
            ->orderColumn('tanggal', 'created_at $1')
            ->orderColumn('waktu', 'created_at $1')
            ->rawColumns(['url', 'page', 'user_agent'])
            ->make(true);
    }
}
