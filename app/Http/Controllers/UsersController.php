<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index()
    {
        $users = User::where('role', 2)
            ->with('profilePicture')
            ->get()
            ->map(function ($user) {

                return $user;
            });

        $moderators = User::where('role', 1)
            ->with('profilePicture')
            ->get()
            ->map(function ($user) {
                return $user;
            });

        return Inertia::render('Admin/User/UserManagement', [
            'users' => $users,
            'moderators' => $moderators,
        ]);
    }

    public function setModerator(User $user)
    {
        $user->update(['role' => 1]);

        return redirect()->back()->with('success', 'User set as moderator successfully');
    }

    public function setUser(User $user)
    {
        $user->update(['role' => 2]);

        return redirect()->back()->with('success', 'Moderate set to user successfully');
    }
}
