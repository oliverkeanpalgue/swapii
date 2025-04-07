<?php

namespace App\Http\Controllers;

use App\Models\ProfilePicture;
use App\Models\Rating;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $ratings = Rating::with('user')
            ->where('rated_user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn ($rating) => [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'description' => $rating->description,
                'is_anonymous' => $rating->is_anonymous,
                'created_at' => $rating->getRawOriginal('created_at'),
                'user' => [
                    'name' => $rating->is_anonymous
                        ? $rating->user->anonymizedName
                        : $rating->user->name,
                ],
            ]);

        // $profilePicture = ProfilePicture::where('user_id', $user->id)->first();
        // if ($profilePicture) {
        //     $user->profile_picture = Storage::url($profilePicture->image_path);
        // }

        // Debug the ratings
        Log::info('Ratings data:', ['ratings' => $ratings->toArray()]);

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            // 'hasVerifiedDetails' => $request->user()->verifDetails()->exists(),
            'ratings' => $ratings,
            'averageRating' => Rating::where('rated_user_id', $user->id)->avg('rating'),
            'ratingsCount' => Rating::where('rated_user_id', $user->id)->count(),
            'activeTab' => $request->input('activeTab', 0),
        ]);
    }

    public function update(Request $request)
    {
        try {
            // Validate profile picture
            $validatedData = $request->validate([
                'profile_picture' => ['nullable', 'image', 'max:2048'],
            ]);

            $user = Auth::user();

            // Handle profile picture if uploaded
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');

                // Store the new profile picture
                $path = $file->store('profile-pictures', 'public');

                // Delete the old profile picture
                if ($user->profile_image) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                // Update the user record
                $user->update(['profile_image' => $path]);
            }

            return back()->with('message', 'Profile picture updated successfully');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    // public function verify(Request $request)
    // {
    //     // Validate the request
    //     $validatedData = $request->validate([
    //         'image' => 'required|image|max:2048', // Image file validation
    //         'id_image' => 'required|image|max:2048', // ID image file validation
    //         'school' => 'required|string',
    //         'other_school' => 'nullable|string|required_if:school,Other',
    //     ]);

    //     // Store the uploaded files
    //     $imagePath = $request->file('image')->store('verification_images', 'public');
    //     $idImagePath = $request->file('id_image')->store('verification_id_images', 'public');

    //     // Determine the school name
    //     $schoolName = $request->school == 'Other' ? $request->other_school : $request->school;

    //     // Create verification details
    //     $user = $request->user();
    //     $user->verifDetails()->create([
    //         'image' => $imagePath,
    //         'id_image' => $idImagePath,
    //         'school' => $schoolName,
    //     ]);

    //     return back()->with([
    //         'status' => 'Verification details submitted successfully',
    //         'verificationStatus' => 'pending',
    //         'hasVerifiedDetails' => true,
    //     ]);
    // }

    public function userRatings(Request $request)
    {
        $user = $request->user();
        $ratings = Rating::with('user')
            ->where('rated_user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn ($rating) => [
                'id' => $rating->id,
                'rating' => $rating->rating,
                'description' => $rating->description,
                'is_anonymous' => $rating->is_anonymous,
                'created_at' => $rating->getRawOriginal('created_at'),
                'user' => [
                    'name' => $rating->is_anonymous
                        ? $rating->user->anonymizedName
                        : $rating->user->name,
                ],
            ]);

        return Inertia::render('Profile/Partials/ProfileRating', [
            'ratings' => $ratings,
            'averageRating' => number_format($user->averageRating(), 2),
            'ratingsCount' => $user->ratings()->count(),
        ]);
    }

    private function anonymizeName($name)
    {
        if (! $name || $name === 'Unknown User') {
            return $name;
        }

        $parts = explode(' ', $name);
        if (count($parts) < 2) {
            return str_repeat('*', strlen($name));
        }

        $firstName = $parts[0];
        $lastName = end($parts);

        $firstAnon = $firstName[0].str_repeat('*', strlen($firstName) - 1);
        $lastAnon = $lastName[0].str_repeat('*', strlen($lastName) - 1);

        return $firstAnon.' '.$lastAnon;
    }
}
