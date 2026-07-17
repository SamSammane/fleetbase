#!/usr/bin/env python3
"""Scrape the upstream platform how-to guides, rebrand to CBRE Fleet, and
assemble a topic-sectioned platform-docs.md for the agent's docs tool."""
import json
import os
import re
import time
import urllib.request

FKEY = os.environ['FIRECRAWL_API_KEY']
OUT = r'f:\cursor\fleetbase\deploy\vast\fleet-agent\platform-docs.md'

PAGES = [
    ('Core concepts', 'https://fleetbase.io/docs/fleet-ops/getting-started/core-concepts'),
    ('Orders: managing', 'https://fleetbase.io/docs/fleet-ops/operations/orders/managing-orders'),
    ('Orders: lifecycle', 'https://fleetbase.io/docs/fleet-ops/operations/orders/order-lifecycle'),
    ('Orders: scheduling', 'https://fleetbase.io/docs/fleet-ops/operations/orders/scheduling-orders'),
    ('Orders: importing', 'https://fleetbase.io/docs/fleet-ops/operations/orders/importing-orders'),
    ('Orders: kanban board', 'https://fleetbase.io/docs/fleet-ops/operations/orders/kanban-board'),
    ('Orders: tracking', 'https://fleetbase.io/docs/fleet-ops/operations/orders/tracking'),
    ('Orders: proof of delivery', 'https://fleetbase.io/docs/fleet-ops/operations/orders/proof-of-delivery'),
    ('Order configurations', 'https://fleetbase.io/docs/fleet-ops/operations/order-configurations/overview'),
    ('Scheduler: order scheduling', 'https://fleetbase.io/docs/fleet-ops/operations/scheduler/order-scheduling'),
    ('Scheduler: driver shifts', 'https://fleetbase.io/docs/fleet-ops/operations/scheduler/driver-shift-schedules'),
    ('Drivers', 'https://fleetbase.io/docs/fleet-ops/resources/drivers/overview'),
    ('Vehicles', 'https://fleetbase.io/docs/fleet-ops/resources/vehicles/overview'),
    ('Places', 'https://fleetbase.io/docs/fleet-ops/resources/places/overview'),
    ('Fleets', 'https://fleetbase.io/docs/fleet-ops/resources/fleets/overview'),
    ('Contacts & customers', 'https://fleetbase.io/docs/fleet-ops/resources/contacts/overview'),
    ('Vendors', 'https://fleetbase.io/docs/fleet-ops/resources/vendors/overview'),
    ('Issues', 'https://fleetbase.io/docs/fleet-ops/resources/issues/overview'),
    ('Fuel reports', 'https://fleetbase.io/docs/fleet-ops/resources/fuel-reports/overview'),
    ('Work orders', 'https://fleetbase.io/docs/fleet-ops/maintenance/work-orders/overview'),
    ('Maintenance schedules', 'https://fleetbase.io/docs/fleet-ops/maintenance/schedules/overview'),
    ('Parts inventory', 'https://fleetbase.io/docs/fleet-ops/maintenance/parts/overview'),
    ('Devices & telematics', 'https://fleetbase.io/docs/fleet-ops/connectivity/devices/overview'),
    ('Service areas & geofences', 'https://fleetbase.io/docs/fleet-ops/operations/service-areas-geofences/geofences'),
]


def scrape(url):
    req = urllib.request.Request(
        'https://api.firecrawl.dev/v1/scrape',
        data=json.dumps({'url': url, 'formats': ['markdown'], 'onlyMainContent': True}).encode(),
        headers={'Authorization': f'Bearer {FKEY}', 'Content-Type': 'application/json'},
    )
    with urllib.request.urlopen(req, timeout=90) as r:
        d = json.loads(r.read())
    return (d.get('data') or {}).get('markdown') or ''


def rebrand(md):
    md = md.replace('FleetbaseAttribution', '__FB__')
    md = re.sub(r'\bFleetbase\b', 'CBRE Fleet', md)
    md = md.replace('__FB__', 'FleetbaseAttribution')
    md = re.sub(r'https?://(www\.)?fleetbase\.io/docs[^)\s\]]*', 'https://fleet-app.qgi.dev/docs/', md)
    # strip nav/footer noise and image links
    md = re.sub(r'!\[[^\]]*\]\([^)]*\)', '', md)
    md = re.sub(r'\n{3,}', '\n\n', md)
    return md.strip()


sections = []
for title, url in PAGES:
    try:
        md = scrape(url)
        if len(md) < 200:
            print('THIN:', title, len(md))
            continue
        md = rebrand(md)[:9000]
        sections.append(f'<!-- SECTION: {title} -->\n# {title}\n\n{md}\n')
        print('OK:', title, len(md))
        time.sleep(1.2)
    except Exception as e:
        print('ERR:', title, str(e)[:80])

doc = '\n\n'.join(sections)
open(OUT, 'w', encoding='utf-8', newline='\n').write(doc)
print('TOTAL:', len(doc), 'chars,', len(sections), 'sections')
