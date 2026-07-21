/**
 * Fleet metric catalog — the single source of truth for canned, deterministic
 * queries. The MCP server executes these; the bridge advertises them in the
 * system prompt as skills. One definition per business question, so the same
 * question always yields the same numbers.
 *
 * :company is bound server-side. Templates ending in _WINDOW accept {days}.
 */

export const METRICS = {
    fleet_status: {
        description: 'Vehicles, drivers, devices: totals, online counts, and vehicles by segment (lm/mm).',
        sql: `SELECT
  (SELECT COUNT(*) FROM vehicles WHERE company_uuid = :company AND deleted_at IS NULL) AS vehicles_total,
  (SELECT COUNT(*) FROM vehicles WHERE company_uuid = :company AND deleted_at IS NULL AND online = 1) AS vehicles_online,
  (SELECT COUNT(*) FROM vehicles WHERE company_uuid = :company AND deleted_at IS NULL AND JSON_UNQUOTE(JSON_EXTRACT(meta,'$.segment')) = 'lm') AS lm_vans,
  (SELECT COUNT(*) FROM vehicles WHERE company_uuid = :company AND deleted_at IS NULL AND JSON_UNQUOTE(JSON_EXTRACT(meta,'$.segment')) = 'mm') AS mm_trailers,
  (SELECT COUNT(*) FROM drivers WHERE company_uuid = :company AND deleted_at IS NULL) AS drivers_total,
  (SELECT COUNT(*) FROM drivers WHERE company_uuid = :company AND deleted_at IS NULL AND online = 1) AS drivers_online,
  (SELECT COUNT(*) FROM devices WHERE company_uuid = :company AND deleted_at IS NULL) AS devices_total`,
    },

    revenue_window: {
        description: 'Delivery revenue over the trailing {days} days (default 30): successful transactions linked to orders, plus order counts. THE canonical revenue definition.',
        params: ['days'],
        sql: `SELECT
  COUNT(DISTINCT o.uuid) AS orders_with_revenue,
  SUM(t.amount) / 100 AS revenue_usd,
  ROUND(AVG(t.amount) / 100, 2) AS avg_order_value_usd
FROM orders o
JOIN transactions t ON t.uuid = o.transaction_uuid AND t.status = 'success'
WHERE o.company_uuid = :company AND o.deleted_at IS NULL
  AND o.created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)`,
    },

    orders_summary: {
        description: 'Order counts by status (completed / active dispatch / scheduled), trailing 30 days plus current.',
        sql: `SELECT status, COUNT(*) AS count
FROM orders
WHERE company_uuid = :company AND deleted_at IS NULL
GROUP BY status ORDER BY count DESC`,
    },

    top_dsps: {
        description: 'DSP league table: completed orders and revenue per DSP (from assigned vehicle meta), trailing 30 days.',
        sql: `SELECT
  JSON_UNQUOTE(JSON_EXTRACT(v.meta,'$.dsp_code')) AS dsp,
  COUNT(*) AS completed_orders,
  COALESCE(SUM(t.amount) / 100, 0) AS revenue_usd
FROM orders o
JOIN vehicles v ON v.uuid = o.vehicle_assigned_uuid
LEFT JOIN transactions t ON t.uuid = o.transaction_uuid AND t.status = 'success'
WHERE o.company_uuid = :company AND o.deleted_at IS NULL AND o.status = 'completed'
  AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND JSON_UNQUOTE(JSON_EXTRACT(v.meta,'$.dsp_code')) IS NOT NULL
GROUP BY dsp ORDER BY completed_orders DESC`,
    },

    open_work_orders: {
        description: 'Open work orders (not approved/closed) with priority, status, subject, and vehicle name.',
        sql: `SELECT w.code, w.subject, w.status, w.priority, DATE(w.due_at) AS due, v.name AS vehicle
FROM work_orders w
LEFT JOIN vehicles v ON v.uuid = w.target_uuid
WHERE w.company_uuid = :company AND w.deleted_at IS NULL
  AND w.status NOT IN ('approved','closed')
ORDER BY FIELD(w.priority,'critical','high','normal','low'), w.due_at`,
    },

    work_orders_by_priority: {
        description: 'Open work-order counts grouped by priority.',
        sql: `SELECT priority, COUNT(*) AS count
FROM work_orders
WHERE company_uuid = :company AND deleted_at IS NULL AND status NOT IN ('approved','closed')
GROUP BY priority ORDER BY FIELD(priority,'critical','high','normal','low')`,
    },

    maintenance_spend_window: {
        description: 'Completed maintenance spend over trailing {days} days (default 30): labor, parts, total.',
        params: ['days'],
        sql: `SELECT
  COUNT(*) AS jobs,
  COALESCE(SUM(labor_cost),0) / 100 AS labor_usd,
  COALESCE(SUM(parts_cost),0) / 100 AS parts_usd,
  COALESCE(SUM(total_cost),0) / 100 AS total_usd
FROM maintenances
WHERE company_uuid = :company AND deleted_at IS NULL AND status = 'completed'
  AND completed_at >= DATE_SUB(NOW(), INTERVAL :days DAY)`,
    },

    vehicle_profile: {
        description: 'Full profile for one vehicle by name fragment ({vehicle}, e.g. "Van 103"): identity, meta, its devices, and its work orders.',
        params: ['vehicle'],
        multi: {
            identity: `SELECT name, vin, plate_number, make, model, year, odometer, status, online,
  JSON_UNQUOTE(JSON_EXTRACT(meta,'$.segment')) AS segment,
  JSON_UNQUOTE(JSON_EXTRACT(meta,'$.dsp_code')) AS dsp,
  JSON_UNQUOTE(JSON_EXTRACT(meta,'$.home_station')) AS home_station
FROM vehicles WHERE company_uuid = :company AND deleted_at IS NULL AND name LIKE :vehicle LIMIT 3`,
            devices: `SELECT d.name, d.type, d.serial_number, d.status
FROM devices d JOIN vehicles v ON v.uuid = d.attachable_uuid
WHERE v.company_uuid = :company AND v.name LIKE :vehicle AND d.deleted_at IS NULL`,
            work_orders: `SELECT w.code, w.subject, w.status, w.priority
FROM work_orders w JOIN vehicles v ON v.uuid = w.target_uuid
WHERE v.company_uuid = :company AND v.name LIKE :vehicle AND w.deleted_at IS NULL
ORDER BY w.created_at DESC LIMIT 8`,
        },
    },

    campaign_progress: {
        description: 'Retrofit campaign burn-down: population, completed, scheduled, pending per campaign.',
        sql: `SELECT c.code, c.name,
  COUNT(a.uuid) AS population,
  SUM(a.status = 'completed') AS completed,
  SUM(a.status = 'scheduled') AS scheduled,
  SUM(a.status = 'pending') AS pending
FROM commandiq_campaigns c
LEFT JOIN commandiq_campaign_assignments a ON a.campaign_uuid = c.uuid AND a.deleted_at IS NULL
WHERE c.company_uuid = :company AND c.deleted_at IS NULL
GROUP BY c.uuid, c.code, c.name`,
    },

    parts_inventory: {
        description: 'Parts inventory with quantities and reorder posture (reorder point from meta).',
        sql: `SELECT sku, name, quantity_on_hand, unit_cost / 100 AS unit_cost_usd, status,
  JSON_EXTRACT(meta,'$.reorder_point') AS reorder_point
FROM parts WHERE company_uuid = :company AND deleted_at IS NULL ORDER BY quantity_on_hand ASC`,
    },

    invoices_summary: {
        description: 'Ledger invoices: counts and totals by status (paid/pending), amounts in USD.',
        sql: `SELECT status, COUNT(*) AS count, SUM(total_amount) / 100 AS total_usd, SUM(balance) / 100 AS outstanding_usd
FROM ledger_invoices WHERE company_uuid = :company AND deleted_at IS NULL GROUP BY status`,
    },

    paid_invoices: {
        description: 'Invoices paid in the trailing {days} days (default 30): number, customer, amount USD, paid date.',
        params: ['days'],
        sql: `SELECT i.number, i.total_amount / 100 AS amount_usd, DATE(i.paid_at) AS paid_on, c.name AS customer
FROM ledger_invoices i LEFT JOIN contacts c ON c.uuid = i.customer_uuid
WHERE i.company_uuid = :company AND i.status = 'paid' AND i.deleted_at IS NULL
  AND i.paid_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
ORDER BY i.paid_at DESC LIMIT 50`,
    },

    availability_windows: {
        description: 'Forecast service availability windows in the next 7 days by station, with confidence.',
        sql: `SELECT w.location_code, DATE(w.starts_at) AS day, COUNT(*) AS windows, ROUND(AVG(w.confidence), 2) AS avg_confidence
FROM commandiq_availability_windows w
WHERE w.company_uuid = :company AND w.deleted_at IS NULL
  AND w.starts_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
GROUP BY w.location_code, DATE(w.starts_at) ORDER BY day, w.location_code`,
    },
};

export function catalogPromptBlock() {
    const lines = ['Skills catalog for fleet_insights (metric -> what it returns):'];
    for (const [key, def] of Object.entries(METRICS)) {
        const p = def.params && def.params.length ? ` (params: ${def.params.join(', ')})` : '';
        lines.push(`- ${key}${p}: ${def.description}`);
    }
    return lines.join('\n');
}
