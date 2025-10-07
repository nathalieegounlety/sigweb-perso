<?php

namespace App\Providers;

use App\Repositories\Contracts\RolesRepositoryInterface;
use App\Repositories\Contracts\UsersRepositoryInterface;
use App\Repositories\Eloquent\RolesRepository;
use App\Repositories\Eloquent\UsersRepository;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\contracts\RolesServiceInterface;
use App\Services\contracts\PermissionsServiceInterface;
use App\Services\Impl\AuthService;
use App\Services\Impl\PermissionsService;
use App\Services\Impl\RolesService;
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
        $this->app->bind(RolesRepositoryInterface::class, RolesRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(RolesServiceInterface::class, RolesService::class);
        $this->app->bind(PermissionsServiceInterface::class, PermissionsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
