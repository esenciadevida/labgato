<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('public.home');
    }
}
