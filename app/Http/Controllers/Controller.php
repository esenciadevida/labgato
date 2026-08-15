<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

abstract class Controller
{
    protected function redirectToListing(string $fallbackRoute, array $parameters = []): RedirectResponse
    {
        $intended = session('intended_listing_url');

        if (is_string($intended) && $intended !== '') {
            return redirect()->to($intended);
        }

        return redirect()->route($fallbackRoute, $parameters);
    }
}
