<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>כניסה — ניהול קמפיינים</title>
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;margin:0;background:#f5f7fa;display:flex;align-items:center;justify-content:center;min-height:100vh;color:#222}
.card{background:#fff;border-radius:16px;padding:48px 40px;text-align:center;max-width:400px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.10)}
.logo{font-size:2.4rem;margin-bottom:.3em}
h1{font-size:1.4rem;margin:.2em 0 .5em;color:#1a1a1a}
.sub{color:#888;font-size:.9rem;margin-bottom:2em;line-height:1.5}
.btn-google{display:flex;align-items:center;justify-content:center;gap:10px;background:#1a73e8;color:#fff;border:none;border-radius:10px;padding:14px 24px;font-size:1rem;font-weight:600;cursor:pointer;text-decoration:none;width:100%;transition:background .15s}
.btn-google:hover{background:#1558b0}
.btn-google svg{flex-shrink:0}
.error{background:#fff3f3;color:#c0392b;border:1px solid #f5c6c6;border-radius:8px;padding:10px 14px;margin-bottom:1.2em;font-size:.9rem}
</style>
</head>
<body>
<div class="card">
  <div class="logo">🧘</div>
  <h1>ניהול קמפיינים — קושקה יוגה</h1>
  <p class="sub">התחבר עם חשבון Google המורשה כדי לנהל את קמפיינות המטא.</p>

  @if($error ?? null)
    <div class="error">{{ $error }}</div>
  @endif

  <a href="/koshka/auth/google" class="btn-google">
    <svg width="20" height="20" viewBox="0 0 48 48"><path fill="#fff" d="M44.5 20H24v8.5h11.8C34.7 33.9 30.1 37 24 37c-7.2 0-13-5.8-13-13s5.8-13 13-13c3.1 0 5.9 1.1 8.1 2.9l6.4-6.4C34.6 4.1 29.6 2 24 2 11.8 2 2 11.8 2 24s9.8 22 22 22c11 0 21-8 21-22 0-1.3-.2-2.7-.5-4z"/></svg>
    כניסה עם Google
  </a>
</div>
</body>
</html>
