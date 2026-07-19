<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHandler;
use App\Services\contracts\DashboardInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private DashboardInterface $dashboardService;

    public function __construct(DashboardInterface $dashboardService)
    {
        // restriction des accès
        $this->middleware('auth:api',['except' => ['test']]);
        $this->dashboardService= $dashboardService;
    }

    public function adminIndicateurs(Request $request)
    {
        try {
             $data = $this->dashboardService->getAdminIndicateurs();
             return ResponseHandler::success('ok',$data);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation ".$request, 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }

    public function adminIndicateurStats(Request $request)
    {
        try {
             $data = $this->dashboardService->getAdminIndicateurStats();
             return ResponseHandler::success('ok',$data);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseHandler::error("Erreur de validation ".$request, 422, $e->errors());
        } catch (\Exception $e) {
            return ResponseHandler::error("Une erreur interne est survenue", 500, $e->getMessage());
        }
    }
}
