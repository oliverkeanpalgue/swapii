<?php

namespace App\Http\Controllers;

use App\Jobs\SuggestionEmailSentJob;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Inertia\Inertia; // Import Feedback model

class FeedbackController extends Controller
{
    public function index()
    {
        return Inertia::render('Feedback/AddFeedback');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'feedback' => 'required',
        ], [
            'title.required' => 'Please select a category',
            'feedback.required' => 'Please provide your feedback',
        ]);

        $user = Auth::user();
        $user->feedback()->create($validated);
        dispatch(new SuggestionEmailSentJob($user->email));

        return redirect()->route('feedback.index')->with('success', 'Thank you for your feedback!');
    }
}
