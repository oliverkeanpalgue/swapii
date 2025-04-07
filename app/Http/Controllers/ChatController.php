<?php

namespace App\Http\Controllers;

use App\Events\myEvent;
use App\Events\newMessageEvent;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\ProfilePicture;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
// use Illuminate\Container\Attributes\Storage;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    //
    public function friends()
    {
        try {
            $user = Auth::user();

            $chatRooms = ChatRoom::with(['sender' => function ($query) {
                $query->with('profilePicture');
            }, 'receiver' => function ($query) {
                $query->with('profilePicture');
            }, 'messages' => function ($query) {
                $query->latest()->first();
            }])
                ->where(function ($query) use ($user) {
                    $query->where('receiver_id', $user->id)
                        ->orWhere('sender_id', $user->id);
                })
                ->has('messages')
                ->get()
                ->map(function ($chatRoom) use ($user) {
                    $otherUser = $chatRoom->sender_id === $user->id ? $chatRoom->receiver : $chatRoom->sender;

                    // Get unread messages count
                    $unreadCount = Message::where('chat_room_id', $chatRoom->id)
                        ->where('user_id', '!=', $user->id)
                        ->where('is_read', false)
                        ->count();

                    // Format the response
                    return [
                        'id' => $chatRoom->id,
                        'sender' => [
                            'id' => $chatRoom->sender->id,
                            'name' => $chatRoom->sender->name,
                            'profilePicture' => $chatRoom->sender->profile_picture,
                        ],
                        'receiver' => [
                            'id' => $chatRoom->receiver->id,
                            'name' => $chatRoom->receiver->name,
                            'profilePicture' => $chatRoom->receiver->profile_picture,
                        ],
                        'messages' => $chatRoom->messages,
                        'unreadCount' => $unreadCount,
                    ];
                });

            return response()->json($chatRooms);
        } catch (\Exception $e) {
            \Log::error('Error in friends method: '.$e->getMessage());

            return response()->json(['error' => 'Failed to load chat rooms'], 500);
        }
    }

   public function sendMsgImg(Request $request, $id)
   {
       try {
           foreach ($request->file('files') as $file) {
               $extension = strtolower($file->getClientOriginalExtension());
               
               // Check if file is an image or video
               $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
               $isVideo = in_array($extension, ['mp4', 'mov', 'avi', 'wmv']);
               
               if (!$isImage && !$isVideo) {
                   continue; // Skip unsupported file types
               }

               $filename = time() . '_' . $file->getClientOriginalName();
               
               // Ensure directory exists
               $storage_path = storage_path('app/public/chat-attachments');
               if (!file_exists($storage_path)) {
                   mkdir($storage_path, 0755, true);
               }

               if ($file->move($storage_path, $filename)) {
                   $attachmentPath = 'chat-attachments/' . $filename;

                   // Set default message based on file type
                   $defaultMessage = $isVideo ? 'Sent a video' : 'Sent a picture';
                   $message = $request->input('message') ?: $defaultMessage;

                   $message = Message::create([
                       'chat_room_id' => $id,
                       'message' => $message,
                       'user_id' => auth()->id(),
                       'attachments' => $attachmentPath
                   ]);

                   event(new myEvent($message));
               }
           }

           return back();

       } catch (\Exception $e) {
           \Log::error('File upload error: ' . $e->getMessage());
           return response()->json(['error' => $e->getMessage()], 500);
       }
   }

    public function sendMsg(Request $request, $id)
    {
        // dd($request->all(), $id);
        // Find the existing chat room
        $chatRoom = ChatRoom::where(function ($query) use ($id) {
            $query->where('receiver_id', $id)->where('sender_id', Auth::user()->id);
        })
            ->orWhere(function ($query) use ($id) {
                $query->where('receiver_id', Auth::user()->id)->where('sender_id', $id);
            })
            ->first();

        // If no chat room exists, create one
        if (! $chatRoom) {
            $sent = $chatRoom = ChatRoom::create([
                'receiver_id' => $id,
                'sender_id' => Auth::user()->id,
            ]);
        }

        // Send the message in the chat room
        $sent = $chatRoom->messages()->create([
            'message' => $request->message,
            'user_id' => Auth::user()->id,
        ]);

        // broadcast(new myEvent($sent))->toOthers();
        event(new myEvent($sent));

        // event(new newMessageEvent($sent));
        return Redirect::back();
    }

    public function chat($id)
    {
        $user = User::findOrFail($id);
        $userProfilePicture = ProfilePicture::where('user_id', $user->id)->first();
        if ($userProfilePicture) {
            $user->profilePicture = $userProfilePicture->image_path;
        }

        $authUserProfilePicture = ProfilePicture::where('user_id', Auth::user()->id)->first();
        if ($authUserProfilePicture) {
            Auth::user()->profilePicture = $authUserProfilePicture->image_path;
        }

        $chatRoom = ChatRoom::with('messages')
            ->where(function ($query) use ($id) {
                $query->where('receiver_id', Auth::user()->id)
                    ->Where('sender_id', $id);
            })
            ->orWhere(function ($query) use ($id) {
                $query->where('receiver_id', $id)
                    ->Where('sender_id', Auth::user()->id);
            })
            ->first();

        if (! $chatRoom) {
            $chatRoom = ChatRoom::firstOrCreate([
                'sender_id' => Auth::user()->id,
                'receiver_id' => $user->id,
            ]);
        }

        return Inertia::render(
            'Chat/Messages',
            [
                'user' => $user,
                'authUser' => Auth::user(),
                'chatRoom' => $chatRoom,
                'messages' => $chatRoom->messages, // Ensure messages are passed
            ]
        );
    }

    public function messages($id)
    {
        try {
            $user = Auth::user();
            $otherUser = User::findOrFail($id);

            // Get or create chat room
            $chatRoom = ChatRoom::with('messages')
                ->where(function ($query) use ($user, $otherUser) {
                    $query->where('sender_id', $user->id)
                        ->where('receiver_id', $otherUser->id);
                })
                ->orWhere(function ($query) use ($user, $otherUser) {
                    $query->where('sender_id', $otherUser->id)
                        ->where('receiver_id', $user->id);
                })
                ->first();

            if (! $chatRoom) {
                $chatRoom = ChatRoom::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $otherUser->id,
                ]);
            }

            // Format user data
            $userData = [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'profilePicture' => optional($otherUser->profilePicture)->image_path,
            ];

            return Inertia::render('Chat/Messages', [
                'user' => $userData,
                'chatRoom' => [
                    'id' => $chatRoom->id,
                    'messages' => $chatRoom->messages,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in messages method: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to load chat messages');
        }
    }

    public function markAsRead(Request $request)
    {
        $chatRoomId = $request->chat_room_id;
        $userId = Auth::id();

        Message::where('chat_room_id', $chatRoomId)
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
