---
description: Clear the 12h ReportBuilder file cache for a specific connection / type / date
---

Steps:
1. Ask user for: connection ID (or "all"), report type (or "all"), date (or "today").
2. Cache key format: `connection+type+date`, file driver.
3. Options:
   - Single key: `php artisan cache:forget "<key>"`
   - Whole cache: `php artisan cache:clear` (nuke option, confirm first)
   - File-level: rm matching files in `storage/framework/cache/data/`
4. After clear: confirm next `ReportController@generate/{type}` call rebuilds (check `storage/logs/laravel.log` for fresh GA4/GSC fetch logs).
5. Reminder: cache key includes date, so stale entries auto-drop daily. Manual purge only needed if data correction or schema change.
6. If on prod: SSH `master_vultr_ath@104.238.128.199` → `cd /home/325771.cloudwaysapps.com/qzyqpaznzq/public_html` first.
