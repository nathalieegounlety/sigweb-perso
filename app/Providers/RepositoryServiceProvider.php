<?php

namespace App\Providers;
 
use App\Repositories\Contracts\UsersRepositoryInterface; 
use App\Repositories\Eloquent\UsersRepository;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Impl\AuthService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // liaison de l'interface au service
        $this->app->bind(UsersRepositoryInterface::class, UsersRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
