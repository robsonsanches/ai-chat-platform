<?php

namespace App\Services;

use App\Ai\Agents\ChatAgent;
use App\Models\User;

class MessageService
{
    protected ?User $user;

    public function __construct(?User $user = null)
    {
        $this->user = $user ?? auth()->user();
    }

    public function send(
        string $content,
        ?string $conversationId = null
    ): array {

        $agent = new ChatAgent();

        if ($conversationId) {
            $agent->continue((string) $conversationId, (object) $this->user);
        } else {
            $agent->forUser($this->user);
        }

        $response = $agent->prompt($content);

        return [
            'conversation_id' => $response->conversationId,
            'response' => $response->text,
        ];
    }

}