<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Boost result — {{ parse_url($sub->url, PHP_URL_HOST) }}</title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;max-width:880px;margin:0 auto;padding:20px;color:#222;line-height:1.55;background:#fafbfc}
.topbar{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #eee;margin-bottom:1.5em}
.brand{font-weight:700;color:#1a73e8;font-size:1.15rem;text-decoration:none}
.back{color:#666;text-decoration:none;font-size:.9rem}
.back:hover{color:#1a73e8}
h1{font-size:1.6rem;margin:.2em 0 .15em;color:#0f172a;word-break:break-all}
.sub{color:#64748b;font-size:.95rem;margin-bottom:1.6em}
.section{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:18px 22px;margin-bottom:1em}
.section h3{margin:0 0 .5em;font-size:1.05rem;color:#0f172a;display:flex;align-items:center;gap:8px}
.status-pill{font-size:.78rem;padding:3px 10px;border-radius:999px;font-weight:600}
.status-ok{background:#dcfce7;color:#15803d}
.status-warn{background:#fef3c7;color:#92400e}
.status-fail{background:#fee2e2;color:#991b1b}
.engines{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:8px;margin-top:.6em}
.engine{padding:10px;border:1px solid #e2e8f0;border-radius:8px;text-align:center;font-size:.85rem;background:#f8fafc}
.engine-name{font-weight:600;color:#475569;margin-bottom:3px}
.engine-status{font-size:.78rem}
.kv{display:grid;grid-template-columns:140px 1fr;gap:8px;font-size:.88rem;margin-top:.4em}
.kv dt{color:#64748b;font-weight:500}
.kv dd{margin:0;color:#0f172a;font-family:'SF Mono',Monaco,Consolas,monospace;font-size:.82rem;word-break:break-all}
.btn{display:inline-block;padding:8px 16px;background:#1a73e8;color:#fff;border-radius:6px;text-decoration:none;font-size:.88rem;font-weight:500;margin-top:.5em;margin-right:6px}
.btn:hover{background:#1557b8}
.btn-secondary{background:#e2e8f0;color:#0f172a}
.btn-secondary:hover{background:#cbd5e1}
.code-block{background:#0f172a;color:#e2e8f0;padding:12px 14px;border-radius:8px;font-family:'SF Mono',Monaco,Consolas,monospace;font-size:.8rem;overflow-x:auto;margin-top:.5em;white-space:pre-wrap}
.steps{margin:0;padding-left:1.4em;font-size:.9rem;color:#334155}
.steps li{margin-bottom:.4em}
.followup{background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);border:1px solid #93c5fd;color:#1e40af;padding:14px 18px;border-radius:10px;font-size:.9rem;margin-bottom:1.4em}
.followup h3{margin:0 0 .3em;color:#1e3a8a;font-size:1rem}
</style>
</head>
<body>

<div class="topbar">
<a href="/" class="brand">SEOKRU Analytics</a>
<a href="{{ route('boost.form') }}" class="back">← Boost another URL</a>
</div>

<h1>{{ $sub->url }}</h1>
<p class="sub">Boosted {{ $sub->created_at->diffForHumans() }} · Domain <code>{{ $sub->domain }}</code></p>

<div class="followup">
<h3>📅 Follow-up checks scheduled</h3>
We'll re-check Google index status at 24h, 72h, and 7 days. Check back here for the verdict.
</div>

{{-- IndexNow --}}
@if($sub->indexnow_result)
@php $in = $sub->indexnow_result; @endphp
<div class="section">
<h3>
⚡ IndexNow ping
@php $okCount = collect($in['engines'] ?? [])->filter(fn($e) => $e['ok'] ?? false)->count(); @endphp
<span class="status-pill {{ $okCount > 0 ? 'status-ok' : 'status-fail' }}">{{ $okCount }}/{{ count($in['engines'] ?? []) }} engines accepted</span>
</h3>

@if(!($in['key_file_installed'] ?? false))
<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:10px 14px;font-size:.88rem;color:#78350f;margin:.6em 0">
<strong>⚠ Key file not installed yet.</strong> IndexNow rejects pings until you upload a verification file:
<ol class="steps" style="margin-top:.5em">
<li>Download the key file: <a href="{{ route('boost.indexnow.key', $sub) }}" class="btn btn-secondary" style="margin-top:0;padding:4px 10px;font-size:.82rem">Download {{ $in['key'] }}.txt</a></li>
<li>Upload to your site root so it's reachable at: <code>{{ $in['key_location'] }}</code></li>
<li>Re-boost this URL once installed.</li>
</ol>
</div>
@else
<p style="font-size:.88rem;color:#15803d;margin:.4em 0">✓ Key file verified at {{ $in['key_location'] }}</p>
@endif

<div class="engines">
@foreach($in['engines'] ?? [] as $name => $r)
<div class="engine">
<div class="engine-name">{{ ucfirst($name) }}</div>
<div class="engine-status">
@if($r['ok'] ?? false)
<span class="status-pill status-ok">{{ $r['status'] }}</span>
@else
<span class="status-pill status-fail">{{ $r['status'] ?? 'err' }}</span>
@endif
</div>
</div>
@endforeach
</div>
<p style="font-size:.82rem;color:#64748b;margin-top:.8em">Bing index → ChatGPT, Copilot. Yep/Brave → Brave Search → Claude web search.</p>
</div>
@endif

{{-- Indexing API --}}
@if($sub->indexing_api_result)
@php $ia = $sub->indexing_api_result; @endphp
<div class="section">
<h3>
🔍 Google Indexing API
<span class="status-pill {{ ($ia['ok'] ?? false) ? 'status-ok' : 'status-fail' }}">{{ ($ia['ok'] ?? false) ? 'Submitted' : 'Failed' }}</span>
</h3>
@if($ia['ok'] ?? false)
<p style="font-size:.88rem;color:#334155">URL update notification sent to Google. Officially for JobPosting/VideoObject; for other URLs Google may ignore but often crawls.</p>
<dl class="kv">
<dt>Status</dt><dd>HTTP {{ $ia['status'] ?? '—' }}</dd>
@if(data_get($ia, 'body.urlNotificationMetadata.url'))
<dt>URL</dt><dd>{{ data_get($ia, 'body.urlNotificationMetadata.url') }}</dd>
<dt>Latest update</dt><dd>{{ data_get($ia, 'body.urlNotificationMetadata.latestUpdate.notifyTime', '—') }}</dd>
@endif
</dl>
@else
@php $iaMsg = $ia['reason'] ?? $ia['error'] ?? data_get($ia, 'body.error.message') ?? 'Unknown error (HTTP ' . ($ia['status'] ?? '?') . ')'; @endphp
<p style="font-size:.88rem;color:#991b1b">{{ $iaMsg }}</p>
@if(data_get($ia, 'body.error.status'))
<p style="font-size:.78rem;color:#64748b;font-family:monospace">{{ data_get($ia, 'body.error.status') }}</p>
@endif
@if(!($conn ?? false))
<a href="/auth/google" class="btn">Connect Google to enable</a>
@else
<p style="font-size:.82rem;color:#64748b">If you connected before, you may need to reconnect to grant the new Indexing API scope.</p>
<a href="/auth/google" class="btn">Reconnect Google</a>
@endif
@endif
</div>
@endif

{{-- Wayback Machine --}}
@if($sub->wayback_result)
@php $wb = $sub->wayback_result; @endphp
<div class="section">
<h3>
📚 Wayback Machine snapshot
<span class="status-pill {{ ($wb['ok'] ?? false) ? 'status-ok' : 'status-fail' }}">{{ ($wb['ok'] ?? false) ? 'Saved' : 'Failed' }}</span>
</h3>
@if($wb['snapshot_url'] ?? null)
<p style="font-size:.88rem;color:#334155">Persistent snapshot saved at archive.org. Indexable by Google/Bing.</p>
<a href="{{ $wb['snapshot_url'] }}" target="_blank" class="btn">View snapshot →</a>
<dl class="kv"><dt>Archive URL</dt><dd>{{ $wb['snapshot_url'] }}</dd></dl>
@else
<p style="font-size:.88rem;color:#991b1b">{{ $wb['error'] ?? 'Could not capture snapshot. Wayback may rate-limit; retry later.' }}</p>
@endif
</div>
@endif

{{-- Archive.today --}}
@if($sub->archive_today_result)
@php $at = $sub->archive_today_result; @endphp
<div class="section">
<h3>
🗄 Archive.today snapshot
<span class="status-pill {{ ($at['ok'] ?? false) ? 'status-ok' : 'status-fail' }}">{{ ($at['ok'] ?? false) ? 'Saved' : 'Failed' }}</span>
</h3>
@if($at['snapshot_url'] ?? null)
<p style="font-size:.88rem;color:#334155">Alternate persistent archive (archive.ph). Often captures pages Wayback can't.</p>
<a href="{{ $at['snapshot_url'] }}" target="_blank" class="btn">View snapshot →</a>
<dl class="kv"><dt>Archive URL</dt><dd>{{ $at['snapshot_url'] }}</dd></dl>
@else
<p style="font-size:.88rem;color:#991b1b">{{ $at['error'] ?? 'Capture failed (HTTP ' . ($at['status'] ?? '?') . '). archive.ph may be rate-limiting.' }}</p>
@endif
</div>
@endif

{{-- GitHub Gist --}}
@if($sub->gist_result)
@php $g = $sub->gist_result; @endphp
<div class="section">
<h3>
🐙 GitHub Gist
<span class="status-pill {{ ($g['ok'] ?? false) ? 'status-ok' : 'status-warn' }}">{{ ($g['ok'] ?? false) ? 'Published' : ($g['reason'] ?? 'Skipped') }}</span>
</h3>
@if($g['gist_url'] ?? null)
<p style="font-size:.88rem;color:#334155">Public gist created with link to your URL. github.com is crawled by Google in minutes.</p>
<a href="{{ $g['gist_url'] }}" target="_blank" class="btn">View gist →</a>
@elseif(!($g['ok'] ?? false))
<p style="font-size:.88rem;color:#78350f">{{ $g['reason'] ?? $g['error'] ?? 'Failed.' }}</p>
@endif
</div>
@endif

{{-- Bluesky --}}
@if($sub->bluesky_result)
@php $bs = $sub->bluesky_result; @endphp
<div class="section">
<h3>
🦋 Bluesky post
<span class="status-pill {{ ($bs['ok'] ?? false) ? 'status-ok' : 'status-warn' }}">{{ ($bs['ok'] ?? false) ? 'Posted' : ($bs['reason'] ?? 'Skipped') }}</span>
</h3>
@if($bs['post_url'] ?? null)
<p style="font-size:.88rem;color:#334155">Posted on Bluesky. Posts indexed by Google + Bing.</p>
<a href="{{ $bs['post_url'] }}" target="_blank" class="btn">View post →</a>
@elseif(!($bs['ok'] ?? false))
<p style="font-size:.88rem;color:#78350f">{{ $bs['reason'] ?? $bs['error'] ?? 'Failed.' }}</p>
@endif
</div>
@endif

{{-- Telegram --}}
@if($sub->telegram_result)
@php $tg = $sub->telegram_result; @endphp
<div class="section">
<h3>
✈ Telegram channel post
<span class="status-pill {{ ($tg['ok'] ?? false) ? 'status-ok' : 'status-warn' }}">{{ ($tg['ok'] ?? false) ? 'Posted' : ($tg['reason'] ?? 'Skipped') }}</span>
</h3>
@if($tg['post_url'] ?? null)
<p style="font-size:.88rem;color:#334155">Posted to public Telegram channel. t.me/ pages indexed by Google.</p>
<a href="{{ $tg['post_url'] }}" target="_blank" class="btn">View post →</a>
@elseif(!($tg['ok'] ?? false))
<p style="font-size:.88rem;color:#78350f">{{ $tg['reason'] ?? 'Failed.' }}</p>
@endif
</div>
@endif

{{-- WebSub --}}
@if($sub->websub_result)
@php $ws = $sub->websub_result; @endphp
<div class="section">
<h3>
📡 WebSub feed ping
<span class="status-pill {{ ($ws['ok'] ?? false) ? 'status-ok' : 'status-warn' }}">{{ ($ws['ok'] ?? false) ? 'Pinged' : 'Skipped' }}</span>
</h3>
@if($ws['ok'] ?? false)
<p style="font-size:.88rem;color:#334155">Pinged Google's pubsubhubbub hub for feed: <code>{{ $ws['feed_url'] ?? '' }}</code></p>
<dl class="kv">
@foreach($ws['hubs'] ?? [] as $name => $h)
<dt>{{ ucfirst($name) }}</dt><dd>HTTP {{ $h['status'] ?? '?' }}{{ ($h['ok'] ?? false) ? ' ✓' : '' }}</dd>
@endforeach
</dl>
@else
<p style="font-size:.88rem;color:#78350f">{{ $ws['reason'] ?? 'No RSS/Atom feed detected on this domain. WebSub skipped.' }}</p>
@endif
</div>
@endif

{{-- Inspection follow-ups --}}
@if($sub->inspection_24h || $sub->inspection_72h || $sub->inspection_7d)
<div class="section">
<h3>📊 Index status follow-up</h3>
@foreach(['inspection_24h' => '24 hours', 'inspection_72h' => '72 hours', 'inspection_7d' => '7 days'] as $slot => $label)
@if($sub->{$slot})
@php
$verdict = data_get($sub->{$slot}, 'body.inspectionResult.indexStatusResult.verdict', 'PENDING');
$cov = data_get($sub->{$slot}, 'body.inspectionResult.indexStatusResult.coverageState', '');
@endphp
<div style="padding:8px 0;border-bottom:1px solid #f1f5f9">
<strong style="color:#475569;font-size:.88rem">{{ $label }}:</strong>
<span class="status-pill {{ $verdict === 'PASS' ? 'status-ok' : ($verdict === 'FAIL' ? 'status-fail' : 'status-warn') }}">{{ $verdict }}</span>
@if($cov)<span style="font-size:.82rem;color:#64748b;margin-left:6px">{{ $cov }}</span>@endif
</div>
@endif
@endforeach
</div>
@endif

</body>
</html>
