#!/usr/bin/env python3
"""Register @ifs/commandiq-engine's lazy bundle in the console asset manifest.

The console build's manifest generator only includes @fleetbase-scoped engine
bundles; this injects our engine both into the host manifest meta and as a
dedicated per-engine meta so ember-asset-loader resolves it either way.
"""
from urllib.parse import quote, unquote
import json
import os
import re
import sys

DIST = '/opt/console/dist'
ENGINE = '@ifs/commandiq-engine'
ASSET_DIR = os.path.join(DIST, 'engines-dist', ENGINE, 'assets')
index_path = os.path.join(DIST, 'index.html')

if not os.path.isdir(ASSET_DIR):
    print('SKIP: no lazy bundle at', ASSET_DIR)
    sys.exit(0)

assets = []
for f in sorted(os.listdir(ASSET_DIR)):
    uri = '/engines-dist/{}/assets/{}'.format(ENGINE, f)
    if f.endswith('.js'):
        assets.append({'uri': uri, 'type': 'js'})
    elif f.endswith('.css'):
        assets.append({'uri': uri, 'type': 'css'})

src = open(index_path, encoding='utf-8').read()

# 1. Merge into the host manifest meta
m = re.search(r'(<meta name="@fleetbase/console/config/asset-manifest" content=")([^"]*)(")', src)
if m:
    manifest = json.loads(unquote(m.group(2))) if m.group(2) else {'bundles': {}}
    manifest.setdefault('bundles', {})[ENGINE] = {'assets': assets}
    encoded = quote(json.dumps(manifest), safe='@/')
    src = src[:m.start()] + m.group(1) + encoded + m.group(3) + src[m.end():]
    print('host manifest merged')
else:
    print('WARN: host manifest meta not found')

# 2. Dedicated per-engine meta (fallback path)
dedicated = {'bundles': {ENGINE: {'assets': assets}}}
meta = '<meta name="{}/config/asset-manifest" content="{}">'.format(
    ENGINE, quote(json.dumps(dedicated), safe='@/')
)
src = re.sub(r'<meta name="@ifs/commandiq-engine/config/asset-manifest"[^>]*>\n?', '', src)
src = src.replace('</head>', meta + '\n</head>')

open(index_path, 'w', encoding='utf-8').write(src)
print('patched with', len(assets), 'assets')
