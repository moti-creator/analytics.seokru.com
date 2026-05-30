<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>כל הקמפיינים — קושקה</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,sans-serif;background:#f0f2f5;color:#1c1e21;line-height:1.5;font-size:15px}
.topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:10}
.brand{font-weight:700;font-size:1rem;display:flex;align-items:center;gap:8px}
.brand a{color:#1a73e8;text-decoration:none;font-size:.85rem;font-weight:500}
.user{font-size:.78rem;color:#666}
.wrap{max-width:1000px;margin:0 auto;padding:14px}
.flash{background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:.88rem}
.error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:.88rem}

.bulk{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap}
.bulk button,.bulk .btn{background:#fff;border:1px solid #ddd;border-radius:8px;padding:8px 14px;font-size:.85rem;cursor:pointer;color:#1c1e21;font-family:inherit;text-decoration:none}
.bulk button:hover,.bulk .btn:hover{background:#f0f4ff;border-color:#1a73e8;color:#1a73e8}

.campaign{background:#fff;border-radius:12px;margin-bottom:12px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.campaign-head{padding:12px 16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.campaign-name{font-weight:600;font-size:.95rem;display:flex;align-items:center;gap:8px;flex:1;min-width:200px}
.badge{padding:3px 8px;border-radius:6px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.03em}
.badge.active{background:#d4edda;color:#155724}
.badge.paused{background:#fff3cd;color:#856404}
.badge.archived,.badge.deleted{background:#e9ecef;color:#6c757d}

.campaign-stats{display:flex;gap:14px;flex-wrap:wrap;padding:0 16px 12px;font-size:.82rem;color:#555}
.campaign-stats > span{display:flex;flex-direction:column;align-items:flex-start}
.campaign-stats .k{font-size:.7rem;color:#999;text-transform:uppercase;letter-spacing:.03em}
.campaign-stats .v{font-weight:600;color:#1c1e21;font-size:.92rem}

.campaign-actions{display:flex;gap:6px;padding:10px 16px;background:#fafbfc;border-top:1px solid #eee;flex-wrap:wrap}
.act{background:#fff;border:1px solid #ddd;border-radius:6px;padding:6px 10px;font-size:.8rem;cursor:pointer;color:#1c1e21;font-family:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.act:hover{background:#f0f4ff;border-color:#1a73e8;color:#1a73e8}
.act.danger:hover{background:#fff3f3;border-color:#dc3545;color:#dc3545}
.act.primary{background:#1a73e8;color:#fff;border-color:#1a73e8}

.adsets{border-top:1px solid #eee;background:#fafbfc;padding:12px 16px}
.adsets h4{font-size:.85rem;color:#666;margin-bottom:10px;text-transform:uppercase}
.adset{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:10px 12px;margin-bottom:8px}
.adset-name{font-weight:600;font-size:.88rem;margin-bottom:6px;display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap}
.adset-row{display:flex;gap:8px;flex-wrap:wrap;align-items:center;font-size:.82rem;color:#666;margin-top:4px}
.adset-row form{display:inline-flex;gap:4px;align-items:center}
.adset-row input[type=number],.adset-row input[type=date]{padding:4px 6px;border:1px solid #ccc;border-radius:5px;font-size:.82rem;width:80px;font-family:inherit}
.adset-row input[type=date]{width:130px}
.adset-row select{padding:4px 6px;border:1px solid #ccc;border-radius:5px;font-size:.82rem;font-family:inherit}
.mini-btn{background:#1a73e8;color:#fff;border:none;border-radius:5px;padding:5px 10px;font-size:.78rem;cursor:pointer;font-family:inherit}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand"><a href="/koshka">← חזרה</a><span>כל הקמפיינים</span></div>
  <div class="user">{{ $userEmail }}</div>
</div>

<div class="wrap">

@if($flash ?? null)<div class="flash">✓ {{ $flash }}</div>@endif
@if($error ?? null)<div class="error">{{ $error }}</div>@endif

<div class="bulk">
  <form method="POST" action="/koshka/campaigns/pause-all" onsubmit="return confirm('להשהות את כל הקמפיינים הפעילים?')">@csrf<button>⏸ השהיית כל הפעילים</button></form>
  <form method="POST" action="/koshka/campaigns/activate-all" onsubmit="return confirm('להפעיל את כל המושהים?')">@csrf<button>▶️ הפעלת כל המושהים</button></form>
  <a href="/koshka/all" class="btn">🔄 רענון</a>
</div>

@forelse($campaigns as $c)
<div class="campaign">
  <div class="campaign-head">
    <div class="campaign-name"><span class="badge {{ strtolower($c['status']) }}">{{ $c['status'] }}</span><span>{{ $c['name'] }}</span></div>
  </div>
  <div class="campaign-stats">
    <span><span class="k">הוצאה 7ימ׳</span><span class="v">₪{{ number_format($c['stats']['spend'], 0) }}</span></span>
    <span><span class="k">לידים</span><span class="v">{{ $c['stats']['leads'] }}</span></span>
    <span><span class="k">CPL</span><span class="v">{{ $c['cpl'] !== null ? '₪'.number_format($c['cpl'], 1) : '—' }}</span></span>
    <span><span class="k">CTR</span><span class="v">{{ number_format($c['stats']['ctr'], 2) }}%</span></span>
    <span><span class="k">קליקים</span><span class="v">{{ number_format($c['stats']['clicks']) }}</span></span>
    <span><span class="k">חשיפות</span><span class="v">{{ number_format($c['stats']['impressions']) }}</span></span>
  </div>
  <div class="campaign-actions">
    @if($c['status'] !== 'ACTIVE')
      <form method="POST" action="/koshka/campaign/{{ $c['id'] }}/status">@csrf<input type="hidden" name="status" value="ACTIVE"><button class="act primary">▶️ הפעל</button></form>
    @else
      <form method="POST" action="/koshka/campaign/{{ $c['id'] }}/status">@csrf<input type="hidden" name="status" value="PAUSED"><button class="act">⏸ השהה</button></form>
    @endif
    <a class="act" href="{{ ($expandCampaign ?? null) === $c['id'] ? '/koshka/all' : '/koshka/all?expand='.$c['id'] }}">{{ ($expandCampaign ?? null) === $c['id'] ? '▲ סגור' : '▼ Ad Sets' }}</a>
    <button class="act" onclick="renameCampaign('{{ $c['id'] }}', @js($c['name']))">✏️ שינוי שם</button>
    <form method="POST" action="/koshka/campaign/{{ $c['id'] }}/duplicate" onsubmit="return confirm('לשכפל?')">@csrf<button class="act">📋 שכפל</button></form>
    <form method="POST" action="/koshka/campaign/{{ $c['id'] }}/status" onsubmit="return confirm('להעביר לארכיון? פעולה זו לא הפיכה!')">@csrf<input type="hidden" name="status" value="ARCHIVED"><button class="act danger">🗄️ ארכיון</button></form>
  </div>
  @if(($expandCampaign ?? null) === $c['id'])
  <div class="adsets">
    <h4>Ad Sets ({{ count($expandedAdsets) }})</h4>
    @forelse($expandedAdsets as $as)
    <div class="adset">
      <div class="adset-name">
        <span><span class="badge {{ strtolower($as['status']) }}">{{ $as['status'] }}</span> {{ $as['name'] }}</span>
        @if($as['status'] !== 'ACTIVE')
          <form method="POST" action="/koshka/adset/{{ $as['id'] }}/status">@csrf<input type="hidden" name="status" value="ACTIVE"><button class="mini-btn">הפעל</button></form>
        @else
          <form method="POST" action="/koshka/adset/{{ $as['id'] }}/status">@csrf<input type="hidden" name="status" value="PAUSED"><button class="mini-btn" style="background:#666">השהה</button></form>
        @endif
      </div>
      <div class="adset-row">
        <form method="POST" action="/koshka/adset/{{ $as['id'] }}/budget">
          @csrf
          <span>תקציב:</span>
          <input type="number" name="amount" min="1" max="10000" step="1" value="{{ ($as['daily_budget'] ?? null) ? round($as['daily_budget']/100) : (($as['lifetime_budget'] ?? null) ? round($as['lifetime_budget']/100) : '') }}" placeholder="₪" required>
          <select name="type">
            <option value="daily" {{ ($as['daily_budget'] ?? null) ? 'selected' : '' }}>יומי</option>
            <option value="lifetime" {{ ($as['lifetime_budget'] ?? null) ? 'selected' : '' }}>חיים</option>
          </select>
          <button class="mini-btn">עדכן</button>
        </form>
      </div>
      <div class="adset-row">
        <form method="POST" action="/koshka/adset/{{ $as['id'] }}/schedule">
          @csrf
          <span>מ-</span>
          <input type="date" name="start_time" value="{{ !empty($as['start_time']) ? date('Y-m-d', strtotime($as['start_time'])) : '' }}">
          <span>עד</span>
          <input type="date" name="end_time" value="{{ !empty($as['end_time']) ? date('Y-m-d', strtotime($as['end_time'])) : '' }}">
          <button class="mini-btn">שמור</button>
        </form>
      </div>
      <div class="adset-row">
        <button class="mini-btn" style="background:#6c757d" onclick="loadAds('{{ $as['id'] }}', this)">👁️ הצג מודעות</button>
        <div class="ads-list" id="ads-{{ $as['id'] }}" style="display:none;width:100%;margin-top:8px"></div>
      </div>
    </div>
    @empty
    <div style="color:#888;font-size:.85rem">אין Ad Sets.</div>
    @endforelse
  </div>
  @endif
</div>
@empty
<div style="text-align:center;padding:40px;color:#888">אין קמפיינים.</div>
@endforelse

</div>

<script>
async function loadAds(adsetId, btn) {
  const container = document.getElementById('ads-' + adsetId);
  if (container.style.display === 'block') { container.style.display = 'none'; btn.textContent = '👁️ הצג מודעות'; return; }
  btn.disabled = true; btn.textContent = 'טוען...';
  try {
    const r = await fetch(`/koshka/adset/${adsetId}/ads`);
    const ads = await r.json();
    if (!ads.length) { container.innerHTML = '<div style="color:#888;font-size:.82rem">אין מודעות</div>'; }
    else {
      container.innerHTML = ads.map(a => `
        <div style="border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;margin-top:6px;background:#fff">
          <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap">
            <span style="font-size:.85rem"><span class="badge ${(a.effective_status||a.status||'').toLowerCase()}">${a.effective_status||a.status}</span> ${a.name}</span>
            <select onchange="loadPreview('${a.id}', this.value)" style="font-size:.78rem;padding:3px 6px;border-radius:5px;border:1px solid #ccc">
              <option value="">תצוגה...</option>
              <option value="MOBILE_FEED_STANDARD">פייסבוק מובייל</option>
              <option value="INSTAGRAM_STANDARD">אינסטגרם פיד</option>
              <option value="INSTAGRAM_STORY">אינסטגרם סטורי</option>
              <option value="DESKTOP_FEED_STANDARD">פייסבוק דסקטופ</option>
            </select>
          </div>
          <div id="preview-${a.id}" style="margin-top:8px"></div>
        </div>
      `).join('');
    }
    container.style.display = 'block';
    btn.textContent = '▲ סגור';
  } catch (e) { container.innerHTML = '<div style="color:#dc3545">שגיאה</div>'; container.style.display = 'block'; }
  btn.disabled = false;
}

async function loadPreview(adId, format) {
  if (!format) return;
  const target = document.getElementById('preview-' + adId);
  target.innerHTML = '<div style="color:#888;font-size:.82rem">טוען תצוגה...</div>';
  try {
    const r = await fetch(`/koshka/ad/${adId}/preview?format=${format}`);
    const html = await r.text();
    target.innerHTML = html || '<div style="color:#888;font-size:.82rem">אין תצוגה זמינה</div>';
  } catch (e) { target.innerHTML = '<div style="color:#dc3545">שגיאה</div>'; }
}

function renameCampaign(id, current) {
  const name = prompt('שם חדש לקמפיין:', current);
  if (!name || name === current) return;
  const f = document.createElement('form');
  f.method = 'POST'; f.action = `/koshka/campaign/${id}/rename`;
  f.innerHTML = `<input name="_token" value="{{ csrf_token() }}"><input name="name" value="${name.replace(/"/g,'&quot;')}">`;
  document.body.appendChild(f); f.submit();
}
</script>
</body>
</html>
