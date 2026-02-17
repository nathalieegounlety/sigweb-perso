<?php

namespace App\Providers;

use App\Repositories\Contracts\RolesRepositoryInterface;
use App\Repositories\Contracts\UsersRepositoryInterface;
use App\Repositories\Eloquent\RolesRepository;
use App\Repositories\Eloquent\UsersRepository;
use App\Services\contracts\AuditeursInterface;
use App\Services\contracts\AuthServiceInterface;
use App\Services\contracts\ComptabilitesInterface;
use App\Services\contracts\DashboardInterface;
use App\Services\contracts\DocumentsInterface;
use App\Services\contracts\FedapayInterfarce;
use App\Services\contracts\FormateursInterface;
use App\Services\contracts\FormationsdscInterface;
use App\Services\contracts\FormationsInterface;
use App\Services\contracts\GrilletarifairesInterface;
use App\Services\contracts\InscritstypepiecesInterface;
use App\Services\contracts\KkiapayInterfarce;
use App\Services\contracts\ModepaiementsInterface;
use App\Services\contracts\MoodlesmodulesInterface;
use App\Services\contracts\OuvragesInterface;
use App\Services\contracts\PaiementsInterface;
use App\Services\contracts\ReductionsInterface;
use App\Services\contracts\RolesServiceInterface;
use App\Services\contracts\PermissionsServiceInterface;
use App\Services\contracts\PointDePaiementsInterface;
use App\Services\contracts\SignatairesInterface;
use App\Services\contracts\TypeformationsInterface;
use App\Services\contracts\UsersInterface;
use App\Services\Impl\AuditeursService;
use App\Services\Impl\AuthService;
use App\Services\Impl\ComptabilitesService;
use App\Services\Impl\DashboardService;
use App\Services\Impl\DocumentsService;
use App\Services\Impl\FedapayService;
use App\Services\Impl\FormateursService;
use App\Services\Impl\FormationsdscService;
use App\Services\Impl\FormationsService;
use App\Services\Impl\GrilletarifairesService;
use App\Services\Impl\InscritstypepiecesService;
use App\Services\Impl\KkiapayService;
use App\Services\Impl\ModepaiementsService;
use App\Services\Impl\MoodlesmodulesService;
use App\Services\Impl\OuvragesService;
use App\Services\Impl\PaiementsService;
use App\Services\Impl\PermissionsService;
use App\Services\Impl\PointdepaiementsService;
use App\Services\Impl\ReductionsService;
use App\Services\Impl\RolesService;
use App\Services\Impl\SignatairesService;
use App\Services\Impl\TypeformationsService;
use App\Services\Impl\UsersService;
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
        $this->app->bind(ModepaiementsInterface::class, ModepaiementsService::class);
        $this->app->bind(FormationsInterface::class, FormationsService::class);
        $this->app->bind(DocumentsInterface::class, DocumentsService::class);
        $this->app->bind(GrilletarifairesInterface::class, GrilletarifairesService::class);
        $this->app->bind(TypeformationsInterface::class, TypeformationsService::class);
        $this->app->bind(UsersInterface::class, UsersService::class);
        $this->app->bind(AuditeursInterface::class, AuditeursService::class);
        $this->app->bind(OuvragesInterface::class, OuvragesService::class);
        $this->app->bind(ReductionsInterface::class, ReductionsService::class);
        $this->app->bind(SignatairesInterface::class, SignatairesService::class);
        $this->app->bind(PaiementsInterface::class, PaiementsService::class);
        $this->app->bind(FedapayInterfarce::class, FedapayService::class);
        $this->app->bind(KkiapayInterfarce::class, KkiapayService::class);
        $this->app->bind(ComptabilitesInterface::class, ComptabilitesService::class);
        $this->app->bind(DashboardInterface::class, DashboardService::class);
        $this->app->bind(FormationsdscInterface::class, FormationsdscService::class);
        $this->app->bind(InscritstypepiecesInterface::class, InscritstypepiecesService::class);
        $this->app->bind(PointDePaiementsInterface::class, PointdepaiementsService::class);
        $this->app->bind(MoodlesmodulesInterface::class, MoodlesmodulesService::class);
        $this->app->bind(FormateursInterface::class, FormateursService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
