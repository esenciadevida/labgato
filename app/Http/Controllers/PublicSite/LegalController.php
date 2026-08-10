<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;

class LegalController extends Controller
{
    public function privacy()
    {
        return view('legal.privacy-policy');
    }

    public function terms()
    {
        return view('legal.terms-of-service');
    }

    public function disclaimer()
    {
        return view('legal.disclaimer');
    }

    public function accessibility()
    {
        return view('legal.accessibility-statement');
    }
}
