<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Pick Property</title>
<style>
body{font-family:system-ui,sans-serif;max-width:640px;margin:60px auto;padding:20px;line-height:1.6}
label{display:block;margin:1.2em 0 .3em;font-weight:600}
select,button{width:100%;padding:12px;font-size:1rem;border-radius:6px;border:1px solid #ccc}
button{background:#1a73e8;color:#fff;border:none;font-weight:600;cursor:pointer;margin-top:1.5em}
button:hover{background:#1557b0}
.muted{color:#888;font-size:.9rem}
</style></head>
<body>
<h2>Connected as {{ $conn->email }} ✓</h2>
<p class="muted">Pick which GA4 property + Search Console site you want in the report.</p>

<form method="POST" action="{{ route('generate') }}">
@csrf
<label>GA4 Property</label>
<select name="ga4_property_id" required>
<option value="">— Select —</option>
@foreach($properties as $p)
<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
@endforeach
</select>

<label>Search Console Site</label>
<select name="gsc_site_url" required>
<option value="">— Select —</option>
@foreach($sites as $s)
<option value="{{ $s['url'] }}">{{ $s['url'] }}</option>
@endforeach
</select>

<button type="submit">Generate my report →</button>
</form>

@if(empty($properties))
<p class="muted" style="color:#c00">No GA4 properties found for this account.</p>
@endif
@if(empty($sites))
<p class="muted" style="color:#c00">No Search Console sites found for this account.</p>
@endif
</body>
</html>
