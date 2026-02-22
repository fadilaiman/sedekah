<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MagicLinkToken;
use App\Models\User;
use App\Notifications\MagicLinkNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MagicLinkController extends Controller
{
    // Show the admin login form
    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/AdminLogin');
    }

    // Send magic-link email
    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->email;
        $throttleKey = 'magic-link:' . $email;

        // Rate limit: 5 attempts per 15 minutes
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        // Only allow existing admin users
        $user = User::where('email', $email)->first();

        if (! $user || ! $user->isAdmin()) {
            // Always show same message to prevent email enumeration
            RateLimiter::hit($throttleKey, 900);
            return back()->with('status', 'If that email is registered, you will receive a login link.');
        }

        RateLimiter::hit($throttleKey, 900);

        // Delete old unused tokens for this email
        MagicLinkToken::where('email', $email)
            ->whereNull('used_at')
            ->delete();

        // Create new token (valid for 30 minutes)
        $token = Str::random(64);
        MagicLinkToken::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addMinutes(30),
        ]);

        // Send email
        $user->notify(new MagicLinkNotification($token));

        return back()->with('status', 'If that email is registered, you will receive a login link.');
    }

    // Verify token and log in user
    public function verifyToken(Request $request, string $token): RedirectResponse
    {
        $record = MagicLinkToken::where('token', $token)->first();

        if (! $record || ! $record->isValid()) {
            return Redirect::route('admin.login')
                ->withErrors(['token' => 'This login link is invalid or has expired.']);
        }

        $user = User::where('email', $record->email)->first();

        if (! $user || ! $user->isAdmin()) {
            return Redirect::route('admin.login')
                ->withErrors(['token' => 'This login link is invalid.']);
        }

        // Mark token as used
        $record->update(['used_at' => now()]);

        // Log the user in
        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return Redirect::route('admin.dashboard');
    }

    // Log out
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('admin.login');
    }
}
