#!/usr/bin/env python3
"""Harden AI capability activation and error isolation.

1. Broaden matchesPrompt trigger lists — natural phrasings ("fleet summary",
   "deliveries this month", "Van 103") activate the right capabilities.
2. Isolate every sub-query in SearchResourcesCapability with rescue() so a
   single bad column/relation can never blank the whole result set again.
"""
import re

BASE = '/opt/fleetbase/api/vendor/fleetbase/fleetops-api/server/src/Support/Ai/Capabilities/'

def patch(path, replacements):
    src = open(path, encoding='utf-8').read()
    changed = False
    for old, new in replacements:
        if new in src:
            continue
        if old not in src:
            print('MISS in', path.split('/')[-1], ':', old[:60])
            continue
        src = src.replace(old, new, 1)
        changed = True
    open(path, 'w', encoding='utf-8').write(src)
    return changed

# 1a. SearchResources: broader triggers
print('search triggers:', patch(BASE + 'SearchResourcesCapability.php', [(
    "return $this->containsAny($prompt, ['find', 'show', 'open', 'look up', 'tell me about', 'status of', 'order', 'vehicle', 'driver', 'work order', 'maintenance', 'device', 'sensor', 'telematic']);",
    "return $this->containsAny($prompt, ['find', 'show', 'open', 'look up', 'tell me about', 'status of', 'order', 'vehicle', 'driver', 'work order', 'maintenance', 'device', 'sensor', 'telematic', 'van', 'truck', 'trailer', 'unit', 'priority', 'critical', 'urgent', 'high', 'asset', 'which', 'what', 'everything about', 'fleet']);",
)]))

# 1b. AssetStatus: broader triggers
print('asset triggers:', patch(BASE + 'AssetStatusCapability.php', [(
    "return $this->containsAny($prompt, ['offline', 'online', 'asset status', 'driver status', 'vehicle status', 'device status', 'sensor status', 'telematic status', 'drivers down', 'devices down', 'vehicles down']);",
    "return $this->containsAny($prompt, ['offline', 'online', 'asset status', 'driver status', 'vehicle status', 'device status', 'sensor status', 'telematic status', 'drivers down', 'devices down', 'vehicles down', 'fleet', 'summary', 'overview', 'how many', 'vehicles', 'drivers', 'devices', 'status']);",
)]))

# 1c. OrderInsights: deliveries/revenue phrasing counts as orders
print('insights triggers:', patch(BASE + 'OrderInsightsCapability.php', [(
    "return str_contains($prompt, 'order') && $this->containsAny($prompt,",
    "return $this->containsAny($prompt, ['order', 'deliver', 'revenue', 'earning', 'shipment', 'how did we do']) && $this->containsAny($prompt,",
)]))

# 2. SearchResources: rescue() around every sub-query
path = BASE + 'SearchResourcesCapability.php'
src = open(path, encoding='utf-8').read()
pattern = re.compile(r"'(\w+)'(\s+)=> \$this->(\w+)\(\$terms\),")
if 'rescue(fn () => $this->orders' not in src:
    src = pattern.sub(lambda m: "'{}'{}=> rescue(fn () => $this->{}($terms), [], false),".format(m.group(1), m.group(2), m.group(3)), src)
open(path, 'w', encoding='utf-8').write(src)
print('rescue wrapped:', src.count('rescue(fn () =>'))
