<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $pageTitle = 'Professional Laboratory Toolkit';
        $metaDescription = 'LabGato provides laboratory professionals with essential tools, calculators, reference ranges, SOPs, and knowledge resources.';

        return view('public.home', compact('pageTitle', 'metaDescription'));
    }
}
