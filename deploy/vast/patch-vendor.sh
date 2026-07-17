#!/usr/bin/env bash
# Post-composer vendor patches for the cloud demo API.
# Re-run after any `composer install/update` on the instance.
set -x
cd /opt/fleetbase/api/vendor/fleetbase

# Fix: AI resource search queries a `sensor_type` column that doesn't exist
# in the sensors table (SQL error surfaces as a tool failure in the AI panel).
sed -i "s/'serial_number', 'imei', 'type', 'sensor_type', 'status'/'serial_number', 'imei', 'type', 'status'/" \
  fleetops-api/server/src/Support/Ai/Capabilities/SearchResourcesCapability.php

# Brand: AI system prompts (providers) — the assistant identifies as CBRE Fleet AI.
sed -i 's/You are Fleetbase AI, an operations copilot inside Fleetbase/You are CBRE Fleet AI, an operations copilot inside IFS CommandIQ/; s/use Fleetbase capability context/use platform capability context/; s/use Fleetbase temporal context/use platform temporal context/; s/unless Fleetbase provided/unless the platform provided/' \
  ai/server/src/Services/AnthropicProvider.php ai/server/src/Services/OpenAIProvider.php

# Brand: capability context strings + docs references (namespaces contain
# Fleetbase\ so only replace exact phrases, never the bare word).
sed -i "s|Fleetbase did not return|The platform did not return|g; s|Fleetbase documentation references|IFS CommandIQ documentation references|g; s|Fleetbase query summaries|platform query summaries|g" \
  fleetops-api/server/src/Support/Ai/Capabilities/*.php ai/server/src/Support/*.php 2>/dev/null

sed -i -E "s|https://(www\.)?fleetbase\.io/docs[a-zA-Z0-9/._-]*|https://fleet-app.qgi.dev/docs/|g" \
  fleetops-api/server/src/Support/Ai/Capabilities/*.php

service php8.2-fpm restart
echo VENDOR_PATCH_DONE

# AI search result quality (rich fields, drill-downs, relevance)
python3 /opt/deploy/patch-ai-search.py
service php8.2-fpm restart

# Capability activation + isolation hardening
python3 /opt/deploy/patch-ai-harden.py
service php8.2-fpm restart

# Provider timeout for agentic answers (agent loop can exceed 30s cold)
sed -i "s/Http::timeout(30)/Http::timeout(150)/g; s/Http::timeout(60)/Http::timeout(150)/g" /opt/fleetbase/api/vendor/fleetbase/ai/server/src/Services/OpenAIProvider.php
service php8.2-fpm restart
