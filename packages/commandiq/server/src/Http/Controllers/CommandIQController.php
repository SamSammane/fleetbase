<?php

namespace IFS\CommandIQ\Http\Controllers;

use Fleetbase\Http\Controllers\FleetbaseController;

class CommandIQController extends FleetbaseController
{
    /**
     * The package namespace used to resolve models from.
     */
    public string $namespace = '\\IFS\\CommandIQ';
}
