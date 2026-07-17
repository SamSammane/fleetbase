# IFS CommandIQ — Live Demo Scenario

**Audience:** CBRE stakeholders (Patrick Houlihan, Denzil Dsouza; cc Ben Cook, Jen Holibaugh)
**Presenter:** Sam Sammane (QGI)
**Duration:** ~30 minutes + Q&A
**Format:** Screen-share of the live cloud console, no slides required

## Environments

| Surface | URL | Notes |
|---|---|---|
| Live console | <https://fleet-app.qgi.dev> | Cloudflare Access — invitees log in with their email + one-time PIN |
| API | <https://fleet-api.qgi.dev> | Backing the console; not shown directly |
| Walkthrough page (leave-behind) | <https://fleet-demo.qgi.dev> | Static screenshot tour; same Access invitee list |

Demo admin: `sam@qgi.dev` (password shared separately — do not write it into this file).

---

## Pre-demo checklist (T-30 minutes)

1. Vast instance **44065900** is running (`vastai show instance 44065900`) — the entire
   stack lives on it.
2. Open <https://fleet-app.qgi.dev> in a fresh browser profile: confirm the Access PIN
   flow works and the sign-in page shows the **IFS mark** (circular green/grey ring).
3. Sign in once and confirm the dashboard loads with the fleet map centered on Dallas.
4. Keep one tab logged in (your walkthrough tab) and one incognito tab ready
   (to show the invitee login experience if asked).
5. Run the AI regression check on the instance (60 seconds):
   `php artisan tinker --execute="require '/opt/deploy/test-ai-caps.php';"` from
   `/opt/fleetbase/api` — expect `ALL_CHECKS_PASSED`. It verifies every demo AI
   question activates its capability with real data, and that the dataset floor
   (fleet, orders, revenue) is intact.
6. Fallback if the live console is unreachable: present <https://fleet-demo.qgi.dev>
   (static tour with the same story), and reschedule the interactive portion.

---

## The story

> IFS runs maintenance, device retrofit, and repair programs across two very different
> fleets: **Last-Mile** delivery vans that come home to a station every night, and
> **Middle-Mile** trailers that roam the national network. Today's platform —
> **IFS CommandIQ**, built on the CBRE Fleet platform core — is the system of record
> for those assets and the work done on them. The next releases add the differentiator:
> *forecasting where every asset will be, and scheduling the work into those windows.*

The seeded dataset tells one coherent story — every screen below reinforces it:

- **6 LM vans** (Ford Transit / Ram ProMaster) operated by DSPs **RRLG / BLZE / SWFT**
  out of **DAL3 (Dallas)** and **DAU5 (Austin)** delivery stations
- **2 MM trailers** touching **FTW1 (Fort Worth)** and **SAT2 (San Antonio)** hubs
- **6 work orders** spanning the full lifecycle, including two retrofit campaigns
  (TELEM-24 telematics install, CONSP-26 conspicuity tape)
- Serialized **Netradyne cameras / Geotab GO9** devices, PM schedules, parts inventory
  with a live reorder flag, and active warranties

---

## Act 1 — Access & branding (3 min)

1. Open <https://fleet-app.qgi.dev> (incognito first, if you want to show the gate).
   - **Say:** "Access is restricted at the network edge — only the five of you plus our
     team can even reach this page. You'll get a one-time PIN by email; there's nothing
     to install."
2. Sign in at the console login (IFS mark, IFS CommandIQ title).
   - **Say:** "This is a fully white-labeled deployment — IFS CommandIQ brand,
     CBRE Fleet platform naming throughout, running on our cloud."

## Act 2 — Command dashboard & live map (4 min)

1. Land on the **Default Dashboard**.
   - Point out: Live Fleet Map with the vans clustered around Dallas stations,
     Maintenance Overview (overdue / next 7 days / month-to-date spend),
     earnings & orders KPIs.
   - **Say:** "One pane of glass — assets, maintenance posture, and cost posture.
     Program KPIs land here as widgets, so a CBRE program view is a configuration
     exercise, not a rebuild." (FR-15)

## Act 3 — The fleet, VIN by VIN (5 min)

1. **Fleet-Ops → Resources → Vehicles** — the 8-asset registry.
   - **Say:** "Every asset is segmented Last-Mile or Middle-Mile, with its DSP and home
     station — the segmentation drives how we'll forecast availability." (FR-1, FR-17)
2. Click **LM Van 101** → the detail panel.
   - Point out: VIN `1FTBW3XM5PKA10141`, plate, odometer, live coordinates, and the
     tabs — **Devices** (serialized installed base), **Schedules**, **Work Orders**,
     **Maintenance history**.
   - **Say:** "Everything keys off the VIN. The camera on this van is tracked by serial
     number with its install date — that's the installed base that failure-rate and
     warranty analytics run on." (FR-24, FR-38)
3. **Resources → Places** — DAL3, DAU5, FTW1, SAT2.
   - **Say:** "These are the locations we forecast against — stations for the vans,
     hubs for the trailers." (FR-19, FR-21)

## Act 4 — Maintenance operations (8 min)

1. **Maintenance → Work Orders** — the six-order board.
   - Walk the statuses left to right: submitted → scheduled → in progress → repaired →
     approved → closed. Note the categories: corrective, preventive, device replacement,
     and the two **campaigns**.
   - Open the brake-pad order (critical): show the VIN-keyed instructions with station,
     DSP contact, photo requirements, and the audit trail.
   - **Say:** "Every work order is VIN-keyed and carries the DSP contact. Every change is
     name-and-timestamp logged. QC closure rights are role-restricted — a technician can
     mark work repaired, but only QC can close." (FR-11/12/24/25, FR-28)
2. **Maintenance → Schedules** — the PM triggers.
   - **Say:** "Distance-based preventive triggers — 5,000 miles on the vans, 25,000 on
     the trailer. The scheduler evaluates these daily and auto-creates the work order
     when a unit comes due." (FR-9)
3. **Maintenance → Parts** — the inventory.
   - Point at the mirror assembly: 3 on hand, flagged **Reorder**.
   - **Say:** "Camera kits, GO9 units, tape, repair parts — consumption against reorder
     points, so campaign material never becomes the bottleneck." (FR-31/32)

## Act 5 — The roadmap: what CommandIQ adds (5 min)

Use the walkthrough page's roadmap table (<https://fleet-demo.qgi.dev>) or speak to it:

- **v1 — Availability forecasting** (the differentiator): learn DSP return-time patterns
  per station and weekday; project *availability windows*; for trailers, forecast hub
  arrivals with ETA. Work gets scheduled into windows the asset was already going to be
  in — no out-of-route miles, no waiting on a vehicle that isn't there. (FR-3–5, 19–22)
- **v2 — Scheduling, QC, and lifecycle**: auto-matching work orders to windows,
  technician skill and shop-capacity constraints, least-stops day routing, the QC
  approve/reject loop, campaigns with burn-down, warranty claims, and RMA/depot returns.
  (FR-23, 27–30, 36–45, 50/51, 100/101)
- **Gate to call out honestly:** the forecasting engine needs sanctioned Relay Garage /
  REACH data access (spec AC-1) — that confirmation is the critical path, and it's a
  CBRE/Amazon action item, not an engineering one.

## Close (2 min)

- Recap: live today (registry, work orders, PM, parts, warranty, serialized devices) vs
  v1/v2 (forecasting, matched scheduling, QC workflow, campaigns/claims/RMA).
- The quote covers v1 scope and timeline; the walkthrough page stays available to them
  behind the same login.
- Ask: confirmation on Relay Garage / REACH data access, and the "major hub" list.

---

## Likely questions & answers

| Question | Answer |
|---|---|
| "Is this mocked?" | No — every screen is a live application over a real database. Offer to create a work order live. |
| "Where does it run?" | QGI cloud for the demo; production hosting/data residency is an open spec item (AC-4) — deployable to CBRE-preferred infrastructure. |
| "How do drivers/technicians use it?" | Technician mobile app (offline-capable, photo capture, barcode lookup) is scoped in v2 planning; the API it consumes already exists. |
| "Can we see our own branding?" | Already done — branding is runtime-configurable (Admin → Branding), demonstrated by this very deployment. |
| "What about our data feeds?" | Integration clients for Relay Garage, REACH, and Geotab are scaffolded; they activate once sanctioned credentials are provided (AC-1/AC-3 — no stored portal logins). |
| "Security?" | Network-edge allowlist (Cloudflare Access) + role-based access in-app + full audit logging on work orders. |

## Fallbacks

- **Console down:** present <https://fleet-demo.qgi.dev> (same story, static screenshots).
- **A page errors mid-demo:** move to the next act; the acts are independent.
- **Attendee can't get a PIN:** their email must match the invitee list exactly —
  additions take one minute (Cloudflare Access policy), or proceed on your screen-share.
