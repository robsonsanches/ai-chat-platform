<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ConversationServiceInterface;
use App\Services\ConversationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ConversationServiceInterface::class, ConversationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
