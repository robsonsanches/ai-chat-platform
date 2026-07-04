<?php

namespace App\Services;

use App\Ai\Agents\ChatAgent;
use App\Exceptions\ConversationProcessingException;
use App\Models\User;
use App\Models\ConversationMessage;
use App\Contracts\ConversationMessageServiceInterface;
use Illuminate\Support\Facades\Auth;

class ConversationMessageService implements ConversationMessageServiceInterface
{
    protected ?User $user;
    protected ChatAgent $agent;
    protected ConversationMessage $conversationMessage;

    public function __construct(
        ?User $user = null,
        ?ChatAgent $agent = null,
        ?ConversationMessage $conversationMessage = null
    ) {
        $this->user = $user ?? Auth::user();
        $this->agent = $agent ?? resolve(ChatAgent::class);
        $this->conversationMessage = $conversationMessage ?? resolve(ConversationMessage::class);
    }

    public function send(
        string $content,
        ?string $conversationId = null,
        ?User $user = null
    ): array {
        $this->user = $user ?? $this->user;

        if ($conversationId) {
            $this->agent->continue((string) $conversationId, (object) $this->user);
        } else {
            $this->agent->forUser($this->user);
        }

        $response = $this->agent->prompt($content);

        if (!$response || !isset($response->conversationId)) {
            throw new ConversationProcessingException();
        }

        return [
            'conversation_id' => $response->conversationId,
            'message' => $this->getLatestMessage($response->conversationId)
        ];
    }

    public function listMessages(string $conversationId, int $perPage = 15, string $orderBy = 'id', string $orderDirection = 'asc')
    {
        $allowedSorts = ['id', 'created_at'];
        $allowedDirections = ['asc', 'desc'];

        $orderBy = in_array($orderBy, $allowedSorts, true) ? $orderBy : 'id';
        $orderDirection = in_array(strtolower($orderDirection), $allowedDirections, true)
            ? strtolower($orderDirection)
            : 'asc';

        return $this->conversationMessage
            ->where('conversation_id', $conversationId)
            ->orderBy($orderBy, $orderDirection)
            ->paginate($perPage);
    }

    public function getLatestMessage(string $conversationId, $role = 'assistant'): ?ConversationMessage
    {
        return (new $this->conversationMessage)
            ->where('conversation_id', $conversationId)
            ->where('role', $role)
            ->latest()
            ->first();
    }

}