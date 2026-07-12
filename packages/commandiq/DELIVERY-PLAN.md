# IFS CommandIQ — Phased Delivery Plan

Maps the functional specification (v0.4) onto the CBRE Fleet platform running in this repo.
Each phase ships independently usable value; FR numbers reference the spec.

**Platform baseline (already running):** CBRE Fleet v0.7.51 (white-labeled Fleetbase core) branded as IFS CommandIQ —
FleetOps (vehicles, assets, work orders, maintenance schedules, parts, warranties, serialized
vehicle devices, telematics registry, VROOM route optimizer), IAM (roles/policies), Customer
Portal, AI engine, dashboards, webhooks, real-time socket layer.

---

## Phase 0 — Foundations & data rights (1–2 weeks, mostly non-engineering)

The critical path. Nothing in Phase 2 can be validated without it.

- Confirm sanctioned access + refresh cadence for Relay Garage and REACH (AC-1, Section 11).
  Obtain written data-use scope (AC-2).
- Confirm Geotab account/API access (INT-2).
- Define the MM "major hub" set and the criticality model for FR-10.
- Decide hosting/data residency (AC-4).
- Stand up IAM roles: Program Manager, Scheduler, Technician, QC Reviewer, DSP, Admin
  (Section 5) — pure configuration, no code.
- **Exit criteria:** signed data-use scope; API credentials in hand; roles created.

## Phase 1 — Asset backbone & work order operations (2–3 weeks)

Make the platform the system of record for the fleet and manual work order flow.

- **FR-1/2/17/18**: Relay Garage vehicle-registry sync (`commandiq:sync-relay-garage`);
  LM/MM segment classification; maintenance-status tracking with write-back to Relay Garage.
- **FR-11/12/24/25**: adopt FleetOps `WorkOrder` with CommandIQ lifecycle statuses
  (submitted → scheduled → in progress → repaired → QC → approved → closed); VIN-keyed
  auto-creation populating Station ID, DSP code, Asset ID, DSP contact; audit trail via
  the platform activity log.
- **FR-26/49**: required-photo-set validation per WO type; labor time capture.
- CSV work-order import (FR-11 "from a csv").
- **Exit criteria:** full fleet visible in console; work orders created manually, via CSV,
  and via API; every change audited.

## Phase 2 — Forecasting engine (3–4 weeks) ← the differentiator

- **FR-19/20 (LM)**: DSP return-pattern learning (`ReturnPattern`) from telematics arrival
  history; projected `AvailabilityWindow` rows over the configured horizon; pre-dispatch
  location validation against live telematics.
- **FR-21/22 (MM)**: hub-arrival forecasting from travel patterns; arrival notifications.
- **FR-3/4/5**: forecast board UI in the console engine (windows by asset, location, time).
- **INT-2**: Geotab registered as a telematics provider in the FleetOps telematics registry
  (reuses `fleetops:sync-telematics` infrastructure).
- **FR-16**: forecast-vs-actual accuracy tracking from day one.
- **Exit criteria:** ≥1 station cohort with measured forecast accuracy; forecast board live.

## Phase 3 — Scheduling & QC (3–4 weeks)

- **FR-6/7/8/23**: `commandiq:match-work-orders` proposes WO ↔ window matches; scheduler
  confirm/adjust/override UI; conflict detection.
- **FR-50/51**: skill/certification matching (CBRE-provided data) and shop/bay/crew capacity
  model.
- **FR-100/101**: technician day-routing via the platform's VROOM optimizer (8-hour day,
  fewest stops).
- **FR-27..30/58**: QC queue — approve/reject endpoints wired to IAM (`QC Reviewer` role is
  the only closer, FR-28); rejection feedback loop to technician + supervisor; rework
  analytics; digital inspection checklists.
- **FR-14**: notifications for matches, schedule changes, missed windows (platform
  notification registry; channels per Section 11 decision).
- **Exit criteria:** end-to-end flow — forecast → match → schedule → execute → QC → close.

## Phase 4 — Lifecycle domains (3–4 weeks, parallelizable)

- **FR-36/37**: warranty coverage detection at WO creation (native `Warranty`) + claims
  workflow (`WarrantyClaim`) with recovery tracking.
- **FR-38..40**: installed-base history on `VehicleDevice` (install/replacement chain,
  batch/lot failure analysis).
- **FR-41/42**: RMA workflow (`RmaCase`) — repair/replace/advance-replacement dispositions,
  core credits, multi-depot.
- **FR-43..45**: campaigns with burn-down reporting and bundling into scheduled visits.
- **FR-31/32/55**: barcode check-in/out on `Part`, reorder points, parts-demand forecasting.
- **FR-53**: DTC ↔ warranty/TSB/recall cross-referencing.
- **FR-56/57**: intake portal (Customer Portal extension) + knowledge base.
- **Exit criteria:** each domain usable standalone from the CommandIQ menu.

## Phase 5 — Mobile field app (4–6 weeks, can start after Phase 1 API stabilizes)

- **FR-46/47/48, NFR-7**: offline-first technician app (React Native, same API) — photo
  capture with required-set enforcement, signature capture, QR/barcode asset lookup with
  history + docs, sync-on-reconnect.
- **FR-102**: photo → work-order creation using the platform AI engine (vision).
- **Exit criteria:** technician completes a WO end-to-end offline, syncs, and it lands in QC.

## Phase 6 — Analytics, SLAs & hardening (2–3 weeks)

- **FR-15/16/33/34/35/52**: program status & burn-down, forecast accuracy, technician
  performance (throughput, QC rejection rate), failure-rate forecasting from
  device-replacement cases, cost/TCO and cost-per-mile reporting.
- **Section 14 SLAs**: measurement + daily/weekly report automation (10:00 AM CST schedules),
  % to Plan ≥85%, First Pass Yield ≥99%, downtime ≤12h tracking.
- **FR-104 (future)**: AI photo inspection on "repaired" transition.
- NFR pass: load, uptime target, security review, audit-log verification (NFR-1..6).

---

## Deferred (Section 12)
Purchase-order issuance · driver-facing mobile experience · financial settlement/invoicing.

## Standing risks
1. **Data access is the schedule.** Phases 2+ assume Relay Garage/REACH data arrives at a
   usable cadence — Phase 0 exit criteria are hard gates.
2. **Platform upgradeability.** All CommandIQ code stays in this extension package; core
   files are never modified, so upstream platform updates remain a `docker compose pull` away.
3. **Open spec questions** (Section 11) are each pinned to the phase that needs the answer;
   resolve no later than the prior phase's exit.
