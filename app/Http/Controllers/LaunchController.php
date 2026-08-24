<?php

namespace App\Http\Controllers;

use App\Http\Controllers\PublicSite\HomeController;
use App\Models\LaunchSetting;
use Illuminate\Http\Request;

class LaunchController extends Controller
{
    /**
     * Pre-launch this is the launch wall; once live it serves the normal
     * home page. The launch setting — never the browser — decides which.
     */
    public function wall()
    {
        $settings = LaunchSetting::current();

        if ($settings->isLive()) {
            return app(HomeController::class)->index();
        }

        // Logged-in members have nothing to do on the public countdown -
        // send them to their waiting room instead of looping them back here
        // after every login.
        if (auth()->user() && ! auth()->user()->isAdmin()) {
            return redirect()->route('launch.dashboard');
        }

        return view('launch.wall', [
            'settings' => $settings,
            'pageTitle' => 'Labgoto is coming',
        ]);
    }

    public function dashboard(Request $request)
    {
        $settings = LaunchSetting::current();

        if ($settings->isLive()) {
            return redirect()->route('home');
        }

        return view('launch.dashboard', [
            'settings' => $settings,
            'user' => $request->user(),
        ]);
    }
}
