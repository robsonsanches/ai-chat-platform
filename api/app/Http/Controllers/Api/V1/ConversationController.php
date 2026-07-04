<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Requests\UpdateConversationRequest;
use App\Contracts\ConversationServiceInterface;
use App\Contracts\ConversationMessageServiceInterface;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ConversationController extends Controller
{
    public function __construct(
        protected ConversationServiceInterface $conversationService,
        protected ConversationMessageServiceInterface $conversationMessageService
    ) {
    }

    protected function handleServiceException(Throwable $exception)
    {
        if ($exception instanceof ApiException) {
            return $this->error(
                message: $exception->getMessage(),
                statusCode: $exception->getStatusCode(),
                errors: $exception->getErrors(),
                code: $exception->getErrorCode()
            );
        }

        return $this->error('Erro interno do servidor', 500, null, 'SERVER_ERROR');
    }

    public function index()
    {
        try {
            $perPage = request()->query('per_page', 15);
            $orderBy = request()->query('order_by', 'id');
            $order = request()->query('order', 'asc');

            $conversations = $this->conversationService->listConversations(
                $perPage,
                $orderBy,
                $order
            );

            return $this->success(
                $conversations,
                'Conversas encontradas com sucesso'
            );
        } catch (Throwable $exception) {
            return $this->handleServiceException($exception);
        }
    }

    public function store(StoreConversationRequest $request)
    {
        try {
            $result = $this->conversationService->processConversation(
                messageContent: $request->input('message.content'),
                conversationId: $request->input('conversation_id'),
                title: $request->input('title'),
                user: Auth::user(),
            );

            return $this->success(
                $result,
                'Conversa processada com sucesso'
            );
        } catch (Throwable $exception) {
            return $this->handleServiceException($exception);
        }
    }

    public function show(string $id)
    {
        try {
            $conversation = $this->conversationService->findConversationById($id);

            return $this->success(
                $conversation,
                'Conversa encontrada com sucesso'
            );
        } catch (Throwable $exception) {
            return $this->handleServiceException($exception);
        }
    }

    public function update(UpdateConversationRequest $request, string $id)
    {
        try {
            $conversation = $this->conversationService->updateConversationTitle(
                $id,
                $request->input('title')
            );

            return $this->success(
                $conversation,
                'Conversa atualizada com sucesso'
            );
        } catch (Throwable $exception) {
            return $this->handleServiceException($exception);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->conversationService->deleteConversation($id);

            return $this->success(null, 'Conversa deletada com sucesso');
        } catch (Throwable $exception) {
            return $this->handleServiceException($exception);
        }
    }

    public function messages(string $conversationId)
    {
        try {
            $perPage = request()->query('per_page', 15);
            $orderBy = request()->query('order_by', 'id');
            $order = request()->query('order', 'asc');

            $messages = $this->conversationMessageService->listMessages(
                $conversationId,
                $perPage,
                $orderBy,
                $order
            );

            return $this->success(
                $messages,
                'Mensagens encontradas com sucesso'
            );
        } catch (Throwable $exception) {
            return $this->handleServiceException($exception);
        }
    }

}