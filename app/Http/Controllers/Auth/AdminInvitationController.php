<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminWelcomeEmail;
use App\Models\AdminInvitation;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Throwable;

class AdminInvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = AdminInvitation::findByPlainToken($token);

        if (! $invitation || ! $invitation->isPending()) {
            return redirect()->route('home')
                ->with('error', 'This invitation link is invalid, has expired, or was already used.');
        }

        return view('auth.admin-invitation', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function accept(Request $request, string $token)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(config('labgoto.security.password_min_length', 8))->letters()->numbers()],
        ]);

        $invitation = AdminInvitation::findByPlainToken($token);

        if (! $invitation || ! $invitation->isPending()) {
            return redirect()->route('home')
                ->with('error', 'This invitation link is invalid, has expired, or was already used.');
        }

        if (User::where('email', $invitation->email)->exists()) {
            $invitation->update(['accepted_at' => now()]);

            return redirect()->route('login')
                ->with('error', 'An account with this email already exists. Please sign in instead.');
        }

        $administrator = DB::transaction(function () use ($invitation, $validated) {
            $user = User::create([
                'name' => $invitation->name,
                'email' => $invitation->email,
                'password' => $validated['password'],
                'user_type' => 'admin',
                'is_active' => true,
                'password_set_at' => now(),
            ]);

            $user->roles()->attach($invitation->role_id);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        app(AuditLogService::class)->logWithSubject(
            'administrators.invitation_accepted',
            $administrator,
            ['role_id' => $invitation->role_id],
            $request
        );

        try {
            Mail::to($administrator->email)->send(new AdminWelcomeEmail(
                $administrator,
                $invitation->role?->name ?? 'Administrator',
                route('login'),
            ));
        } catch (Throwable $e) {
            Log::warning('Admin welcome email failed for '.$administrator->email.': '.$e->getMessage());
        }

        Auth::login($administrator);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Welcome to the team! Your administrator account is ready.');
    }
}
