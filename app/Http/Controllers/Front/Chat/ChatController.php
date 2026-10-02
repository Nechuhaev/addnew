<?php

namespace App\Http\Controllers\Front\Chat;

use App\Ad;
use App\Conversation;
use App\Http\Controllers\Controller;
use App\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Список усіх діалогів поточного користувача (і як покупця, і як
     * продавця, якщо в нього є магазин) — відсортовано за свіжістю.
     */
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::where('shop_user_id', $userId)
            ->orWhere('buyer_user_id', $userId)
            ->orderByDesc('last_message_at')
            ->get();

        foreach ($conversations as $c) {
            $c->other = $c->otherParty($userId);
            $c->unread = $c->unreadCountFor($userId);
            $c->lastMessage = $c->messages()->orderByDesc('id')->first();
        }

        return view('front.chat.index', ['conversations' => $conversations]);
    }

    /**
     * Одна розмова — повна історія повідомлень. Одразу позначає чужі
     * непрочитані повідомлення як прочитані (звичайна поведінка
     * месенджера: відкрив — значить побачив).
     */
    public function show($id)
    {
        $userId = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->shop_user_id != $userId && $conversation->buyer_user_id != $userId) {
            abort(403);
        }

        $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $other = $conversation->otherParty($userId);
        $messages = $conversation->messages()->with('sender')->get();

        return view('front.chat.show', [
            'conversation' => $conversation,
            'other' => $other,
            'messages' => $messages,
        ]);
    }

    /**
     * Почати (або відкрити вже наявний) діалог із магазином. Виклик
     * із кнопки "Написати продавцю" на сторінці оголошення/магазину.
     */
    public function start(Request $request, $shopId)
    {
        $userId = Auth::id();
        $shop = User::findOrFail($shopId);

        if ($userId == $shop->id) {
            return redirect()->back()->with('error', 'Не можна написати самому собі.');
        }

        $adId = $request->input('ad_id');

        $query = Conversation::where('shop_user_id', $shop->id)
            ->where('buyer_user_id', $userId);
        if ($adId) {
            $query->where('ad_id', $adId);
        } else {
            $query->whereNull('ad_id');
        }
        $conversation = $query->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'shop_user_id' => $shop->id,
                'buyer_user_id' => $userId,
                'ad_id' => $adId,
                'last_message_at' => now(),
            ]);
        }

        return redirect(route('chat.show', $conversation->id));
    }

    /**
     * Надіслати повідомлення в наявний діалог.
     */
    public function send(Request $request, $id)
    {
        $userId = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->shop_user_id != $userId && $conversation->buyer_user_id != $userId) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $userId,
            'body' => $validated['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'id' => $message->id,
                'body' => $message->body,
                'sender_id' => $message->sender_id,
                'created_at' => $message->created_at->format('H:i'),
            ]);
        }

        return redirect(route('chat.show', $conversation->id));
    }

    /**
     * AJAX-полінг: нові повідомлення в діалозі з ID більшим за $afterId.
     * Викликається з JS кожні кілька секунд, поки відкрита сторінка діалогу.
     */
    public function poll(Request $request, $id)
    {
        $userId = Auth::id();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->shop_user_id != $userId && $conversation->buyer_user_id != $userId) {
            abort(403);
        }

        $afterId = (int) $request->input('after_id', 0);

        $newMessages = $conversation->messages()
            ->where('id', '>', $afterId)
            ->get();

        // Одразу позначаємо як прочитані те, що прийшло від іншої
        // сторони, поки ми "дивимось" на відкритий діалог.
        $conversation->messages()
            ->where('id', '>', $afterId)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json($newMessages->map(function ($m) use ($userId) {
            return [
                'id' => $m->id,
                'body' => $m->body,
                'is_mine' => $m->sender_id == $userId,
                'created_at' => $m->created_at->format('H:i'),
            ];
        }));
    }

    /**
     * Загальний лічильник непрочитаних по ВСІХ діалогах поточного
     * користувача — для бейджа в шапці сайту.
     */
    public function unreadCount()
    {
        $userId = Auth::id();

        $count = Message::whereIn('conversation_id', function ($q) use ($userId) {
                $q->select('id')->from('conversations')
                    ->where('shop_user_id', $userId)
                    ->orWhere('buyer_user_id', $userId);
            })
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
