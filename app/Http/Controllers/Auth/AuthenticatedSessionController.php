<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    private const ROLE_ADMIN = 0;

    private const ROLE_MODERATOR = 1;

    private const ROLE_USER = 2;

    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Instead of regenerating the session which can invalidate other sessions,
        // we'll just regenerate the CSRF token
        $request->session()->regenerateToken();

        $user = Auth::user();

        // Check if user is banned
        if ($user->is_banned) {
            $this->performLogout($request);

            return redirect()->route('login')->withErrors([
                'email' => 'This account has been banned. Please contact support for assistance.',
            ]);
        }

        // Check if user is suspended
        if ($user->is_suspended && $user->suspended_until && Carbon::parse($user->suspended_until)->isFuture()) {
            $this->performLogout($request);

            return redirect()->route('login')->withErrors([
                'email' => sprintf(
                    'This account is suspended until %s. Please try again later.',
                    $user->suspended_until
                ),
            ]);
        }

        // Clear any existing password reset tokens
        if ($user->passToken) {
            $user->passToken()->where('is_set', false)->delete();
        }

        // Define role-based redirects
        $roleRedirects = [
            self::ROLE_ADMIN => route('admin', absolute: false),
            self::ROLE_MODERATOR => route('moderator', absolute: false),
            self::ROLE_USER => route('authenticated.dashboard', absolute: false),
        ];

        return redirect()->intended($roleRedirects[$user->role] ?? $roleRedirects[self::ROLE_USER]);
    }

    /**
     * Perform logout operations
     */
    private function performLogout(LoginRequest $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
