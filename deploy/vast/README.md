# Cloud demo deployment (Vast.ai instance)

Scripts that stand up the full IFS CommandIQ demo natively on a GPU-cloud instance
(no Docker available there — the instance itself is a container). Run in order;
each is idempotent enough to re-run.

| Script | Purpose |
|---|---|
| `provision.sh` | apt packages: MySQL, Redis, nginx, PHP (8.2 via ondrej PPA), Node 22, pnpm, cloudflared |
| `deploy-stack.sh` | DB users + dump import, API clone + composer (needs `DBPASS`, `APP_KEY` env), nginx sites for API :8000 / console :4200 |
| `build-console.sh` | Console build: extracts source, links `@ifs/commandiq-engine`, CBRE Fleet engine-text rebrand (`find -L` — pnpm symlinks!), AI-panel patch, ember build, manifest patch |
| `patch-manifest.py` | Registers the commandiq lazy bundle in the asset manifest (host generator only covers @fleetbase scopes); content is URL-encoded |
| `setup-socket.sh` | SocketCluster v17 app + tunnel path ingress + API broadcast env |
| `install-extensions.sh` | CommandIQ + AI provider config (OpenRouter, needs `OPENROUTER_KEY` env) |
| `finalize.sh` | cloudflared tunnel, queue/scheduler under supervisor (run workers as www-data — root-created log files 500 the API), boot persistence via onstart.sh |
| `simulator.php` | Moves 5 vans on Dallas loops for the live map (supervisor program) |
| `seed-*.php`, `fix-morphs.php` | Demo dataset seeders (run via `php artisan tinker --execute="require '...'"`) |

Hard-won gotchas:
- pnpm keys `file:` deps by name@version — **bump the engine version** on every change or you get a stale snapshot.
- `find` without `-L` silently skips pnpm's symlinked packages.
- Cloudflare edge-caches fingerprinted assets across rebuilds — purge the host after deploying a new console build.
- The repo pins php <= 8.2.31; install with `--ignore-platform-req=php`.
- Seeded polymorphic rows must use the FleetOps FQCN morph type (`fix-morphs.php`).
