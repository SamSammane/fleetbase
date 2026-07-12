<?php

/**
 * -------------------------------------------
 * IFS CommandIQ API Configuration
 * -------------------------------------------.
 */
return [
    'api' => [
        'version' => '0.1.0',
        'routing' => [
            'prefix'          => null,
            'internal_prefix' => 'int',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Forecasting (Section 8.2 — FR-3..5, FR-19..22)
    |--------------------------------------------------------------------------
    */
    'forecasting' => [
        // How far ahead availability windows are computed (FR-5)
        'horizon_days' => env('COMMANDIQ_FORECAST_HORIZON_DAYS', 7),
        // Minimum dwell time (minutes) at a location to count as serviceable (FR-4)
        'min_window_minutes' => env('COMMANDIQ_MIN_WINDOW_MINUTES', 60),
        // Trailing days of DSP return history used for LM pattern averages (FR-19)
        'lm_pattern_lookback_days' => env('COMMANDIQ_LM_LOOKBACK_DAYS', 28),
        // Named set of MM "major hubs" (Section 11 open question — configure per program)
        'mm_major_hubs' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Integrations (Section 10 — INT-1/2/3/5)
    | AC-3: approved authentication only; never store user portal credentials.
    |--------------------------------------------------------------------------
    */
    'integrations' => [
        'relay_garage' => [
            'host'    => env('RELAY_GARAGE_HOST'),
            'api_key' => env('RELAY_GARAGE_API_KEY'),
        ],
        'reach' => [
            'host'    => env('REACH_HOST'),
            'api_key' => env('REACH_API_KEY'),
        ],
        'geotab' => [
            'host'     => env('GEOTAB_HOST'),
            'database' => env('GEOTAB_DATABASE'),
            'username' => env('GEOTAB_USERNAME'),
            'password' => env('GEOTAB_PASSWORD'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Quality Control (Section 8.8 — FR-27..30, FR-58)
    |--------------------------------------------------------------------------
    */
    'qc' => [
        // Only this IAM role may close work orders (FR-28)
        'closer_role' => env('COMMANDIQ_QC_ROLE', 'CommandIQ QC Reviewer'),
        // Minimum photo evidence per work order type (Section 11: min before + after)
        'default_required_photos' => ['before', 'after'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduling (Section 8.3 — FR-100/101)
    |--------------------------------------------------------------------------
    */
    'scheduling' => [
        'technician_day_hours' => env('COMMANDIQ_TECH_DAY_HOURS', 8),
    ],
];
