# Noindex + Mobile Viewport Patch — 2026-05-21

Wave 3 audit verdict: `analytics.seokru.com` is an internal app and must be
de-indexed. This bundle hard-blocks search engines and adds mobile viewport
meta to the public-facing blades. Login gating is a separate follow-up.

## Files in this bundle

| File | Destination |
|---|---|
| `middleware-Noindex.php` | `app/Http/Middleware/Noindex.php` |
| `kernel-patch.diff` | Apply to `app/Http/Kernel.php` |
| `viewport-patch.diff` | Apply to `resources/views/landing.blade.php` (+ siblings — see diff notes) |
| `robots.txt.new` | Replace `public/robots.txt` |

## Deploy sequence

Run on the server, from project root:

```bash
# 1. Drop the new middleware into place
cp fixes/noindex-2026-05-21/middleware-Noindex.php app/Http/Middleware/Noindex.php

# 2. Patch the Kernel
patch -p1 < fixes/noindex-2026-05-21/kernel-patch.diff

# 3. Patch landing.blade.php (then repeat the same one-line insert
#    after <meta charset="utf-8"> in the other blades listed in the diff)
patch -p1 < fixes/noindex-2026-05-21/viewport-patch.diff

# 4. Replace robots.txt
cp fixes/noindex-2026-05-21/robots.txt.new public/robots.txt

# 5. Refresh Laravel caches
composer dump-autoload -o
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# 6. Verify
curl -sI https://analytics.seokru.com/ | grep -i x-robots-tag
#   expect: X-Robots-Tag: noindex, nofollow, noarchive, nosnippet

curl -s https://analytics.seokru.com/ | grep -i 'name="robots"'
#   expect: <meta name="robots" content="noindex,nofollow">

curl -s https://analytics.seokru.com/robots.txt
#   expect:
#   User-agent: *
#   Disallow: /

curl -s https://analytics.seokru.com/ | grep -i viewport
#   expect: <meta name="viewport" content="width=device-width, initial-scale=1">
```

## Rollback

```bash
git checkout app/Http/Kernel.php resources/views/landing.blade.php public/robots.txt
rm app/Http/Middleware/Noindex.php
php artisan optimize
```

## Follow-ups (not in this patch)

- Put the app behind auth (`auth` middleware on all routes except `/landing`,
  `/legal/*`, and webhook endpoints).
- Request URL removal in Google Search Console once de-indexing is live.
- Consider serving an `X-Frame-Options: DENY` header from the same middleware
  if framing is undesirable.
