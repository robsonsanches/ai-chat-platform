<?php

namespace App\Contracts;

use App\Models\User;

interface ConversationMessageServiceInterface
{
    public function send(
        string $content,
        ?string $conversationId = null,
        ?User $user = null
    ): array;

    public function listMessages(string $conversationId, int $perPage = 15, string $orderBy = 'id', string $orderDirection = 'asc');

    public function getLatestMessage(string $conversationId);
}