<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ConversationServiceInterface;
use App\Contracts\ConversationMessageServiceInterface;
use App\Services\ConversationService;
use App\Services\ConversationMessageService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ConversationServiceInterface::class, ConversationService::class);
        $this->app->bind(ConversationMessageServiceInterface::class, ConversationMessageService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
