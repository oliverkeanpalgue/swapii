<?php

namespace App\Http\Controllers;

use App\Http\Requests\postItemRequest;
use App\Jobs\ModPostJob;
use App\Models\Item;
use App\Models\Tag;
use App\Models\TemporaryImage; // Ensure the Tag model is imported
use App\Services\ContentModerationService;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PostController extends Controller
{
    protected $contentModerationService;

    public function __construct(ContentModerationService $contentModerationService)
    {
        $this->contentModerationService = $contentModerationService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $items = Item::with([
            'category',
            'images',
            'rejectReasons' => function ($query) {
                $query->orderBy('created_at', 'desc');
            },
        ])
            ->where('user_id', $user->id)
            ->withCount(['tradeRequests' => function ($query) {
                $query->whereIn('status', ['pending', 'processing']);
            }])
            ->latest()
            ->get()
            ->groupBy(function ($item) {
                return match (true) {
                    $item['approval'] === 'pending' => 'pending',
                    $item['approval'] === 'rejected' => 'rejected',
                    $item['status'] === 'unlisted' => 'unlisted',
                    $item['status'] === 'traded' => 'traded',
                    default => 'active'
                };
            });

        return Inertia::render('Post/ManagePost', [
            'activeItems' => ['data' => $items->get('active') ?? collect()],
            'pendingItems' => ['data' => $items->get('pending') ?? collect()],
            'unlistedItems' => ['data' => $items->get('unlisted') ?? collect()],
            'tradedItems' => ['data' => $items->get('traded') ?? collect()],
            'rejectedItems' => ['data' => $items->get('rejected') ?? collect()],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */


    public function store(postItemRequest $request)
    {
        // Check for profanity in input fields
        $user = Auth::user();
        $profanityCheck = $this->contentModerationService->checkMultipleFields([
            'item_name' => $request->item_name,
            'item_description' => $request->item_description,
            'preferred_item' => $request->preferred_item,
        ]);




        if ($profanityCheck['has_profanity']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Inappropriate content detected in '.$profanityCheck['field'].'. Please revise your input.');
        }

        $item = Item::create([
            'category_id' => $request->item_category,
            'user_id' => Auth::user()->id,
            'item_description' => $request->item_description,
            'item_name' => $request->item_name,
            'preferred_items' => $request->preferred_item,
        ]);

        // Optimize tag attachment by fetching existing tags in one query
        $existingTags = Tag::whereIn('code', collect($request->item_tags)->pluck('code'))->get()->keyBy('code');
        $item->tags()->attach($existingTags->pluck('id')->toArray());

        // Use a single query to get all temporary images
        $tempImages = TemporaryImage::whereIn('folder', $request->item_image)->get();

        // Prepare an array for bulk insert of images
        $imagesToInsert = [];
        foreach ($tempImages as $img) {
            $newImagePath = 'uploadedItemsImages/'.$img->file;
            Storage::disk('public')->copy('images/tmp/'.$img->folder.'/'.$img->file, $newImagePath);
            $imagesToInsert[] = ['image' => $newImagePath, 'item_id' => $item->id];
            Storage::disk('public')->deleteDirectory('images/tmp/'.$img->folder);
        }

        // Bulk insert images to reduce the number of queries
        if (! empty($imagesToInsert)) {
            $item->images()->insert($imagesToInsert);
        }
        // dispatch(new ModPostJob($item->item_name, $item->id));

        $preferredItem = $request->preferred_item;
        $nounsArray = $this->extractNouns($preferredItem);

        foreach ($nounsArray as $noun) {
            $preference = $item->preferences()->where('preference', $noun)->first();
            if ($preference) {
                // Increment the counter if the noun already exists
                $preference->increment('counter');
            } else {
                // Create a new preference if it doesn't exist
                $user->preferences()->create([
                    'item_id' => $item->id,
                    'preference' => $noun,
                    'counter' => 1, // Initialize counter to 1 for new entries
                ]);
            }
        }

        return to_route('post-management.index');
    }

    protected function extractNouns($text) {
        // Updated regex to extract words that are three letters or longer
        preg_match_all('/\b\w{3,}\b/', $text, $matches);
        return $matches[0];
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $item = Item::with(['images', 'tags'])->where('id', $id)->first();

        return Inertia::render('Post/EditProductForm', ['item' => $item]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(postItemRequest $request, $id)
    {
        $item = Item::findOrFail($id);
        $item->update([
            'item_name' => $request->item_name,
            'item_description' => $request->item_description,
            'preferred_items' => $request->preferred_item,
            'category_id' => $request->item_category,
            'user_id' => Auth::user()->id,

        ]);

        $item->tags()->sync($request->item_tags);

        $tempImages = TemporaryImage::whereIn('folder', $request->item_image)->get();

        foreach ($tempImages as $img) {
            Storage::disk('public')->copy('images/tmp/'.$img->folder.'/'.$img->file, 'uploadedItemsImages/'.$img->file);
            // Storage::copy('images/tmp/' . $img->folder .'/'. $img->file, 'postedItemsImages/'.$img->file);
            // $path = Storage::disk('public')->putFile('postedItemsImages', new File($img->file));
            $item->images()->create([
                'image' => 'uploadedItemsImages/'.$img->file,
            ]);
            Storage::disk('public')->deleteDirectory('images/tmp/'.$img->folder);
            // $img->delete();

        }

        return to_route('post-management.index')->with('success', "$item->item_name Successfully edited");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        if ($item->tradeRequests()->doesntExist()) {
            $item->delete();

            return to_route('post-management.index')->with('success', 'Item deleted successfully');
        } else {
            // session()->flash('error', 'This item has existing trade requests');
            return to_route('post-management.index')->with('error', 'This item has existing trade requests');
        }

    }

    public function reupload($id)
    {
        try {
            $item = Item::findOrFail($id);
            $item->update([
                'status' => 'available',
            ]);

            return redirect()->back()->with('success', 'Item restored successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to restore item');
        }
    }

    public function unlist(Item $id)
    {
        if ($id->tradeRequests()->whereIn('status', ['processing', 'requested'])->exists()) {
            return back()->with('error', 'Cannot unlist item with ongoing trade process');
        }
        $cancelledCount = $id->tradeRequests()
            ->whereNotIn('status', ['cancelled', 'rejected', 'success'])
            ->update(['status' => 'rejected']);

        $id->update(['status' => 'unlisted']);

        $message = 'Item unlisted successfully';
        if ($cancelledCount > 0) {
            $message .= ". {$cancelledCount} active trade request".($cancelledCount > 1 ? 's were' : ' was').' cancelled';
        }

        return to_route('post-management.index')->with('success', $message);
    }

    /**
     * Resubmit a rejected post for approval
     */
    public function resubmit(postItemRequest $request, $id)
    {
        $item = Item::with('images')->findOrFail($id);

        // Update the item details
        $item->update([
            'item_name' => $request->item_name,
            'item_description' => $request->item_description,
            'preferred_items' => $request->preferred_item,
            'category_id' => $request->item_category,
            'approval' => 'pending',  // Change status to pending
            'reject_reason' => null,   // Clear previous reject reason
        ]);

        // Update tags
        $item->tags()->sync($request->item_tags);

        // Handle new images if any
        if ($request->has('item_image') && ! empty($request->item_image)) {
            $tempImages = TemporaryImage::whereIn('folder', $request->item_image)->get();

            foreach ($tempImages as $img) {
                Storage::disk('public')->copy('images/tmp/'.$img->folder.'/'.$img->file, 'uploadedItemsImages/'.$img->file);
                $item->images()->create([
                    'image' => 'uploadedItemsImages/'.$img->file,
                ]);
                Storage::disk('public')->deleteDirectory('images/tmp/'.$img->folder);
            }
        }

        // Dispatch moderation job
        dispatch(new ModPostJob($item->item_name, $item->id));

        return to_route('post-management.index')->with('success', 'Item resubmitted for approval successfully');
    }
}
