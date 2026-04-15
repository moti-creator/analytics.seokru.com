<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Weekly Report</title>
<style>
body{font-family:system-ui,Arial,sans-serif;max-width:760px;margin:40px auto;padding:20px;color:#222;line-height:1.6}
h1{border-bottom:2px solid #1a73e8;padding-bottom:.3em}
h2{color:#1a73e8;margin-top:2em}
.meta{color:#666;font-size:.9rem}
.btn{display:inline-block;background:#1a73e8;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;margin:1em 0}
</style></head>
<body>
<h1>Weekly Analytics Report</h1>
<p class="meta">
{{ $report->connection->email }} · 
GA4: {{ $report->connection->ga4_property_id }} · 
GSC: {{ $report->connection->gsc_site_url }} · 
Generated {{ $report->created_at->format('M j, Y H:i') }}
</p>

<a href="{{ route('report.pdf', $report->id) }}" class="btn">Download PDF</a>

{!! $report->narrative !!}
</body>
</html>
