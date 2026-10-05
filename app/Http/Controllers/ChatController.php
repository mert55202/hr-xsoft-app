<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Support\ChatAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(ChatAccess $access): View
    {
        $ids = $access->partnerIds(auth()->user());

        return view('chat.index', [
            'partners' => User::query()->whereIn('id', $ids === [] ? [0] : $ids)->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request, User $user, ChatAccess $access): View|JsonResponse
    {
        abort_unless($access->allows($request->user(), $user), 403);
        $conversation = $this->conversation($request->user(), $user);
        $after = (int) $request->integer('after');
        $messages = $conversation->messages()->with('sender')->where('id', '>', $after)->orderBy('id')->get();
        $messages->where('sender_id', '!=', $request->user()->id)->each->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'messages' => $messages->map(fn (Message $message): array => [
                    'id' => $message->id,
                    'body' => $message->body,
                    'mine' => (int) $message->sender_id === (int) $request->user()->id,
                    'name' => $message->sender->name,
                ]),
            ]);
        }

        return view('chat.show', [
            'partner' => $user,
            'conversation' => $conversation,
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, User $user, ChatAccess $access): RedirectResponse
    {
        abort_unless($access->allows($request->user(), $user), 403);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $conversation = $this->conversation($request->user(), $user);
        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back();
    }

    private function conversation(User $left, User $right): Conversation
    {
        $low = min($left->id, $right->id);
        $high = max($left->id, $right->id);

        return Conversation::query()->firstOrCreate([
            'low_user_id' => $low,
            'high_user_id' => $high,
        ]);
    }
}
