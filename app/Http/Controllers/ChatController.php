<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Repositories\ChatRepository;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Message;
use Illuminate\Support\Facades\Redirect;

class ChatController extends Controller
{
    /**
     * Chat view.
     *
     * @return \Inertia\Response
     */

    public function __construct(private ChatRepository $chat) {
        $this->chat = $chat;
    }

    public function index(Request $request, ?int $receiverId = null)
    {
        $messages = empty($receiverId) ? [] : $this->chat->getUserMessages($request->user()->id, $receiverId);

        return Inertia::render('Chat/Chat', [
            'messages' => $messages,
            // 'recentMessages' => $this->chat->getRecentUsersWithMessage($request->user()->id)
        ]);
    }

    /**
     * Chat store
     *
     * @return \Inertia\Response
     */
    public function store(Request $request, ?int $receiverId = null)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        if (empty($receiverId)) {
            return;
        }

        try {
            $message = $this->chat->sendMessage([
                'sender_id' => (int) $request->user()->id,
                'receiver_id' => $receiverId,
                'message' => $request->message,
            ]);

            event(new MessageSent($message));

            return Redirect::route('chat.index', $receiverId);
        } catch (\Throwable $th) {
            return Redirect::route('chat.index', $receiverId);
        }
    }
}
