<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#1a73e8">
<title>קושקה — לידים</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,sans-serif;background:#f0f2f5;color:#1c1e21;line-height:1.5;font-size:15px}
.topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:10}
.brand{font-weight:700;font-size:1rem;display:flex;align-items:center;gap:8px}
.brand a{color:#1a73e8;text-decoration:none;font-size:.8rem}
.user{font-size:.78rem;color:#666}
.wrap{max-width:760px;margin:0 auto;padding:14px}
.head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:10px;flex-wrap:wrap}
.head h1{font-size:1.2rem;font-weight:700}
.status{font-size:.78rem;color:#666;display:flex;align-items:center;gap:6px}
.dot{width:8px;height:8px;border-radius:50%;background:#28a745;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:10px;padding:11px 14px;margin-bottom:14px;font-size:.88rem}
.empty{text-align:center;padding:50px 20px;color:#888;background:#fff;border-radius:14px}
.empty .big-icon{font-size:3rem;margin-bottom:12px}
.lead{background:#fff;border-radius:12px;padding:14px 16px;margin-bottom:8px;box-shadow:0 1px 3px rgba(0,0,0,.05);border-right:4px solid #1a73e8;transition:all .15s}
.lead.new{border-right-color:#28a745;animation:flash 1.2s ease-out}
@keyframes flash{0%{background:#e8f5e9}100%{background:#fff}}
.lead-head{display:flex;justify-content:space-between;align-items:baseline;gap:10px;margin-bottom:4px}
.lead-name{font-weight:700;font-size:1.02rem}
.lead-time{font-size:.78rem;color:#888;white-space:nowrap}
.lead-phone{font-family:'SF Mono',Menlo,monospace;font-size:.95rem;color:#1a73e8;direction:ltr;text-align:right;display:inline-block}
.lead-phone a{color:#1a73e8;text-decoration:none}
.lead-phone a:hover{text-decoration:underline}
.lead-field{display:flex;gap:8px;margin-top:4px;font-size:.86rem}
.lead-field .k{color:#888;min-width:80px}
.lead-field .v{color:#1c1e21;flex:1}
.lead-meta{display:flex;gap:10px;margin-top:8px;padding-top:8px;border-top:1px dashed #eee;font-size:.74rem;color:#999;flex-wrap:wrap}
.lead-meta span{background:#f6f8fa;padding:2px 8px;border-radius:6px}
.actions{display:flex;gap:8px;margin-top:10px}
.act{padding:8px 12px;border-radius:8px;border:1px solid #ddd;background:#fff;font-size:.82rem;cursor:pointer;font-family:inherit;color:#1c1e21;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
.act:hover{background:#f0f4ff;border-color:#1a73e8;color:#1a73e8}
.act.wa{background:#25d366;color:#fff;border-color:#25d366}
.act.wa:hover{background:#1eb955;color:#fff}
.act.call{background:#1a73e8;color:#fff;border-color:#1a73e8}
.muted{color:#888;font-size:.85rem}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand"><span>🧘</span>קושקה <a href="/koshka">← דשבורד</a></div>
  <div class="user">{{ $userEmail }}</div>
</div>

<div class="wrap">

<div class="head">
  <h1>📥 לידים <span class="muted" id="lead-count">({{ count($leads) }})</span></h1>
  <div class="status"><span class="dot"></span><span id="status-text">מתעדכן אוטומטית כל 30 שנ׳</span></div>
</div>

@if($error)
<div class="error">{{ $error }}</div>
@endif

@if(empty($leads) && !$error)
<div class="empty">
  <div class="big-icon">📭</div>
  <h3>אין לידים עדיין</h3>
  <p>הדף יתעדכן אוטומטית כשיגיע ליד חדש</p>
</div>
@endif

<div id="leads-list">
  @foreach($leads as $lead)
    @include('koshka.partials.lead', ['lead' => $lead])
  @endforeach
</div>

</div>

<audio id="chime" preload="auto" src="data:audio/mpeg;base64,SUQzBAAAAAABEVRYWFgAAAAtAAADY29tbWVudABCaWdTb3VuZEJhbmsuY29tIC8gTGFTb25vdGhlcXVlLm9yZwBURU5DAAAAHQAAA1N3aXRjaCBQbHVzIMKpIE5DSCBTb2Z0d2FyZQBUSVQyAAAABgAAAzIyMzUAVFNTRQAAAA8AAANMYXZmNTcuODMuMTAwAAAAAAAAAAAAAAD/80DEAAAAA0gAAAAATEFNRTMuMTAwVVVVVVVVVVVVVUxBTUUzLjEwMFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV/80DEVwAAA0gAAAAAVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV/80DErQAAA0gAAAAAVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV"></audio>

<script>
const POLL_MS = 30000;
let lastTopId = @json($lastLeadTs ? $leads[0]['id'] : null);
let knownIds = new Set([@foreach($leads as $l)'{{ $l['id'] }}',@endforeach]);

// Permission for browser notifications
if ('Notification' in window && Notification.permission === 'default') {
  Notification.requestPermission();
}

function fmtTime(iso) {
  const d = new Date(iso);
  const now = new Date();
  const diffSec = (now - d) / 1000;
  if (diffSec < 60) return 'עכשיו';
  if (diffSec < 3600) return Math.floor(diffSec/60) + ' דק׳';
  if (diffSec < 86400) return Math.floor(diffSec/3600) + ' שעות';
  return d.toLocaleDateString('he-IL') + ' ' + d.toTimeString().slice(0,5);
}

function getField(lead, names) {
  for (const f of (lead.field_data || [])) {
    if (names.includes(f.name)) return (f.values || [])[0] || '';
  }
  return '';
}

function leadHtml(lead, isNew) {
  const name = getField(lead, ['full_name','first_name']);
  const phone = getField(lead, ['phone_number','phone']);
  const phoneClean = (phone || '').replace(/[^\d+]/g,'');
  const waPhone = phoneClean.replace(/^0/, '972');
  const duration = getField(lead, ['practice_duration']);
  const formName = lead._form_name || '';
  const adName = lead.ad_name || '';
  const campName = lead.campaign_name || '';

  let extra = '';
  for (const f of (lead.field_data || [])) {
    if (!['full_name','first_name','last_name','phone_number','phone','email'].includes(f.name)) {
      extra += `<div class="lead-field"><span class="k">${f.name}:</span><span class="v">${(f.values||[]).join(', ')}</span></div>`;
    }
  }

  return `
    <div class="lead ${isNew ? 'new' : ''}">
      <div class="lead-head">
        <div class="lead-name">${name || '(ללא שם)'}</div>
        <div class="lead-time" title="${lead.created_time}">${fmtTime(lead.created_time)}</div>
      </div>
      ${phone ? `<div class="lead-phone"><a href="tel:${phoneClean}">${phone}</a></div>` : ''}
      ${extra}
      <div class="lead-meta">
        ${formName ? `<span>טופס: ${formName}</span>` : ''}
        ${campName ? `<span>קמפיין: ${campName}</span>` : ''}
        ${adName ? `<span>מודעה: ${adName}</span>` : ''}
      </div>
      <div class="actions">
        ${phoneClean ? `<a class="act call" href="tel:${phoneClean}">📞 התקשרי</a>` : ''}
        ${waPhone ? `<a class="act wa" href="https://wa.me/${waPhone}" target="_blank">💬 וואטסאפ</a>` : ''}
      </div>
    </div>
  `;
}

async function poll() {
  try {
    const r = await fetch('/koshka/leads?format=json');
    const data = await r.json();
    const newLeads = (data.leads || []).filter(l => !knownIds.has(l.id));

    if (newLeads.length > 0) {
      const container = document.getElementById('leads-list');
      newLeads.reverse().forEach(l => {
        knownIds.add(l.id);
        container.insertAdjacentHTML('afterbegin', leadHtml(l, true));
      });
      document.getElementById('lead-count').textContent = '(' + (parseInt(document.getElementById('lead-count').textContent.replace(/\D/g,'')) + newLeads.length) + ')';

      // Chime + notification
      try { document.getElementById('chime').play().catch(()=>{}); } catch(e){}
      if (Notification.permission === 'granted') {
        const l = newLeads[0];
        const name = getField(l, ['full_name','first_name']) || 'ליד חדש';
        new Notification('🔔 ליד חדש: ' + name, {
          body: getField(l, ['phone_number','phone']) || '',
          icon: '/favicon.ico',
          tag: 'lead-' + l.id,
        });
      }
      // Page title flash
      document.title = `(${newLeads.length}) קושקה — לידים`;
      setTimeout(() => { document.title = 'קושקה — לידים'; }, 8000);
    }

    document.getElementById('status-text').textContent = 'עודכן ב-' + new Date().toTimeString().slice(0,5);
  } catch (e) {
    document.getElementById('status-text').textContent = 'שגיאה ברענון';
  }
}

setInterval(poll, POLL_MS);
</script>

</body>
</html>
