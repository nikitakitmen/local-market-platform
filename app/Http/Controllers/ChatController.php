<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Простой чат «покупатель ↔ производитель» без WebSocket:
 * сообщения хранятся в БД, новые подгружаются AJAX-опросом раз в несколько секунд.
 *
 * Один контроллер обслуживает обе стороны: /account/messages (покупатель)
 * и /producer/messages (производитель).
 */
class ChatController extends Controller
{
    public function index(Request $request, ?Conversation $conversation = null): View
    {
        $user = $request->user();
        $asProducer = $request->routeIs('producer.*');
        $producer = $asProducer ? $user->producer : null;

        abort_if($asProducer && ! $producer, 403);

        $conversations = Conversation::query()
            ->when($asProducer, fn ($q) => $q->where('producer_id', $producer->id), fn ($q) => $q->where('buyer_id', $user->id))
            ->with(['latestMessage', 'buyer', 'producer'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', $user->id)])
            ->orderByDesc('last_message_at')
            ->get();

        $messages = collect();

        if ($conversation) {
            // Диалог должен принадлежать текущей стороне (покупатель или производитель)
            $belongs = $asProducer ? $conversation->producer_id === $producer->id : $conversation->buyer_id === $user->id;
            abort_unless($belongs, 403);

            $conversation->markAsReadFor($user);
            $conversation->load(['buyer', 'producer']);
            $messages = $conversation->messages()->oldest('id')->get();
        }

        return view('chat.index', [
            'conversations' => $conversations,
            'conversation' => $conversation,
            'messages' => $messages,
            'asProducer' => $asProducer,
        ]);
    }

    /** Начать диалог с производителем (или открыть существующий). */
    public function start(Request $request, Producer $producer): RedirectResponse
    {
        $user = $request->user();

        if ($producer->user_id === $user->id) {
            return back()->with('error', 'Нельзя написать самому себе.');
        }

        abort_unless($producer->isApproved(), 404);

        $conversation = Conversation::firstOrCreate(
            ['buyer_id' => $user->id, 'producer_id' => $producer->id],
            ['last_message_at' => now()]
        );

        // Если вопрос задаётся со страницы товара — подставляем заготовку сообщения
        $draft = null;
        if ($request->filled('product_id') && $product = $producer->products()->find($request->integer('product_id'))) {
            $draft = 'Здравствуйте! Вопрос по товару «'.$product->name.'»: ';
        }

        return redirect()->route('account.messages', $conversation)->with('chat_draft', $draft);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse|RedirectResponse
    {
        $this->authorize('view', $conversation);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $message = $conversation->addMessage($request->user(), $data['body']);

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->present($message, $request->user())]);
        }

        return back();
    }

    /** AJAX-опрос: новые сообщения после указанного id и статус прочтения моих сообщений. */
    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $user = $request->user();
        $messages = $conversation->messages()
            ->where('id', '>', $request->integer('after'))
            ->oldest('id')
            ->get();

        $conversation->markAsReadFor($user);

        $readUpTo = $conversation->messages()
            ->where('sender_id', $user->id)
            ->whereNotNull('read_at')
            ->max('id');

        return response()->json([
            'messages' => $messages->map(fn (Message $message) => $this->present($message, $user))->values(),
            'read_up_to' => (int) $readUpTo,
        ]);
    }

    private function present(Message $message, User $user): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'time' => $message->created_at->format('H:i'),
            'mine' => $message->sender_id === $user->id,
            'read' => $message->read_at !== null,
        ];
    }
}
