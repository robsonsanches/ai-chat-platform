<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Requests\UpdateConversationRequest;
use App\Contracts\ConversationServiceInterface;

class ConversationController extends Controller
{
    public function __construct(
        protected ConversationServiceInterface $conversationService
    ) {
    }

    public function index()
    {
        $perPage = request()->query('per_page', 15);
        $conversations = $this->conversationService->listConversations($perPage);

        if (!$conversations) {
            return $this->error('Conversas não encontradas', 404);
        }

        return $this->success(
            $conversations,
            'Conversas encontradas com sucesso'
        );
    }

    public function store(StoreConversationRequest $request)
    {
        $result = $this->conversationService->processConversation(
            $request->input('message.content'),
            $request->input('conversation_id'),
            $request->input('title')
        );

        if (!$result) {
            return $this->error('Erro ao processar a conversa', 500);
        }

        return $this->success(
            $result,
            'Conversa processada com sucesso'
        );
    }

    public function show(string $id)
    {
        $conversation = $this->conversationService->findConversationById($id);

        if (!$conversation) {
            return $this->error('Conversa não encontrada', 404);
        }

        return $this->success(
            $conversation,
            'Conversa encontrada com sucesso'
        );
    }

    public function update(UpdateConversationRequest $request, string $id)
    {
        $conversation = $this->conversationService->updateConversationTitle(
            $id,
            $request->input('title')
        );

        if (!$conversation) {
            return $this->error('Conversa não encontrada', 404);
        }

        return $this->success(
            $conversation,
            'Conversa atualizada com sucesso'
        );
    }

    public function destroy(string $id)
    {
        if (!$this->conversationService->deleteConversation($id)) {
            return $this->error('Conversa não encontrada', 404);
        }

        return $this->success(null, 'Conversa deletada com sucesso');
    }

    public function messages(string $conversationId)
    {
        $perPage = request()->query('per_page', 15);
        $messages = $this->conversationService->listMessages($conversationId, $perPage);

        if (!$messages) {
            return $this->error('Mensagens não encontradas', 404);
        }

        return $this->success(
            $messages,
            'Mensagens encontradas com sucesso'
        );
    }

}