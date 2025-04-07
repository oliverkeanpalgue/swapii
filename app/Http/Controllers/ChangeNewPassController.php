<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeNewPassController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'password' => 'required|confirmed',
            'password_confirmation' => 'required',
        ]);

        try {
            $user = Auth::user();
            $user->update([
                'password' => bcrypt($request->password),
            ]);

            $user->PassToken()->delete();

            return to_route('dashboard')->with('success', 'Password reset successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to reset password. Please try again.']);
        }
    }
}
