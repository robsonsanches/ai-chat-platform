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
        ?string $conversationId = null
    ): array {

        $agent = new ChatAgent();

        if ($conversationId) {
            $agent->continue((string) $conversationId, (object) $this->user);
        } else {
            $agent->forUser($this->user);
        }

        $response = $agent->prompt($message);

        return [
            'conversation_id' => $response->conversationId,
            'response' => $response->text,
        ];
    }
}