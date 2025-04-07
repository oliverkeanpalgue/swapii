<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->firstOrFail();

        if ($user->role === 0) {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status == Password::RESET_LINK_SENT) {
                return back()->with('status', __($status));
            }

            throw ValidationException::withMessages([
                'email' => [trans($status)],
            ]);
        } else {
            $existingToken = $user->PassToken()->first();

            if ($existingToken) {
                // Update the existing token
                $existingToken->update(['token' => Str::random(8)]);

                return to_route('login')->with('success', 'You have previously sent a request to the admin, please wait and check your email for your temporary token.');
            } else {
                // Create a new token
                $user->PassToken()->create(['token' => Str::random(8)]);

                return to_route('login')->with('success', 'We sent a request to the admin, please check your email for your token once processed!');
            }
        }
    }
}
