<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MessageRequest;
use App\Services\MessageService;

class MessageController extends Controller
{
    public function __construct(
        protected MessageService $messageService
    ) {
    }

    public function store(MessageRequest $request)
    {
        $result = $this->messageService->send(
            content: $request->content,
            conversationId: $request->conversation_id
        );

        return $this->success(
            $result,
            'Mensagem enviada com sucesso'
        );
    }

}