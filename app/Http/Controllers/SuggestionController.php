<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Inertia\Inertia;

class SuggestionController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::with('user')->get()->map(fn ($feedback) => [
            'title' => $feedback->title,
            'feedback' => $feedback->feedback,
            'user' => $feedback->user->name ?? 'Unknown', // Using null coalescing to handle cases where user might not exist
            'created_at' => $feedback->created_at,
        ]);

        return Inertia::render('Admin/Suggestion/SuggestionManagement', ['feedbacks' => $feedbacks]);

    }
}
