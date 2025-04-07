<?php

namespace App\Http\Controllers;

use App\Models\ProfilePicture;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AdminProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $profilePicture = ProfilePicture::where('user_id', $user->id)->first();

        return Inertia::render('Admin/Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function update(Request $request)
    {
        try {
            // Validate all inputs
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
                }

                // Update or create profile picture
                ProfilePicture::updateOrCreate(
                    ['user_id' => $user->id],
                    ['image_path' => $path]
                );
            }

            return back()->with('success', 'Profile updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
