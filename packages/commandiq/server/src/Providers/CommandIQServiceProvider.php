<?php

namespace IFS\CommandIQ\Providers;

use Fleetbase\Providers\CoreServiceProvider;
use Fleetbase\Support\Utils;
use Illuminate\Database\Eloquent\Relations\Relation;

if (!Utils::classExists(CoreServiceProvider::class)) {
    throw new \Exception('CommandIQ cannot be loaded without `fleetbase/core-api` installed!');
}

/**
 * IFS CommandIQ service provider.
 */
class CommandIQServiceProvider extends CoreServiceProvider
{
    /**
     * The observers registered with the service provider.
     *
     * @var array
     */
    public $observers = [];

    /**
     * The console commands registered with the service provider.
     *
     * @var array
     */
    public $commands = [
        \IFS\CommandIQ\Console\Commands\ForecastAvailabilityWindows::class,
        \IFS\CommandIQ\Console\Commands\MatchWorkOrdersToWindows::class,
        \IFS\CommandIQ\Console\Commands\SyncRelayGarage::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(CoreServiceProvider::class);
    }

    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMorphMap();
        $this->registerObservers();
        $this->registerCommands();
        $this->scheduleCommands(function ($schedule) {
            // FR-19/21: recompute availability windows from fresh route/telematics data
            $schedule->command('commandiq:forecast-availability')->hourly()->withoutOverlapping()->storeOutputInDb();
            // FR-6/23: propose schedule matches for open work orders
            $schedule->command('commandiq:match-work-orders')->hourly()->withoutOverlapping()->storeOutputInDb();
            // INT-1/3: ingest Relay Garage / REACH data (sanctioned channels only, AC-1)
            $schedule->command('commandiq:sync-relay-garage')->everyFifteenMinutes()->withoutOverlapping()->storeOutputInDb();
        });

        $this->loadRoutesFrom(__DIR__ . '/../routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        $this->mergeConfigFrom(__DIR__ . '/../../config/commandiq.php', 'commandiq');
    }

    /**
     * Register the morph map for CommandIQ models.
     *
     * @return void
     */
    public function registerMorphMap()
    {
        Relation::morphMap([
            'availability_window' => \IFS\CommandIQ\Models\AvailabilityWindow::class,
            'return_pattern'      => \IFS\CommandIQ\Models\ReturnPattern::class,
            'campaign'            => \IFS\CommandIQ\Models\Campaign::class,
            'campaign_assignment' => \IFS\CommandIQ\Models\CampaignAssignment::class,
            'warranty_claim'      => \IFS\CommandIQ\Models\WarrantyClaim::class,
            'rma_case'            => \IFS\CommandIQ\Models\RmaCase::class,
            'qc_review'           => \IFS\CommandIQ\Models\QcReview::class,
            'intake_request'      => \IFS\CommandIQ\Models\IntakeRequest::class,
        ], false);
    }
}
