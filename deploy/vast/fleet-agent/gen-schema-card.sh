#!/usr/bin/env bash
set -e
TABLES="vehicles drivers work_orders maintenances maintenance_schedules orders payloads places parts devices sensors telematics warranties contacts transactions fuel_reports issues equipment commandiq_availability_windows commandiq_return_patterns commandiq_campaigns commandiq_campaign_assignments commandiq_warranty_claims commandiq_rma_cases commandiq_qc_reviews commandiq_intake_requests invoices invoice_items"
OUT=/opt/fleet-agent/schema-card.md
{
  echo "# Fleet database schema (read-only)"
  echo
  echo "Conventions: primary keys are uuid strings; company-owned rows have company_uuid = '967490f8-9bbb-4b26-9167-566d1f4dda28'. Money columns are integer cents. Timestamps are UTC. Soft deletes: filter deleted_at IS NULL. vehicles.meta is JSON with keys segment ('lm'|'mm'), dsp_code, home_station. Order revenue: orders.transaction_uuid -> transactions.uuid, transactions.amount is cents, status 'success'. Work orders link vehicles via target_uuid. Devices attach to vehicles via attachable_uuid."
  echo
  for t in $TABLES; do
    cols=$(mysql -N fleetbase -e "DESCRIBE $t" 2>/dev/null | awk '{printf "%s(%s), ", $1, $2}')
    if [ -n "$cols" ]; then
      echo "## $t"
      echo "$cols"
      echo
    fi
  done
} > "$OUT"
chown fleetai:fleetai "$OUT"
wc -l "$OUT"
supervisorctl restart fleet-agent >/dev/null
sleep 4
curl -s http://127.0.0.1:8055/health
echo
