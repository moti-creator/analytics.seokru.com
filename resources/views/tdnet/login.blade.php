<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>TDNet Dashboard — Sign in</title>
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;max-width:420px;margin:80px auto;padding:24px;color:#222}
h1{font-size:1.4rem;margin:0 0 .2em}
p.sub{color:#666;margin:0 0 1.5em;font-size:.95rem}
.gbtn{display:inline-flex;align-items:center;gap:10px;background:#fff;color:#3c4043;border:1px solid #dadce0;padding:11px 22px;border-radius:8px;font-weight:500;font-size:.95rem;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,.05);transition:box-shadow .15s,background .15s}
.gbtn:hover{box-shadow:0 2px 6px rgba(0,0,0,.1);background:#f8f9fa}
.gbtn svg{width:18px;height:18px}
.err{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:10px 12px;border-radius:8px;font-size:.9rem;margin-bottom:1em}
.note{margin-top:1.6em;font-size:.78rem;color:#888;line-height:1.5}
</style>
</head>
<body>
<h1>TDNet Outreach Dashboard</h1>
<p class="sub">Internal — sales team only. Sign in with your work Google account.</p>

@if(!empty($error))
  <div class="err">{{ $error }}</div>
@endif

<a class="gbtn" href="/tdnet/auth/google">
  <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg"><path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3c-1.6 4.7-6 8-11.3 8-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.1 7.9 3l5.7-5.7C34.5 6.1 29.5 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.4-.4-3.5z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.6 16 18.9 13 24 13c3.1 0 5.8 1.1 7.9 3l5.7-5.7C34.5 6.1 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.4 0 10.2-2 13.9-5.4l-6.4-5.4C29.4 35.1 26.8 36 24 36c-5.3 0-9.7-3.3-11.3-8l-6.5 5C9.6 39.6 16.3 44 24 44z"/><path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-.8 2.3-2.3 4.3-4.4 5.7l6.4 5.4C39.7 36.4 44 30.7 44 24c0-1.3-.1-2.4-.4-3.5z"/></svg>
  Sign in with Google
</a>

<div class="note">
  Only emails on the TDNet allowlist may access this dashboard. If your email is rejected, ask Moti to add it.
</div>
</body>
</html>
