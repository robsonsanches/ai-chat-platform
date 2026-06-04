<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChatRequest;
use App\Services\ChatService;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService
    ) {
    }

    public function store(ChatRequest $request)
    {
        $result = $this->chatService->sendMessage(
            message: $request->message,
            conversationId: $request->conversation_id
        );

        return $this->success(
            $result,
            'Mensagem enviada com sucesso'
        );
    }
}