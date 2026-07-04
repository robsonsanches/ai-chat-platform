<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Requests\UpdateConversationRequest;
use App\Contracts\ConversationServiceInterface;
use App\Contracts\ConversationMessageServiceInterface;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function __construct(
        protected ConversationServiceInterface $conversationService,
        protected ConversationMessageServiceInterface $conversationMessageService
    ) {
    }

    public function index()
    {
        $perPage = request()->query('per_page', 15);
        $orderBy = request()->query('order_by', 'id');
        $order = request()->query('order', 'asc');

        $conversations = $this->conversationService->listConversations(
            $perPage,
            $orderBy,
            $order
        );

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
        $result = $this->conversationMessageService->send(
            content: $request->input('message.content'),
            conversationId: $request->input('conversation_id'),
            user: Auth::user(),
        );

        if ($request->input('title')) {
            $this->conversationService->updateConversationTitle(
                $result['conversation_id'],
                $request->input('title')
            );
        }

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
        $orderBy = request()->query('order_by', 'id');
        $order = request()->query('order', 'asc');

        $messages = $this->conversationMessageService->listMessages(
            $conversationId, 
            $perPage, 
            $orderBy, 
            $order
        );

        if (!$messages) {
            return $this->error('Mensagens não encontradas', 404);
        }

        return $this->success(
            $messages,
            'Mensagens encontradas com sucesso'
        );
    }

}