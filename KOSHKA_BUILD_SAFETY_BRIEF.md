# Koshka Build — Safety Brief for the Builder

**Read this before writing a single line.** You are extending `/koshka` (Meta Edge dashboard for Daniela) inside a Laravel app that ALSO serves two other production apps on the same domain. Breaking a shared file takes all three down. This brief defines the hard isolation boundaries.

---

## The situation: one Laravel app, three sibling apps

`analytics.seokru.com` is a single Laravel 10 install (PHP 8.3). It hosts three independent apps, isolated only by convention — not by separate codebases:

| App | URL prefix | Controller | Auth middleware | Views dir | Purpose |
|---|---|---|---|---|---|
| **Analytics** (original) | `/` | `ReportController`, `AskController` | `RestoreConnection` (cookie) | `resources/views/*.blade.php` (root) | GA4/GSC → LLM reports SaaS |
| **TDNet** | `/tdnet/*` | `TdnetController` | `tdnet.auth` | `resources/views/tdnet/` | Outreach lead dashboard |
| **Koshka** (yours) | `/koshka/*` | `KoshkaController` | `koshka.auth` | `resources/views/koshka/` | Meta Ads manager |

**`/koshka` already exists and partially works.** Routes are at `routes/web.php` lines ~51-80. Do not rebuild from zero — extend what's there.

---

## HARD RULES — violating any of these can break the sibling apps

### 1. Stay inside your namespace. Touch only Koshka-prefixed things.
**Allowed to create/edit:**
- `app/Http/Controllers/KoshkaController.php` (+ any `app/Http/Controllers/Koshka/*.php` you add)
- `app/Services/Koshka*.php` or `app/Services/Koshka/*.php` (e.g. `MetaAdsService.php` — but name it `KoshkaMetaAdsService` or put it in a `Koshka/` subdir)
- `app/Http/Middleware/KoshkaAuth.php` (already exists as `koshka.auth`)
- `resources/views/koshka/**` (your Blade templates)
- `app/Models/Koshka*.php` (any new models)
- Koshka-prefixed routes in `routes/web.php` (the `/koshka/*` block ONLY)
- Koshka-prefixed migrations (new tables, see rule 4)
- `public/koshka/**` if you need static assets (create the dir)

**FORBIDDEN to edit:**
- Anything under `/` analytics: `ReportController`, `AskController`, `ReportBuilder`, `GoogleService`, `GroqService`, `GeminiService`, `AgentService`, `BoostService` and all Boost channel services, `TelegramService`, root Blade views (`landing.blade.php`, `report.blade.php`, etc.)
- Anything `Tdnet*` / `tdnet/*`
- `routes/web.php` blocks for `/` and `/tdnet/*`
- `bootstrap/app.php`, `app/Providers/*` unless adding a NEW provider that only Koshka uses (and register it additively — never edit existing provider bodies)

### 2. `.env` is shared. Only add `KOSHKA_*` / `META_*` keys. Never edit existing keys.
Existing Koshka/Meta env (already set in prod, do not duplicate or rename):
```
ANTHROPIC_API_KEY=...           # shared — Koshka may READ, must not change
KOSHKA_ALLOWED_EMAILS=moti@seokru.com,studiokoshka@gmail.com
META_ACCESS_TOKEN=...
META_AD_ACCOUNT_ID=act_355872272
```
New env you add → prefix `KOSHKA_` or `META_`. Add the key to `.env.example` too (with a placeholder value, NOT the real secret). Never touch `GOOGLE_*`, `GROQ_*`, `GEMINI_*`, `TELEGRAM_*`, `RESEND_*` — those belong to siblings.

Config: read env through a NEW config file `config/koshka.php`. Do NOT add Koshka keys into `config/services.php` (shared — a merge conflict there breaks all three). Reference as `config('koshka.meta_token')`.

### 3. Routes: only inside the existing `/koshka` block. Never reorder others.
- Append your new routes to the existing `Route::middleware(['koshka.auth'])->group(...)` block.
- Public Koshka routes (auth redirect/callback/logout) stay outside the group, but still `/koshka/*` prefixed.
- Do NOT add a catch-all (`Route::any('{any}', ...)` / `->where('any', '.*')`) — it would swallow `/` and `/tdnet` requests.
- Do NOT rename existing route `->name()` keys (`koshka.index`, `tdnet.index`, etc.). Other views link via `route('...')`.

### 4. Database: additive migrations only. New tables, prefixed `koshka_`.
- New tables: name them `koshka_campaigns_cache`, `koshka_leads`, etc.
- NEVER write a migration that alters/drops a table you didn't create. Shared tables (`users`, `boost_submissions`, `sessions`, TDNet's `*_leads`) are off-limits.
- One migration = one new table or one additive column on a `koshka_*` table. No `down()` that drops a sibling table.
- Before `php artisan migrate` on prod: confirm the migration only references `koshka_*` tables.

### 5. Auth: reuse `koshka.auth`. Don't touch the shared session/guard config.
- `koshka.auth` middleware already gates on `KOSHKA_ALLOWED_EMAILS` via Google OAuth. Reuse it.
- Do NOT edit `config/auth.php`, `config/session.php`, or the default `web` guard — TDNet and Analytics depend on the exact current session behavior.
- Koshka session keys must be namespaced: `session('koshka_email')`, not `session('email')`. Collision with `tdnet_email` / analytics `connection` would cross-auth users between apps.

### 6. No shared-dependency changes without explicit approval.
- Do NOT run `composer require` / `composer update` (changes `composer.lock` → affects all three apps + risks prod deploy breakage).
- Do NOT run `npm install` / bump `package.json` / change `vite.config.js`.
- If Koshka genuinely needs a new package, STOP and ask Moti first. Most Meta Graph API work needs only Laravel HTTP client (`Illuminate\Support\Facades\Http`) — already available.

### 7. Frontend isolation.
- Koshka views must be self-contained: inline `<style>`/`<script>` or assets under `public/koshka/`. Do NOT edit shared `resources/css/`, `resources/js/`, or the Vite-built bundle the analytics app uses.
- Do NOT register a global Blade layout edit. If you extend a layout, create `resources/views/koshka/layout.blade.php` — never modify a root layout.

---

## SAFE WORKFLOW (do this in order)

1. **Branch.** `git checkout -b koshka/meta-edge-dashboard`. Never build on `main`/`master`. (Repo: github.com/moti-creator/analytics.seokru.com)
2. **Map before touching.** `grep -rn "koshka" routes/ app/ resources/` — read every existing Koshka file. Know what's already built.
3. **Pre-flight snapshot.** Run the sibling smoke test (below) and record it passes BEFORE you change anything. That's your baseline.
4. **Build inside the boundary.** Only Koshka-namespaced files per rules above.
5. **Local verify.** `php artisan route:list | grep -E "tdnet|^.*GET.*\/ "` — confirm `/` and `/tdnet` routes still resolve and you added no catch-all.
6. **Sibling smoke test (MANDATORY before any prod deploy):**
   ```bash
   php artisan route:clear && php artisan view:clear
   php artisan route:list                      # all 3 apps' routes present, no duplicates
   curl -I http://localhost:8000/              # analytics landing → 200
   curl -I http://localhost:8000/tdnet         # → 302 (redirect to auth) or 200, NOT 500
   curl -I http://localhost:8000/koshka        # your app
   # tail storage/logs/laravel.log → zero new PHP fatals
   ```
   If `/` or `/tdnet` return 500 or a different status than baseline → you broke a sibling. Revert and diagnose before continuing.
7. **Migrations dry-run.** `php artisan migrate --pretend` — confirm SQL only touches `koshka_*` tables.
8. **Commit small, Koshka-only diffs.** Each commit message prefixed `koshka:`. Never bundle a sibling-file change into a Koshka commit.

---

## DEPLOY (prod is live — siblings serve real users)

Prod path: `/home/325771.cloudwaysapps.com/qzyqpaznzq/public_html` on `ssh master_vultr_ath@104.238.128.199` (key alias `cc1`).

```bash
# 1. On prod, snapshot current state first
cd /home/325771.cloudwaysapps.com/qzyqpaznzq/public_html
git status                      # must be clean; if not, STOP and ask Moti
curl -I https://analytics.seokru.com/        # baseline 200
curl -I https://analytics.seokru.com/tdnet   # baseline status

# 2. Pull your branch (or merge to main first via PR)
git pull origin koshka/meta-edge-dashboard
php artisan migrate --pretend   # eyeball: koshka_* only
php artisan migrate             # only if pretend is clean
php artisan view:clear && php artisan route:clear
# config NOT cached in prod (env() reads direct) — but if you added config/koshka.php and prod caches later, run config:clear

# 3. Post-deploy verify ALL THREE
curl -I https://analytics.seokru.com/        # still 200?
curl -I https://analytics.seokru.com/tdnet   # same as baseline?
curl -I https://analytics.seokru.com/koshka  # your app live?
tail -n 50 storage/logs/laravel.log          # no new fatals
```
If any sibling regresses: `git reset --hard <prev-commit>` + `php artisan view:clear route:clear`, then diagnose locally. Never debug-edit on prod.

---

## Meta Graph API notes (your actual feature work)

- Token + account already in env: `META_ACCESS_TOKEN`, `META_AD_ACCOUNT_ID=act_355872272`.
- Use Laravel `Http::get('https://graph.facebook.com/v21.0/...')` — no SDK install needed (rule 6).
- Cache Meta API responses (file cache, like the analytics app's 12h `Cache::remember()` pattern) — Meta rate-limits hard. Namespace cache keys `koshka:*`.
- There is a Meta Ads MCP available in this environment (`ads_*` tools) for read/insight calls during dev — use it to inspect campaign structure before coding against the live token.
- `KOSHKA_ALLOWED_EMAILS` gates access — Daniela's email must be added there when she's onboarded (ask Moti; it's a prod `.env` edit, additive to the existing comma list).

---

## One-line summary for the builder

> You are a guest in a shared house. Build only in the `koshka` room. Never touch the `/` or `/tdnet` rooms, never edit shared plumbing (`.env` existing keys, `composer.lock`, `config/services.php`, `config/auth.php`, session config), additive `koshka_`-prefixed migrations only, and smoke-test all three apps before every deploy.
