<?php

namespace App\Http\Controllers;

use App\Models\ProfilePicture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ModeratorProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('Moderator/Profile/Edit', [
            'status' => session('status'),
        ]);
    }

    public function update(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$request->user()->id],
                'profile_picture' => ['nullable', 'image', 'max:2048'],
            ]);

            $user = $request->user();

            // Update user information
            $user->fill([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
            ]);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            // Handle profile picture if uploaded
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $path = $file->store('profile-pictures', 'public');

                // Delete old profile picture if exists
                $oldProfilePicture = ProfilePicture::where('user_id', $user->id)->first();
                if ($oldProfilePicture) {
                    Storage::disk('public')->delete($oldProfilePicture->image_path);
                    $oldProfilePicture->delete();
                }

                // Create new profile picture
                ProfilePicture::create([
                    'user_id' => $user->id,
                    'image_path' => $path,
                ]);
            }

            return back()->with('message', 'Profile updated successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete profile picture if exists
        $profilePicture = ProfilePicture::where('user_id', $user->id)->first();
        if ($profilePicture) {
            Storage::disk('public')->delete($profilePicture->image_path);
            $profilePicture->delete();
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
