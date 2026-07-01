<?php

namespace App\Contracts;

use App\Models\User;
use App\Models\Conversation;

interface ConversationServiceInterface
{
    public function processConversation(
        string $messageContent, 
        ?string $conversationId = null, 
        ?string $title = null, 
        ?User $user = null
    ): ?array;

    public function listConversations(int $perPage = 15);

    public function findConversationById(string $id);

    public function updateConversationTitle(string $id, string $title): ?Conversation;

    public function deleteConversation(string $id): bool;

    public function listMessages(string $conversationId, int $perPage = 15);
}