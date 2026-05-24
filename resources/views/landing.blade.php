<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>SEOKRU Analytics — GA4 + Search Console in one plain-English report</title>
<meta name="description" content="Ask any question about your site's traffic. We join Google Analytics 4 with Search Console data and answer in plain English. In 60 seconds.">
<meta property="og:title" content="SEOKRU Analytics">
<meta property="og:site_name" content="SEOKRU Analytics">
<meta property="og:description" content="GA4 + Search Console in one plain-English report.">
<meta property="og:url" content="https://analytics.seokru.com">
<meta name="application-name" content="SEOKRU Analytics">
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16.png">
<link rel="apple-touch-icon" sizes="180x180" href="/favicon-180.png">
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;max-width:980px;margin:0 auto;padding:20px;color:#222;line-height:1.55;background:#fafbfc}

/* Top bar */
.topbar{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #eee;margin-bottom:1.5em;flex-wrap:wrap;gap:10px}
.topbar .brand{font-weight:700;color:#1a73e8;font-size:1.15rem}
.topbar .right{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.btn-connect{display:inline-block;background:#1a73e8;color:#fff;padding:8px 20px;border-radius:6px;text-decoration:none;font-weight:600;font-size:.9rem}
.btn-connect:hover{background:#1557b8}
.btn-connect-lg{padding:14px 36px;font-size:1.1rem;border-radius:10px}
.btn-sm{padding:6px 14px;border:1px solid #ddd;border-radius:6px;cursor:pointer;color:#555;font-size:.85rem;background:#fff;text-decoration:none}
.btn-sm:hover{border-color:#1a73e8;color:#1a73e8}

/* Property selector */
.property-picker{background:linear-gradient(135deg,#fffbeb 0%,#fef3c7 100%);border:2px solid #f59e0b;border-radius:14px;padding:24px 28px;margin-bottom:2em;text-align:center}
.property-picker h2{margin:0 0 .4em;font-size:1.15rem;color:#92400e}
.property-picker p{margin:0 0 1em;color:#78716c;font-size:.9rem}
.property-picker .selects{display:flex;justify-content:center;gap:12px;flex-wrap:wrap}
.property-picker select{padding:10px 16px;border:2px solid #f59e0b;border-radius:8px;font-size:.95rem;min-width:240px;background:#fff;color:#222;cursor:pointer}
.property-picker select:focus{outline:none;border-color:#d97706;box-shadow:0 0 0 3px rgba(245,158,11,.2)}

/* Hero */
h1{font-size:2rem;margin:.1em 0 .15em}
.sub{color:#666;font-size:1.05rem;margin-bottom:1.5em}

/* Connect CTA */
.connect-cta{text-align:center;background:linear-gradient(135deg,#f5f8ff 0%,#eef3ff 100%);border:2px solid #1a73e8;border-radius:14px;padding:32px;margin-bottom:2em}
.connect-cta h2{margin:0 0 .3em;color:#1a73e8;font-size:1.2rem}
.connect-cta p{margin:0 0 1.2em;color:#666;font-size:.95rem}

/* Property switcher (state 3) */
.prop-switch{display:flex;gap:12px;align-items:stretch;margin-bottom:1.5em;flex-wrap:wrap}
.prop-pick{flex:1;min-width:240px;display:flex;flex-direction:column;gap:4px;padding:10px 14px;border-radius:10px;border:2px solid transparent;transition:all .2s}
.prop-pick.ga4{background:#eef3ff;border-color:#c7d7ff}
.prop-pick.ga4:hover{background:#dce7ff;border-color:#1a73e8;box-shadow:0 4px 14px rgba(26,115,232,.18)}
.prop-pick.gsc{background:#f3eaff;border-color:#d8b4ff}
.prop-pick.gsc:hover{background:#ebdbff;border-color:#7c3aed;box-shadow:0 4px 14px rgba(124,58,237,.18)}
.prop-lbl{font-size:.72rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;display:flex;align-items:center;gap:6px}
.prop-pick.ga4 .prop-lbl{color:#1a73e8}
.prop-pick.gsc .prop-lbl{color:#7c3aed}
.prop-lbl .dot{width:8px;height:8px;border-radius:50%}
.prop-pick.ga4 .dot{background:#1a73e8}
.prop-pick.gsc .dot{background:#7c3aed}
.prop-pick select{padding:6px 8px;border:1px solid rgba(0,0,0,.1);border-radius:6px;font-size:.88rem;background:#fff;cursor:pointer;font-family:inherit}
.prop-pick select:focus{outline:2px solid currentColor;outline-offset:1px}

/* Ask hero */
.ask-hero{background:linear-gradient(135deg,#f5f8ff 0%,#eef3ff 100%);border:1px solid #d8e4ff;border-radius:14px;padding:24px 28px;margin-bottom:1.6em;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.ask-hero h2{margin:0 0 .2em;font-size:1.2rem;color:#1a73e8}
.ask-hero p{margin:0;color:#555;font-size:.92rem}
.ask-hero a.btn-ask{background:#1a73e8;color:#fff;padding:14px 28px;border-radius:10px;text-decoration:none;font-weight:600;font-size:1rem;white-space:nowrap;box-shadow:0 4px 14px rgba(26,115,232,.25)}
.ask-hero a.btn-ask:hover{background:#1557b8}

/* ACCORDION sections */
.accordion{margin-bottom:1em}
.acc-section{border:1px solid #e2e8f0;border-radius:12px;background:#fff;margin-bottom:10px;overflow:hidden;transition:all .2s}
.acc-section[open]{box-shadow:0 4px 18px rgba(15,23,42,.06)}
.acc-section summary{padding:16px 22px;cursor:pointer;list-style:none;display:flex;justify-content:space-between;align-items:center;gap:12px;user-select:none}
.acc-section summary::-webkit-details-marker{display:none}
.acc-section summary:hover{background:#f8fafc}
.acc-title{display:flex;align-items:center;gap:12px;flex:1}
.acc-icon{width:38px;height:38px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:1.15rem;flex-shrink:0}
.acc-text h3{margin:0;font-size:1.05rem;color:#0f172a;font-weight:600}
.acc-text p{margin:2px 0 0;font-size:.85rem;color:#64748b}
.acc-chev{color:#94a3b8;font-size:1.1rem;transition:transform .2s}
.acc-section[open] .acc-chev{transform:rotate(180deg)}

.acc-discover .acc-icon{background:#dbeafe;color:#1e40af}
.acc-diagnose .acc-icon{background:#fee2e2;color:#991b1b}
.acc-track .acc-icon{background:#dcfce7;color:#15803d}
.acc-boost .acc-icon{background:#ffedd5;color:#9a3412}

.acc-body{padding:6px 18px 18px}

/* Cards */
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px}
.card{border:1px solid #e3e3e3;border-radius:10px;padding:16px 18px;transition:all .2s;text-decoration:none;color:inherit;display:block;background:#fff}
.card:hover{border-color:#1a73e8;box-shadow:0 4px 18px rgba(26,115,232,.12);transform:translateY(-1px)}
.card h4{margin:0 0 .3em;color:#1a73e8;font-size:1rem;font-weight:600}
.card p{margin:0;font-size:.85rem;color:#555;line-height:1.4}
.badge{display:inline-block;background:#f0f7ff;color:#1a73e8;font-size:.7rem;padding:2px 7px;border-radius:4px;margin-top:.6em;font-weight:500}
.card-cross{border-color:#d8b4ff;background:linear-gradient(135deg,#faf5ff 0%,#fff 100%)}
.card-cross:hover{border-color:#7c3aed;box-shadow:0 4px 18px rgba(124,58,237,.14)}
.card-cross h4{color:#7c3aed}
.badge-cross{background:#f3e8ff;color:#7c3aed}
.card-llm{border-color:#fb923c;background:linear-gradient(135deg,#fff7ed 0%,#fff 100%)}
.card-llm:hover{border-color:#ea580c;box-shadow:0 4px 18px rgba(234,88,12,.16)}
.card-llm h4{color:#ea580c}
.badge-llm{background:#ffedd5;color:#ea580c}
.card-green{border-color:#86efac;background:linear-gradient(135deg,#f0fdf4 0%,#fff 100%)}
.card-green:hover{border-color:#22c55e;box-shadow:0 4px 18px rgba(34,197,94,.16)}
.card-green h4{color:#15803d}
.badge-green{background:#dcfce7;color:#15803d}
.card-boost{border-color:#fdba74;background:linear-gradient(135deg,#fff7ed 0%,#ffedd5 100%)}
.card-boost:hover{border-color:#ea580c;box-shadow:0 4px 18px rgba(234,88,12,.18)}
.card-boost h4{color:#9a3412}
.card-gated{opacity:.55;pointer-events:none;position:relative}
.card-gated:hover{transform:none;box-shadow:none}
.tag-new{font-size:.62rem;background:#ea580c;color:#fff;padding:2px 6px;border-radius:4px;margin-left:4px;vertical-align:middle;font-weight:700;letter-spacing:.03em}

/* Recent */
.recent-section{margin-top:1.5em;border-top:1px solid #eee;padding-top:1.5em}
.recent-section h3{font-size:.95rem;color:#555;margin:0 0 .6em}
.recent-list{list-style:none;padding:0;margin:0}
.recent-list li{padding:8px 0;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center}
.recent-list a{color:#1a73e8;text-decoration:none;font-size:.9rem}
.recent-list a:hover{text-decoration:underline}
.recent-list .meta{color:#999;font-size:.8rem}
.recent-list .type-badge{background:#f0f7ff;color:#1a73e8;font-size:.7rem;padding:2px 6px;border-radius:3px;margin-left:6px}

.foot{margin-top:2.5em;color:#aaa;font-size:.82rem;text-align:center}
</style>
</head>
<body>

{{-- ============ TOP BAR ============ --}}
<div class="topbar">
<div class="brand">SEOKRU Analytics</div>
<div class="right">
@if($conn)
    <span style="color:#555;font-size:.85rem">{{ $conn->email }}</span>
    <form method="post" action="{{ route('logout') }}" style="margin:0">@csrf<button type="submit" class="btn-sm">Log out</button></form>
@endif
</div>
</div>

{{-- ============ HERO ============ --}}
<h1>SEOKRU Analytics</h1>
<p class="sub" style="font-size:1.15rem;color:#444;margin-bottom:.4em"><strong>GA4 + Search Console in one plain-English report.</strong></p>
<p class="sub">Ask any question. Or pick a preset. 60 seconds.</p>

{{-- ============ STATE 1: NOT CONNECTED ============ --}}
@if(!$conn)
<div class="connect-cta">
    <h2>Step 1 — Connect your Google account</h2>
    <p>We'll read your GA4 + Search Console data (read-only) and answer questions in plain English.</p>
    <a href="/auth/google" class="btn-connect btn-connect-lg">Connect Google →</a>
</div>

{{-- ============ STATE 2: CONNECTED, NO PROPERTY ============ --}}
@elseif(!$hasProperty)
<div class="property-picker">
    <h2>Step 2 — Choose your site</h2>
    <p>Select a GA4 property or Search Console site to unlock all reports.</p>
    <form method="post" action="{{ route('dashboard.property') }}" id="propForm">
    @csrf
    <div class="selects">
    <select name="ga4_property_id" onchange="document.getElementById('propForm').submit()">
    <option value="">— Select GA4 property —</option>
    @foreach($properties as $p)
    <option value="{{ $p['id'] }}" @if($conn->ga4_property_id === $p['id']) selected @endif>{{ $p['name'] }}</option>
    @endforeach
    </select>
    <select name="gsc_site_url" onchange="document.getElementById('propForm').submit()">
    <option value="">— Select Search Console site —</option>
    @foreach($sites as $s)
    <option value="{{ $s['url'] }}" @if($conn->gsc_site_url === $s['url']) selected @endif>{{ $s['url'] }}</option>
    @endforeach
    </select>
    </div>
    </form>
</div>

{{-- ============ STATE 3: READY ============ --}}
@else
<form method="post" action="{{ route('dashboard.property') }}" id="propForm" class="prop-switch">
@csrf
<label class="prop-pick ga4">
<span class="prop-lbl"><span class="dot"></span>Google Analytics 4</span>
<select name="ga4_property_id" onchange="document.getElementById('propForm').submit()">
<option value="">— not selected —</option>
@foreach($properties as $p)
<option value="{{ $p['id'] }}" @if($conn->ga4_property_id === $p['id']) selected @endif>{{ $p['name'] }}</option>
@endforeach
</select>
</label>
<label class="prop-pick gsc">
<span class="prop-lbl"><span class="dot"></span>Search Console</span>
<select name="gsc_site_url" onchange="document.getElementById('propForm').submit()">
<option value="">— not selected —</option>
@foreach($sites as $s)
<option value="{{ $s['url'] }}" @if($conn->gsc_site_url === $s['url']) selected @endif>{{ $s['url'] }}</option>
@endforeach
</select>
</label>
</form>

<div class="ask-hero">
    <div>
    <h2>Ask anything in plain English</h2>
    <p>"Which posts lost traffic last month?" · "Top converting queries" · "Mobile vs desktop"</p>
    </div>
    <a href="{{ route('ask.form') }}" class="btn-ask">Open Ask Mode →</a>
</div>
@endif

@php
$gated = !$hasProperty;
$gscReady = $conn && $conn->gsc_site_url;
$ga4Ready = $conn && $conn->ga4_property_id;
@endphp

{{-- ============ ACCORDION REPORTS ============ --}}
<div class="accordion">

{{-- ====== OVERVIEW ====== --}}
<details class="acc-section acc-overview" open>
<summary>
<div class="acc-title">
<span class="acc-icon">📈</span>
<div class="acc-text">
<h3>Overview — the big picture</h3>
<p>What every marketer wants in 30 seconds. Up or down? Where from? Who?</p>
</div>
</div>
<span class="acc-chev">▾</span>
</summary>
<div class="acc-body">
<div class="grid">

<a class="card card-hero @if(!$ga4Ready || !$gscReady) card-gated @endif" href="{{ ($ga4Ready && $gscReady) ? route('generate.direct', 'site_overview') : '#' }}">
<h4>Site Overview <span class="tag-new">NEW</span></h4>
<p>One screen, all signals. KPIs, new queries, winners, losers, new pages, new referrers, 404s, striking distance. The morning briefing.</p>
<span class="badge">GA4 × GSC</span>
</a>

<a class="card @if(!$ga4Ready) card-gated @endif" href="{{ $ga4Ready ? route('generate.direct', 'traffic_snapshot') : '#' }}">
<h4>Traffic Snapshot</h4>
<p>Sessions, users, channels, devices — last 30 days vs previous.</p>
<span class="badge">GA4 + GSC</span>
</a>

</div>
</div>
</details>

{{-- ====== DISCOVER ====== --}}
<details class="acc-section acc-discover">
<summary>
<div class="acc-title">
<span class="acc-icon">🔍</span>
<div class="acc-text">
<h3>Discover — find opportunities</h3>
<p>Where you're already close to ranking, and what's working quietly.</p>
</div>
</div>
<span class="acc-chev">▾</span>
</summary>
<div class="acc-body">
<div class="grid">

<a class="card @if(!$gscReady) card-gated @endif" href="{{ $gscReady ? route('generate.direct', 'keyword_rankings') : '#' }}">
<h4>Keyword Rankings — Web</h4>
<p>Query × month heatmap. Top 50 keywords, last 13 months.</p>
<span class="badge">Search Console</span>
</a>

<a class="card @if(!$gscReady) card-gated @endif" href="{{ $gscReady ? route('generate.direct', 'keyword_rankings_news') : '#' }}">
<h4>Keyword Rankings — News</h4>
<p>Pivot for Top Stories / News tab only.</p>
<span class="badge">GSC · News</span>
</a>

<a class="card @if($gated) card-gated @endif" href="{{ $gated ? '#' : route('generate.direct', 'striking_distance') }}">
<h4>Striking-Distance Keywords</h4>
<p>Ranked 4-20 with high impressions — quick wins.</p>
<span class="badge">Search Console</span>
</a>

<a class="card card-cross @if($gated) card-gated @endif" href="{{ $gated ? '#' : route('generate.direct', 'silent_winners') }}">
<h4>Silent Winners</h4>
<p>Ranking well but barely clicked — title &amp; intent gaps.</p>
<span class="badge badge-cross">GA4 × GSC</span>
</a>

<a class="card card-cross @if($gated) card-gated @endif" href="{{ $gated ? '#' : route('generate.direct', 'converting_queries') }}">
<h4>Converting Queries Slipping</h4>
<p>Revenue pages losing Google rank.</p>
<span class="badge badge-cross">GA4 × GSC</span>
</a>

</div>
</div>
</details>

{{-- ====== DIAGNOSE ====== --}}
<details class="acc-section acc-diagnose">
<summary>
<div class="acc-title">
<span class="acc-icon">🩺</span>
<div class="acc-text">
<h3>Diagnose — find problems</h3>
<p>What's broken, decaying, leaking, or fighting itself.</p>
</div>
</div>
<span class="acc-chev">▾</span>
</summary>
<div class="acc-body">
<div class="grid">

<a class="card @if($gated) card-gated @endif" href="{{ $gated ? '#' : route('generate.direct', 'content_decay') }}">
<h4>Content Decay</h4>
<p>Pages losing traffic and by how much.</p>
<span class="badge">GA4</span>
</a>

<a class="card card-cross @if($gated) card-gated @endif" href="{{ $gated ? '#' : route('generate.direct', 'cannibalization') }}">
<h4>Cannibalization Detector</h4>
<p>Multiple URLs fighting for same query.</p>
<span class="badge badge-cross">GA4 × GSC</span>
</a>

<a class="card @if($gated) card-gated @endif" href="{{ $gated ? '#' : route('generate.direct', 'conversion_leak') }}">
<h4>Conversion Leak</h4>
<p>High-traffic pages not converting.</p>
<span class="badge">GA4</span>
</a>

<a class="card card-cross @if($gated) card-gated @endif" href="{{ $gated ? '#' : route('generate.direct', 'brand_rescue') }}">
<h4>Brand Rescue vs Real Growth</h4>
<p>Is brand traffic masking non-brand decay?</p>
<span class="badge badge-cross">GA4 × GSC</span>
</a>

<a class="card @if($gated) card-gated @endif" href="{{ $gated ? '#' : route('generate.direct', 'anomaly') }}">
<h4>Weekly Anomaly Scan</h4>
<p>Metrics that moved &gt;20% this week.</p>
<span class="badge">GA4 + GSC</span>
</a>

</div>
</div>
</details>

{{-- ====== TRACK ====== --}}
<details class="acc-section acc-track">
<summary>
<div class="acc-title">
<span class="acc-icon">📊</span>
<div class="acc-text">
<h3>Track — monitor signals</h3>
<p>What's changing over time. Brand mix, AI visibility, new referrers.</p>
</div>
</div>
<span class="acc-chev">▾</span>
</summary>
<div class="acc-body">
<div class="grid">

<a class="card @if($gated) card-gated @endif" href="{{ $gated ? '#' : route('generate.direct', 'brand_split') }}">
<h4>Brand vs Non-Brand</h4>
<p>Split queries by brand vs non-brand.</p>
<span class="badge">Search Console</span>
</a>

<a class="card card-llm @if(!$ga4Ready) card-gated @endif" href="{{ $ga4Ready ? route('generate.direct', 'llm_traffic') : '#' }}">
<h4>LLM Traffic <span class="tag-new">NEW</span></h4>
<p>Visitors from ChatGPT, Perplexity, Claude, Gemini, Copilot. How much, which AI, what pages.</p>
<span class="badge badge-llm">GA4 · AI Referrals</span>
</a>

<a class="card card-green @if(!$ga4Ready) card-gated @endif" href="{{ $ga4Ready ? route('generate.direct', 'new_referrers') : '#' }}">
<h4>New Referring Domains <span class="tag-new">NEW</span></h4>
<p>Domains that started sending traffic in last 30 days. New backlinks &amp; mentions caught early.</p>
<span class="badge badge-green">GA4 · Referrals</span>
</a>

</div>
</div>
</details>

{{-- ====== BOOST ====== --}}
<details class="acc-section acc-boost">
<summary>
<div class="acc-title">
<span class="acc-icon">⚡</span>
<div class="acc-text">
<h3>Boost — fast-index your pages <span class="tag-new">NEW</span></h3>
<p>Submit URLs to Bing, Yandex, Brave, Google + auto-generate llms.txt for AI crawlers.</p>
</div>
</div>
<span class="acc-chev">▾</span>
</summary>
<div class="acc-body">
<div class="grid">

<a class="card card-boost" href="{{ route('boost.form') }}">
<h4>⚡ Boost Visibility</h4>
<p>Submit a URL to every indexing channel that exists. Bing → ChatGPT/Copilot. Brave → Claude. Google Indexing API. llms.txt for AI crawlers.</p>
<span class="badge" style="background:#ffedd5;color:#9a3412">IndexNow + Indexing API + llms.txt</span>
</a>

</div>
</div>
</details>

</div>

{{-- ============ RECENT REPORTS ============ --}}
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

@include('partials.footer')

</body>
</html>
