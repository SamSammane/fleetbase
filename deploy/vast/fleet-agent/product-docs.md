Integrated Fleet Solutions 

# IFS CommandIQ Documentation

 IFS CommandIQ is the maintenance command platform for CBRE-managed fleet programs,
 built on the CBRE Fleet platform core. It is the system of record for assets, work
 orders, preventive maintenance, parts, and — in upcoming releases — availability
 forecasting and availability-matched scheduling.

 Two operating segments drive everything: Last-Mile vans return to an assigned
 station on a recurring daily pattern; Middle-Mile trailers travel nationally and
 periodically reach major hubs. Assets carry their segment, DSP, and home station, and
 the platform's forecasting and scheduling behavior follows from them.

## Signing in

- Open https://fleet-app.qgi.dev . 

- At the network gate, enter your work email and choose Send me a code . Enter the one-time PIN from your inbox. Access is limited to invited emails; sessions last 24 hours. 

- At the IFS CommandIQ sign-in, use your console account credentials. If you don't have an account yet, ask your program administrator for an invite. 

## Dashboard & live map

 The home dashboard is a widget board — KPIs (earnings, active orders), the Live Fleet Map , maintenance posture (overdue, next 7 days, month-to-date spend), and financial activity. Widgets can be added, resized, and rearranged per user. 
 The Live Fleet Map (also full-screen under Fleet-Ops) shows every online asset in real time over websockets. Icons update as vehicles move; click any unit for its detail panel. 

## Fleet & assets

 Under Fleet-Ops → Resources : 

- Vehicles — the asset registry. Each record is VIN-keyed with plate, make/model/year, odometer, live location, and segment metadata (LM/MM, DSP code, home station). 

- Drivers — operator profiles with assignments and live status. 

- Places — geocoded delivery stations and hubs; the locations forecasting works against. 

 Open a vehicle to see its tabs: Devices (serialized installed base — cameras, telematics units, with serials and install dates), Schedules , Work Orders , and Maintenance history . 

## Maintenance & work orders

 Under Fleet-Ops → Maintenance . Work orders are VIN-keyed and carry station, DSP contact, priority, cost estimates, and photo requirements. Every change is name-and-timestamp audit-logged. 

### Lifecycle

 | 
 | Status | Meaning 

 | Submitted | Created from intake, PM trigger, campaign, or manually 

 | Scheduled | Matched to a service window and technician 

 | In progress | Technician on the work 

 | Repaired | Technician done — awaiting QC review 

 | Approved | QC accepted the evidence 

 | Closed | Final. Only the QC role can close a work order 

### Preventive maintenance

 Schedules hold distance-, time-, or engine-hour-based triggers (e.g. every 5,000 mi on vans, 25,000 mi on trailers). The platform evaluates them daily and auto-creates work orders as units come due. 

## Parts & inventory

 Maintenance → Parts tracks SKUs, serials, quantities on hand, and unit costs. Items at or below their reorder point are flagged Reorder so campaign material and repair parts never become the bottleneck. 

## CBRE Fleet AI assistant Claude-powered

 Click the brain icon in the header to open the assistant — a docked panel that stays open while you work. Ask it operational questions in plain language: 

- "Which vehicles are overdue for PM?" 

- "Summarize open work orders at DAL3 by priority." 

- "Which parts are below reorder point?" 

 The assistant answers from live platform data and never claims an action happened unless the platform confirmed it. Conversations are logged and auditable (Admin → AI Config → Task & Chat Logs). 

## Availability forecasting v1

 The CommandIQ forecasting engine learns DSP return-time patterns per station and weekday, and projects availability windows — periods when an asset will be at a serviceable location long enough to work on. For trailers, it forecasts hub arrivals with ETAs. Work is then scheduled into windows the asset was already going to be in: no out-of-route miles, no waiting on a vehicle that isn't there. 
 Window data is already served by the API at int/v1/availability-windows ; the visual forecast board ships with v1. 

## Campaigns, claims & RMA v2

- Campaigns — retrofit/remediation programs (e.g. telematics installs, conspicuity tape) tracked against an assigned VIN population with burn-down reporting; non-urgent campaign work bundles into already-scheduled visits. 

- Warranty claims — coverage detection at work-order creation, then claim tracking through submission, adjudication, and recovery. 

- RMA / depot returns — failed devices through repair / replace / advance-replacement dispositions with core-credit tracking across depots. 

- QC queue — review completed work with checklists and photo evidence; approve to close or reject back to the technician with reasons. 

## Roles & permissions

 | 
 | Role | Can 

 | Program Manager | Full program visibility, schedule control, reporting 

 | Scheduler / Coordinator | Build and adjust the service schedule, confirm window matches 

 | Technician | Work assigned orders, capture photos and labor, mark repaired 

 | QC Reviewer | Review completed work; the only role that can close a work order 

 | Administrator | Users, roles, branding, integrations, AI configuration 

 Roles are managed under IAM ; every sensitive action is captured in the audit log. 

## FAQ

### The map shows a vehicle in the wrong place.

 Positions come from telematics; check the unit's device status on the vehicle's Devices tab. Stale devices show their last known position with a last-seen timestamp. 

### Why can't I close a work order?

 Closure is restricted to the QC Reviewer role by design — technicians mark work repaired , QC closes after evidence review. 

### Can we change the branding or logo?

 Yes — Admin → Branding accepts icon and logo uploads and applies them platform-wide immediately. 

### Where does the AI get its answers?

 From your live platform data through governed capability context, processed by Claude. It cannot execute actions without a confirmed tool result, and everything is logged. 

## Support

 Program contact: Sam Sammane, QGI — sam@qgi.dev . For access issues (PIN not arriving, new invitees), or to request accounts and roles, email with your work address and the access you need. 

 IFS CommandIQ · Integrated Fleet Solutions · Technology Solutions — Maintenance & Repair — Reliability — Uptime Optimization