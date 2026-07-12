<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CommandIQ API Routes
|--------------------------------------------------------------------------
|
| Internal console routes (authenticated console sessions) for the
| CommandIQ engine. Public/consumable API routes (SDK, mobile field app)
| are added under a `v1` group in a later phase.
|
*/
Route::prefix(config('commandiq.api.routing.prefix'))->namespace('IFS\CommandIQ\Http\Controllers')->group(
    function ($router) {
        /*
        |--------------------------------------------------------------------------
        | Internal CommandIQ Console Routes
        |--------------------------------------------------------------------------
        */
        $router->group(
            ['prefix' => config('commandiq.api.routing.internal_prefix'), 'middleware' => ['fleetbase.protected']],
            function ($router) {
                $router->group(
                    ['prefix' => 'v1', 'namespace' => 'Internal\v1'],
                    function ($router) {
                        // 8.2 Forecasting — FR-3..5, FR-19..22
                        $router->fleetbaseRoutes('availability-windows');
                        $router->fleetbaseRoutes('return-patterns');

                        // 8.13 Campaign management — FR-43..45
                        $router->fleetbaseRoutes('campaigns', function ($router, $controller) {
                            $router->get('{id}/burn-down', $controller('burnDown'));
                        });
                        $router->fleetbaseRoutes('campaign-assignments');

                        // 8.10 Warranty & claims — FR-36/37
                        $router->fleetbaseRoutes('warranty-claims');

                        // 8.12 RMA / depot returns — FR-41/42
                        $router->fleetbaseRoutes('rma-cases');

                        // 8.8 Quality control — FR-27..30
                        $router->fleetbaseRoutes('qc-reviews', function ($router, $controller) {
                            $router->post('{id}/approve', $controller('approve'));
                            $router->post('{id}/reject', $controller('reject'));
                        });

                        // 8.15 Service intake — FR-56
                        $router->fleetbaseRoutes('intake-requests');
                    }
                );
            }
        );
    }
);
