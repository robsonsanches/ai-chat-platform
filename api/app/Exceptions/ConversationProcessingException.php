<?php

namespace App\Exceptions;

class ConversationProcessingException extends ApiException
{
    public function __construct(string $message = 'Erro ao processar a conversa.', string $errorCode = 'CONVERSATION_PROCESSING_ERROR')
    {
        parent::__construct(message: $message, statusCode: 500, errorCode: $errorCode);
    }
}
