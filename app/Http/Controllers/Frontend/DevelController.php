<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DevelController extends Controller
{
    public function devel()
    {
        return view('frontend.pages.devel');
    }
}
