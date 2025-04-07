<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\UserTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ItemDescriptionController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $item = Item::with(['tags', 'images', 'user', 'category'])
            ->where('id', $id)
            ->firstOrFail();

        $item->increment('view_count');
        // Fetch user tags
        $user = Auth::user();
        $userId = $user->id;
        $userTags = UserTag::where('user_id', $userId)->first();

        // Place item tags in an array
        $itemTags = [];
        foreach ($item->tags as $tag) {
            array_push($itemTags, $tag->id);
        }

        // Check if user has existing tags, otherwise insert entry to user_tags
        // if (! $userTags || ! isset($userTags) || empty($userTags)) {
        //     // Get the first available tag or use a default
        //     $defaultTag = ! empty($itemTags) ? $itemTags[0] : $item->tags->first()->id;

        //     UserTag::where('user_id', $userId)->insert([
        //         'user_id' => $userId,
        //         'first_tag' => isset($itemTags[0]) ? $itemTags[0] : $defaultTag,
        //         'first_score' => isset($itemTags[0]) ? 1 : 0,
        //         'second_tag' => isset($itemTags[1]) ? $itemTags[1] : $defaultTag,
        //         'second_score' => isset($itemTags[1]) ? 1 : 0,
        //         'third_tag' => isset($itemTags[2]) ? $itemTags[2] : $defaultTag,
        //         'third_score' => isset($itemTags[2]) ? 1 : 0,
        //     ]);
        // } else {
        //     // Place user tags in an array
        //     $currentUserTags = [
        //         'first' => ['tag' => $userTags->first_tag, 'score' => $userTags->first_score],
        //         'second' => ['tag' => $userTags->second_tag, 'score' => $userTags->second_score],
        //         'third' => ['tag' => $userTags->third_tag, 'score' => $userTags->third_score],
        //     ];

        //     // Increment score if tag is in item tags, otherwise decrement
        //     foreach ($currentUserTags as $key => $entry) {
        //         if (in_array($entry['tag'], $itemTags)) {
        //             UserTag::where('user_id', $userId)->increment($key.'_score');
        //         } else {
        //             UserTag::where('user_id', $userId)->decrement($key.'_score');
        //         }
        //     }

        //     $columnNamePrefix = ['first', 'second', 'third'];
        //     // Replace tag entry if its corresponding score is 0
        //     foreach ($columnNamePrefix as $column) {
        //         $currentScore = DB::table('user_tags')
        //             ->where('user_id', $userId)
        //             ->value($column.'_score');

        //         if ($currentScore <= 0) {
        //             $currentTags = array_column($currentUserTags, 'tag');
        //             $replacementTag = array_diff($itemTags, $currentTags);

        //             if (! empty($replacementTag)) {
        //                 $replacementTag = reset($replacementTag);
        //                 UserTag::where('user_id', $userId)->update([
        //                     $column.'_tag' => $replacementTag,
        //                     $column.'_score' => 1,
        //                 ]);
        //             }
        //         }
        //     }
        // }

        $relatedItems = Item::with(['images', 'tags', 'category', 'user'])
            ->withCount('tradeRequests')
            ->whereIn('status', ['available', 'requested'])
            ->where('user_id', '!=', Auth()->user()->id)
            ->where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->get();

        return Inertia::render('Item/ItemDescription', [
            'item' => $item,
            'relatedItems' => $relatedItems,
        ]);
    }
}
