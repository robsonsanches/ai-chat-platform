<?php

namespace App\Services;

use App\Models\User;
use App\Models\Conversation;
use App\Contracts\ConversationServiceInterface;
use App\Contracts\ConversationMessageServiceInterface;
use App\Exceptions\ConversationNotFoundException;
use Illuminate\Support\Facades\Auth;

class ConversationService implements ConversationServiceInterface
{
    protected Conversation $conversation;
    protected ConversationMessageServiceInterface $conversationMessageService;

    public function __construct(
        ?Conversation $conversation = null,
        ?ConversationMessageServiceInterface $conversationMessageService = null
    ) {
        $this->conversation = $conversation ?? resolve(Conversation::class);
        $this->conversationMessageService = $conversationMessageService ?? resolve(ConversationMessageServiceInterface::class);
    }

    public function processConversation(
        string $messageContent,
        ?string $conversationId = null,
        ?string $title = null,
        ?User $user = null
    ): array {
        $user = $user ?? Auth::user();

        $response = $this->conversationMessageService->send(
            content: $messageContent,
            conversationId: $conversationId,
            user: $user,
        );

        if ($title) {
            $this->updateConversationTitle($response['conversation_id'], $title);
        }

        return $response;
    }

    public function listConversations(int $perPage = 15, string $orderBy = 'id', string $orderDirection = 'asc')
    {
        $allowedSorts = ['id', 'title'];
        $allowedDirections = ['asc', 'desc'];

        $orderBy = in_array($orderBy, $allowedSorts, true) ? $orderBy : 'id';
        $orderDirection = in_array(strtolower($orderDirection), $allowedDirections, true)
            ? strtolower($orderDirection)
            : 'asc';

        return $this->conversation
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    public function findConversationById(string $id): Conversation
    {
        $conversation = $this->conversation->find($id);

        if (!$conversation) {
            throw new ConversationNotFoundException();
        }

        return $conversation;
    }

    public function updateConversationTitle(string $id, string $title): Conversation
    {
        $conversation = $this->conversation->find($id);

        if (!$conversation) {
            throw new ConversationNotFoundException();
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
            throw new ConversationNotFoundException();
        }

        return $conversation->delete();
    }

}
