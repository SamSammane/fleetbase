# IFS CommandIQ Extension

CBRE Fleet platform extension implementing the IFS CommandIQ functional specification: segment-aware
availability forecasting (Last Mile / Middle Mile), availability-matched maintenance
scheduling, QC review workflow, campaign management, warranty claims, and RMA/depot returns.

This package is dual-sided, following the standard platform extension anatomy (CBRE Fleet is the white-labeled Fleetbase open-source core):

- `server/` — Laravel API package (`ifs/commandiq-api`, namespace `IFS\CommandIQ`)
- `addon/` — Ember engine (`@ifs/commandiq-engine`, mounted at `console.commandiq`)

## Spec traceability

| Spec section | Where it lives |
|---|---|
| 8.1 Asset & Fleet (FR-1/2/17/18) | FleetOps `Vehicle`/`Asset` (native) + `SyncRelayGarage` command + `segment` via meta/custom fields |
| 8.2 Forecasting (FR-3..5, 19..22) | `AvailabilityWindow`, `ReturnPattern` models + `ForecastAvailabilityWindows` command |
| 8.3 Scheduling (FR-6..8, 23, 50/51, 100/101) | `MatchWorkOrdersToWindows` command + FleetOps VROOM engine for technician routing |
| 8.4 PM triggers (FR-9/10/53) | FleetOps `MaintenanceSchedule` (native) + DTC↔warranty matching (Phase 4) |
| 8.5 Work orders (FR-11..13, 24..26, 49, 102) | FleetOps `WorkOrder` (native, with activity-log audit) + CommandIQ automation |
| 8.8 QC (FR-27..30, 58, 104) | `QcReview` model + approve/reject endpoints; closure restricted to QC role via IAM policy |
| 8.9 Inventory (FR-31/32/55) | FleetOps `Part` (native) + reorder-point fields (Phase 4) |
| 8.10 Warranty (FR-36/37) | FleetOps `Warranty` (native coverage) + `WarrantyClaim` (claims workflow) |
| 8.11 Serialized devices (FR-38..40) | FleetOps `VehicleDevice` (native) + install/replacement history (Phase 4) |
| 8.12 RMA (FR-41/42) | `RmaCase` model |
| 8.13 Campaigns (FR-43..45) | `Campaign` + `CampaignAssignment` models, burn-down endpoint |
| 8.15 Intake & KB (FR-56/57) | `IntakeRequest` model + Customer Portal extension (native) |
| 10 Integrations (INT-1/2/3/5) | `Integrations/RelayGarage`, `Integrations/Reach`, `Integrations/Geotab` clients |

See [DELIVERY-PLAN.md](./DELIVERY-PLAN.md) for the phased build-out.

## Local development activation

### API (Laravel)

The dev stack runs the API from the `fleetbase/fleetbase-api` image, so the package must be
mounted into the container and registered with composer:

1. Add a bind mount in `docker-compose.override.yml`:

    ```yaml
    application:
      volumes:
        - ./packages/commandiq:/fleetbase/packages/commandiq
    ```

2. Inside the container (`docker compose exec application bash`):

    ```bash
    composer config repositories.commandiq path ../packages/commandiq
    composer require ifs/commandiq-api:*
    php artisan migrate
    php artisan config:cache && php artisan route:cache
    ```

### Console (Ember)

Use the repo's package linker to wire the engine into the console workspace, then rebuild:

```bash
node scripts/package-linker.mjs enable commandiq --install
docker compose build console && docker compose up -d console
```

The engine registers a **CommandIQ** header menu with Forecast Board, Scheduler, QC Queue,
Campaigns, Warranty Claims, RMA/Returns, and Intake Requests sections.

## Configuration

All settings live in `server/config/commandiq.php` (forecast horizon, LM lookback,
major-hub set, QC closer role, integration credentials via env vars: `RELAY_GARAGE_*`,
`REACH_*`, `GEOTAB_*`).

Per AC-1/AC-3: integrations use sanctioned APIs with approved authentication only — no
user portal credentials are stored.

## Known issue — console engine rendering

The engine builds, installs, and loads on the cloud deployment (lazy bundle fetched,
routes hoisted, mount generated, `setupExtension` bundle registered in the asset
manifest via `deploy/vast/patch-manifest.py`) — but its route templates render blank
and direct navigation to `/commandiq` stalls the boot loader. No runtime errors are
thrown. Suspected interplay between the console's custom extension loader and
non-@fleetbase-scoped lazy engines. The API side (models, migrations, endpoints,
seeded data) is fully functional. Track this against the console's
`load-extensions` initializer before the v1 forecast board work.
