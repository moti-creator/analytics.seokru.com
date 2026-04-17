<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>Dashboard — SEOKRU Analytics</title>
<style>
body{font-family:system-ui,sans-serif;max-width:960px;margin:0 auto;padding:20px;color:#222;line-height:1.55}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #eee;margin-bottom:1.5em;flex-wrap:wrap;gap:10px}
.topbar .brand{font-weight:600;color:#1a73e8;font-size:1.1rem}
.topbar .right{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.topbar select{padding:6px 10px;border:1px solid #ddd;border-radius:6px;font-size:.85rem;max-width:200px}
.topbar .btn-sm{padding:6px 14px;border:1px solid #ddd;border-radius:6px;cursor:pointer;color:#555;font-size:.85rem;background:#fff;text-decoration:none}
.topbar .btn-sm:hover{border-color:#1a73e8;color:#1a73e8}
.property-info{background:#f5f8ff;border:1px solid #d8e4ff;border-radius:8px;padding:10px 16px;margin-bottom:1.5em;font-size:.88rem;color:#555;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px}
.property-info code{background:#e8f0fe;padding:2px 6px;border-radius:3px;font-size:.82rem}

h2{font-size:1.3rem;margin:1.5em 0 .3em}
.sub{color:#666;font-size:.95rem;margin-bottom:1em}

.hero{background:linear-gradient(135deg,#f5f8ff 0%,#eef3ff 100%);border:1px solid #d8e4ff;border-radius:14px;padding:28px;margin-bottom:2em}
.hero textarea{width:100%;min-height:90px;padding:12px;font-size:1rem;border:1px solid #cfd8e3;border-radius:8px;resize:vertical;font-family:inherit;box-sizing:border-box}
.hero textarea:focus{outline:none;border-color:#1a73e8;box-shadow:0 0 0 3px rgba(26,115,232,.12)}
.hero .row{display:flex;justify-content:space-between;align-items:center;margin-top:12px;gap:10px;flex-wrap:wrap}
.hero .hint{color:#777;font-size:.85rem}
.hero button{background:#1a73e8;color:#fff;border:0;padding:10px 22px;border-radius:8px;font-size:.95rem;cursor:pointer;font-weight:500}
.hero button:hover{background:#1557b8}

.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px}
.card{border:1px solid #e3e3e3;border-radius:10px;padding:18px;transition:all .2s;text-decoration:none;color:inherit;display:block}
.card:hover{border-color:#1a73e8;box-shadow:0 4px 18px rgba(26,115,232,.12);transform:translateY(-2px)}
.card h3{margin:0 0 .3em;color:#1a73e8;font-size:1.05rem}
.card p{margin:0;font-size:.88rem;color:#555}
.badge{display:inline-block;background:#f0f7ff;color:#1a73e8;font-size:.7rem;padding:2px 7px;border-radius:4px;margin-top:.6em}
.card-cross{border-color:#d8b4ff;background:linear-gradient(135deg,#faf5ff 0%,#fff 100%)}
.card-cross:hover{border-color:#7c3aed;box-shadow:0 4px 18px rgba(124,58,237,.14)}
.card-cross h3{color:#7c3aed}
.badge-cross{background:#f3e8ff;color:#7c3aed}

.divider{text-align:center;color:#999;font-size:.82rem;margin:1.5em 0 1em;text-transform:uppercase;letter-spacing:.1em}

.recent-section{margin-top:2em;border-top:1px solid #eee;padding-top:1.5em}
.recent-section h3{font-size:.95rem;color:#555;margin:0 0 .6em}
.recent-list{list-style:none;padding:0;margin:0}
.recent-list li{padding:6px 0;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center}
.recent-list a{color:#1a73e8;text-decoration:none;font-size:.9rem}
.recent-list a:hover{text-decoration:underline}
.recent-list .meta{color:#999;font-size:.8rem}
.recent-list .type-badge{background:#f0f7ff;color:#1a73e8;font-size:.7rem;padding:2px 6px;border-radius:3px;margin-left:6px}
</style>
</head>
<body>

<div class="topbar">
<div class="brand">SEOKRU Analytics</div>
<div class="right">
<form method="post" action="{{ route('dashboard.property') }}" id="propForm" style="display:flex;gap:6px;align-items:center;margin:0">
@csrf
<select name="ga4_property_id" onchange="document.getElementById('propForm').submit()">
<option value="">GA4: none</option>
@foreach($properties as $p)
<option value="{{ $p['id'] }}" @if($conn->ga4_property_id === $p['id']) selected @endif>{{ $p['name'] }}</option>
@endforeach
</select>
<select name="gsc_site_url" onchange="document.getElementById('propForm').submit()">
<option value="">GSC: none</option>
@foreach($sites as $s)
<option value="{{ $s['url'] }}" @if($conn->gsc_site_url === $s['url']) selected @endif>{{ $s['url'] }}</option>
@endforeach
</select>
</form>
<form method="post" action="{{ route('logout') }}" style="margin:0">@csrf<button type="submit" class="btn-sm">Log out</button></form>
</div>
</div>

<div class="property-info">
<span>
{{ $conn->email }}
@if($conn->ga4_property_id) · GA4: <code>{{ $conn->ga4_property_id }}</code> @endif
@if($conn->gsc_site_url) · GSC: <code>{{ $conn->gsc_site_url }}</code> @endif
</span>
@if(!$conn->ga4_property_id && !$conn->gsc_site_url)
<span style="color:#c00">⚠ Select at least one property above to generate reports.</span>
@endif
</div>

<div class="hero">
<form method="post" action="{{ route('ask.start') }}">
@csrf
<textarea name="prompt" placeholder="Ask anything — e.g. Which pages lost the most organic traffic last month? Compare mobile vs desktop conversion." required></textarea>
<div class="row">
<span class="hint">Agent fetches GA4 + Search Console data for you.</span>
<button type="submit">Ask →</button>
</div>
</form>
</div>

<div class="divider">— Cross-platform reports (GA4 × GSC) —</div>
<div class="grid">
<a class="card card-cross" href="{{ route('generate.direct', 'silent_winners') }}">
<h3>Silent Winners</h3>
<p>Ranking well but barely clicked — title &amp; intent gaps.</p>
<span class="badge badge-cross">GA4 × GSC</span>
</a>
<a class="card card-cross" href="{{ route('generate.direct', 'converting_queries') }}">
<h3>Converting Queries Slipping</h3>
<p>Revenue pages losing Google rank.</p>
<span class="badge badge-cross">GA4 × GSC</span>
</a>
<a class="card card-cross" href="{{ route('generate.direct', 'cannibalization') }}">
<h3>Cannibalization Detector</h3>
<p>Multiple URLs fighting for same query.</p>
<span class="badge badge-cross">GA4 × GSC</span>
</a>
<a class="card card-cross" href="{{ route('generate.direct', 'brand_rescue') }}">
<h3>Brand Rescue vs Real Growth</h3>
<p>Is brand traffic masking non-brand decay?</p>
<span class="badge badge-cross">GA4 × GSC</span>
</a>
</div>

<div class="divider">— Single-source presets —</div>
<div class="grid">
<a class="card" href="{{ route('generate.direct', 'content_decay') }}">
<h3>Content Decay</h3>
<p>Pages losing traffic and by how much.</p>
<span class="badge">GA4</span>
</a>
<a class="card" href="{{ route('generate.direct', 'striking_distance') }}">
<h3>Striking-Distance Keywords</h3>
<p>Keywords ranked 4-20 with high impressions.</p>
<span class="badge">Search Console</span>
</a>
<a class="card" href="{{ route('generate.direct', 'conversion_leak') }}">
<h3>Conversion Leak</h3>
<p>High-traffic pages not converting.</p>
<span class="badge">GA4</span>
</a>
<a class="card" href="{{ route('generate.direct', 'anomaly') }}">
<h3>Weekly Anomaly Scan</h3>
<p>Metrics that moved >20% this week.</p>
<span class="badge">GA4 + GSC</span>
</a>
<a class="card" href="{{ route('generate.direct', 'brand_split') }}">
<h3>Brand vs Non-Brand</h3>
<p>Split by brand vs non-brand queries.</p>
<span class="badge">Search Console</span>
</a>
</div>

@if($recent->count())
<div class="recent-section">
<h3>Recent reports</h3>
<ul class="recent-list">
@foreach($recent as $r)
<li>
<span>
<a href="{{ route('report.show', $r) }}">{{ Str::limit($r->title, 50) }}</a>
<span class="type-badge">{{ $r->type }}</span>
</span>
<span class="meta">{{ $r->created_at->diffForHumans() }}</span>
</li>
@endforeach
</ul>
</div>
@endif

</body>
</html>
