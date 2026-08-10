<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;

class LegalController extends Controller
{
    public function privacyPolicy()
    {
        $pageTitle = 'Privacy Policy';

        return view('legal.privacy-policy', compact('pageTitle'));
    }

    public function termsOfService()
    {
        $pageTitle = 'Terms of Service';

        return view('legal.terms-of-service', compact('pageTitle'));
    }

    public function disclaimer()
    {
        $pageTitle = 'Disclaimer';

        return view('legal.disclaimer', compact('pageTitle'));
    }

    public function accessibilityStatement()
    {
        $pageTitle = 'Accessibility Statement';

        return view('legal.accessibility-statement', compact('pageTitle'));
    }

    public function cookiePolicy()
    {
        $pageTitle = 'Cookie Policy';

        return view('legal.cookie-policy', compact('pageTitle'));
    }
}
