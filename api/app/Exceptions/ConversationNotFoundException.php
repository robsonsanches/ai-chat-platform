<?php

namespace App\Exceptions;

class ConversationNotFoundException extends ApiException
{
    public function __construct(string $message = 'Conversa não encontrada.', string $errorCode = 'CONVERSATION_NOT_FOUND')
    {
        parent::__construct(message: $message, statusCode: 404, errorCode: $errorCode);
    }
}
