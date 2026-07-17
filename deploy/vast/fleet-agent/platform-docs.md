<!-- SECTION: Core concepts -->
# Core concepts

# Core Concepts

Understand the key building blocks of Fleet-Ops: orders, payloads, drivers, vehicles, fleets, and how they relate.

# [Core Concepts](https://fleet-app.qgi.dev/docs/)

Fleet-Ops is built around a set of entities that represent the real-world objects in your logistics operation. Understanding how these entities relate to each other is the key to using Fleet-Ops effectively.

## [Orders](https://fleet-app.qgi.dev/docs/)

An **Order** is the central entity — it represents a delivery task or service job. Every order has:

- A **status** that progresses through a lifecycle (created → dispatched → started → completed)
- A **payload** containing the pickup location, dropoff location, and items being delivered
- A **driver** and **vehicle** assigned to execute it
- An **Order Configuration** that defines the workflow, fields, and proof of delivery method

Orders are created in the console, dispatched to drivers via the Navigator app, and completed when proof of delivery is captured.

### [Order Statuses](https://fleet-app.qgi.dev/docs/)

Fleet-Ops has five **system order statuses**. They drive the high-level lifecycle Fleet-Ops uses for dispatch, billing, and reporting.

| Status | Always present? | Meaning |
| --- | --- | --- |
| `created` | Yes | The order has been created and is awaiting dispatch |
| `dispatched` | Yes | The order has been sent to a driver |
| `started` | Yes | The driver has accepted and is working the order |
| `canceled` | Yes | The order was cancelled before completion |
| `completed` | Configurable | The order has been delivered |

The first four — `created`, `dispatched`, `started`, `canceled` — are **static**. They exist on every order and cannot be removed. The `completed` status, however, can be replaced or supplemented by **any custom activity that has its `complete` flag set to `true`** in the order's activity flow. When that activity fires (for example, _Items Handed Over_ or _Signature Captured_), Fleet-Ops marks the order as completed. This means an Order Configuration can define its own terminating step under whatever name fits the operation, while still ending the order's lifecycle.

What happens **between `started` and the order's completion activity** is fully configurable. The intermediate steps a driver moves through — _arrived at pickup_, _items loaded_, _en route to dropoff_, and so on — are defined by the **activity flow** on the Order Configuration. Different order types can have completely different activity flows while still sharing the same system-status backbone.

See [Order Configurations](https://fleet-app.qgi.dev/docs/) and [Activity Flows](https://fleet-app.qgi.dev/docs/) for how to design custom workflows.

## [Payload](https://fleet-app.qgi.dev/docs/)

A **Payload** is the container attached to an order. It defines:

- **Pickup** — where the driver collects the items
- **Dropoff** — the primary delivery destination
- **Return** — an optional return-to-sender address if delivery fails
- **Entities** — the individual items being transported (each with its own tracking number)
- **Waypoints** — intermediate stops for multi-drop routes

Each entity in the payload can have its own dimensions, weight, barcode/SKU, and destination. For multi-stop routes, each waypoint can require its own proof of delivery.

## [Order Configuration](https://fleet-app.qgi.dev/docs/)

An **Order Configuration** (or Order Config) is a template that defines how a particular type of order works. It includes:

- The **activity flow** — the sequence of status steps drivers move through
- **Entity definitions** — what fields are required on each item (description, weight, custom fields)
- **POD method** — signature, photo, QR code, or custom data
- **Custom fields** — order-level fields specific to your business

You can have multiple order configs for different service types: standard delivery, pickup & delivery, return-to-sender, multi-drop courier, and so on. Each order selects one config at creation.

See [Order Configurations](https://fleet-app.qgi.dev/docs/) for details.

## [Drivers](https://fleet-app.qgi.dev/docs/)

A **Driver** is a person who executes orders using the Navigator mobile app. Each driver record links to a CBRE Fleet user account and stores:

- Real-time location (latitude, longitude, heading, speed)
- Online/offline status
- Current job assignment
- Skills and constraints used by the Orchestrator (max distance, time window, required skills)
- Shift schedule

Drivers use the Navigator app to receive dispatched orders, navigate routes, update status at each stop, and capture proof of delivery.

## [Vehicles](https://fleet-app.qgi.dev/docs/)

A **Vehicle** is a fleet asset assigned to carry out orders. Vehicle records store:

- Make, model, year, body type, color
- Plate number and VIN
- Capacity (weight in kg, volume in m³)
- Real-time location (from telematics or Navigator app)
- Status: active, inactive, maintenance, offline

Vehicles can have connected devices (GPS trackers, OBD-II scanners, cameras, sensors) through the Connectivity module, and maintenance schedules tracked through the Maintenance module.

## [Fleets](https://fleet-app.qgi.dev/docs/)

A **Fleet** is a logical grouping of drivers and vehicles. Fleets help you organize resources by:

- Geographic zone or service area
- Service type (express, standard, overnight)
- Shift (morning fleet, evening fleet)
- Vendor or subcontractor

Fleets are hierarchical — a top-level fleet (e.g., "Metro") can contain sub-fleets (e.g., "Metro North", "Metro South"). The Orchestrator uses fleet membership to scope order allocation.

## [Places](https://fleet-app.qgi.dev/docs/)

A **Place** is a saved location — a reusable address used as pickup or dropoff points. Places have full address details, precise coordinates, and a contact phone number. They can be linked to Contacts (customers, suppliers) and appear on the live map.

## [Contacts](https://fleet-app.qgi.dev/docs/)

**Contacts** are the businesses and people your operation works with — customers who place orders, suppliers who provide pickups, and facilitators who coordinate deliveries. Contacts have types (customer, supplier, facilitator) and can be linked to specific Places.

## [Vendors](https://fleet-app.qgi.dev/docs/)

**Vendors** are third-party carriers or service providers. Unlike internal drivers, vendors can have their own driver pools and fleets. CBRE Fleet supports **Integrated Vendors** for platforms like Lalamove — where orders can be relayed to the partner's API and quotes fetched automatically.

## [Service Areas & Zones](https://fleet-app.qgi.dev/docs/)

A **Service Area** is a polygon-drawn geographic boundary defining where your operation works. Inside a service area you can define **Zones** — smaller sub-regions. Service areas and zones are used to:

- Scope service rates (different pricing per zone)
- Filter fleet assignment in the Orchestrator
- Trigger geofence events (entry/exit/dwell time)

## [Service Rates](https://fleet-app.qgi.dev/docs/)

**Service Rates** define pricing rules for orders. A rate can be:

- A flat base fee
- Distance-based (per km/mile)
- Weight or volume-based
- Zone-to-zone fixed price
- Tiered by distance brackets

Rates support surcharges for COD (cash on delivery), peak hours, and parcel size. Multiple rates can coexist, scoped to different service areas, order configs, or customer types.

## [Orchestrator](https://fleet-app.qgi.dev/docs/)

The **Orchestrator** is a multi-phase planner that turns a pool of unassigned orders into a committable plan. Rather than running a single fixed pipeline, you compose **phases** in the Orchestrator Workbench and stack them in sequence — for example _Assign Vehicles → Optimize Routes → Assign Drivers_. Each phase runs one mode (vehicle allocation, driver allocation, route optimization, or full allocation) using a configurable engine. You preview the proposed plan and commit it when you're satisfied — or run a single phase on its own when that's all you need.

See [Orchestrator Overview](https://fleet-app.qgi.dev/docs/) for details.

## [Manifests](https://fleet-app.qgi.dev/docs/)

After the Orchestrator commits an assignment plan, it creates a **Manifest** for each driver — an ordered list of stops (orders) they need to complete, with estimated distances and arrival times. The manifest is the driver's physical delivery plan for a shift.

## [How It All Connects](https://fleet-app.qgi.dev/docs/)

```
Order Config ──────────────────────── defines activity flow + entity fields
     │
     ▼
  Order ──── Payload ── Pickup / Dropoff / Entities / Waypoints
     │           │
     │           └── TrackingNumber (per entity)
     │
     ├── Driver (assigned)
     ├── Vehicle (assigned)
     ├── ServiceQuote (pricing)
     ├── Route (calculated directions)
     ├── Proofs (signature / photo / QR)
     └── Manifest (after Orchestrator commit)
            └── ManifestStop (each order in the manifest)


<!-- SECTION: Orders: managing -->
# Orders: managing

Orders

# Managing Orders

Create, edit, assign, dispatch, and manage orders from the Fleet-Ops orders list and detail views.

# [Managing Orders](https://fleet-app.qgi.dev/docs/)

This page covers the day-to-day operations for managing orders in the Fleet-Ops console — creating new orders, assigning drivers, dispatching, and handling exceptions.

## [Creating an Order](https://fleet-app.qgi.dev/docs/)

### [Navigate to Operations → Orders](https://fleet-app.qgi.dev/docs/)

Click **Fleet-Ops** in the top nav, then **Operations → Orders** in the sidebar.

### [Click New Order](https://fleet-app.qgi.dev/docs/)

Click **\+ New Order**. The new order form opens.

### [Select an Order Configuration](https://fleet-app.qgi.dev/docs/)

Choose the **Order Config** that matches the type of delivery. The config determines the workflow steps, required entity fields, and proof of delivery method.

### [Set the customer (optional)](https://fleet-app.qgi.dev/docs/)

Select or create a **Customer** contact. The customer is the recipient of the order — used for notifications and tracking page access.

### [Enter Pickup and Dropoff](https://fleet-app.qgi.dev/docs/)

Enter the **Pickup** address (where items originate) and the **Dropoff** address (primary delivery destination). You can:

- Type an address and select from the geocoder suggestions
- Select a saved Place from your Places library
- Drop a pin on the map

For multi-stop orders, add **Waypoints** using the **\+ Add Waypoint** button.

### [Add Entities (optional)](https://fleet-app.qgi.dev/docs/)

Click **Add Entity** to attach items to the order. Each entity can have:

- Description and SKU/barcode
- Quantity
- Dimensions (length, width, height) and weight
- A destination waypoint (for multi-drop)
- Custom fields defined by the Order Config

Entities each receive their own tracking number automatically.

### [Set scheduling (optional)](https://fleet-app.qgi.dev/docs/)

Enable **Scheduled** and set a pickup date and time if the order should not be dispatched immediately.

### [Add notes and metadata](https://fleet-app.qgi.dev/docs/)

Add any **Notes** visible to the driver and operators, and an **Internal ID** for cross-referencing with your external system.

### [Save](https://fleet-app.qgi.dev/docs/)

Click **Save**. The order is created with status **`created`**. If a scheduled date and time were set, the order remains in `created` until you choose to dispatch it.

## [Assigning a Driver](https://fleet-app.qgi.dev/docs/)

To assign a driver manually:

Open the order detail by clicking the order row.

Click **Assign Driver** in the detail panel.

Select a driver from the available drivers list. Optionally select a vehicle.

Click **Assign**. The driver and vehicle are now linked to the order. The order's system status remains **`created`** until it is dispatched — assignment alone does not change the system status.

For bulk assignment across many orders, use the [Orchestrator](https://fleet-app.qgi.dev/docs/).

## [Dispatching an Order](https://fleet-app.qgi.dev/docs/)

Once a driver is assigned, dispatch the order to the driver's Navigator app:

Open the order detail.

Click **Dispatch**. A confirmation dialog shows the driver name and vehicle.

Confirm the dispatch. The order status moves from **`created`** to **`dispatched`** and the driver receives a push notification.

You can dispatch immediately after assigning a driver, or hold the order in `created` status until you're ready to send it to the driver.

## [Editing an Order](https://fleet-app.qgi.dev/docs/)

Order editing is split between two distinct buttons in the detail panel — **Edit** and **Edit Route**. They open different surfaces because they affect different parts of the order.

### [Edit Details](https://fleet-app.qgi.dev/docs/)

Click **Edit** in the order detail panel to open the **Edit Order** form. This surface covers everything that isn't the geometry of the route:

- Customer and facilitator
- Driver and vehicle assignment
- Scheduled date/time and time window
- Order Configuration custom fields and entity fields
- Notes and internal ID
- Service rate / payment overrides

### [Edit Route](https://fleet-app.qgi.dev/docs/)

Click **Edit Route** to open the **Order Route Editor**. This is a map-driven surface for changing the order's pickup, dropoff, and waypoints — including reordering stops and re-running optimization.

From the route editor you can:

- Add, remove, or reorder waypoints
- Replace pickup or dropoff with a different address or saved Place
- Re-run **Route Optimization** to compute the optimal stop sequence

Editing the route on a dispatched or started order recalculates the route and may require re-dispatching. The driver is notified of route changes.

## [Bulk Actions](https://fleet-app.qgi.dev/docs/)

From the orders list, select multiple orders using the checkboxes, then use the bulk action menu to:

- **Assign** — assign all selected orders to a driver at once
- **Dispatch** — dispatch all assigned orders simultaneously
- **Cancel** — cancel multiple orders with a single reason
- **Export** — export the selected orders to CSV

## [Cancelling an Order](https://fleet-app.qgi.dev/docs/)

Open the order detail and click **Cancel Order**. Enter an optional cancellation reason. The status moves to **`canceled`** and:

- The driver is notified (if already dispatched)
- A cancellation webhook event fires (`order.canceled`)
- The order remains in the list for audit purposes

## [Order Filters and Search](https://fleet-app.qgi.dev/docs/)

Click the **Filters** icon in the orders list toolbar to open the full filter panel. Apply any combination, then click **Apply** to narrow the list (or **Clear** to reset).

| Filter | What it matches |
| --- | --- |
| **ID** | Match on the order's public ID (e.g. `order_qT4zTdE…`) |
| **Internal ID** | Match on your external `internal_id` reference |
| **Payload ID** | Match on a specific payload UUID |
| **Driver Assigned** | Orders assigned to a specific driver |
| **Pickup** | Orders whose pickup is a specific saved Place |
| **Dropoff** | Orders whose dropoff is a specific saved Place |
| **Customer** | Orders for a specific customer contact |
| **Vehicle Assigned** | Orders assigned to a specific vehicle |
| **Facilitator** | Orders associated with a specific facilitator |
| **Scheduled At** | Filter by `scheduled_at` date |
| **Tracking** | Match on tracking number |
| **Type** | Filter by Order Configuration |
| **Status** | Filter by system status — Created, Dispatched, Started, Completed, Canceled |
| **Created** / **Updated** | Filter by `created_at` / `updated_at` date |
| **Created By** / **Updated By** | Filter by the user who created or last updated the order |
| **Without Driver Assigned** | Toggle to show only orders that have no driver linked |

Use the search bar above the filter panel to do a free-text search across order ID, customer name, driver name, and address.

## [Exporting Orders](https://fleet-app.qgi.dev/docs/)

Click **Export** in the orders list to download a CSV of all orders matching the current filter. Exports include all order fields, customer details, and status history.

[Order Lifecycle\\
\\
Understand the system statuses every order moves through, how the activity flow runs inside the started phase, and how an order is completed.](https://fleet-app.qgi.dev/docs/) [Kanban Board\\
\\
Manage orders visually by status column — see all active orders at a glance, drag to reassign, and filter by driver or fleet.](https://fleet-app.qgi.dev/docs/)

### On this page

[Managing Orders](https://fleet-app.qgi.dev/docs/) [Creating an Order](https://fleet-app.qgi.dev/docs/) [Navigate to Operations → Orders](https://fleet-app.qgi.dev/docs/) [Click New Order](https://fleet-app.qgi.dev/docs/) [Select an Order Configuration](https://fleet-app.qgi.dev/docs/) [Set the customer (optional)](https://fleet-app.qgi.dev/docs/) [Enter Pickup and Dropoff](https://fleet-app.qgi.dev/docs/) [Add Entities (optional)](https://fleet-app.qgi.dev/docs/) [Set scheduling (optional)](https://fleet-app.qgi.dev/docs/) [Add notes and metadata](https://fleet-app.qgi.dev/docs/) [Save](https://fleet-app.qgi.dev/docs/) [Assigning a Driver](https://fleet-app.qgi.dev/docs/) [Dispatching an Order](https://fleet-app.qgi.dev/docs/) [Editing an Order](https://fleet-app.qgi.dev/docs/) [Edit Details](https://fleet-app.qgi.dev/docs/) [Edit Route](https://fleet-app.qgi.dev/docs/) [Bulk Actions](https://fleet-app.qgi.dev/docs/) [Cancelling an Order](https://fleet-app.qgi.dev/docs/) [Order Filters and Search](https://fleet-app.qgi.dev/docs/) [Exporting Orders](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Managing Orders \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Orders: lifecycle -->
# Orders: lifecycle

Orders

# Order Lifecycle

Understand the system statuses every order moves through, how the activity flow runs inside the started phase, and how an order is completed.

# [Order Lifecycle](https://fleet-app.qgi.dev/docs/)

Every order in Fleet-Ops moves through a small set of **system statuses** that drive dispatch, billing, reporting, and webhook events. Inside those statuses — particularly between `started` and the order's completion — the **activity flow** defined on the Order Configuration runs the detailed steps the driver moves through. The system-status backbone is the same for every order; the activity flow is what makes one order type different from another.

## [System Statuses](https://fleet-app.qgi.dev/docs/)

| Status | Always present? | Meaning |
| --- | --- | --- |
| `created` | Yes | The order has been created and is awaiting dispatch |
| `dispatched` | Yes | The order has been sent to a driver |
| `started` | Yes | The driver has accepted and is working the order |
| `canceled` | Yes | The order was cancelled before completion |
| `completed` | Configurable | The order has been delivered |

The first four are **static** — they exist on every order and cannot be removed. The `completed` status is configurable: any custom activity in the order's activity flow with its `complete` flag set to `true` will end the order. The default Order Configuration ships with a generic _Completed_ activity, but a courier flow might use _Items Handed Over_, a service flow might use _Job Signed Off_, and so on. Whichever activity carries the flag is the one that completes the order.

## [Status Flow](https://fleet-app.qgi.dev/docs/)

```
created ──► dispatched ──► started ──► completed
                                  │
                            canceled (terminal at any point)
```

### [Created](https://fleet-app.qgi.dev/docs/)

A new order starts in **`created`** the moment it is saved. Created orders are visible in the operations console and in the Orchestrator's order pool, available for manual or automated assignment.

### [Dispatched](https://fleet-app.qgi.dev/docs/)

When the order is sent to a driver — either by clicking **Dispatch** on the order, or as the result of an Orchestrator phase that auto-dispatches — the status moves to **`dispatched`**. The driver receives a push notification in the Navigator app and the order appears in their active job list.

### [Started](https://fleet-app.qgi.dev/docs/)

When the driver accepts and begins working the order in Navigator, the status moves to **`started`**. From this point onward the driver's GPS position streams live to the console, geofence events fire as they arrive at and depart pickup/dropoff locations, and the **activity flow** (defined by the order's Order Configuration) begins running. Each activity step the driver completes is captured with a timestamp, the driver's location, and any required proof.

### [Completed](https://fleet-app.qgi.dev/docs/)

The order is marked **`completed`** when the activity flow reaches an activity whose `complete` flag is set to `true`. That activity is whatever the Order Configuration defines — _Items Delivered_, _Signature Captured_, _Job Closed_, etc. Completion fires:

- A webhook (`order.completed`)
- Customer notifications, if configured
- A final tracking-status entry with the driver's location

### [Canceled](https://fleet-app.qgi.dev/docs/)

An order can move to **`canceled`** at any point before completion. Cancellation reasons can be recorded; canceled orders remain in the system for reporting and audit.

## [Activity Flow vs. System Status](https://fleet-app.qgi.dev/docs/)

The system status (`created`, `dispatched`, `started`, `completed`, `canceled`) is the high-level lifecycle stage visible everywhere in the console and API. It's how Fleet-Ops decides _what stage of life_ the order is in.

The **activity flow**, defined on the Order Configuration, is what runs _inside_ the lifecycle — most importantly between `started` and the activity that completes the order. A simple courier flow might be:

1. _Driver at pickup_
2. _Items picked up_
3. _In transit_
4. _Arrived at dropoff_
5. _Items delivered_    ← `complete: true` ends the order

Each activity has its own conditions (when it can fire), events (what happens when it does), and an optional `complete` flag. Different order types can have completely different activity flows while still sharing the same five system statuses.

See [Activity Flows](https://fleet-app.qgi.dev/docs/) for how to design and edit them.

## [Waypoint Lifecycle](https://fleet-app.qgi.dev/docs/)

Multi-stop orders give each waypoint its own sub-lifecycle. The order's completion activity only fires once all waypoints have been visited and their required POD captured.

Each waypoint can have:

- Its own POD method (signature, photo, barcode, custom)
- A time window (earliest and latest arrival)
- A service-time estimate (how long the driver is expected to spend at the stop)

## [Webhook Events](https://fleet-app.qgi.dev/docs/)

Each system-status transition fires a corresponding webhook event:

| Status | Event |
| --- | --- |
| Driver assigned | `order.driver_assigned` |
| `dispatched` | `order.dispatched` |
| `started` | `order.started` |
| `completed` | `order.completed` |
| `canceled` | `order.canceled` |

Activity-level changes also broadcast — `order.activity_changed`, `waypoint.activity_changed`, `entity.activity_changed`, plus `waypoint.completed` and `entity.completed` — so integrations can subscribe to fine-grained progress, not just the system-status transitions.

See [Webhooks](https://fleet-app.qgi.dev/docs/) for subscription details.

[Orders Overview\\
\\
Learn how orders work in Fleet-Ops — creation, payload structure, assignment, dispatch, and completion.](https://fleet-app.qgi.dev/docs/) [Managing Orders\\
\\
Create, edit, assign, dispatch, and manage orders from the Fleet-Ops orders list and detail views.](https://fleet-app.qgi.dev/docs/)

### On this page

[Order Lifecycle](https://fleet-app.qgi.dev/docs/) [System Statuses](https://fleet-app.qgi.dev/docs/) [Status Flow](https://fleet-app.qgi.dev/docs/) [Created](https://fleet-app.qgi.dev/docs/) [Dispatched](https://fleet-app.qgi.dev/docs/) [Started](https://fleet-app.qgi.dev/docs/) [Completed](https://fleet-app.qgi.dev/docs/) [Canceled](https://fleet-app.qgi.dev/docs/) [Activity Flow vs. System Status](https://fleet-app.qgi.dev/docs/) [Waypoint Lifecycle](https://fleet-app.qgi.dev/docs/) [Webhook Events](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Order Lifecycle \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Orders: scheduling -->
# Orders: scheduling

Orders

# Scheduling Orders

Schedule an order for a specific date and time — set pickup windows, time constraints, and future dispatch.

# [Scheduling Orders](https://fleet-app.qgi.dev/docs/)

Orders can be scheduled for a specific future date and time rather than dispatched immediately. A scheduled order is still a normal order with system status `created` — the scheduling fields tell the Scheduler, the Orchestrator, and the dispatch queue _when_ it's eligible to go out, but Fleet-Ops does not have a separate "scheduled" status.

## [Setting a Scheduled Pickup](https://fleet-app.qgi.dev/docs/)

When creating or editing an order, enable **Schedule** in the order form:

Open the new order form ( **Operations → Orders → + New Order**) or edit an existing order.

Toggle **Schedule Order** on.

Set the **Scheduled At** date and time — the target pickup time.

Optionally set a **Time Window Start** and **Time Window End** to define the acceptable pickup range (e.g., between 09:00 and 12:00).

Save the order. The order is created with status **`created`** and the `scheduled_at` field set; it is not dispatched yet.

## [How Scheduled Orders Behave](https://fleet-app.qgi.dev/docs/)

A `created` order with a `scheduled_at` value behaves differently from one without:

- It is filterable in the orders list using the **Scheduled** quick filter (which shows orders with a future `scheduled_at`)
- It appears in the Scheduler calendar view on its scheduled date
- The Orchestrator can include it in optimization runs that target a specific date or window
- It does not appear in the active dispatch queue until the scheduled window is reached

## [Time Windows](https://fleet-app.qgi.dev/docs/)

The **time window** (start and end time) defines when the order pickup can occur. This is distinct from the scheduled time — it gives the driver a range rather than an exact moment.

Time windows are used by the **Orchestrator** as a hard constraint when assigning and routing orders. An order with a time window of 09:00–12:00 will only be assigned to a driver who can reach the pickup location before 12:00.

For waypoints within an order, each stop can have its own independent time window.

## [Automatic Dispatch at the Scheduled Time](https://fleet-app.qgi.dev/docs/)

Scheduled orders **are dispatched automatically** when the scheduled date and time arrives. The order moves from `created` to `dispatched`, the driver receives a push notification in Navigator, and the order appears in their active job list — no operator action required.

If the order has no driver assigned yet at the scheduled time, dispatch can be configured to either:

- Hold the order until the next Orchestrator run picks it up, or
- Trigger an Orchestrator allocation pass at the scheduled time and dispatch as soon as a driver is matched

You can also dispatch a scheduled order **early** by opening it and clicking **Dispatch** — useful when the driver is ready before the scheduled window.

## [Viewing Scheduled Orders](https://fleet-app.qgi.dev/docs/)

Scheduled orders appear in two places:

1. **Orders List** — use the **Scheduled** quick filter to see all `created` orders with a future `scheduled_at`
2. **Scheduler Calendar** — navigate to **Operations → Scheduler** to see a timeline view of all scheduled orders organized by date and driver

## [Rescheduling](https://fleet-app.qgi.dev/docs/)

To change the scheduled date or time:

1. Open the order detail and click **Edit**
2. Update the **Scheduled At** field and time window
3. Save — the order remains in `created` status with the updated time

If the order has already been dispatched, rescheduling requires cancelling the dispatch first.

[Kanban Board\\
\\
Manage orders visually by status column — see all active orders at a glance, drag to reassign, and filter by driver or fleet.](https://fleet-app.qgi.dev/docs/) [Importing\\
\\
Import many orders at once from a spreadsheet — including multi-waypoint and multi-entity orders — using the Order Import wizard.](https://fleet-app.qgi.dev/docs/)

### On this page

[Scheduling Orders](https://fleet-app.qgi.dev/docs/) [Setting a Scheduled Pickup](https://fleet-app.qgi.dev/docs/) [How Scheduled Orders Behave](https://fleet-app.qgi.dev/docs/) [Time Windows](https://fleet-app.qgi.dev/docs/) [Automatic Dispatch at the Scheduled Time](https://fleet-app.qgi.dev/docs/) [Viewing Scheduled Orders](https://fleet-app.qgi.dev/docs/) [Rescheduling](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Scheduling Orders \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Orders: importing -->
# Orders: importing

Orders

# Importing Orders

Import many orders at once from a spreadsheet — including multi-waypoint and multi-entity orders — using the Order Import wizard.

# [Importing Orders](https://fleet-app.qgi.dev/docs/)

For high-volume operations it isn't practical to create orders one-by-one in the console. Fleet-Ops includes an **Order Import** wizard that ingests `.xlsx` or `.csv` spreadsheets and creates orders, waypoints, entities, customers, and drivers in a single pass. This page walks through the import flow, the spreadsheet shape, and the row-grouping rules that drive multi-stop and multi-item orders.

## [When to Use Import](https://fleet-app.qgi.dev/docs/)

- Onboarding a backlog of orders from another system
- Loading the day's batch from a dispatch sheet maintained externally
- Scripted exports from a WMS, ERP, or e-commerce platform that produces a per-line spreadsheet

For ongoing automated integration, prefer the [REST API](https://fleet-app.qgi.dev/docs/) — import is best for one-shot or periodic loads.

## [Starting an Import](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Operations → Orchestrator** (or the orders list).

Click **Import Orders**. The import modal opens with a file-drop area and a queue list.

Drop or pick one or more files. Accepted formats: `.xlsx`, `.xls`, and `.csv`.

Click **Start Upload**. Files are uploaded, parsed, and processed into orders, waypoints, entities, customers, and drivers as appropriate.

## [The Spreadsheet Template](https://fleet-app.qgi.dev/docs/)

Download the canonical template from the import modal — `Fleetbase_Order_Import_Template.xlsx` — and fill in the **Import** sheet starting at row 2 (row 1 is the header).

### [Required columns](https://fleet-app.qgi.dev/docs/)

Three columns are required on every row:

| Required column | Purpose |
| --- | --- |
| `Order Ref *` | A reference shared across all rows that belong to the same order. The first row carrying a given Order Ref defines the order; subsequent rows with the same Order Ref **append waypoints and entities** to that order. |
| `Pickup Name *` | The label or business name of the pickup location |
| `Dropoff Name *` | The label or business name of the dropoff location |

Required fields are marked with an asterisk (`*`) in the template's header row.

### [Optional columns](https://fleet-app.qgi.dev/docs/)

The template includes 44 columns covering everything an order can carry:

| Group | Columns |
| --- | --- |
| **Order metadata** | `Order Type`, `Scheduled At`, `Notes` |
| **Pickup location** | `Pickup Name *`, `Pickup Street`, `Pickup City`, `Pickup State`, `Pickup Postcode`, `Pickup Country`, `Pickup Lat`, `Pickup Lng` |
| **Dropoff location** | `Dropoff Name *`, `Dropoff Street`, `Dropoff City`, `Dropoff State`, `Dropoff Postcode`, `Dropoff Country`, `Dropoff Lat`, `Dropoff Lng` |
| **Payload summary** | `Payload Type`, `Payload Weight (kg)`, `Payload Volume (m³)` |
| **Customer** | `Customer Name`, `Customer Email`, `Customer Phone`, `Customer Type` |
| **Facilitator** | `Facilitator Name`, `Facilitator Email`, `Facilitator Type` |
| **Driver & vehicle** | `Vehicle Plate`, `Driver Name`, `Driver Email`, `Driver Phone` |
| **Entity (item-level)** | `Entity Name`, `Entity Type`, `Entity SKU`, `Entity Description`, `Entity Weight (kg)`, `Entity Length (cm)`, `Entity Width (cm)`, `Entity Height (cm)`, `Entity Declared Value`, `Entity Currency` |

Hover over any header cell in the **Import** sheet to see a tooltip describing the field.

Columns can appear in any order — the import wizard maps headers to Fleet-Ops fields automatically. Extra columns the wizard doesn't recognise are ignored, so you can keep your own spreadsheet conventions intact.

## [Row Grouping — One Order Across Many Rows](https://fleet-app.qgi.dev/docs/)

The **`Order Ref`** column is the anchor that ties rows together. The wizard uses it to decide whether each row creates a new order or extends an existing one:

- **First occurrence of an Order Ref** — creates the order. Order-level fields (pickup, dropoff, customer, facilitator, driver, vehicle, scheduling) come from this row.
- **Subsequent occurrences of the same Order Ref** — append a waypoint and/or an entity to the order created on the first row. The pickup/dropoff fields define an additional waypoint; the entity fields define an additional item.

This means **one order can occupy any number of rows** depending on how many entities and stops it has.

### [Example — multi-entity order](https://fleet-app.qgi.dev/docs/)

The same Order Ref `ORD-001` appears on two rows. Row 1 sets up the order with a laptop box; row 2 adds a phone charger as a second entity:

```
Order Ref | Pickup Name   | Dropoff Name | Entity Name    | Entity SKU
─────────────────────────────────────────────────────────────────────
ORD-001   | CBRE Fleet HQ  | Customer A   | Laptop Box     | SKU-001
ORD-001   | CBRE Fleet HQ  | Customer A   | Phone Charger  | SKU-002
```

Result: one order from CBRE Fleet HQ → Customer A with two entities attached.

### [Example — multi-waypoint order](https://fleet-app.qgi.dev/docs/)

The same Order Ref appears across rows with **different dropoff locations**, which the wizard reads as additional waypoints:

```
Order Ref | Pickup Name   | Dropoff Name        | Entity Name
────────────────────────────────────────────────────────────────
ORD-007   | CBRE Fleet HQ  | Customer A — North  | Package 1
ORD-007   | CBRE Fleet HQ  | Customer B — East   | Package 2
ORD-007   | CBRE Fleet HQ  | Customer C — South  | Package 3
```

Result: one multi-waypoint order with three stops and three entities — each entity routed to its own waypoint.

You can combine the patterns: multiple entities at a single waypoint, multiple waypoints with one or many entities each, all under the same Order Ref.

## [Field Tips](https://fleet-app.qgi.dev/docs/)

- **Dates and times** — ISO 8601 (`YYYY-MM-DD HH:MM`) is the most reliable format. The parser accepts other formats but ISO eliminates ambiguity. The `Scheduled At` field on the first row of an Order Ref sets the order's `scheduled_at`.
- **Country codes** — use ISO 3166-1 alpha-2 codes (`SG`, `US`, `GB`).
- **Coordinates** — `Pickup Lat` / `Pickup Lng` and `Dropoff Lat` / `Dropoff Lng` are optional. If supplied they pin the location precisely; if omitted the wizard geocodes the address fields.
- **Order Type** — free-form string that Fleet-Ops uses to match an [Order Configuration](https://fleet-app.qgi.dev/docs/). Common values: `transport`, `delivery`, `pickup`, `multi_waypoint`. If no matching config exists the order falls back to the default Transport config.
- **Customer / facilitator / driver** — if a contact, vendor, or driver with the supplied email or name doesn't already exist, the import creates one and links it to the order. Existing records are matched and re-used.
- **Vehicle Plate** — when supplied, the wizard looks up the matching vehicle and pre-assigns it. If no match is found, the order is created without a vehicle and can be assigned later by the Orchestrator or manually.

## [After Import](https://fleet-app.qgi.dev/docs/)

Once the import finishes:

- A summary shows the number of orders, waypoints, entities, customers, and drivers created or updated
- The new orders appear in **Operations → Orders** with system status `created`, ready to be dispatched manually or picked up by the Orchestrator
- Any rows the wizard couldn't process are returned in an error report so you can fix them and re-import

Imported orders behave identically to console-created orders from this point — they participate in [Order Configurations](https://fleet-app.qgi.dev/docs/), the [Orchestrator](https://fleet-app.qgi.dev/docs/), and tracking.

[Scheduling Orders\\
\\
Schedule an order for a specific date and time — set pickup windows, time constraints, and future dispatch.](https://fleet-app.qgi.dev/docs/) [Proof of Delivery\\
\\
Configure and review proof of delivery — signatures, photos, QR scans, and custom data captured via the Navigator app.](https://fleet-app.qgi.dev/docs/)

### On this page

[Importing Orders](https://fleet-app.qgi.dev/docs/) [When to Use Import](https://fleet-app.qgi.dev/docs/) [Starting an Import](https://fleet-app.qgi.dev/docs/) [The Spreadsheet Template](https://fleet-app.qgi.dev/docs/) [Required columns](https://fleet-app.qgi.dev/docs/) [Optional columns](https://fleet-app.qgi.dev/docs/) [Row Grouping — One Order Across Many Rows](https://fleet-app.qgi.dev/docs/) [Example — multi-entity order](https://fleet-app.qgi.dev/docs/) [Example — multi-waypoint order](https://fleet-app.qgi.dev/docs/) [Field Tips](https://fleet-app.qgi.dev/docs/) [After Import](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Importing Orders \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Orders: kanban board -->
# Orders: kanban board

Orders

# Kanban Board

Manage orders visually by status column — see all active orders at a glance, drag to reassign, and filter by driver or fleet.

# [Kanban Board](https://fleet-app.qgi.dev/docs/)

The Kanban Board provides a visual, column-based view of all orders organized by **system status**. Each column represents one of the five system statuses — _created_, _dispatched_, _started_, _completed_, _canceled_ — and each card represents an order. It gives dispatchers a real-time snapshot of the operation's current state without navigating individual order records.

## [Accessing the Kanban Board](https://fleet-app.qgi.dev/docs/)

In Fleet-Ops, navigate to **Operations → Orders** and switch the view toggle from **List** to **Board** (Kanban view) using the view switcher in the top right of the orders page.

## [Board Columns](https://fleet-app.qgi.dev/docs/)

The board has one column per system status:

| Column | Orders shown |
| --- | --- |
| **Created** | Awaiting dispatch |
| **Dispatched** | Sent to the Navigator app, awaiting driver start |
| **Started** | Driver actively executing the order |
| **Completed** | Order completion activity has fired |
| **Canceled** | Canceled orders |

By default, completed and canceled orders are hidden to keep the board focused on active work. Toggle their visibility using the column filter.

## [Order Cards](https://fleet-app.qgi.dev/docs/)

Each order card shows:

- **Order ID** (public\_id — e.g., `ORD-00123`)
- **Customer** name
- **Dropoff address** summary
- **Assigned driver** name (if assigned)
- **Scheduled time** (if scheduled)
- **Status badge**

Click a card to open the full order detail panel.

## [Filtering the Board](https://fleet-app.qgi.dev/docs/)

Use the filter bar at the top to narrow the board to:

- **Driver** — show only orders assigned to a specific driver
- **Fleet** — show only orders belonging to a fleet
- **Date** — filter by scheduled date or creation date
- **Order Config** — filter by order type

Filters stack — combine driver and date to see a specific driver's schedule for a given day.

## [Quick Actions from Cards](https://fleet-app.qgi.dev/docs/)

Hover over an order card to reveal quick action buttons:

- **Dispatch** — dispatch a created order directly from the card without opening the detail
- **Assign** — open the driver/vehicle assignment dialog for a created order
- **Cancel** — cancel the order

## [Using the Board with the Orchestrator](https://fleet-app.qgi.dev/docs/)

The Kanban Board and the [Orchestrator](https://fleet-app.qgi.dev/docs/) work well together. Use the Orchestrator to do bulk assignment and route optimization, then switch to the board view to monitor real-time progress as drivers work through their manifests.

[Managing Orders\\
\\
Create, edit, assign, dispatch, and manage orders from the Fleet-Ops orders list and detail views.](https://fleet-app.qgi.dev/docs/) [Scheduling Orders\\
\\
Schedule an order for a specific date and time — set pickup windows, time constraints, and future dispatch.](https://fleet-app.qgi.dev/docs/)

### On this page

[Kanban Board](https://fleet-app.qgi.dev/docs/) [Accessing the Kanban Board](https://fleet-app.qgi.dev/docs/) [Board Columns](https://fleet-app.qgi.dev/docs/) [Order Cards](https://fleet-app.qgi.dev/docs/) [Filtering the Board](https://fleet-app.qgi.dev/docs/) [Quick Actions from Cards](https://fleet-app.qgi.dev/docs/) [Using the Board with the Orchestrator](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Kanban Board \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Orders: tracking -->
# Orders: tracking

Orders

# Tracking

Track live order progress, driver position, and ETA from the order detail view and live map.

# [Tracking](https://fleet-app.qgi.dev/docs/)

Fleet-Ops provides real-time tracking of order progress and driver location from the moment an order is dispatched until delivery is confirmed. Tracking is visible to operators in the console and optionally to customers via a public tracking link.

## [Live Map](https://fleet-app.qgi.dev/docs/)

The live map ( **Fleet-Ops → Live Map** or the map visible from the dashboard) shows:

- **Driver markers** — real-time position of all active drivers, updated continuously via the Navigator app or telematics devices
- **Order routes** — the calculated route for each active order, shown as a polyline
- **Pickup and dropoff markers** — origin and destination pins for active orders
- **Waypoint markers** — intermediate stop markers for multi-drop orders

Click a driver marker to see:

- Driver name, photo, and status
- Current speed and heading
- Active order assignment
- ETA to next stop

## [Order Detail Tracking](https://fleet-app.qgi.dev/docs/)

From the order detail panel, the tracking section shows:

| Field | Description |
| --- | --- |
| **Driver Location** | Real-time map with driver position |
| **ETA** | Estimated time of arrival at the next stop |
| **Distance Remaining** | Distance from driver to dropoff |
| **Status** | Current order status |
| **Activity Timeline** | All status transitions with timestamps and locations |

The activity timeline records every event with:

- The event — both system-status transitions (`dispatched`, `started`, `completed`, `canceled`) and any activities defined in the order's [activity flow](https://fleet-app.qgi.dev/docs/) (e.g. _picked up_, _in transit_, _arrived at dropoff_)
- The timestamp
- The driver's GPS coordinates at the time of the event
- Any attached proof (photo, signature)

## [Tracking Number & Customer Tracking](https://fleet-app.qgi.dev/docs/)

Every order has a **Tracking Number** (e.g. `FO-XXXXXXXX`) that customers can use on a public tracking page to view the order's current status without logging in.

The tracking page shows:

- Current status and recent activity
- Estimated delivery time
- Map with current driver position (when active)
- Proof of delivery once completed

The public tracking URL is `/~/track-order?order={tracking_number}`. Customers can also visit `/~/track-order` with no query parameter and enter a tracking number into the form.

On CBRE Fleet Cloud the full URLs are:

- Direct deep link: `https://console.fleetbase.io/~/track-order?order={tracking_number}`
- Manual entry: `https://console.fleetbase.io/~/track-order`

For self-hosted instances, replace `console.fleetbase.io` with your console hostname.

## [Entity-Level Tracking](https://fleet-app.qgi.dev/docs/)

Each **entity** (item) in an order has its own tracking number and status. For orders with multiple items, each item's status is tracked independently — so you can see which items have been picked up, which are in transit, and which have been delivered, even within a single order.

This is particularly useful for partial delivery scenarios where some items are delivered but others fail or require return.

## [WebSocket Real-Time Updates](https://fleet-app.qgi.dev/docs/)

For custom applications, driver location and order status updates are available in real time via the SocketCluster WebSocket channel.

Subscribe to `company.{company_id}` to receive:

| Event | Payload |
| --- | --- |
| `driver.location_changed` | Current driver coordinates, speed, heading |
| `order.dispatched` | Order dispatched to driver |
| `order.started` | Driver began the order |
| `order.completed` | Order's completion activity fired |
| `order.canceled` | Order canceled |
| `order.activity_changed` | Any custom activity from the order's activity flow fired |

See [Socket Events](https://fleet-app.qgi.dev/docs/) for subscription details and payload schemas.

## [Position History](https://fleet-app.qgi.dev/docs/)

For historical analysis, Fleet-Ops stores all driver position data. You can view a driver's position history in **Fleet-Ops → Resources → Drivers → \[Driver\] → Positions** — or use the position replay feature to re-watch a past route on the map.

Position data includes: latitude, longitude, heading, speed, altitude, and timestamp per reading.

[Proof of Delivery\\
\\
Configure and review proof of delivery — signatures, photos, QR scans, and custom data captured via the Navigator app.](https://fleet-app.qgi.dev/docs/) [Orchestrator Overview\\
\\
A multi-phase planner that turns a pool of unassigned orders into a committable plan — stack vehicle allocation, driver allocation, and route optimization phases in sequence.](https://fleet-app.qgi.dev/docs/)

### On this page

[Tracking](https://fleet-app.qgi.dev/docs/) [Live Map](https://fleet-app.qgi.dev/docs/) [Order Detail Tracking](https://fleet-app.qgi.dev/docs/) [Tracking Number & Customer Tracking](https://fleet-app.qgi.dev/docs/) [Entity-Level Tracking](https://fleet-app.qgi.dev/docs/) [WebSocket Real-Time Updates](https://fleet-app.qgi.dev/docs/) [Position History](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Tracking \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Orders: proof of delivery -->
# Orders: proof of delivery

Orders

# Proof of Delivery

Configure and review proof of delivery — signatures, photos, QR scans, and custom data captured via the Navigator app.

# [Proof of Delivery](https://fleet-app.qgi.dev/docs/)

**Proof of Delivery (POD)** is evidence captured by the driver at the point of delivery, confirming that an order — or a specific item or waypoint within an order — was successfully handed over. Fleet-Ops supports four POD methods, and POD is configured **per activity** in the Order Configuration's [activity flow](https://fleet-app.qgi.dev/docs/).

## [POD Methods](https://fleet-app.qgi.dev/docs/)

The `pod_method` field on each activity accepts one of four values:

| Method | Value | How it works |
| --- | --- | --- |
| **Scan (barcode / QR)** | `scan` | Driver scans a barcode or QR code on the parcel; the scanned value is recorded against the order, entity, or waypoint |
| **Signature** | `signature` | Recipient signs on the driver's phone screen in the Navigator app |
| **Photo** | `photo` | Driver takes a photo of the delivery |
| **SMS verification** | `sms` | Fleet-Ops sends a one-time confirmation code to the customer by SMS; the driver enters the code from the customer in Navigator to confirm delivery |

POD lives on individual activities — not on the Order Configuration as a whole. This means a single flow can require a barcode scan at pickup, a signature at the dropoff, an SMS code at customer hand-over, and no POD at intermediate transit steps.

## [Configuring POD per Activity](https://fleet-app.qgi.dev/docs/)

POD is configured inside the activity flow, on each activity that should capture proof.

Navigate to **Fleet-Ops → Operations → Order Configurations**.

Open or create an Order Configuration and switch to the **Activity Flow** tab.

On the activity that should capture POD, set:

- `pod_method` — one of `scan`, `signature`, `photo`, or `sms`
- `require_pod: true` — the driver cannot fire the activity in Navigator without capturing POD first

For multi-stop orders, you can configure different POD requirements per waypoint by attaching POD-bearing activities to each stop's sub-flow.

Save the configuration. New orders created against this config will pick up the updated POD requirements.

## [How Drivers Capture POD](https://fleet-app.qgi.dev/docs/)

In the Navigator app, when a driver fires an activity that has `require_pod: true`:

1. The app prompts them to capture POD based on the configured `pod_method`
2. For **`signature`**: the recipient signs directly on screen
3. For **`photo`**: the app opens the camera; the driver photographs the delivery
4. For **`scan`**: the app opens the scanner; the driver scans a barcode or QR code on the parcel
5. For **`sms`**: Fleet-Ops sends a one-time code to the customer's phone; the driver asks the customer for the code and enters it in Navigator
6. The driver confirms — the POD is uploaded as a **Proof** record and the activity advances

## [POD for Entities and Waypoints](https://fleet-app.qgi.dev/docs/)

For orders with multiple items (entities) or multiple stops (waypoints), POD can be captured individually:

- **Per entity** — each item requires its own proof (e.g., photo of each parcel delivered)
- **Per waypoint** — each stop requires POD before the driver can move to the next stop

This creates a granular audit trail for complex multi-drop deliveries.

## [Viewing POD in the Console](https://fleet-app.qgi.dev/docs/)

Captured POD appears in the **Order Detail Panel** under the **Proofs** section:

- Signature images are displayed inline
- Photos are displayed as thumbnails with full-screen view
- Scan records show the captured barcode/QR value and timestamp

Each Proof record stores:

| Field | Meaning |
| --- | --- |
| `subject_uuid` / `subject_type` | The polymorphic subject — Order, Entity, or Waypoint the proof attaches to |
| `file_uuid` | The uploaded image (signature or photo) |
| `raw_data` | The raw scanned value (for `scan` method) |
| `data` | Structured proof metadata |
| `remarks` | Optional notes entered by the driver |
| `created_at` | Timestamp of capture |

## [Downloading POD](https://fleet-app.qgi.dev/docs/)

From the order detail panel, click **Download** on any proof to save the signature image or photo. For bulk exports, use the order export function — CSV exports include proof URLs that link to the stored files.

## [POD via the API](https://fleet-app.qgi.dev/docs/)

POD can also be captured via the API. Three endpoints accept proof submissions:

```
POST /v1/orders/{id}/capture-signature/{subjectId}
POST /v1/orders/{id}/capture-photo/{subjectId}
POST /v1/orders/{id}/capture-qr/{subjectId}
```

The `subjectId` is the UUID of the order, entity, or waypoint to attach the proof to.

See the [API Reference](https://fleet-app.qgi.dev/docs/) for request body schemas.

[Importing\\
\\
Import many orders at once from a spreadsheet — including multi-waypoint and multi-entity orders — using the Order Import wizard.](https://fleet-app.qgi.dev/docs/) [Tracking\\
\\
Track live order progress, driver position, and ETA from the order detail view and live map.](https://fleet-app.qgi.dev/docs/)

### On this page

[Proof of Delivery](https://fleet-app.qgi.dev/docs/) [POD Methods](https://fleet-app.qgi.dev/docs/) [Configuring POD per Activity](https://fleet-app.qgi.dev/docs/) [How Drivers Capture POD](https://fleet-app.qgi.dev/docs/) [POD for Entities and Waypoints](https://fleet-app.qgi.dev/docs/) [Viewing POD in the Console](https://fleet-app.qgi.dev/docs/) [Downloading POD](https://fleet-app.qgi.dev/docs/) [POD via the API](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Proof of Delivery \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Order configurations -->
# Order configurations

Order Configurations

# Order Configurations

Order configurations define custom order types with their own workflows, entity fields, POD methods, and activity steps.

# [Order Configurations](https://fleet-app.qgi.dev/docs/)

An **Order Configuration** (Order Config) is a template that defines how a particular type of order works in Fleet-Ops. Every order you create must be based on an Order Config — it determines the order's lifecycle, the data fields collected on each item, and what proof of delivery is required.

This lets you run different service types from the same Fleet-Ops instance. A courier might have one config for same-day parcels (signature required), another for document delivery (photo required), and a third for returns (no POD needed). Each config has its own workflow and fields.

## [What an Order Config Contains](https://fleet-app.qgi.dev/docs/)

| Component | Purpose |
| --- | --- |
| **Name** | Display name shown in the console |
| **Namespace & Key** | Unique identifiers used by the API and the system (e.g. `system:order-config:transport`) |
| **Description** | Free-text description for operators |
| **Status** | `private` or `public` — controls visibility of the config |
| **Version** | Version string for the config (e.g. `0.0.1`) |
| **Activity Flow** | The graph of activities drivers move through; each activity has its own POD settings, transitions, logic, and events. See [Activity Flows](https://fleet-app.qgi.dev/docs/). |
| **Entity Definitions** | Field templates for each item (entity) created on orders of this type. See [Order Types](https://fleet-app.qgi.dev/docs/). |
| **Tags** | Free-form tags for organisation and filtering |
| **Custom Fields** | Order-level custom fields, attached separately and rendered alongside built-in fields |

POD method and POD-required settings live on individual activities inside the activity flow, not on the config itself.

## [Managing Order Configurations](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Operations → Order Configurations**.

The list shows all existing configs with their name, namespace key, version, and status (active/inactive). From here you can:

- **Create** a new configuration
- **Edit** an existing configuration's flow and fields
- **Duplicate** an existing configuration as a starting point
- **Activate/deactivate** configs to control which types are available when creating orders
- **Delete** configs that are no longer needed (only if no orders reference them)

## [Creating a New Order Configuration](https://fleet-app.qgi.dev/docs/)

Click **\+ New Configuration**.

Set the **Name** (e.g., "Same-Day Delivery") and **Description**.

The **Namespace** key is auto-generated from the name (e.g., `same-day-delivery`) — this is the API identifier for the config.

Configure the **Activity Flow** — the graph of activities drivers move through, including per-activity POD settings (`scan`, `signature`, or `photo`). See [Activity Flows](https://fleet-app.qgi.dev/docs/).

Add **Entity Definitions** — fields required on each item in this order type. See [Order Types](https://fleet-app.qgi.dev/docs/).

Click **Save**. The config is now available for selection when creating orders.

## [The Core Service Configuration](https://fleet-app.qgi.dev/docs/)

Fleet-Ops ships with **one built-in core service configuration**: **Transport**. Every new company gets a Transport config created automatically with `core_service: 1` set, so an out-of-the-box instance always has a usable order config available immediately.

| Field | Value |
| --- | --- |
| **Name** | Transport |
| **Key** | `transport` |
| **Namespace** | `system:order-config:transport` |
| **Default flow** | `created → dispatched → started → enroute → completed` |
| **Default entities** | None — entities are added per order |

Core-service configs are protected: they cannot be deleted (the API rejects deletion when `core_service === 1`). You can duplicate them, edit your own copies, or build new configurations from scratch — for example _Pickup & Delivery_, _Returns_, or _Multi-Drop Courier_ are common patterns customers create on top of the platform.

## [Order Configs and the API](https://fleet-app.qgi.dev/docs/)

When creating orders via the consumable API, reference the order configuration by its **`public_id`** (e.g. `config_dnid32`) using the `order_config` field. You can also pass `type` matching the config's `key` or `namespace` to fall back to a config when no `order_config` is supplied.

```
{
  "order_config": "config_dnid32",
  "payload": {
    "pickup":  { ... },
    "dropoff": { ... }
  }
}
```

**The consumable API never accepts or returns `uuid` values** — every record is referenced by its `public_id` (e.g. `order_5sk3lf`, `driver_tt2x9q`, `vehicle_3hk912`). The internal `uuid` columns are private to the database. Throughout the docs, when an API example shows a field like `order_config`, `driver`, or `vehicle`, supply the matching `public_id`.

## [Related Pages](https://fleet-app.qgi.dev/docs/)

- [Order Types](https://fleet-app.qgi.dev/docs/) — defining entity fields and requirements per order type
- [Activity Flows](https://fleet-app.qgi.dev/docs/) — configuring the step-by-step driver workflow

[Driver Shift Schedules\\
\\
Set up and manage recurring driver shift schedules — define working hours, days, breaks, and HOS compliance rules.](https://fleet-app.qgi.dev/docs/) [Order Types\\
\\
Create and manage custom order types — define entity fields, constraints, and service categories per order configuration.](https://fleet-app.qgi.dev/docs/)

### On this page

[Order Configurations](https://fleet-app.qgi.dev/docs/) [What an Order Config Contains](https://fleet-app.qgi.dev/docs/) [Managing Order Configurations](https://fleet-app.qgi.dev/docs/) [Creating a New Order Configuration](https://fleet-app.qgi.dev/docs/) [The Core Service Configuration](https://fleet-app.qgi.dev/docs/) [Order Configs and the API](https://fleet-app.qgi.dev/docs/) [Related Pages](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Order Configurations \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Scheduler: order scheduling -->
# Scheduler: order scheduling

Scheduler

# Order Scheduling

Schedule orders onto driver timelines using the Scheduler — set pickup windows, assign to shifts, and manage scheduled order queues.

# [Order Scheduling](https://fleet-app.qgi.dev/docs/)

Order scheduling lets you plan deliveries for future dates and assign them to specific driver shifts. Scheduled orders appear in the Scheduler calendar and are held in a waiting state until their scheduled window opens.

## [Scheduling an Order](https://fleet-app.qgi.dev/docs/)

Orders can be scheduled at creation or updated to a scheduled date afterward.

### [During Order Creation](https://fleet-app.qgi.dev/docs/)

1. Open the new order form ( **Operations → Orders → + New Order**)
2. Toggle **Schedule Order** on
3. Set the **Scheduled At** date and time
4. Optionally set a **Time Window Start** and **Time Window End** for the acceptable pickup range
5. Save — the order is created with system status **`created`** and the `scheduled_at` field set; it is not dispatched until you choose to

### [After Creation](https://fleet-app.qgi.dev/docs/)

1. Open an existing `created` order (one that hasn't been dispatched yet)
2. Click **Edit**
3. Enable the schedule toggle and set the date/time
4. Save

## [Scheduling from the Scheduler Calendar](https://fleet-app.qgi.dev/docs/)

You can schedule and assign orders directly from the Scheduler calendar view:

1. Open **Operations → Scheduler**
2. Find an unscheduled order in the order sidebar (right panel)
3. Drag the order card onto a driver row on the target day
4. Set the time if prompted
5. Save — the order is scheduled and assigned to that driver

## [Time Windows](https://fleet-app.qgi.dev/docs/)

Each order can have a **time window** — an acceptable range for the pickup:

| Field | Description |
| --- | --- |
| **Scheduled At** | The target pickup time (informational) |
| **Time Window Start** | Earliest the pickup can occur |
| **Time Window End** | Latest the pickup can occur |

The Orchestrator treats time windows as **hard constraints**. An order with a window of 09:00–11:00 will only be assigned to a driver whose route can reach the pickup before 11:00.

If a driver cannot fit the order within the time window, the order is returned to the unassigned pool with a `time_window_exceeded` reason.

## [Viewing Scheduled Orders](https://fleet-app.qgi.dev/docs/)

Scheduled orders appear in multiple places:

| View | Location |
| --- | --- |
| **Orders list** | **Scheduled** filter tab |
| **Scheduler calendar** | On the assigned driver's row for the scheduled date |
| **Order detail** | Shows the scheduled date and window in the header |

## [Rescheduling](https://fleet-app.qgi.dev/docs/)

To move a scheduled order to a different date or time:

1. In the Scheduler, drag the order card to the new day/driver row
2. Or open the order, click Edit, and update the Scheduled At date

Rescheduling a dispatched order requires cancelling the existing dispatch first.

## [Bulk Scheduling](https://fleet-app.qgi.dev/docs/)

For large batches of orders, use the **Orchestrator** rather than scheduling individually:

1. Run the Orchestrator with the orders pool filtered to the target date
2. The Orchestrator respects time windows and shift schedules when assigning
3. Commit the plan — all orders are scheduled and assigned in one operation

See [Orchestrator Overview](https://fleet-app.qgi.dev/docs/) for bulk assignment details.

[Scheduler Overview\\
\\
The Scheduler provides a fleet-wide calendar view of driver shifts, order assignments, and availability windows.](https://fleet-app.qgi.dev/docs/) [Driver Shift Schedules\\
\\
Set up and manage recurring driver shift schedules — define working hours, days, breaks, and HOS compliance rules.](https://fleet-app.qgi.dev/docs/)

### On this page

[Order Scheduling](https://fleet-app.qgi.dev/docs/) [Scheduling an Order](https://fleet-app.qgi.dev/docs/) [During Order Creation](https://fleet-app.qgi.dev/docs/) [After Creation](https://fleet-app.qgi.dev/docs/) [Scheduling from the Scheduler Calendar](https://fleet-app.qgi.dev/docs/) [Time Windows](https://fleet-app.qgi.dev/docs/) [Viewing Scheduled Orders](https://fleet-app.qgi.dev/docs/) [Rescheduling](https://fleet-app.qgi.dev/docs/) [Bulk Scheduling](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Order Scheduling \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Scheduler: driver shifts -->
# Scheduler: driver shifts

Scheduler

# Driver Shift Schedules

Set up and manage recurring driver shift schedules — define working hours, days, breaks, and HOS compliance rules.

# [Driver Shift Schedules](https://fleet-app.qgi.dev/docs/)

Driver shift schedules define when each driver is available to work. Shifts are used by the Orchestrator as time window constraints during order assignment, and they appear as blocks in the Scheduler calendar.

## [What a Shift Schedule Defines](https://fleet-app.qgi.dev/docs/)

Each shift entry (ScheduleItem) defines:

| Field | Description |
| --- | --- |
| **Day(s)** | Which days of the week this shift applies to |
| **Start Time** | When the driver's shift begins |
| **End Time** | When the driver's shift ends |
| **Break** | Scheduled break duration (minutes) |
| **Recurrence** | How the shift repeats: daily, weekly, specific dates |
| **Timezone** | The timezone for the shift times |

A driver can have multiple shift schedule entries — for example, a different start time on weekdays vs. weekends, or a part-time pattern with alternating days.

## [Managing Shift Schedules](https://fleet-app.qgi.dev/docs/)

### [From the Driver Record](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Resources → Drivers**.

Open a driver record and click the **Schedule** tab.

Click **\+ Add Shift** to create a new shift entry.

Set the days, start time, end time, and recurrence pattern.

Click **Save**. The shift appears in the Scheduler calendar for the driver.

### [From the Scheduler Calendar](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Operations → Scheduler**.

Click an empty time slot in a driver's row to add a shift for that day.

Drag the shift block edges to adjust start/end times.

Click a shift block to edit or delete it.

## [Shift Patterns](https://fleet-app.qgi.dev/docs/)

Common shift patterns and how to configure them:

### [Standard 5-Day Week](https://fleet-app.qgi.dev/docs/)

- Create one entry covering Monday–Friday, 09:00–17:00
- Set recurrence to **Weekly**

### [Split Shifts](https://fleet-app.qgi.dev/docs/)

- Create two entries for the same day (morning: 07:00–12:00, afternoon: 14:00–19:00)
- Both entries appear as separate blocks in the calendar

### [Rotating Roster](https://fleet-app.qgi.dev/docs/)

- Create entries for specific dates rather than day-of-week patterns
- Use **Specific Date** recurrence for irregular schedules

### [Part-Time (Alternate Days)](https://fleet-app.qgi.dev/docs/)

- Create entries for Monday, Wednesday, Friday only
- Or use two separate entries with different day selections

## [Driver Availability Windows](https://fleet-app.qgi.dev/docs/)

The shift schedule's start and end times become the driver's **time window** — used by the Orchestrator when assigning orders. Specifically:

- `time_window_start` = shift start time
- `time_window_end` = shift end time

The Orchestrator will not assign orders to a driver that cannot be completed before `time_window_end`. This prevents drivers from being assigned orders that would run past their scheduled end time.

## [HOS (Hours of Service) Compliance](https://fleet-app.qgi.dev/docs/)

For drivers regulated by HOS rules (commercial transport, freight), Fleet-Ops tracks:

- **Daily driving hours** — alert when approaching the daily driving limit
- **Weekly hours** — alert when approaching the weekly hours cap
- **Required rest periods** — flag when the driver needs a mandatory break or rest period

HOS limits and break requirements are configured in **Fleet-Ops → Settings → Scheduling**.

When a driver's HOS status shows they cannot take additional orders, they are excluded from the Orchestrator's resource pool for that run.

## [Bulk Schedule Management](https://fleet-app.qgi.dev/docs/)

To set shifts for multiple drivers at once:

1. Go to **Fleet-Ops → Operations → Scheduler**
2. Use the **Bulk Edit** option in the toolbar
3. Select multiple drivers
4. Apply a shift template — a pre-defined shift pattern saved in Settings

## [Leave and Unavailability](https://fleet-app.qgi.dev/docs/)

To mark a driver as unavailable for a specific day (sick leave, vacation):

1. Open the driver's record → **Schedule** tab
2. Click **\+ Add Absence** for the relevant dates
3. Set the absence type (annual leave, sick leave, training, etc.)
4. The driver appears as unavailable in the Scheduler and is excluded from Orchestrator runs during that period

[Order Scheduling\\
\\
Schedule orders onto driver timelines using the Scheduler — set pickup windows, assign to shifts, and manage scheduled order queues.](https://fleet-app.qgi.dev/docs/) [Order Configurations\\
\\
Order configurations define custom order types with their own workflows, entity fields, POD methods, and activity steps.](https://fleet-app.qgi.dev/docs/)

### On this page

[Driver Shift Schedules](https://fleet-app.qgi.dev/docs/) [What a Shift Schedule Defines](https://fleet-app.qgi.dev/docs/) [Managing Shift Schedules](https://fleet-app.qgi.dev/docs/) [From the Driver Record](https://fleet-app.qgi.dev/docs/) [From the Scheduler Calendar](https://fleet-app.qgi.dev/docs/) [Shift Patterns](https://fleet-app.qgi.dev/docs/) [Standard 5-Day Week](https://fleet-app.qgi.dev/docs/) [Split Shifts](https://fleet-app.qgi.dev/docs/) [Rotating Roster](https://fleet-app.qgi.dev/docs/) [Part-Time (Alternate Days)](https://fleet-app.qgi.dev/docs/) [Driver Availability Windows](https://fleet-app.qgi.dev/docs/) [HOS (Hours of Service) Compliance](https://fleet-app.qgi.dev/docs/) [Bulk Schedule Management](https://fleet-app.qgi.dev/docs/) [Leave and Unavailability](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Driver Shift Schedules \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Drivers -->
# Drivers

Drivers

# Drivers

Create and manage driver profiles — assign vehicles, track real-time location, configure Navigator access, and set Orchestrator constraints.

# [Drivers](https://fleet-app.qgi.dev/docs/)

**Drivers** are the people who execute orders using the Navigator mobile app. Each driver record links to a CBRE Fleet user account and stores all the information needed for dispatch, real-time tracking, and Orchestrator assignment.

## [Driver Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Name** | Driver's full name |
| **Phone** | Mobile number — used for SMS login and notifications |
| **Email** | Optional — for account notifications |
| **Avatar** | Profile photo shown on the live map and in Navigator |
| **Vehicle** | Default vehicle assigned to this driver |
| **Vendor** | Third-party carrier this driver belongs to (optional) |
| **Driver's License** | License number for compliance records |
| **Skills** | Certifications/skills used by the Orchestrator for assignment |
| **Status** | active, inactive, suspended, on\_leave |
| **Online** | Whether the driver is currently active in Navigator |
| **Location** | Real-time GPS position (latitude, longitude, heading, speed) |
| **Max Travel Time** | Maximum driving time per shift (Orchestrator constraint) |
| **Max Distance** | Maximum route distance (Orchestrator constraint) |
| **Time Window** | Shift start and end times (Orchestrator constraint) |

## [Driver Statuses](https://fleet-app.qgi.dev/docs/)

| Status | Meaning |
| --- | --- |
| `active` | Available for order assignment |
| `inactive` | Not currently working — excluded from Orchestrator |
| `suspended` | Disciplinary hold — excluded from all operations |
| `on_leave` | Temporarily unavailable |

## [Creating a Driver](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Resources → Drivers**.

Click **\+ New Driver**.

Fill in the driver's name, phone number, and optionally email.

Assign a **Vehicle** if the driver has a default vehicle.

Set **Skills** if your operation uses skill-based assignment (e.g., `hazmat`, `refrigerated`, `motorcycle`).

Set **Orchestrator constraints** — max travel time, max distance, and time window — if you want the Orchestrator to enforce limits for this driver.

Click **Save**. The driver can now log in to the Navigator app using their phone number.

## [Driver Detail Panel](https://fleet-app.qgi.dev/docs/)

Click any driver row to open the detail panel:

- **Overview** — contact info, current vehicle, online status, current assignment
- **Map** — mini map with current real-time position
- **Orders** — order history for this driver with status and timestamps
- **Schedule** — shift schedule entries and upcoming assignments
- **Positions** — historical GPS track with position replay
- **Issues** — reported incidents involving this driver

## [Orchestrator Constraints](https://fleet-app.qgi.dev/docs/)

Driver constraints control how the Orchestrator allocates orders to this driver:

| Constraint | Effect |
| --- | --- |
| **Skills** | Only assigned orders that require skills this driver has |
| **Max Travel Time** | Total driving time for the route is capped |
| **Max Distance** | Total route distance is capped |
| **Time Window Start** | No orders dispatched before shift start |
| **Time Window End** | No orders extending past shift end time |

Leave constraints blank to allow unrestricted assignment.

## [Importing Drivers in Bulk](https://fleet-app.qgi.dev/docs/)

Click **Import** in the drivers list, download the CSV template, fill in your driver data, and upload. Fleet-Ops creates all driver records and they can immediately log in via Navigator using their registered phone number.

## [Related Pages](https://fleet-app.qgi.dev/docs/)

- [Navigator Access](https://fleet-app.qgi.dev/docs/) — manage app login and permissions
- [Shift Schedules](https://fleet-app.qgi.dev/docs/) — configure working hours
- [Navigator App Setup](https://fleet-app.qgi.dev/docs/) — install and connect the mobile app

[Resources Overview\\
\\
Manage the core resources that power your fleet operations — drivers, vehicles, fleets, vendors, contacts, and places.](https://fleet-app.qgi.dev/docs/) [Shift Schedules\\
\\
View and manage an individual driver's shift schedule — set working hours, recurring patterns, and availability windows.](https://fleet-app.qgi.dev/docs/)

### On this page

[Drivers](https://fleet-app.qgi.dev/docs/) [Driver Attributes](https://fleet-app.qgi.dev/docs/) [Driver Statuses](https://fleet-app.qgi.dev/docs/) [Creating a Driver](https://fleet-app.qgi.dev/docs/) [Driver Detail Panel](https://fleet-app.qgi.dev/docs/) [Orchestrator Constraints](https://fleet-app.qgi.dev/docs/) [Importing Drivers in Bulk](https://fleet-app.qgi.dev/docs/) [Related Pages](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Drivers \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Vehicles -->
# Vehicles

Vehicles

# Vehicles

Register and manage your vehicle fleet — details, assignments, capacity, tracking, and maintenance history.

# [Vehicles](https://fleet-app.qgi.dev/docs/)

**Vehicles** are the fleet assets assigned to carry out orders. Each vehicle record stores physical details, capacity specifications, real-time location, and links to the driver, maintenance schedules, and connected devices (GPS, OBD-II, cameras, sensors).

## [Vehicle Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Name** | Display name for the vehicle (e.g. _Van 01_) |
| **Internal ID** | Your external reference ID for the vehicle |
| **Description** | Free-text description |
| **Make / Model / Model Type / Year** | Manufacturer information |
| **Color** | Vehicle color |
| **Trim / Transmission** | Trim level and transmission type |
| **Body Type / Body Sub-type / Class / Type** | Vehicle classification (e.g. _Sedan / 4-Door / Passenger / car_) |
| **Plate Number** | License plate |
| **VIN** | Vehicle Identification Number |
| **VIN Data** | Decoded VIN information (from the VIN lookup service) |
| **Call Sign** | Radio or dispatch call sign |
| **Serial Number** | Manufacturer serial number, when applicable |
| **Usage Type** | Commercial, personal, etc. |
| **Ownership Type** | Owned, leased, financed |
| **Fuel Type** | Petrol, diesel, electric, hybrid, etc. |
| **Fuel Volume Unit** | Unit used in fuel reports (litres, gallons) |
| **Odometer / Odometer Unit / Odometer at Purchase** | Current and starting mileage with unit |
| **Measurement System** | Metric or imperial — drives unit interpretation across the record |
| **Payload Capacity** | Maximum payload weight (`payload_capacity`). Unit follows the entity weight units you populate. |
| **Payload Capacity Volume** | Maximum payload volume (`payload_capacity_volume`). Unit follows the entity dimensions you set. |
| **Financing Status / Loan Number of Payments / Loan First Payment** | Financing details for leased or financed vehicles |
| **Status** | active, inactive, maintenance, offline |
| **Online** | Whether the vehicle is currently reporting location |
| **Location / Speed / Heading / Altitude** | Real-time GPS position and motion data |
| **Avatar URL / Photo** | Vehicle photo or icon |
| **Vendor** | Carrier or vendor this vehicle belongs to (optional) |
| **Category / Warranty** | Linked category and warranty records |

## [Vehicle Statuses](https://fleet-app.qgi.dev/docs/)

| Status | Meaning |
| --- | --- |
| `active` | Available for assignment |
| `inactive` | Not currently in service |
| `maintenance` | Undergoing maintenance — excluded from Orchestrator |
| `offline` | Not reporting location — may be out of service area |

## [Creating a Vehicle](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Resources → Vehicles**.

Click **\+ New Vehicle**.

Enter the vehicle details: name, make, model, year, plate number, and VIN.

Set **`payload_capacity`** (weight) and **`payload_capacity_volume`** if you want the Orchestrator to enforce load limits. Use units consistent with how you populate entity weights and dimensions.

Assign the vehicle to a **Driver** if there is a default driver for this vehicle.

Upload a photo or select an avatar icon.

Click **Save**.

## [Vehicle Detail Panel](https://fleet-app.qgi.dev/docs/)

Click any vehicle row to open the detail panel, which includes:

- **Overview** — all vehicle attributes and current assignment
- **Map** — current real-time position
- **Positions** — historical GPS track with position replay
- **Devices** — connected GPS trackers, OBD-II scanners, cameras, sensors
- **Equipment** — attached tools, trailers, containers
- **Schedules** — maintenance schedules linked to this vehicle
- **Work Orders** — open and completed work orders
- **Maintenance** — full maintenance history

## [Fleet Assignment](https://fleet-app.qgi.dev/docs/)

Vehicles can belong to one or more **Fleets**. Fleet membership determines which vehicles the Orchestrator considers when allocating orders for a specific fleet's work pool.

To add a vehicle to a fleet:

1. Open the Fleet record → **Vehicles** tab → click **Add Vehicle**
2. Or open the Vehicle record → **Fleets** tab → click **Add to Fleet**

## [Related Pages](https://fleet-app.qgi.dev/docs/)

- [Capacity & Payload](https://fleet-app.qgi.dev/docs/) — configure weight and volume limits
- [Tracking](https://fleet-app.qgi.dev/docs/) — real-time position and position history
- [Maintenance](https://fleet-app.qgi.dev/docs/) — schedules and work orders
- [Connectivity](https://fleet-app.qgi.dev/docs/) — telematics devices and sensors

[Navigator Access\\
\\
Manage a driver's access to the Navigator app — login, device registration, permissions, and auth token management.](https://fleet-app.qgi.dev/docs/) [Capacity & Payload\\
\\
Configure vehicle load capacity — weight and volume limits used by the Orchestrator to prevent overloading during batch order assignment.](https://fleet-app.qgi.dev/docs/)

### On this page

[Vehicles](https://fleet-app.qgi.dev/docs/) [Vehicle Attributes](https://fleet-app.qgi.dev/docs/) [Vehicle Statuses](https://fleet-app.qgi.dev/docs/) [Creating a Vehicle](https://fleet-app.qgi.dev/docs/) [Vehicle Detail Panel](https://fleet-app.qgi.dev/docs/) [Fleet Assignment](https://fleet-app.qgi.dev/docs/) [Related Pages](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Vehicles \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Places -->
# Places

Places

# Places

Create and manage a library of saved locations — pickup points, warehouses, hubs, and delivery addresses used across orders.

# [Places](https://fleet-app.qgi.dev/docs/)

**Places** are saved locations in Fleet-Ops — reusable addresses for pickup points, warehouses, distribution hubs, customer addresses, and any other location your operation regularly uses. When creating orders, you can select from your Places library instead of entering addresses each time.

## [Place Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Name** | Display name (e.g., "Main Warehouse", "Customer HQ") |
| **Type** | Category of location (see types below) |
| **Street 1 / Street 2** | Street address |
| **City** | City |
| **Province / State** | State or province |
| **Postal Code** | Postal or ZIP code |
| **Country** | Country (ISO code) |
| **Neighborhood / District** | Sub-area within the city |
| **Building** | Building name or number |
| **Security Access Code** | Gate or access code for the driver |
| **Phone** | Contact phone at this location |
| **Location** | Precise coordinates (latitude, longitude) |

## [Place Types](https://fleet-app.qgi.dev/docs/)

| Type | Use case |
| --- | --- |
| **Customer** | Delivery address for a customer |
| **Supplier** | Pickup location for a supplier |
| **Warehouse** | Internal storage and dispatch hub |
| **Hub** | Sorting or transfer facility |
| **Service Point** | Drop-off locker or service location |
| **Return Center** | Destination for returned items |

## [Creating a Place](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Resources → Places**.

Click **\+ New Place**.

Enter a **Name** and select the **Type**.

Enter the address details. As you type, the geocoder suggests addresses — select one to auto-fill the fields and set the coordinates.

Adjust the pin on the map if the geocoded position needs correction.

Set the **Security Access Code** if the driver needs a code to enter.

Click **Save**. The place is now available for selection when creating orders.

## [Using Places in Orders](https://fleet-app.qgi.dev/docs/)

When creating an order's payload, the **Pickup** and **Dropoff** fields support autocomplete from your Places library. Type the place name or address and select from the suggestions. Selecting a saved place also loads the contact phone and access code automatically.

## [Place Detail Panel](https://fleet-app.qgi.dev/docs/)

Click any place to open its detail panel:

- **Overview** — address details and map
- **Orders** — orders that have used this place as pickup or dropoff
- **Activity** — recent delivery activity at this location
- **Performance** — delivery success rate and average time at location
- **Documents** — attachments and notes for the location
- **Rules** — automated rules for orders at this location (optional)

## [On the Live Map](https://fleet-app.qgi.dev/docs/)

Places appear as markers on the live map when their map display is enabled. This gives dispatchers a visual reference for all known pickup and delivery locations across the fleet's operational area.

## [Service Areas & Zones](https://fleet-app.qgi.dev/docs/)

Places are not contained by Service Areas or Zones — there is no foreign key from a Place to a Service Area, and a Place's geographic location does not automatically associate it with the polygon-bounded areas defined elsewhere in Fleet-Ops. Service Areas and Zones operate as a separate scoping concept used by Service Rates and the Orchestrator to filter routes and pricing; they don't index Places.

See [Service Rates](https://fleet-app.qgi.dev/docs/) for how Service Areas and Zones scope pricing.

## [Importing Places](https://fleet-app.qgi.dev/docs/)

Click **Import** in the places list to bulk-upload from CSV. Download the template, fill in your location data, and upload. Fleet-Ops geocodes any addresses that don't have coordinates.

[Contacts\\
\\
Manage the people and businesses your operation works with — customers, suppliers, and facilitators — with linked locations and custom fields.](https://fleet-app.qgi.dev/docs/) [Issues\\
\\
Log, track, and resolve operational issues — vehicle faults, driver incidents, accidents, and delivery exceptions — with priority and status tracking.](https://fleet-app.qgi.dev/docs/)

### On this page

[Places](https://fleet-app.qgi.dev/docs/) [Place Attributes](https://fleet-app.qgi.dev/docs/) [Place Types](https://fleet-app.qgi.dev/docs/) [Creating a Place](https://fleet-app.qgi.dev/docs/) [Using Places in Orders](https://fleet-app.qgi.dev/docs/) [Place Detail Panel](https://fleet-app.qgi.dev/docs/) [On the Live Map](https://fleet-app.qgi.dev/docs/) [Service Areas & Zones](https://fleet-app.qgi.dev/docs/) [Importing Places](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Places \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Fleets -->
# Fleets

Fleets

# Fleets

Organize drivers and vehicles into named fleets — group by zone, service type, or shift for structured dispatch and Orchestrator scoping.

# [Fleets](https://fleet-app.qgi.dev/docs/)

A **Fleet** is a logical grouping of drivers and vehicles. Fleets let you organize your operation by route, service area, service type, shift time, or any other dimension that makes sense for your business. The Orchestrator uses fleet membership to scope which drivers and vehicles are considered when allocating orders.

## [Fleet Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Name** | Fleet display name (e.g., "Metro North", "Express Delivery") |
| **Color** | Color coding for map visualization |
| **Task** | Optional label describing the fleet's purpose (e.g., "Same-day delivery") |
| **Service Area** | Geographic zone this fleet operates in |
| **Zone** | Sub-zone within the service area |
| **Vendor** | Carrier/vendor that owns this fleet (optional) |
| **Parent Fleet** | Parent fleet for hierarchical organization |
| **Status** | active or inactive |

## [Live Metrics](https://fleet-app.qgi.dev/docs/)

Each fleet card shows live counts:

- **Drivers** — total drivers in the fleet
- **Drivers Online** — drivers currently active in Navigator
- **Vehicles** — total vehicles in the fleet
- **Vehicles Online** — vehicles currently reporting location

## [Creating a Fleet](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Resources → Fleets**.

Click **\+ New Fleet**.

Set the fleet **Name**, **Color**, and optionally the **Task** label.

Set the **Service Area** and **Zone** to scope the fleet geographically.

Click **Save**. The fleet is created empty — add drivers and vehicles next.

## [Adding Drivers to a Fleet](https://fleet-app.qgi.dev/docs/)

1. Open the fleet record and click the **Drivers** tab
2. Click **Add Driver** and select from the drivers list
3. Drivers can belong to multiple fleets

Or from the driver record:

1. Open **Fleet-Ops → Resources → Drivers → \[Driver\]**
2. Click the **Fleets** tab → **Add to Fleet**

## [Adding Vehicles to a Fleet](https://fleet-app.qgi.dev/docs/)

1. Open the fleet record and click the **Vehicles** tab
2. Click **Add Vehicle** and select from the vehicles list
3. Vehicles can belong to multiple fleets

## [Hierarchical Fleets](https://fleet-app.qgi.dev/docs/)

Fleets support a parent-child hierarchy. For example:

```
Metropolitan Fleet
  ├─ Metro North
  ├─ Metro Central
  └─ Metro South
```

Set the **Parent Fleet** field when creating a sub-fleet. The parent fleet shows aggregated counts (drivers, vehicles) across all child fleets.

## [How Fleets Are Used in Dispatch](https://fleet-app.qgi.dev/docs/)

### [Orchestrator Scoping](https://fleet-app.qgi.dev/docs/)

When running the Orchestrator, you can filter by fleet. Only drivers and vehicles in the selected fleet are included in the optimization run. This lets you run separate passes for different operational zones.

### [Orders List Filtering](https://fleet-app.qgi.dev/docs/)

In the orders list, filter by fleet to see only orders assigned to drivers in that fleet.

### [Scheduler Calendar](https://fleet-app.qgi.dev/docs/)

The Scheduler can be filtered by fleet to show only the drivers in a specific fleet's shift calendar.

## [Use Cases](https://fleet-app.qgi.dev/docs/)

| Fleet Type | Example |
| --- | --- |
| **Geographic** | "Northside Fleet", "CBD Fleet" |
| **Service Type** | "Same-Day Fleet", "Scheduled Fleet", "Express" |
| **Shift-Based** | "Morning Shift", "Evening Shift" |
| **Vehicle Type** | "Motorcycle Fleet", "Refrigerated Fleet" |
| **Vendor** | "Partner Carrier A", "Subcontractor B" |

[Vehicle Tracking\\
\\
Monitor real-time vehicle position, view trip history, and replay historical routes from the vehicle detail panel.](https://fleet-app.qgi.dev/docs/) [Vendors\\
\\
Manage third-party carriers and service providers — assign drivers, configure integrations, and relay orders to partner logistics platforms.](https://fleet-app.qgi.dev/docs/)

### On this page

[Fleets](https://fleet-app.qgi.dev/docs/) [Fleet Attributes](https://fleet-app.qgi.dev/docs/) [Live Metrics](https://fleet-app.qgi.dev/docs/) [Creating a Fleet](https://fleet-app.qgi.dev/docs/) [Adding Drivers to a Fleet](https://fleet-app.qgi.dev/docs/) [Adding Vehicles to a Fleet](https://fleet-app.qgi.dev/docs/) [Hierarchical Fleets](https://fleet-app.qgi.dev/docs/) [How Fleets Are Used in Dispatch](https://fleet-app.qgi.dev/docs/) [Orchestrator Scoping](https://fleet-app.qgi.dev/docs/) [Orders List Filtering](https://fleet-app.qgi.dev/docs/) [Scheduler Calendar](https://fleet-app.qgi.dev/docs/) [Use Cases](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Fleets \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Contacts & customers -->
# Contacts & customers

Contacts

# Contacts

Manage the people and businesses your operation works with — customers, suppliers, and facilitators — with linked locations and custom fields.

# [Contacts](https://fleet-app.qgi.dev/docs/)

**Contacts** are the people and businesses your logistics operation interacts with. They serve as customers who receive deliveries, suppliers who provide pickups, and facilitators who coordinate operations. Contacts are linked to Places (their physical location) and can be assigned to orders in the customer or facilitator role.

## [Contact Types](https://fleet-app.qgi.dev/docs/)

| Type | Role |
| --- | --- |
| **Customer** | Recipient of a delivery — linked to the dropoff location |
| **Supplier** | Origin of items — linked to the pickup location |
| **Facilitator** | Coordinates or manages the order on behalf of a customer |
| **Contact** | General contact not tied to a specific role |

## [Contact Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| `name` | Full name or business name |
| `title` | Job title or role label |
| `email` | Email address — used for order notifications |
| `phone` | Phone number — used for SMS notifications |
| `type` | customer, supplier, facilitator, contact |
| `notes` | Free-text notes about the contact |
| `place_uuid` | Linked Place record (physical address) |
| `photo_uuid` | Profile photo or logo |
| `internal_id` | Your external reference ID for the contact |
| `meta` | Custom fields specific to your operation |

## [Creating a Contact](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Resources → Contacts**.

Click **\+ New Contact**.

Enter the contact's name, phone, and email.

Select the **Type** (customer, supplier, facilitator, or contact).

Link a **Place** — the contact's physical address. Select from existing places or enter a new one.

Click **Save**.

## [Using Contacts on Orders](https://fleet-app.qgi.dev/docs/)

When creating an order, you can assign a contact as:

- **Customer** — the recipient. The customer's phone and email are used for delivery notifications.
- **Facilitator** — the coordinator. If a facilitator is set, they receive operational notifications instead of the customer.

The contact's linked Place can be used directly as the pickup or dropoff address when creating the order payload.

## [Customers Sub-Section](https://fleet-app.qgi.dev/docs/)

The sidebar has a dedicated **Customers** sub-section within Contacts that filters to show only contacts of type **customer**. This is a convenience view for operations focused on customer management.

## [Custom Fields](https://fleet-app.qgi.dev/docs/)

Additional data specific to your operation can be attached to contacts using **Custom Fields**. Navigate to **Fleet-Ops → Settings → Custom Fields → Contacts** to define fields like account number, loyalty tier, or contract type.

## [Importing Contacts](https://fleet-app.qgi.dev/docs/)

Click **Import** in the contacts list to bulk-import from a CSV file. Download the template, fill in your data, and upload. Fleet-Ops creates all contact records with their linked Places.

## [Notifications](https://fleet-app.qgi.dev/docs/)

When a contact is linked to an order as the customer, they receive:

- **Dispatch notification** — when the order is dispatched to a driver
- **In-transit notification** — when the driver is on the way
- **Completed notification** — when delivery is confirmed with proof

Notification channels (SMS, email, push) and message templates are configured in **Fleet-Ops → Settings → Notifications**.

[Integrated Vendors\\
\\
Connect external logistics platforms (Lalamove and others) using the Integrated Vendor framework, and learn how to register your own provider.](https://fleet-app.qgi.dev/docs/) [Places\\
\\
Create and manage a library of saved locations — pickup points, warehouses, hubs, and delivery addresses used across orders.](https://fleet-app.qgi.dev/docs/)

### On this page

[Contacts](https://fleet-app.qgi.dev/docs/) [Contact Types](https://fleet-app.qgi.dev/docs/) [Contact Attributes](https://fleet-app.qgi.dev/docs/) [Creating a Contact](https://fleet-app.qgi.dev/docs/) [Using Contacts on Orders](https://fleet-app.qgi.dev/docs/) [Customers Sub-Section](https://fleet-app.qgi.dev/docs/) [Custom Fields](https://fleet-app.qgi.dev/docs/) [Importing Contacts](https://fleet-app.qgi.dev/docs/) [Notifications](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Contacts \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Vendors -->
# Vendors

Vendors

# Vendors

Manage third-party carriers and service providers — assign drivers, configure integrations, and relay orders to partner logistics platforms.

# [Vendors](https://fleet-app.qgi.dev/docs/)

**Vendors** are third-party carriers or service providers in your logistics network. Unlike internal drivers and vehicles, a vendor represents an external company that provides their own drivers and fleet. Orders can be assigned to a vendor, who then dispatches using their own resources.

## [Vendor Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Name** | Vendor company name |
| **Email** | Contact email |
| **Phone** | Contact phone |
| `website_url` | Vendor's website URL |
| **Country** | Primary operating country |
| **Business ID** | Business registration number |
| **Type** | Carrier, courier, partner, etc. |
| **Place** | Vendor's physical address/headquarters |
| **Status** | active, inactive, suspended |
| **Notes** | Internal notes about the vendor |

## [Vendor vs. Contact](https://fleet-app.qgi.dev/docs/)

Contacts represent individuals (customers, suppliers). Vendors represent companies that provide logistics services — they can have their own driver pools and fleet, and can be integrated with external APIs.

## [Creating a Vendor](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Resources → Vendors**.

Click **\+ New Vendor**.

Enter the vendor name, contact details, and type.

Link a **Place** for the vendor's headquarters or dispatch hub.

Click **Save**.

## [Assigning Drivers and Fleets to a Vendor](https://fleet-app.qgi.dev/docs/)

External carriers can have their own drivers and vehicles registered in Fleet-Ops:

1. Open the vendor record
2. Navigate to the **Drivers** tab → click **Add Driver**
3. Navigate to the **Fleets** tab → click **Add Fleet**

Vendor-assigned drivers and vehicles are included in Orchestrator runs when the vendor's fleet is selected.

## [Integrated Vendors](https://fleet-app.qgi.dev/docs/)

For external logistics platforms (like Lalamove) connected via their API, Fleet-Ops uses a separate model called **`IntegratedVendor`** with its own framework for adding new providers. See [Integrated Vendors](https://fleet-app.qgi.dev/docs/) for the dedicated guide.

## [Using Vendors as Order Facilitators](https://fleet-app.qgi.dev/docs/)

When creating an order, you can assign a vendor as the **Facilitator** — the entity responsible for executing the delivery. The facilitator role is separate from the driver assignment: the vendor is the company, the driver is the individual executing the job.

This is useful for:

- Third-party carrier orders where the vendor dispatches internally
- Integrated vendor orders relayed to a partner platform
- Sub-contracting specific order types to specialist carriers

## [Sub-Organizations](https://fleet-app.qgi.dev/docs/)

If your CBRE Fleet instance is part of a multi-tenant setup, a vendor can be linked to another CBRE Fleet organization via the `connect_company_uuid` field. This enables cross-organization order relay and tracking.

## [Related Pages](https://fleet-app.qgi.dev/docs/)

- [Integrated Vendors](https://fleet-app.qgi.dev/docs/) — connect external logistics platforms (Lalamove, etc.) and add your own provider via the framework

[Fleets\\
\\
Organize drivers and vehicles into named fleets — group by zone, service type, or shift for structured dispatch and Orchestrator scoping.](https://fleet-app.qgi.dev/docs/) [Integrated Vendors\\
\\
Connect external logistics platforms (Lalamove and others) using the Integrated Vendor framework, and learn how to register your own provider.](https://fleet-app.qgi.dev/docs/)

### On this page

[Vendors](https://fleet-app.qgi.dev/docs/) [Vendor Attributes](https://fleet-app.qgi.dev/docs/) [Vendor vs. Contact](https://fleet-app.qgi.dev/docs/) [Creating a Vendor](https://fleet-app.qgi.dev/docs/) [Assigning Drivers and Fleets to a Vendor](https://fleet-app.qgi.dev/docs/) [Integrated Vendors](https://fleet-app.qgi.dev/docs/) [Using Vendors as Order Facilitators](https://fleet-app.qgi.dev/docs/) [Sub-Organizations](https://fleet-app.qgi.dev/docs/) [Related Pages](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Vendors \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Issues -->
# Issues

Issues

# Issues

Log, track, and resolve operational issues — vehicle faults, driver incidents, accidents, and delivery exceptions — with priority and status tracking.

# [Issues](https://fleet-app.qgi.dev/docs/)

**Issues** are operational incidents, faults, and exceptions that need to be tracked and resolved. Drivers can report issues from the Navigator app, and operators can log them from the console. Issues are linked to the driver, vehicle, or location involved and tracked through a resolution workflow.

## [Issue Categories](https://fleet-app.qgi.dev/docs/)

| Category | Examples |
| --- | --- |
| **Mechanical** | Breakdown, flat tire, engine fault, warning light |
| **Accident** | Collision, near-miss, vehicle damage |
| **Behavioral** | Speeding, unsafe driving, customer complaint |
| **Traffic** | Traffic violation, parking fine |
| **Fuel** | Fuel discrepancy, suspected theft |
| **Maintenance** | Overdue service, parts failure |
| **Device** | GPS tracker offline, OBD-II disconnected |
| **Customer** | Failed delivery, access issue, wrong address |

## [Issue Priority Levels](https://fleet-app.qgi.dev/docs/)

| Priority | Response expectation |
| --- | --- |
| **Low** | No immediate action required |
| **Medium** | Address within the working day |
| **High** | Address within the hour |
| **Critical** | Immediate response required — vehicle out of service |

## [Issue Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Title** | Short description of the issue |
| **Report** | Detailed description |
| **Category** | Type of issue (mechanical, behavioral, etc.) |
| **Priority** | low, medium, high, critical |
| **Driver** | Driver involved in the incident |
| **Vehicle** | Vehicle involved (if applicable) |
| **Location** | GPS coordinates where the issue occurred |
| **Tags** | Labels for filtering and categorization |
| **Reported By** | Who logged the issue (driver or operator) |
| **Assigned To** | Who is responsible for resolving it |
| **Status** | Current resolution stage |
| **Resolved At** | Timestamp when the issue was closed |

## [Issue Statuses](https://fleet-app.qgi.dev/docs/)

`status` is a free-form string — operators can use any value. Newly created issues default to **`pending`** if no status is supplied. Common values used in practice:

| Status | Meaning |
| --- | --- |
| `pending` | Default status when an issue is logged but not yet triaged |
| `assigned` | Assigned to a resolver |
| `in_progress` | Being actively worked on |
| `resolved` | Resolution applied, pending confirmation |
| `closed` | Fully resolved and closed |

The status setter dasherizes the input, so `In Progress` and `in_progress` are stored consistently.

## [Creating an Issue](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Resources → Issues**.

Click **\+ New Issue**.

Set the **Title**, **Category**, and **Priority**.

Write the **Report** — a detailed description of what happened.

Link the **Driver** and/or **Vehicle** involved.

Assign to an **Assignee** responsible for resolution.

Add **Tags** for grouping (e.g., "fleet-north", "urgent").

Click **Save**.

## [Reporting Issues from the Navigator App](https://fleet-app.qgi.dev/docs/)

Drivers can report issues directly from the Navigator app — they don't need console access. The flow lives on the **Issues** screen and reaches it from the main Navigator menu:

The driver opens **Issues** in Navigator and taps **\+ New Issue**.

Navigator pre-fills the driver and the driver's current location (read live from the device's GPS). The driver picks the **Category** and **Priority** and writes the **Report**.

The driver can attach photo evidence captured directly from the camera, link the issue to the **Vehicle** they're driving, and tag it.

On submit, Fleet-Ops creates the issue with status `pending`. Operators receive a notification based on the issue's priority.

Drivers can also edit issues they've previously submitted (e.g. add follow-up photos or update the description) from the **Issues** screen. The same record is visible to operators in the console.

## [Resolving Issues](https://fleet-app.qgi.dev/docs/)

1. Open an issue and click **Assign** to set a resolver
2. As work progresses, update the status through the workflow
3. When resolved, click **Resolve** and add resolution notes
4. Click **Close** to finalize

Resolved and closed issues remain in the list for reporting and audit purposes.

## [Issue Reporting](https://fleet-app.qgi.dev/docs/)

Export issues from the list to CSV for reporting. Filter by date range, category, priority, driver, or vehicle to generate targeted reports on fleet incidents.

[Places\\
\\
Create and manage a library of saved locations — pickup points, warehouses, hubs, and delivery addresses used across orders.](https://fleet-app.qgi.dev/docs/) [Fuel Reports\\
\\
Record and monitor fleet fuel consumption — track volume, cost, odometer readings, and fuel-up locations for cost analysis and fraud detection.](https://fleet-app.qgi.dev/docs/)

### On this page

[Issues](https://fleet-app.qgi.dev/docs/) [Issue Categories](https://fleet-app.qgi.dev/docs/) [Issue Priority Levels](https://fleet-app.qgi.dev/docs/) [Issue Attributes](https://fleet-app.qgi.dev/docs/) [Issue Statuses](https://fleet-app.qgi.dev/docs/) [Creating an Issue](https://fleet-app.qgi.dev/docs/) [Reporting Issues from the Navigator App](https://fleet-app.qgi.dev/docs/) [Resolving Issues](https://fleet-app.qgi.dev/docs/) [Issue Reporting](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Issues \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Fuel reports -->
# Fuel reports

Fuel Reports

# Fuel Reports

Record and monitor fleet fuel consumption — track volume, cost, odometer readings, and fuel-up locations for cost analysis and fraud detection.

# [Fuel Reports](https://fleet-app.qgi.dev/docs/)

**Fuel Reports** are records of fleet fuel consumption. Drivers submit reports when they fill up, and operators can log reports from the console. Each report captures the volume, cost, odometer reading, and location of the fuel transaction. Fuel reports are used for cost tracking, consumption analysis, and detecting anomalies.

## [Fuel Report Fields](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Driver** | The driver who reported the fuel purchase |
| **Vehicle** | The vehicle that was refuelled |
| **Reported By** | User who created the report (may differ from driver) |
| **Volume** | Fuel volume purchased (liters or gallons) |
| **Metric Unit** | liters or gallons |
| **Amount** | Total cost of the fuel purchase |
| **Currency** | Currency of the amount |
| **Odometer** | Current odometer reading at the time of fill-up |
| **Location** | GPS coordinates of the fuel station |
| **Report** | Notes or additional details |
| **Status** | pending, approved, rejected |

## [Fuel Report Statuses](https://fleet-app.qgi.dev/docs/)

| Status | Meaning |
| --- | --- |
| `pending` | Submitted, awaiting operator review |
| `approved` | Verified and accepted |
| `rejected` | Flagged as inaccurate or rejected |

## [Creating a Fuel Report](https://fleet-app.qgi.dev/docs/)

### [From the Console](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Resources → Fuel Reports**.

Click **\+ New Fuel Report**.

Select the **Driver** and **Vehicle**.

Enter the **Volume**, **Metric Unit**, and **Amount**.

Enter the **Odometer** reading.

Set the location by entering an address or using the map.

Add any **Notes** about the transaction.

Click **Save**.

### [From the Navigator App](https://fleet-app.qgi.dev/docs/)

Drivers can submit and edit fuel reports directly from the Navigator app — no console access needed:

The driver opens **Fuel Reports** in Navigator and taps **\+ New Fuel Report**.

Navigator pre-fills the driver and the driver's current GPS location. The driver enters the **Volume**, **Metric Unit**, **Amount**, **Currency**, and **Odometer** reading; selects the **Vehicle**; and adds optional notes.

On submit, Fleet-Ops creates the fuel report with status `pending`. It appears in the console for operator approval.

Drivers can also **edit existing fuel reports** they previously submitted from the same screen — for example to correct an odometer reading after taking a clearer photo. The same record is visible to operators in the console.

## [Reviewing and Approving Reports](https://fleet-app.qgi.dev/docs/)

Fuel reports submitted by drivers start in `pending` status. Operators review and either:

- **Approve** — confirm the report is accurate; it is included in fuel cost calculations
- **Reject** — flag the report as inaccurate with a reason

Rejected reports notify the driver and can be resubmitted with corrections.

## [Analysis Use Cases](https://fleet-app.qgi.dev/docs/)

Fuel reports enable:

| Analysis | How |
| --- | --- |
| **Cost per km** | Combine odometer data with order distance data |
| **Consumption anomalies** | Flag high volume vs. distance ratio (potential fraud) |
| **Fleet cost allocation** | Break down fuel costs per vehicle or fleet |
| **Budget tracking** | Compare actual fuel spend vs. budget |
| **Service interval planning** | Use odometer readings to trigger maintenance reminders |

## [Exporting Fuel Data](https://fleet-app.qgi.dev/docs/)

Click **Export** in the fuel reports list to download a CSV of all reports. Filter by date range, driver, or vehicle before exporting for targeted cost reports.

[Issues\\
\\
Log, track, and resolve operational issues — vehicle faults, driver incidents, accidents, and delivery exceptions — with priority and status tracking.](https://fleet-app.qgi.dev/docs/) [Maintenance Overview\\
\\
Manage vehicle and equipment maintenance — preventive schedules, work orders, parts inventory, and maintenance history.](https://fleet-app.qgi.dev/docs/)

### On this page

[Fuel Reports](https://fleet-app.qgi.dev/docs/) [Fuel Report Fields](https://fleet-app.qgi.dev/docs/) [Fuel Report Statuses](https://fleet-app.qgi.dev/docs/) [Creating a Fuel Report](https://fleet-app.qgi.dev/docs/) [From the Console](https://fleet-app.qgi.dev/docs/) [From the Navigator App](https://fleet-app.qgi.dev/docs/) [Reviewing and Approving Reports](https://fleet-app.qgi.dev/docs/) [Analysis Use Cases](https://fleet-app.qgi.dev/docs/) [Exporting Fuel Data](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Fuel Reports \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Work orders -->
# Work orders

Work Orders

# Work Orders

Create, assign, and track maintenance work orders — from initial task assignment through checklist completion and cost tracking to closure.

# [Work Orders](https://fleet-app.qgi.dev/docs/)

A **Work Order** is an assigned maintenance or repair task. Work orders can be generated automatically from maintenance schedules when service thresholds are reached, or created manually for ad-hoc repairs and inspections. Each work order is tracked from open to completion with a full cost record.

## [Work Order Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Code** | Auto-generated reference code (e.g., WO-00123) |
| **Subject** | Title of the task (e.g., "Oil Change", "Brake Pad Replacement") |
| **Target** | The vehicle, driver, or equipment the work is performed on |
| **Assignee** | Who is responsible for completing the task |
| **Priority** | low, medium, high, critical |
| **Status** | Current stage in the workflow |
| **Opened At** | When the work order was created |
| **Due At** | Deadline for completion |
| **Closed At** | When the work order was completed and closed |
| **Instructions** | Detailed task instructions |
| **Checklist** | Structured list of sub-tasks to complete |
| **Estimated Cost** | Pre-work cost estimate |
| **Approved Budget** | Authorized spend amount |
| **Actual Cost** | Real cost after completion |
| **Currency** | Currency for all cost fields |
| **Cost Center** | Budget code/department for accounting |

## [Work Order Status Flow](https://fleet-app.qgi.dev/docs/)

```
open ──► in_progress ──► closed
            │
        canceled (terminal at any point)
```

| Status | Meaning |
| --- | --- |
| `open` | Created, not yet started — the default for new work orders |
| `in_progress` | Work has begun |
| `closed` | Work complete, all checklist items done, `closed_at` set |
| `canceled` | Canceled before completion |

Assignment is captured by the polymorphic `assignee_uuid` / `assignee_type` fields, not by status — a work order in `open` can already have an assignee.

## [Creating a Work Order](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Maintenance → Work Orders**.

Click **\+ New Work Order**.

Set the **Subject** and **Priority**.

Select the **Target** — a Vehicle, Driver, or Equipment record.

Set the **Assignee** — a user or team responsible for completing the work.

Set the **Due At** date.

Add **Instructions** describing what needs to be done.

Build the **Checklist** — add individual task items the assignee must check off (e.g., "Drain old oil", "Replace oil filter", "Refill with new oil", "Run engine and check for leaks").

Set the **Estimated Cost** and **Approved Budget**.

Click **Save**.

## [Checklist](https://fleet-app.qgi.dev/docs/)

The checklist breaks the work order into discrete, trackable steps. Assignees check off each item as they complete it. The work order cannot move to `pending_review` until all checklist items are marked done.

Checklist items can include:

- Inspection steps
- Parts replacement
- Fluid top-ups
- Sensor calibration
- Road test

## [Cost Tracking](https://fleet-app.qgi.dev/docs/)

Work orders track three cost values:

| Field | When set |
| --- | --- |
| **Estimated Cost** | Set at creation by the scheduler or manager |
| **Approved Budget** | Authorized by a supervisor before work begins |
| **Actual Cost** | Recorded by the assignee during or after work completion |

Cost breakdown can be itemized using line items — listing parts, labor, and other expenses separately. Parts used from the Parts inventory are automatically linked and their cost added to the actual cost.

## [Email Notifications](https://fleet-app.qgi.dev/docs/)

From the work order detail, click **Send Email** to notify the assignee or stakeholders about the work order status, due date, or instructions. The email includes the work order code, subject, instructions, and a link to the work order in the console.

## [Attaching Work Orders to Vehicles](https://fleet-app.qgi.dev/docs/)

Work orders are linked to their target vehicle and appear in:

- **Fleet-Ops → Resources → Vehicles → \[Vehicle\] → Work Orders** tab
- The vehicle's maintenance cost history

[Maintenance Schedules\\
\\
Set up preventive maintenance rules for vehicles and equipment — trigger work orders automatically by mileage, engine hours, or calendar interval.](https://fleet-app.qgi.dev/docs/) [Equipment\\
\\
Track fleet equipment assets — tools, trailers, containers — with maintenance schedules, work orders, and warranty coverage.](https://fleet-app.qgi.dev/docs/)

### On this page

[Work Orders](https://fleet-app.qgi.dev/docs/) [Work Order Attributes](https://fleet-app.qgi.dev/docs/) [Work Order Status Flow](https://fleet-app.qgi.dev/docs/) [Creating a Work Order](https://fleet-app.qgi.dev/docs/) [Checklist](https://fleet-app.qgi.dev/docs/) [Cost Tracking](https://fleet-app.qgi.dev/docs/) [Email Notifications](https://fleet-app.qgi.dev/docs/) [Attaching Work Orders to Vehicles](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Work Orders \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Maintenance schedules -->
# Maintenance schedules

Schedules

# Maintenance Schedules

Set up preventive maintenance rules for vehicles and equipment — trigger work orders automatically by mileage, engine hours, or calendar interval.

# [Maintenance Schedules](https://fleet-app.qgi.dev/docs/)

**Maintenance Schedules** are preventive maintenance rules that monitor vehicles and equipment and automatically generate work orders when service is due. Each schedule defines a target (vehicle or equipment), an interval method, and thresholds — Fleet-Ops tracks usage and fires work orders when the threshold is reached.

## [Schedule Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Name** | Description of the maintenance task (e.g., "Oil Change", "Brake Inspection") |
| **Target** | The vehicle or equipment being maintained |
| **Interval Method** | How the service interval is measured |
| **Interval Type** | The specific trigger type within the method |
| **Interval Value** | The threshold amount (e.g., 10,000 km, 90 days) |
| **Last Service Date** | Date of the most recent service |
| **Last Service Odometer** | Odometer reading at last service |
| **Last Service Engine Hours** | Engine hours at last service |
| **Next Due Date** | Calculated next service date |
| **Next Due Odometer** | Calculated next odometer trigger |
| **Default Priority** | Priority for auto-generated work orders |
| **Default Assignee** | Who work orders are automatically assigned to |
| **Instructions** | Specific instructions for the service task |
| **Reminder Offsets** | Advance warning periods (e.g., 7 days before due) |
| **Status** | active, paused, completed |

## [Interval Methods](https://fleet-app.qgi.dev/docs/)

| Method | Triggers when |
| --- | --- |
| **Time-based** | A calendar interval elapses (e.g., every 90 days) |
| **Mileage-based** | The odometer exceeds a threshold (e.g., every 10,000 km) |
| **Engine Hours-based** | Engine hours exceed a threshold (e.g., every 250 hours) |
| **Hybrid** | A combination of the above — triggers on whichever comes first |

## [Creating a Maintenance Schedule](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Maintenance → Schedules**.

Click **\+ New Schedule**.

Select the **Target** — a Vehicle or Equipment record.

Enter a **Name** for the maintenance task.

Select the **Interval Method** and set the **Interval Value** (e.g., 10,000 km or 90 days).

Enter the **Last Service Date** and odometer/engine hours at last service. Fleet-Ops uses these to calculate the next due date immediately.

Set the **Default Priority** and **Default Assignee** for auto-generated work orders.

Add **Reminder Offsets** — e.g., send a reminder 7 days and 1 day before the due date.

Add any **Instructions** the mechanic needs for this task.

Click **Save**. Fleet-Ops starts monitoring the vehicle and will trigger a work order when the threshold is reached.

## [Automatic Work Order Generation](https://fleet-app.qgi.dev/docs/)

When a schedule's threshold is reached:

1. Fleet-Ops creates a **Work Order** automatically with the schedule's default priority and assignee
2. The work order appears in **Maintenance → Work Orders** marked as open
3. The designated assignee receives a notification
4. The vehicle's status can be automatically set to `maintenance` (configurable)

## [Calendar Feed and iCal Export](https://fleet-app.qgi.dev/docs/)

Maintenance schedules can be exported to a calendar:

- **iCal Export** — download an `.ics` file of all scheduled maintenance dates
- **Calendar Feed URL** — subscribe from Google Calendar, Outlook, or any calendar app using the feed URL

This lets fleet managers track upcoming maintenance alongside other operations calendars.

## [Pausing and Resuming Schedules](https://fleet-app.qgi.dev/docs/)

If a vehicle is taken out of service or sold:

1. Open the schedule and click **Pause** — the schedule stops monitoring but is not deleted
2. Click **Resume** when the vehicle returns to service

Paused schedules do not generate work orders. The next due date is recalculated from the resume date.

## [Attaching Schedules to Vehicles](https://fleet-app.qgi.dev/docs/)

Maintenance schedules can also be created directly from the vehicle record:

1. Open **Fleet-Ops → Resources → Vehicles → \[Vehicle\]**
2. Click the **Schedules** tab
3. Click **\+ Add Schedule** — same form as above, with the vehicle pre-filled

[Maintenance Overview\\
\\
Manage vehicle and equipment maintenance — preventive schedules, work orders, parts inventory, and maintenance history.](https://fleet-app.qgi.dev/docs/) [Work Orders\\
\\
Create, assign, and track maintenance work orders — from initial task assignment through checklist completion and cost tracking to closure.](https://fleet-app.qgi.dev/docs/)

### On this page

[Maintenance Schedules](https://fleet-app.qgi.dev/docs/) [Schedule Attributes](https://fleet-app.qgi.dev/docs/) [Interval Methods](https://fleet-app.qgi.dev/docs/) [Creating a Maintenance Schedule](https://fleet-app.qgi.dev/docs/) [Automatic Work Order Generation](https://fleet-app.qgi.dev/docs/) [Calendar Feed and iCal Export](https://fleet-app.qgi.dev/docs/) [Pausing and Resuming Schedules](https://fleet-app.qgi.dev/docs/) [Attaching Schedules to Vehicles](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Maintenance Schedules \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Parts inventory -->
# Parts inventory

Parts

# Parts

Track parts inventory used in fleet maintenance — log parts used in work orders, manage supplier info, and track costs.

# [Parts](https://fleet-app.qgi.dev/docs/)

**Parts** is the inventory management section for maintenance components. It tracks the parts available for vehicle and equipment maintenance — oil filters, brake pads, belts, fluids, and any other consumables used in work orders.

## [Part Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| `name` | Part name (e.g., _Oil Filter – Toyota Hiace_) |
| `manufacturer` / `model` | Manufacturer name and OEM model |
| `sku` | Internal stock keeping unit |
| `serial_number` | Serial number, when applicable |
| `barcode` | Barcode for scanning |
| `description` | Detailed description of the part |
| `vendor_uuid` | Vendor or supplier who provides this part |
| `warranty_uuid` | Linked warranty record |
| `unit_cost` / `msrp` / `currency` | Cost per unit, manufacturer's suggested retail price, and currency |
| `quantity_on_hand` | Current stock level |
| `reorder_point` | Alert threshold — when `quantity_on_hand` drops below this, the part is flagged `is_low_stock` and a low-stock alert can fire. Stored under the `specs` JSON; defaults to 5. |
| `asset_type` / `asset_uuid` | Polymorphic link to the asset this part belongs to (e.g. a vehicle or piece of equipment) |
| `photo_uuid` | Reference to an uploaded photo of the part |
| `status` / `type` | Free-form classification fields |

## [Creating a Part Record](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Maintenance → Parts**.

Click **\+ New Part**.

Enter the **Name**, **Part Number**, and **SKU**.

Set the **Supplier** and **Unit Cost**.

Optionally set the **Quantity on Hand** if you want to track stock levels.

Click **Save**.

## [Using Parts in Work Orders](https://fleet-app.qgi.dev/docs/)

When completing a work order, the assignee can log which parts were used:

1. In the work order detail, click **\+ Add Part**
2. Select the part from the inventory
3. Enter the quantity used
4. The part cost is automatically added to the work order's **Actual Cost**

Parts used in completed work orders are recorded in the maintenance history, giving a full record of what components were replaced in each vehicle over its lifetime.

## [Cost Tracking](https://fleet-app.qgi.dev/docs/)

Parts costs flow into the work order cost breakdown:

```
Work Order Actual Cost = Labor Cost + Parts Cost + Other Costs
```

Parts selected from the inventory carry their unit cost, which multiplied by quantity gives the line item cost in the work order's cost breakdown.

## [Supplier Management](https://fleet-app.qgi.dev/docs/)

Each part can be linked to a **Vendor** (supplier) record from **Fleet-Ops → Resources → Vendors**. This lets you:

- Track which supplier provides each part
- Identify the preferred supplier for reorders
- Reference supplier contact information when placing orders

## [Importing Parts](https://fleet-app.qgi.dev/docs/)

Click **Import** in the parts list to bulk-upload your parts inventory from a CSV file. This is useful when onboarding an existing parts catalog.

[Equipment\\
\\
Track fleet equipment assets — tools, trailers, containers — with maintenance schedules, work orders, and warranty coverage.](https://fleet-app.qgi.dev/docs/) [Connectivity Overview\\
\\
Connect Fleet-Ops to external hardware — telematics modems, GPS devices, IoT sensors, and third-party data providers.](https://fleet-app.qgi.dev/docs/)

### On this page

[Parts](https://fleet-app.qgi.dev/docs/) [Part Attributes](https://fleet-app.qgi.dev/docs/) [Creating a Part Record](https://fleet-app.qgi.dev/docs/) [Using Parts in Work Orders](https://fleet-app.qgi.dev/docs/) [Cost Tracking](https://fleet-app.qgi.dev/docs/) [Supplier Management](https://fleet-app.qgi.dev/docs/) [Importing Parts](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Parts \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Devices & telematics -->
# Devices & telematics

Devices

# Devices

Manage GPS trackers, OBD-II scanners, dash cameras, and other IoT devices connected to your fleet vehicles.

# [Devices](https://fleet-app.qgi.dev/docs/)

**Devices** are the physical hardware units connected to your fleet vehicles — GPS trackers, OBD-II scanners, dash cameras, and IoT sensors. While telematics modems are managed in the Telematics section (they bridge to a provider's cloud), Devices tracks the individual hardware installed in or on vehicles.

Navigate to **Fleet-Ops → Connectivity → Devices**.

## [Device Categories](https://fleet-app.qgi.dev/docs/)

| Category | Examples |
| --- | --- |
| **GPS Tracker** | Standalone GPS tracker (non-OBD), asset tracker |
| **OBD-II Scanner** | Engine diagnostic reader via OBD-II port |
| **Dash Camera** | Front/rear facing cameras, event-triggered video |
| **Temperature Sensor** | Cold chain monitoring sensor |
| **Humidity Sensor** | Cargo humidity monitoring |
| **Door Sensor** | Cargo door open/close detection |
| **Motion Sensor** | Impact or vibration detection |
| **Pressure Sensor** | Tire pressure monitoring (TPMS) |
| **Fuel Level Sensor** | Tank-mounted fuel gauge |

## [Device Attributes](https://fleet-app.qgi.dev/docs/)

| Field | Description |
| --- | --- |
| **Name** | Device identifier |
| **Type** | Specific hardware type |
| **Category** | Device category (GPS, OBD-II, camera, etc.) |
| **Vendor** | Manufacturer or supplier |
| **Description** | Notes about the device |
| **Status** | active, inactive, offline, faulty |
| **Meta** | Additional key-value data (IMEI, firmware, etc.) |

## [Adding a Device](https://fleet-app.qgi.dev/docs/)

Navigate to **Fleet-Ops → Connectivity → Devices**.

Click **\+ New Device**.

Enter the **Name**, **Type**, and **Category**.

Set the **Vendor** and any identifying information in **Meta** (e.g., IMEI, serial number, firmware version).

Click **Save**.

## [Linking Devices to Vehicles](https://fleet-app.qgi.dev/docs/)

Devices are linked to vehicles through the vehicle record:

1. Open **Fleet-Ops → Resources → Vehicles → \[Vehicle\]**
2. Click the **Devices** tab
3. Click **\+ Add Device** and select from the devices list
4. Set the **Mounting Location** (e.g., dashboard, cargo area, wheel well)
5. Save

A vehicle can have multiple devices — e.g., one GPS tracker, one OBD-II scanner, and one temperature sensor.

## [Device Events](https://fleet-app.qgi.dev/docs/)

Every time a device sends data, a **DeviceEvent** is recorded. These events are visible in the device detail panel:

- GPS coordinates and timestamp
- OBD-II readings (RPM, fuel level, coolant temperature, fault codes)
- Sensor readings (temperature values, door status, motion events)
- Alert events (speeding, harsh braking, engine fault)

View device events at **Fleet-Ops → Connectivity → Devices → \[Device\] → Events**.

## [Device Status Monitoring](https://fleet-app.qgi.dev/docs/)

Device status is updated based on data received:

- `active` — data received within the expected interval
- `offline` — no data received beyond the expected interval
- `faulty` — reporting error codes or data quality issues

Offline devices can be flagged to create an Issue automatically, alerting the fleet manager to investigate.

[Telematics\\
\\
Connect telematics providers to Fleet-Ops — enter API credentials, discover devices, link them to vehicles, and start receiving live telemetry data.](https://fleet-app.qgi.dev/docs/) [Sensors\\
\\
Manage environmental and condition sensors — temperature, humidity, door, pressure, and motion monitoring for cargo and vehicle condition tracking.](https://fleet-app.qgi.dev/docs/)

### On this page

[Devices](https://fleet-app.qgi.dev/docs/) [Device Categories](https://fleet-app.qgi.dev/docs/) [Device Attributes](https://fleet-app.qgi.dev/docs/) [Adding a Device](https://fleet-app.qgi.dev/docs/) [Linking Devices to Vehicles](https://fleet-app.qgi.dev/docs/) [Device Events](https://fleet-app.qgi.dev/docs/) [Device Status Monitoring](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Devices \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)


<!-- SECTION: Service areas & geofences -->
# Service areas & geofences

Service Areas & Geofences

# Geofences

How service areas and zones become live geofences — entry, exit, dwell, and speed-limit events, the detection engine, and the reports that surface them.

# [Geofences](https://fleet-app.qgi.dev/docs/)

A **geofence** in Fleet-Ops is any [Service Area](https://fleet-app.qgi.dev/docs/) or [Zone](https://fleet-app.qgi.dev/docs/) that has at least one trigger enabled. Geofences detect when a driver or vehicle crosses a boundary and emit events that downstream features react to: auto-arrival, dwell-time SLAs, restricted-area alerts, and speed-limit reporting.

Geofences are **not a separate object** in Fleet-Ops — they're the operational behavior of service areas and zones. Toggle the trigger fields and you have a geofence; clear them and the same polygon still defines pricing scope but doesn't emit events.

## [Triggers](https://fleet-app.qgi.dev/docs/)

Every service area and zone exposes the same four trigger fields:

| Field | Type | What it does |
| --- | --- | --- |
| `trigger_on_entry` | bool | Emit an `entered` event the first tick a subject is inside the boundary after being outside. |
| `trigger_on_exit` | bool | Emit an `exited` event the first tick a subject is outside the boundary after being inside. |
| `dwell_threshold_minutes` | integer | Schedule a `dwelled` event N minutes after entry, only if the subject has remained inside continuously. |
| `speed_limit_kmh` | integer | Recorded for the boundary. Combined with vehicle telematics to flag speed-limit violations inside it. |

Entry / exit / dwell are evaluated on every driver and vehicle location update. Speed-limit violation evaluation is driven by the telematics integration when speed data is reported.

## [How Detection Works](https://fleet-app.qgi.dev/docs/)

Geofence detection lives in `GeofenceIntersectionService` ( [server source](https://github.com/fleetbase/fleetops/blob/main/server/src/Support/GeofenceIntersectionService.php)). On every location update for a driver or vehicle:

**Bounding-box prefilter.** A `MBRContains` query uses the MySQL spatial index to quickly narrow the candidate set of zones and service areas whose minimum bounding rectangle contains the new point. This is fast — milliseconds — even with thousands of geofences per company.

**Precise containment check.** For each candidate, a `ST_Contains` query runs over the actual polygon to confirm or reject the point. This is the authoritative containment test.

**State comparison.** The result is compared against the subject's last-known state in `driver_geofence_states` / `vehicle_geofence_states` (per-geofence rows tracking `is_inside`, `entered_at`, `dwell_job_id`).

**Emit transitions.** Each detected transition writes a row to `geofence_events_log` with `event_type` (`entered`, `exited`, or `dwelled`), the subject UUID, the geofence UUID, and `geofence_type` (`zone` or `service_area`).

**Dispatch events.** Laravel events fire (e.g. `DriverArrivedAtGeofence`), so webhooks, notifications, and automations can react.

Because the prefilter uses the spatial index and the precise check only runs on the narrowed set, this scales well — adding more geofences increases candidate evaluation linearly, but spatial-index lookups stay sublinear.

## [Dwell Detection](https://fleet-app.qgi.dev/docs/)

Dwell is implemented as a deferred job, not as a per-tick check:

1. On `entered`, a queued job is scheduled to run after `dwell_threshold_minutes`.
2. The job's UUID is stored on the state row as `dwell_job_id`.
3. When the job fires, it confirms the subject is still inside before emitting the `dwelled` event.
4. If the subject exits before the threshold elapses, the `exited` handler cancels the pending dwell job.

This means a `dwell_threshold_minutes` change applies to _new_ entries after the change — already-in-flight dwell jobs use whatever threshold was active when the entry happened. To re-evaluate existing entries with the new threshold, exit and re-enter the geofence.

## [Events Emitted](https://fleet-app.qgi.dev/docs/)

| Event | Fires when |
| --- | --- |
| `DriverArrivedAtGeofence` | A driver enters a geofence with `trigger_on_entry = true`. |
| `DriverLeftGeofence` | A driver exits a geofence with `trigger_on_exit = true`. |
| `DriverDwelledAtGeofence` | A driver has remained inside for `dwell_threshold_minutes`. |
| `VehicleEntered/Left/DwelledAtGeofence` | Same set, but for vehicles when vehicle telematics are wired up. |

Subscribe via webhooks ( **Console → Developers → Webhooks**) or via Laravel event listeners in a server-side extension. The webhook payload includes the geofence UUID, public ID, name, type discriminator (`zone` or `service_area`), and the subject's location at the time of the event.

## [Common Use Cases](https://fleet-app.qgi.dev/docs/)

| Use case | Configuration |
| --- | --- |
| **Auto-arrival at pickup / dropoff** | Set a small zone (a few hundred meters) around each known facility with `trigger_on_entry = true`. Wire `DriverArrivedAtGeofence` to advance the order's activity flow automatically. |
| **Restricted-area alerts** | Mark sensitive areas (no-fly zones, customer-prohibited yards) as geofences with `trigger_on_entry = true` and route the event to an alert channel. |
| **Dwell-time SLA** | For pickup points where drivers should be in and out fast, set `dwell_threshold_minutes` to the SLA. The `dwelled` event becomes a dispatcher escalation signal. |
| **Speed-limit reporting** | Set `speed_limit_kmh` on areas with known limits (school zones, depots). The Violations report aggregates breaches over time. |
| **Service-area billing reconciliation** | Use entry/exit events to confirm a delivery actually crossed into the billed service area, in case of after-the-fact disputes. |

## [Reporting](https://fleet-app.qgi.dev/docs/)

| Endpoint | Purpose |
| --- | --- |
| `GET /geofence-events` | Raw event history. Filter by subject, geofence, type, date range. |
| `GET /dwell-report` | Aggregated dwell durations per geofence per subject, for SLA analytics. |
| `GET /geofence-violations` | Aggregated speed-limit and restricted-area violations, surfaced by the **Violations** dashboard widget. |

All three power the geofence widgets on the FleetOps **Analytics → Reports** dashboards.

## [Configuration Workflow](https://fleet-app.qgi.dev/docs/)

In the console, geofence triggers are part of the service-area / zone edit modal — there is no separate "Geofences" page in the UI. To turn an existing polygon into a geofence, go to **Fleet-Ops → Dashboard → Map** tab and:

1. Right-click the service-area or zone polygon on the map (or open the map toolbar's service-areas panel and pick the area from the list).
2. Choose **Edit** from the contextual menu.
3. In the **Geofence Triggers** section of the modal, toggle entry / exit and set the dwell or speed-limit value.
4. Save. Subsequent location updates immediately respect the new configuration — there's no separate deploy step.

To remove a geofence, clear the trigger fields. The polygon stays in place for pricing and visualization, but no new events fire.

## [Permissions](https://fleet-app.qgi.dev/docs/)

Geofence configuration follows standard Fleet-Ops role-based access:

- **Manage service areas / zones** to edit trigger fields (typically operations admin role).
- **View geofence events** to access the events and violations reports.

See [Identity & Access](https://fleet-app.qgi.dev/docs/) for how to assign these permissions.

## [See Also](https://fleet-app.qgi.dev/docs/)

- [Service Areas](https://fleet-app.qgi.dev/docs/) — the larger polygons most geofences are built on.
- [Zones](https://fleet-app.qgi.dev/docs/) — finer-grained polygons for tight geofences.
- [Service Rates](https://fleet-app.qgi.dev/docs/) — pricing scoped to the same polygons.

[Zones\\
\\
Nest precise operational boundaries inside a service area for finer dispatch, pricing, and geofencing.](https://fleet-app.qgi.dev/docs/) [Resources Overview\\
\\
Manage the core resources that power your fleet operations — drivers, vehicles, fleets, vendors, contacts, and places.](https://fleet-app.qgi.dev/docs/)

### On this page

[Geofences](https://fleet-app.qgi.dev/docs/) [Triggers](https://fleet-app.qgi.dev/docs/) [How Detection Works](https://fleet-app.qgi.dev/docs/) [Dwell Detection](https://fleet-app.qgi.dev/docs/) [Events Emitted](https://fleet-app.qgi.dev/docs/) [Common Use Cases](https://fleet-app.qgi.dev/docs/) [Reporting](https://fleet-app.qgi.dev/docs/) [Configuration Workflow](https://fleet-app.qgi.dev/docs/) [Permissions](https://fleet-app.qgi.dev/docs/) [See Also](https://fleet-app.qgi.dev/docs/)

 [Chat with us](https://wa.me/6588345437?text=Hi%20Fleetbase%20team!%20I%27d%20like%20to%20learn%20more%20about%20your%20logistics%20platform.) Geofences \| CBRE Fleet

[Start Free Trial](https://console.fleetbase.io/onboard)
