<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>TDNet Dashboard — Login</title>
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;max-width:420px;margin:80px auto;padding:24px;color:#222}
h1{font-size:1.4rem;margin:0 0 .2em}
p.sub{color:#666;margin:0 0 1.5em;font-size:.95rem}
label{display:block;font-size:.85rem;color:#444;margin-bottom:6px}
input{width:100%;padding:10px 12px;border:1px solid #cfd8e3;border-radius:8px;font-size:1rem}
input:focus{outline:none;border-color:#1a73e8;box-shadow:0 0 0 3px rgba(26,115,232,.12)}
button{margin-top:12px;background:#1a73e8;color:#fff;border:none;padding:10px 22px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.95rem}
button:hover{background:#1557b8}
.err{background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:10px 12px;border-radius:8px;font-size:.9rem;margin-bottom:1em}
</style>
</head>
<body>
<h1>TDNet Outreach Dashboard</h1>
<p class="sub">Internal — sales-team only.</p>
@if(!empty($error))
  <div class="err">{{ $error }}</div>
@endif
<form method="POST" action="/tdnet">
  @csrf
  <label>Password</label>
  <input type="password" name="password" autofocus autocomplete="current-password">
  <button type="submit">Sign in</button>
</form>
</body>
</html>
