<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Ben Friedman — Portfolio Analytics | SEOKRU</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1a2233;background:#f7f8fb;line-height:1.5}
.wrap{max-width:1040px;margin:0 auto;padding:0 20px 64px}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:#12213f;color:#fff}
.topbar .brand{font-weight:700;letter-spacing:.01em}
.topbar .right{display:flex;align-items:center;gap:12px;font-size:.85rem}
.btn-sm{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);color:#fff;padding:6px 12px;border-radius:6px;cursor:pointer;font-size:.82rem}
.btn-sm:hover{background:rgba(255,255,255,.24)}
.banner{background:#eef3ff;border:1px solid #c7d7ff;border-radius:10px;padding:12px 16px;margin:20px 0;font-size:.9rem;color:#1a3a6b}
h1{font-size:1.6rem;margin:24px 0 4px}
.sub{color:#5a6478;margin:0 0 20px}
.prop-switch{display:flex;gap:12px;flex-wrap:wrap;margin:0 0 8px}
.prop-pick{flex:1;min-width:260px;display:flex;flex-direction:column;gap:5px;padding:12px 14px;border-radius:10px;border:2px solid transparent}
.prop-pick.ga4{background:#eef3ff;border-color:#c7d7ff}
.prop-pick.gsc{background:#f3eaff;border-color:#d8b4ff}
.prop-lbl{font-size:.7rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;display:flex;align-items:center;gap:6px}
.prop-pick.ga4 .prop-lbl{color:#1a73e8}
.prop-pick.gsc .prop-lbl{color:#7c3aed}
.dot{width:8px;height:8px;border-radius:50%}
.prop-pick.ga4 .dot{background:#1a73e8}
.prop-pick.gsc .dot{background:#7c3aed}
.prop-pick select{padding:8px;border:1px solid rgba(0,0,0,.12);border-radius:6px;font-size:.9rem;background:#fff;cursor:pointer;font-family:inherit}
.status{font-size:.82rem;color:#5a6478;margin:6px 0 24px}
.status b{color:#12213f}
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px}
.card{display:block;padding:16px 18px;background:#fff;border:1px solid #e4e8f0;border-radius:12px;text-decoration:none;color:#1a2233;transition:all .15s}
.card:hover{border-color:#1a73e8;box-shadow:0 4px 18px rgba(26,115,232,.12);transform:translateY(-1px)}
.card .t{font-weight:600;margin-bottom:6px}
.card .need{font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:#8a94a8}
.card-gated{opacity:.5;pointer-events:none}
.tag{display:inline-block;font-size:.64rem;font-weight:700;padding:2px 6px;border-radius:4px;margin-left:6px}
.tag.ga4{background:#e3edff;color:#1a73e8}
.tag.gsc{background:#efe1ff;color:#7c3aed}
.section-h{margin:34px 0 12px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#8a94a8}
.ask{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;background:#12213f;color:#fff;border-radius:12px;padding:18px 22px;margin:24px 0}
.ask h2{margin:0 0 4px;font-size:1.1rem}
.ask p{margin:0;font-size:.86rem;color:#b8c4dc}
.btn-ask{background:#fff;color:#12213f;text-decoration:none;font-weight:600;padding:10px 18px;border-radius:8px;white-space:nowrap}
.recent a{display:block;padding:10px 14px;background:#fff;border:1px solid #e4e8f0;border-radius:8px;text-decoration:none;color:#1a2233;margin-bottom:8px;font-size:.9rem}
.recent a:hover{border-color:#1a73e8}
.empty{color:#8a94a8;font-size:.9rem}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">SEOKRU · Ben Friedman</div>
  <div class="right">
    <a href="/bf/status" class="btn-sm" style="text-decoration:none">📊 Status &amp; Reports</a>
    @if($conn)<span>{{ $conn->email }}</span>
    <form method="post" action="{{ route('logout') }}" style="margin:0">@csrf<button class="btn-sm" type="submit">Log out</button></form>@endif
  </div>
</div>

<div class="wrap">
  <div class="banner"><strong>{{ $scopeLabel }}</strong> — scoped to the six Friedman domains only: crypto-jews, 1in56million, get-out-now, projectshreeram, interfaith-sanctuary, thelastdate.</div>

  <h1>Portfolio Analytics</h1>
  <p class="sub">GA4 + Search Console reports for the six sites, in plain English. Pick a site, then a report.</p>

  @if(empty($properties) && empty($sites))
    <p class="empty">No Friedman GA4 properties or Search Console sites found on this Google account yet. Once GA4/GSC access is granted for these domains, they'll appear here.</p>
  @else
  <form method="post" action="{{ url('/bf/property') }}" id="propForm" class="prop-switch">
    @csrf
    <label class="prop-pick ga4">
      <span class="prop-lbl"><span class="dot"></span>Google Analytics 4</span>
      <select name="ga4_property_id" onchange="document.getElementById('propForm').submit()">
        <option value="">— select site —</option>
        @foreach($properties as $p)
        <option value="{{ $p['id'] }}" @if($conn->ga4_property_id === $p['id']) selected @endif>{{ $p['name'] }}</option>
        @endforeach
      </select>
    </label>
    <label class="prop-pick gsc">
      <span class="prop-lbl"><span class="dot"></span>Search Console</span>
      <select name="gsc_site_url" onchange="document.getElementById('propForm').submit()">
        <option value="">— select site —</option>
        @foreach($sites as $s)
        <option value="{{ $s['url'] }}" @if($conn->gsc_site_url === $s['url']) selected @endif>{{ $s['url'] }}</option>
        @endforeach
      </select>
    </label>
  </form>
  @php($ga4Ready = $conn && $conn->ga4_property_id)
  @php($gscReady = $conn && $conn->gsc_site_url)
  <p class="status">Active: GA4 <b>{{ $ga4Ready ? 'connected' : '—' }}</b> · Search Console <b>{{ $gscReady ? $conn->gsc_site_url : '—' }}</b></p>

  <div class="ask">
    <div><h2>Ask anything in plain English</h2><p>"Which pages lost traffic last month?" · "Top converting queries" · "Mobile vs desktop"</p></div>
    <a class="btn-ask" href="{{ route('ask.form') }}">Open Ask Mode →</a>
  </div>

  <div class="section-h">Reports</div>
  <div class="cards">
    @foreach($types as $key => $t)
      @php($needs = $t['needs'] ?? [])
      @php($ready = (!in_array('ga4',$needs) || $ga4Ready) && (!in_array('gsc',$needs) || $gscReady))
      <a class="card @if(!$ready) card-gated @endif" href="{{ $ready ? url('/bf/generate/'.$key) : '#' }}">
        <div class="t">{{ $t['title'] ?? $key }}</div>
        <div class="need">
          @foreach($needs as $n)<span class="tag {{ $n }}">{{ strtoupper($n) }}</span>@endforeach
        </div>
      </a>
    @endforeach
  </div>

  @if($recent->isNotEmpty())
  <div class="section-h">Recent reports</div>
  <div class="recent">
    @foreach($recent as $r)
    <a href="{{ route('report.show', $r) }}?from=bf">{{ $r->title }} <span style="color:#8a94a8;font-size:.8rem">· {{ $r->created_at->diffForHumans() }}</span></a>
    @endforeach
  </div>
  @endif
  @endif
</div>

</body>
</html>
