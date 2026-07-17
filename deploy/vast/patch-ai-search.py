#!/usr/bin/env python3
"""Upgrade the AI SearchResourcesCapability result quality.

- work orders: include priority, due date, and the target vehicle name;
  phrase-first matching; higher limit
- vehicles: rich record (plate, VIN, segment, DSP, station) and, for narrow
  matches, installed devices + linked work orders (true drill-down)
- generic results: include priority when present
- providers: forbid placeholder tokens like @@AICODE0@@ in output
"""
import re
import sys

CAP = '/opt/fleetbase/api/vendor/fleetbase/fleetops-api/server/src/Support/Ai/Capabilities/SearchResourcesCapability.php'
PROVIDERS = [
    '/opt/fleetbase/api/vendor/fleetbase/ai/server/src/Services/AnthropicProvider.php',
    '/opt/fleetbase/api/vendor/fleetbase/ai/server/src/Services/OpenAIProvider.php',
]

src = open(CAP, encoding='utf-8').read()

WORK_ORDERS = '''protected function workOrders(array $terms): array
    {
        if (!$this->can('fleet-ops see work-order')) {
            return [];
        }

        $phrase = trim(implode(' ', $terms));
        $query  = WorkOrder::with('target')->where('company_uuid', session('company'));
        $query->where(function ($q) use ($terms, $phrase) {
            if ($phrase !== '') {
                $q->orWhere('subject', 'like', '%' . $phrase . '%');
            }
            $this->whereLikeAny($q, ['public_id', 'uuid', 'code', 'subject', 'status', 'priority'], $terms);
        });

        return $query->limit(8)->get()->map(function ($wo) {
            return [
                'id'       => $wo->public_id,
                'code'     => $wo->code,
                'subject'  => $wo->subject,
                'status'   => $wo->status,
                'priority' => $wo->priority,
                'due_at'   => $wo->due_at ? $wo->due_at->toDateString() : null,
                'vehicle'  => $wo->target ? $wo->target->name : null,
                'route'    => 'console.fleet-ops.maintenance.work-orders.index.details',
                'models'   => [$wo->public_id ?: $wo->uuid],
            ];
        })->values()->all();
    }'''

VEHICLES = '''protected function vehicles(array $terms): array
    {
        if (!$this->can('fleet-ops see vehicle')) {
            return [];
        }

        $phrase = trim(implode(' ', $terms));
        $query  = Vehicle::where('company_uuid', session('company'));
        $query->where(function ($q) use ($terms, $phrase) {
            if ($phrase !== '') {
                $q->orWhere('name', 'like', '%' . $phrase . '%')
                  ->orWhere('vin', 'like', '%' . $phrase . '%')
                  ->orWhere('plate_number', 'like', '%' . $phrase . '%');
            }
            $this->whereLikeAny($q, ['name', 'make', 'model', 'plate_number', 'vin', 'public_id', 'internal_id'], $terms);
        });

        $matches = $query->limit(5)->get();

        // A term containing a digit (unit number, VIN fragment, plate) strongly
        // identifies specific units - prefer those matches for drill-downs.
        $specific = collect($terms)->filter(fn ($t) => preg_match('/\d/', $t))->values();
        if ($specific->isNotEmpty()) {
            $sq = Vehicle::where('company_uuid', session('company'));
            $sq->where(function ($w) use ($specific) {
                foreach ($specific as $t) {
                    foreach (['name', 'vin', 'plate_number', 'public_id'] as $col) {
                        $w->orWhere($col, 'like', '%' . $t . '%');
                    }
                }
            });
            $strong = $sq->limit(5)->get();
            if ($strong->isNotEmpty() && $strong->count() <= 3) {
                $matches = $strong;
            }
        }

        $narrow  = $matches->count() <= 2;

        return $matches->map(function (Vehicle $vehicle) use ($narrow) {
            $meta = is_array($vehicle->meta) ? $vehicle->meta : [];
            $row  = [
                'id'           => $vehicle->public_id,
                'name'         => $vehicle->name,
                'status'       => $vehicle->status,
                'online'       => (bool) $vehicle->online,
                'plate_number' => $vehicle->plate_number,
                'vin'          => $vehicle->vin,
                'make'         => $vehicle->make,
                'model'        => $vehicle->model,
                'year'         => $vehicle->year,
                'odometer'     => $vehicle->odometer,
                'segment'      => $meta['segment'] ?? null,
                'dsp_code'     => $meta['dsp_code'] ?? null,
                'home_station' => $meta['home_station'] ?? null,
                'route'        => 'console.fleet-ops.management.vehicles.index.details',
                'models'       => [$vehicle->public_id ?: $vehicle->uuid],
            ];

            if ($narrow) {
                $row['installed_devices'] = \\Fleetbase\\FleetOps\\Models\\Device::where('attachable_uuid', $vehicle->uuid)
                    ->get()
                    ->map(fn ($d) => ['name' => $d->name, 'type' => $d->type, 'serial_number' => $d->serial_number, 'status' => $d->status])
                    ->values()->all();
                $row['work_orders'] = \\Fleetbase\\FleetOps\\Models\\WorkOrder::where('company_uuid', session('company'))
                    ->where('target_uuid', $vehicle->uuid)
                    ->orderByDesc('created_at')->limit(6)
                    ->get()
                    ->map(fn ($w) => ['code' => $w->code, 'subject' => $w->subject, 'status' => $w->status, 'priority' => $w->priority])
                    ->values()->all();
            }

            return $row;
        })->values()->all();
    }'''

def replace_method(source, name, new_body):
    pattern = re.compile(r'protected function ' + name + r'\(array \$terms\): array.*?\n    \}', re.S)
    if not pattern.search(source):
        print('MISS:', name)
        return source
    return pattern.sub(lambda m: new_body, source, count=1)

if "'vehicle'  => $wo->target" not in src:
    src = replace_method(src, 'workOrders', WORK_ORDERS)
src = replace_method(src, 'vehicles', VEHICLES)
src = src.replace("'status' => $record->status ?? null,", "'status' => $record->status ?? null,\n                'priority' => $record->priority ?? null,")
open(CAP, 'w', encoding='utf-8').write(src)
print('capability patched:', "'installed_devices'" in src and "$wo->target" in src)

RULE = ' Never output placeholder tokens such as @@AICODE0@@ or template markers; always write identifiers, codes, and values as plain readable text.'
for f in PROVIDERS:
    s = open(f, encoding='utf-8').read()
    if 'placeholder tokens' not in s:
        s = s.replace("confirming it.';", "confirming it." + RULE + "';")
    open(f, 'w', encoding='utf-8').write(s)
    print('provider rule:', 'placeholder tokens' in s, f.split('/')[-1])
