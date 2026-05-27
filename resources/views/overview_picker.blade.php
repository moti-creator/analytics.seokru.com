<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<title>Site Overview — Pick Site</title>
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<style>
body { font-family: system-ui, Arial, sans-serif; max-width: 980px; margin: 30px auto; padding: 20px; color: #222; line-height: 1.5; }
h1 { border-bottom: 2px solid #1a73e8; padding-bottom: .3em; }
.intro { color: #555; font-size: .95rem; margin-bottom: 1.5em; }
table { width: 100%; border-collapse: collapse; margin: 1em 0; }
th, td { padding: 10px 12px; border-bottom: 1px solid #eee; text-align: left; vertical-align: middle; }
th { background: #f5f8ff; color: #555; font-size: .82rem; text-transform: uppercase; letter-spacing: .4px; }
.site-url { font-family: ui-monospace, monospace; font-size: .85rem; color: #1a73e8; }
.site-label { font-weight: 600; font-size: 1rem; }
select { padding: 6px 8px; border: 1px solid #ccd; border-radius: 6px; font-size: .9rem; min-width: 240px; max-width: 320px; }
.btn { background: #1a73e8; color: #fff; padding: 8px 18px; border-radius: 6px; border: 0; cursor: pointer; font-size: .92rem; font-weight: 600; }
.btn:hover { background: #1557b0; }
.btn-back { background: #fff; color: #1a73e8; border: 1px solid #1a73e8; padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: .85rem; display: inline-block; margin-bottom: 1em; }
.empty { color: #999; font-style: italic; padding: 2em; text-align: center; }
.match-hint { color: #6b7280; font-size: .78rem; margin-left: 4px; }
.no-match { color: #dc2626; font-size: .8rem; }
form { margin: 0; }
.row-form { display: flex; gap: 8px; align-items: center; }
.host-row { background: #fafbfc; }
.host-row .host-label { font-size: .88rem; color: #555; padding-left: 22px; position: relative; }
.host-row .host-label::before { content: "↳"; position: absolute; left: 6px; color: #888; }
.host-sessions { color: #6b7280; font-size: .78rem; margin-left: 6px; }
</style>
</head>
<body>
<a href="/" class="btn-back">← Back</a>
<h1>Site Overview — Pick a Site</h1>
<p class="intro">Choose a site to run the Pareto dashboard. Each row pairs a Search Console site with a matching GA4 property (auto-detected by domain). Override the GA4 picker if the match looks wrong.</p>

@if(empty($pairs))
<p class="empty">No Search Console sites found. Make sure the Google account you signed in with has GSC access.</p>
@else
<table>
<thead>
<tr>
<th>Site (Search Console)</th>
<th>GA4 Property</th>
<th></th>
</tr>
</thead>
<tbody>
@foreach($pairs as $i => $pair)
<tr>
<td>
<div class="site-label">{{ $pair['site_label'] }}</div>
<div class="site-url">{{ $pair['site'] }}</div>
</td>
<td>
<form method="POST" action="{{ route('overview.run') }}" id="form-{{ $i }}">
@csrf
<input type="hidden" name="gsc_site_url" value="{{ $pair['site'] }}">
<select name="ga4_property_id" required>
<option value="">— pick GA4 property —</option>
@foreach($properties as $p)
<option value="{{ $p['id'] }}" @if($p['id'] === $pair['ga4_match']) selected @endif>
{{ $p['name'] }}{{ $p['id'] === $pair['ga4_match'] ? ' (auto-match)' : '' }}
</option>
@endforeach
</select>
@if(!$pair['ga4_match'])
<div class="no-match">No GA4 match by domain — pick manually.</div>
@endif
</form>
</td>
<td>
<button type="submit" form="form-{{ $i }}" class="btn">Generate (all hosts)</button>
</td>
</tr>
@if(!empty($pair['hostnames']))
@foreach($pair['hostnames'] as $j => $hn)
<tr class="host-row">
<td>
<div class="host-label">{{ $hn['host'] }}<span class="host-sessions">{{ number_format($hn['sessions']) }} sessions / 30d</span></div>
</td>
<td>
<form method="POST" action="{{ route('overview.run') }}" id="form-{{ $i }}-host-{{ $j }}">
@csrf
<input type="hidden" name="gsc_site_url" value="{{ $hn['gsc_site_url'] ?? $pair['site'] }}">
<input type="hidden" name="ga4_property_id" value="{{ $pair['ga4_match'] }}">
<input type="hidden" name="host_filter" value="{{ $hn['host'] }}">
<span style="color:#6b7280;font-size:.82rem">GSC: <code>{{ $hn['gsc_site_url'] ?? $pair['site'] }}</code></span>
</form>
</td>
<td>
<button type="submit" form="form-{{ $i }}-host-{{ $j }}" class="btn" style="background:#10b981">Generate</button>
</td>
</tr>
@endforeach
@endif
@endforeach
</tbody>
</table>
@endif

@if(empty($properties))
<p class="empty">No GA4 properties available for this Google account.</p>
@endif
</body>
</html>
