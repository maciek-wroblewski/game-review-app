<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function fetchConversations()
    {
        if(!Auth::check()) return response()->json([]);

        $userId = Auth::id();
        
        $messages = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->latest()
            ->get();
            
        $conversations = [];
        $seen = [];
        
        foreach($messages as $msg) {
            $otherId = $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            if(!isset($seen[$otherId])) {
                $seen[$otherId] = true;
                $otherUser = User::find($otherId);
                $conversations[] = [
                    'user' => [
                        'id' => $otherUser->id,
                        'name' => $otherUser->name,
                        'avatar_url' => $otherUser->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($otherUser->name).'&background=random',
                    ],
                    'last_message' => $msg->body,
                    'time' => $msg->created_at->diffForHumans(),
                ];
            }
        }

        return response()->json($conversations);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string|max:1000'
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $request->receiver_id,
            'body' => $request->body,
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }
}
