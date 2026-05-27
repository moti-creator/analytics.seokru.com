<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<title>Boost Visibility — SEOKRU Analytics</title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;max-width:880px;margin:0 auto;padding:20px;color:#222;line-height:1.55;background:#fafbfc}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #eee;margin-bottom:1.5em}
.brand{font-weight:700;color:#1a73e8;font-size:1.15rem;text-decoration:none}
.back{color:#666;text-decoration:none;font-size:.9rem}
.back:hover{color:#1a73e8}
h1{font-size:1.8rem;margin:.2em 0 .15em;color:#0f172a}
.sub{color:#64748b;font-size:1.05rem;margin-bottom:1.6em}
.hero{background:linear-gradient(135deg,#fff7ed 0%,#ffedd5 100%);border:2px solid #ea580c;border-radius:14px;padding:24px 28px;margin-bottom:1.8em}
.hero h2{margin:0 0 .25em;color:#9a3412;font-size:1.2rem;display:flex;align-items:center;gap:10px}
.hero p{margin:0 0 1em;color:#7c2d12;font-size:.95rem}
.hero-icon{width:32px;height:32px;background:#ea580c;color:#fff;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem}
form.boost-form{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:22px;margin-bottom:1.6em}
form.boost-form label.url-label{display:block;font-weight:600;color:#334155;margin-bottom:.5em;font-size:.95rem}
form.boost-form input[type=url]{width:100%;padding:12px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:1rem;font-family:inherit}
form.boost-form input[type=url]:focus{outline:none;border-color:#ea580c;box-shadow:0 0 0 3px rgba(234,88,12,.15)}
.channels{margin:1.2em 0;display:grid;gap:10px}
.channel{border:1px solid #e2e8f0;border-radius:8px;padding:12px 14px;display:flex;gap:12px;align-items:flex-start;cursor:pointer;transition:all .15s;background:#fff}
.channel:hover{border-color:#ea580c;background:#fff7ed}
.channel input{margin-top:3px}
.channel-info{flex:1}
.channel-name{font-weight:600;color:#0f172a;font-size:.95rem;margin-bottom:2px}
.channel-desc{color:#64748b;font-size:.85rem;line-height:1.45}
.tier-badge{display:inline-block;font-size:.7rem;font-weight:700;padding:2px 7px;border-radius:4px;margin-left:6px;vertical-align:middle}
.tier-free{background:#dcfce7;color:#15803d}
.tier-pro{background:#fef3c7;color:#92400e}
.tier-soon{background:#e0e7ff;color:#4338ca}
button.submit{background:#ea580c;color:#fff;border:0;padding:12px 28px;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;width:100%}
button.submit:hover{background:#c2410c}
.errors{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 14px;border-radius:8px;margin-bottom:1em;font-size:.9rem}
.recent{margin-top:2em}
.recent h3{font-size:1rem;color:#475569;margin-bottom:.6em}
.recent-list{list-style:none;padding:0;margin:0}
.recent-list li{padding:10px 14px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;background:#fff;font-size:.88rem}
.recent-list a{color:#ea580c;text-decoration:none;font-weight:500}
.recent-list a:hover{text-decoration:underline}
.recent-list .meta{color:#94a3b8;font-size:.8rem}
.notice{background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px;font-size:.85rem;color:#78350f;margin-top:1em}
</style>
</head>
<body>

<div class="topbar">
<a href="/" class="brand">SEOKRU Analytics</a>
<a href="/" class="back">← Back to dashboard</a>
</div>

<h1>Boost Visibility</h1>
<p class="sub">Submit a URL to indexing channels + create persistent archive snapshots. Faster Google + Bing indexing. AI search visibility (ChatGPT, Claude, Copilot) via Bing.</p>

<div class="hero">
<h2><span class="hero-icon">⚡</span> What happens when you click Boost</h2>
<p>We ping every legit indexing channel: IndexNow (Bing/Yandex/Naver fan-out → ChatGPT, Copilot, Claude), Google's Indexing API, plus snapshot the page on Wayback Machine + Archive.today (persistent indexable URLs that act like pseudo-backlinks). WebSub pings the feed hub if your site has RSS. Then we monitor index status at 24h, 72h, 7d.</p>
</div>

@if($errors->any())
<div class="errors">
@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
</div>
@endif

<form method="post" action="{{ route('boost.submit') }}" class="boost-form">
@csrf
<label class="url-label" for="url">Page URL to boost</label>
<input type="url" name="url" id="url" placeholder="https://example.com/page-to-index" value="{{ old('url') }}" required>

<div class="channels">

<label class="channel">
<input type="checkbox" name="indexnow" value="1" checked>
<div class="channel-info">
<div class="channel-name">IndexNow protocol <span class="tier-badge tier-free">Free</span></div>
<div class="channel-desc">Pings Bing, Yandex, Yep, Seznam, Naver. Bing index feeds ChatGPT, Copilot. Brave Search supports IndexNow → Claude web search. Requires uploading a verification key file to your domain (we provide).</div>
</div>
</label>

<label class="channel">
<input type="checkbox" name="indexing_api" value="1" {{ $conn ? 'checked' : '' }} {{ $conn ? '' : 'disabled' }}>
<div class="channel-info">
<div class="channel-name">Google Indexing API <span class="tier-badge tier-pro">{{ $conn ? 'Ready' : 'Connect Google' }}</span></div>
<div class="channel-desc">Officially for JobPosting/VideoObject pages, but nudges crawl for any URL. Requires Google connection (Owner-level GSC access). 200 publish requests/day.</div>
</div>
</label>

<label class="channel">
<input type="checkbox" name="wayback" value="1" checked>
<div class="channel-info">
<div class="channel-name">Wayback Machine snapshot <span class="tier-badge tier-free">Free</span></div>
<div class="channel-desc">Creates a permanent archive at <code>web.archive.org/web/{url}</code>. The snapshot is publicly indexable, crawled by Google + Bing, cited by some LLMs. Acts like a persistent backlink.</div>
</div>
</label>

<label class="channel">
<input type="checkbox" name="archive_today" value="1" checked>
<div class="channel-info">
<div class="channel-name">Archive.today snapshot <span class="tier-badge tier-free">Free</span></div>
<div class="channel-desc">Alternate web archive at <code>archive.ph/{url}</code>. Captures pages Wayback can't (heavy JS, paywalled). Another persistent indexable URL.</div>
</div>
</label>

<label class="channel">
<input type="checkbox" name="gist" value="1" checked>
<div class="channel-info">
<div class="channel-name">GitHub Gist <span class="tier-badge tier-pro">Real backlink</span></div>
<div class="channel-desc">Auto-creates a public gist on github.com with the URL. github.com crawled by Google in minutes — gist becomes a real, persistent backlink. Requires GITHUB_GIST_TOKEN env.</div>
</div>
</label>

<label class="channel">
<input type="checkbox" name="bluesky" value="1" checked>
<div class="channel-info">
<div class="channel-name">Bluesky post <span class="tier-badge tier-pro">Real backlink</span></div>
<div class="channel-desc">Posts on Bluesky via app password. Bluesky posts are crawled by Google + Bing within hours. Requires BLUESKY_HANDLE + BLUESKY_APP_PASSWORD.</div>
</div>
</label>

<label class="channel">
<input type="checkbox" name="telegram" value="1" checked>
<div class="channel-info">
<div class="channel-name">Telegram public channel <span class="tier-badge tier-pro">Real backlink</span></div>
<div class="channel-desc">Posts URL to a public Telegram channel via bot. t.me/{channel}/{id} pages are indexed by Google. Requires TELEGRAM_BOOST_CHANNEL env.</div>
</div>
</label>

<label class="channel">
<input type="checkbox" name="websub" value="1" checked>
<div class="channel-info">
<div class="channel-name">WebSub feed ping <span class="tier-badge tier-free">Free</span></div>
<div class="channel-desc">Pings Google's pubsubhubbub hub if your site has an RSS/Atom feed. Triggers Google to re-fetch the feed — surfaces new posts faster. Auto-skipped if no feed found.</div>
</div>
</label>

<label class="channel" style="opacity:.6">
<input type="checkbox" name="reddit" value="1" disabled>
<div class="channel-info">
<div class="channel-name">Reddit Seed (relevant subreddits) <span class="tier-badge tier-soon">Soon</span></div>
<div class="channel-desc">AI picks 1-3 relevant subreddits, drafts native posts, you approve + submit via your Reddit account. Reddit pages crawl fast and feed ChatGPT citations heavily.</div>
</div>
</label>

</div>

<button type="submit" class="submit">⚡ Boost This URL</button>
</form>

<div class="notice">
<strong>Rate limits:</strong> {{ \App\Services\BoostService::MAX_PER_USER_PER_WEEK }} URLs / 7 days per account · {{ \App\Services\BoostService::MAX_PER_DOMAIN_PER_DAY }} URLs / 24h per domain. Prevents abuse.
</div>

@if($recent->count())
<div class="recent">
<h3>Your recent boosts</h3>
<ul class="recent-list">
@foreach($recent as $r)
<li>
<a href="{{ route('boost.show', $r) }}">{{ \Illuminate\Support\Str::limit($r->url, 70) }}</a>
<span class="meta">{{ $r->created_at->diffForHumans() }} · @if($r->indexed === true) ✅ indexed @elseif($r->indexed === false) ⏳ not yet @else pending @endif</span>
</li>
@endforeach
</ul>
</div>
@endif

</body>
</html>
