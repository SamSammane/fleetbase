#!/usr/bin/env bash
# Build the IFS CommandIQ console (CBRE Fleet rebrand) on the Vast instance.
set -x

rm -rf /opt/console
mkdir -p /opt/console
tar -xzf /opt/deploy/console-src.tgz -C /opt
mv /opt/console 2>/dev/null || true
[ -d /opt/console ] || mv /opt/console* /opt/console 2>/dev/null || true
# tarball extracts to /opt/console (dir name 'console')
ls /opt/console | head -5

cd /opt/console

# Production env baked into the build
cat > environments/.env.production <<'ENV'
API_HOST=https://fleet-api.qgi.dev
API_NAMESPACE=int/v1
API_SECURE=true
SOCKETCLUSTER_PATH=/socketcluster/
SOCKETCLUSTER_HOST=fleet-app.qgi.dev
SOCKETCLUSTER_SECURE=true
SOCKETCLUSTER_PORT=443
OSRM_HOST=https://router.project-osrm.org
DISABLE_FLEETBASE_ATTRIBUTION=true
ENV

export PNPM_HOME=/root/.pnpm
export PATH=$PNPM_HOME:$PATH
pnpm config set network-timeout 300000 || true
npm pkg set "dependencies.@ifs/commandiq-engine=file:../packages/commandiq"
pnpm install --no-frozen-lockfile 2>&1 | tail -3

# CBRE Fleet text rebrand inside engine packages (visible strings only).
# Protect component names (e.g. FleetbaseAttribution) before word replace.
find -L node_modules/@fleetbase -type f \( -name "*.hbs" -o -path "*translations*.yaml" \) 2>/dev/null | while read -r f; do
  sed -i 's/FleetbaseAttribution/__FBATTR__/g; s/\bFleetbase\b/CBRE Fleet/g; s/__FBATTR__/FleetbaseAttribution/g' "$f"
done
grep -rLl --include=*.hbs x node_modules/@fleetbase 2>/dev/null >/dev/null; grep -rl "CBRE Fleet" -R node_modules/@fleetbase/ --include=*.hbs | wc -l

# Dock the AI panel: remove click-to-close on the overlay container
sed -i 's|\(class="fleetbase-ai-overlay"[^>]*\){{on "click" this.close}}|\1|' node_modules/@fleetbase/ai-engine/addon/components/ai-prompt.hbs

# Register the commandiq lazy bundle in the asset manifest post-build
ember build --environment production 2>&1 | tail -5

ls -la dist/ | head -5
grep -o "<title>[^<]*</title>" dist/index.html
python3 /opt/deploy/patch-manifest.py
service nginx reload
curl -s http://localhost:4200 | grep -o "<title>[^<]*</title>"
echo CONSOLE_BUILD_DONE
