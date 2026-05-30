---
description: Scaffold a new report type in ReportBuilder (TYPES + method + switch + landing card)
---

Steps:
1. Ask user for: report key (snake_case), title, data source (GA4 / GSC / both), preset card y/n.
2. Add key to `app/Services/ReportBuilder.php`:
   - `TYPES` array (always)
   - `PREBUILT_TYPES` array (only if preset card)
3. Add `<key>Report($g)` method. Must return `['type','title','metrics','narrative']`. Pre-compute ALL deltas in PHP — prompt says "USE THEM EXACTLY" to prevent LLM math hallucination.
4. Add switch case in `build()`.
5. If preset card: add card in `resources/views/landing.blade.php`.
6. Verify: `php artisan tinker` → instantiate ReportBuilder, dry-call new method with mock data.
7. Deploy via the file-by-file curl+mv pattern (master_vultr_ath can't `git pull` — see CLAUDE.md gotcha).
8. After deploy: `php artisan view:clear && php artisan route:clear`.
