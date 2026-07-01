<?php

namespace App\Services;

use App\Models\User;
use App\Models\Conversation;
use App\Ai\Agents\ChatAgent;
use App\Contracts\ConversationServiceInterface;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\ConversationStore;

class ConversationService implements ConversationServiceInterface
{
    protected Conversation $conversation;
    protected ConversationStore $store;
    protected ChatAgent $agent;

    public function __construct()
    {
        $this->conversation = resolve(Conversation::class);
        $this->store = resolve(ConversationStore::class);
        $this->agent = new ChatAgent();
    }

    public function processConversation(
        string $messageContent, 
        ?string $conversationId = null, 
        ?string $title = null, 
        ?User $user = null
    ): ?array
    {
        $user = $user ?? Auth::user();

        if ($conversationId) {
            $this->agent->continue((string) $conversationId, (object) $user);
        } else {
            $this->agent->forUser($user);
        }

        if (!$response = $this->agent->prompt($messageContent)) {
            return null;
        }

        if ($title) {
            $this->updateConversationTitle($response->conversationId, $title);
        }

        return [
            'conversation_id' => $response->conversationId,
            'message' => $this->store->getLatestConversationMessages($response->conversationId, 1)?->first(),
        ];
    }

    public function listConversations(int $perPage = 15)
    {
        return $this->conversation->paginate($perPage);
    }

    public function findConversationById(string $id)
    {
        return $this->conversation->find($id);
    }

    public function updateConversationTitle(string $id, string $title): ?Conversation
    {
        $conversation = $this->conversation->find($id);

        if (!$conversation) {
            return null;
        }

        $conversation->update([
            'title' => $title,
        ]);

        return $conversation->refresh();
    }

    public function deleteConversation(string $id): bool
    {
        $conversation = $this->conversation->find($id);

        if (!$conversation) {
            return false;
        }

        return $conversation->delete();
    }

    public function listMessages(string $conversationId, int $perPage = 15)
    {
        return $this->store->getLatestConversationMessages($conversationId, $perPage);
    }
}
