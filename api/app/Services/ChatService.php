<?php

namespace App\Services;

use App\Ai\Agents\ChatAgent;
use App\Models\User;

class ChatService
{
    protected ?User $user;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    public function sendMessage(
        string $message,
        ?int $conversationId = null
    ): array {

        $agent = new ChatAgent();

        if ($conversationId) {
            $agent->continue((string) $conversationId, (object) ['id' => $conversationId]);
        }

        $response = $agent->forUser($this->user)->prompt($message);

        return [
            'conversation_id' => $response->conversationId,
            'response' => $response->text,
        ];
    }
}