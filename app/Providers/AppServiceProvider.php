<?php

namespace App\Providers;

use App\Interfaces\BuilderDtoServiceInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Services\BuilderDtoService;
use App\Services\TransactionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BuilderDtoServiceInterface::class, BuilderDtoService::class);
        $this->app->bind(TransactionServiceInterface::class, TransactionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
