<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PostRejectReason;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ModPostManagementController extends Controller
{
    public function index()
    {
        $pending = Item::with(['tags', 'images', 'user', 'category'])->where('approval', 'pending')->latest()->paginate(10);
        $approved = Item::with(['tags', 'images', 'user', 'category'])->where('approval', 'approved')->latest()->paginate(10);
        $rejected = Item::with(['images', 'user', 'category',
            'rejectReasons' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
        ])->where('approval', 'rejected')->latest()->paginate(10);

        return Inertia::render('Moderator/Posts/PostManagement', [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
        ]);
    }

    public function acceptPost(Item $item)
    {
        try {
            $item->update([
                'approval' => 'approved',
                'status' => 'available',
            ]);

            $item->log()->create([
                'user_id' => Auth::user()->id,
                'action' => 'accept',
            ]);

        } catch (\Exception $e) {
            return back()->withErrors([
                'message' => 'Error accepting the post: '.$e->getMessage(),
            ]);
        }

        return to_route('mod.post.index');
    }

    public function rejectPost(Item $item, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $item->update(['approval' => 'rejected']);
            PostRejectReason::create([
                'item_id' => $item->id,
                'reason' => $request->reason,
            ]);

            $item->log()->create([
                'user_id' => Auth::user()->id,
                'action' => 'reject',
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'message' => 'Error rejecting the post: '.$e->getMessage(),
            ]);
        }

        return to_route('mod.post.index');
    }

    public function show($id)
    {
        $item = Item::with(['tags', 'images', 'user', 'category'])->where('id', $id)->first();

        return Inertia::render('Moderator/Posts/PostDescription', ['item' => $item]);
    }
}
