#!/usr/bin/env bash
# Creates the Cloudflare Access application + allow-policy for fleet-demo.qgi.dev.
#
# Requires a Cloudflare API token with:  Account → Access: Apps and Policies → Edit
# (create at https://dash.cloudflare.com/profile/api-tokens)
#
# Usage:
#   CLOUDFLARE_API_TOKEN=xxxx bash demo-tunnel/setup-access.sh
set -euo pipefail

ACCOUNT_ID="b8038ddd571876b12db7483696c399e3"
HOSTNAME="fleet-demo.qgi.dev"
API="https://api.cloudflare.com/client/v4/accounts/${ACCOUNT_ID}/access/apps"

: "${CLOUDFLARE_API_TOKEN:?Set CLOUDFLARE_API_TOKEN (Account > Access: Apps and Policies > Edit)}"

ALLOWED_EMAILS=(
  "Patrick.Houlihan@cbre.com"
  "Denzil.Dsouza@cbre.com"
  "Ben.Cook1@cbre.com"
  "Jen.Holibaugh@cbre.com"
  "sam@qgi.dev"
  "waseem@qgi.dev"
)

emails_json=$(printf '{"email":{"email":"%s"}},' "${ALLOWED_EMAILS[@]}")
emails_json="[${emails_json%,}]"

echo "Creating Access application for ${HOSTNAME}..."
app=$(curl -s -X POST "$API" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
  -H "Content-Type: application/json" \
  --data "{
    \"name\": \"IFS CommandIQ Demo\",
    \"domain\": \"${HOSTNAME}\",
    \"type\": \"self_hosted\",
    \"session_duration\": \"24h\",
    \"auto_redirect_to_identity\": false
  }")
app_id=$(echo "$app" | grep -o '"id":"[a-f0-9-]*"' | head -1 | cut -d'"' -f4)
if [ -z "$app_id" ]; then
  echo "App creation failed:"; echo "$app"; exit 1
fi
echo "App created: $app_id"

echo "Creating allow policy (${#ALLOWED_EMAILS[@]} emails, one-time PIN login)..."
policy=$(curl -s -X POST "${API}/${app_id}/policies" \
  -H "Authorization: Bearer $CLOUDFLARE_API_TOKEN" \
  -H "Content-Type: application/json" \
  --data "{
    \"name\": \"IFS demo invitees\",
    \"decision\": \"allow\",
    \"precedence\": 1,
    \"include\": ${emails_json}
  }")
echo "$policy" | grep -q '"success":true' && echo "Policy created. ${HOSTNAME} is now login-gated." || { echo "Policy creation failed:"; echo "$policy"; exit 1; }
