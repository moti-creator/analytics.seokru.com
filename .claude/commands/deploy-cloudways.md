---
description: Deploy changed files to Cloudways via file-by-file curl+mv (master_vultr_ath workaround)
---

Steps:
1. `git status` — list changed files. Confirm user wants to deploy ALL of them.
2. `git push origin main` first. Get current SHA from `https://api.github.com/repos/moti-creator/analytics.seokru.com/commits/main` to bust GitHub raw cache.
3. SSH: `ssh master_vultr_ath@104.238.128.199` (alias `cc1`).
4. `cd /home/325771.cloudwaysapps.com/qzyqpaznzq/public_html`.
5. For each changed file (master_vultr_ath CANNOT `git pull` — perms on FETCH_HEAD):
   ```
   curl -sL https://raw.githubusercontent.com/moti-creator/analytics.seokru.com/<SHA>/<path> -o /tmp/$(basename <path>)
   rm -f <path>
   mv /tmp/$(basename <path>) <path>
   ```
6. After all files moved:
   - `php artisan view:clear`
   - `php artisan route:clear`
   - Only if config changed: `php artisan config:clear` (config NOT cached in prod — `env()` reads direct, so usually skip)
7. Smoke test: `curl -I https://analytics.seokru.com/` returns 200.
8. Tail prod log: `tail -50 storage/logs/laravel.log` for errors.
