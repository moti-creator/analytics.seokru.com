<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#1a73e8">
<title>קושקה יוגה — היום</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:system-ui,-apple-system,sans-serif;background:#f0f2f5;color:#1c1e21;line-height:1.5;font-size:15px}

.topbar{background:#fff;border-bottom:1px solid #e5e7eb;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:10}
.brand{font-weight:700;font-size:1rem;display:flex;align-items:center;gap:8px}
.brand .emoji{font-size:1.3rem}
.user{font-size:.78rem;color:#666;display:flex;align-items:center;gap:8px}
.user button{color:#1a73e8;background:none;border:none;cursor:pointer;font-family:inherit;font-size:.78rem}

.wrap{max-width:640px;margin:0 auto;padding:14px}

.flash{background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:10px;padding:11px 14px;margin-bottom:14px;font-size:.88rem}
.error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:10px;padding:11px 14px;margin-bottom:14px;font-size:.88rem}

/* Hero: today */
.hero{background:linear-gradient(135deg,#1a73e8 0%,#3b82f6 100%);color:#fff;border-radius:18px;padding:22px 20px;margin-bottom:18px;box-shadow:0 4px 20px rgba(26,115,232,.25)}
.hero .label{font-size:.78rem;opacity:.85;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px}
.hero .big{font-size:2.6rem;font-weight:800;line-height:1.1;margin-bottom:6px;letter-spacing:-.02em}
.hero .sub{font-size:.92rem;opacity:.95}
.hero .week-row{display:flex;gap:18px;margin-top:14px;padding-top:14px;border-top:1px solid rgba(255,255,255,.2);font-size:.85rem}
.hero .week-row > div{flex:1}
.hero .week-row .k{font-size:.7rem;opacity:.8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px}
.hero .week-row .v{font-weight:700;font-size:1.05rem}
.hero .delta{font-size:.74rem;opacity:.85;margin-top:1px}

/* Section header */
.section-h{font-size:.82rem;color:#666;text-transform:uppercase;letter-spacing:.05em;margin:18px 4px 10px;font-weight:600;display:flex;align-items:center;gap:6px}

/* Overview strip */
.overview{display:flex;flex-direction:column;gap:6px;margin-bottom:8px}
.ov-item{background:#fff;border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px;font-size:.88rem;box-shadow:0 1px 2px rgba(0,0,0,.04);border-right:3px solid #ddd}
.ov-item.ov-good{border-right-color:#28a745;background:#f6fdf8}
.ov-item.ov-bad{border-right-color:#dc3545;background:#fff8f8}
.ov-item.ov-neutral{border-right-color:#6c757d}
.ov-icon{font-size:1.2rem;flex-shrink:0}
.ov-text{color:#1c1e21;line-height:1.4}

/* Insight card */
.card{background:#fff;border-radius:14px;padding:16px 16px 12px;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,.06);border-right:4px solid #ddd}
.card.danger{border-right-color:#dc3545}
.card.warning{border-right-color:#ff9800}
.card.info{border-right-color:#1a73e8}
.card.success{border-right-color:#28a745}

.card-head{display:flex;gap:10px;align-items:flex-start;margin-bottom:10px}
.card-icon{font-size:1.6rem;line-height:1}
.card-body{flex:1;min-width:0}
.card-title{font-weight:700;font-size:.98rem;color:#1a1a1a;margin-bottom:4px;word-wrap:break-word}
.card-detail{color:#555;font-size:.88rem;line-height:1.45}

.card-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.act-btn{padding:9px 14px;border-radius:10px;border:1px solid #ddd;background:#fff;font-size:.86rem;cursor:pointer;font-family:inherit;color:#1c1e21;font-weight:500;transition:all .15s;display:inline-flex;align-items:center;justify-content:center;min-height:38px}
.act-btn:hover{background:#f0f4ff;border-color:#1a73e8;color:#1a73e8}
.act-btn.primary{background:#1a73e8;color:#fff;border-color:#1a73e8}
.act-btn.primary:hover{background:#1558b0;color:#fff}
.act-btn.ghost{border:none;color:#888;background:transparent}
.act-btn.ghost:hover{background:#f0f0f0;color:#555}

/* Empty state */
.empty{text-align:center;padding:50px 20px;color:#888;background:#fff;border-radius:14px}
.empty .big-icon{font-size:3rem;margin-bottom:12px}
.empty h3{color:#444;font-size:1.05rem;font-weight:600;margin-bottom:6px}
.empty p{font-size:.88rem}

/* Quick actions */
.quick{display:grid;grid-template-columns:1fr;gap:8px;margin-top:8px}
.quick a{background:#fff;border-radius:12px;padding:14px 16px;text-decoration:none;color:#1c1e21;display:flex;align-items:center;gap:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);font-size:.92rem;font-weight:500;transition:transform .12s,box-shadow .12s}
.quick a:hover{transform:translateY(-1px);box-shadow:0 3px 8px rgba(0,0,0,.10)}
.quick a .qi{font-size:1.4rem}
.quick a .qsub{font-size:.78rem;color:#888;font-weight:400;display:block;margin-top:1px}
.quick a .qmain{flex:1}
.quick a .qarrow{color:#ccc;font-size:1.1rem}

@media(min-width:500px){
  .quick{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand"><span class="emoji">🧘</span>קושקה</div>
  <div class="user">
    <span>{{ $userEmail }}</span>
    <form method="POST" action="/koshka/logout" style="display:inline">@csrf<button>יציאה</button></form>
  </div>
</div>

<div class="wrap">

@if($flash ?? null)<div class="flash">✓ {{ $flash }}</div>@endif
@if($error ?? null)<div class="error">{{ $error }}</div>@endif

{{-- HERO --}}
@if($today)
<div class="hero">
  <div class="label">היום</div>
  <div class="big">₪{{ number_format($today['spend'], 0) }}</div>
  <div class="sub">{{ $today['leads'] }} {{ $today['leads'] == 1 ? 'ליד' : 'לידים' }} · {{ $today['clicks'] }} קליקים · {{ number_format($today['impressions']) }} חשיפות</div>

  @if($week)
  <div class="week-row">
    <div>
      <div class="k">השבוע</div>
      <div class="v">₪{{ number_format($week['this']['spend'], 0) }}</div>
      @if($week['delta']['spend'] !== null)
        <div class="delta">{{ $week['delta']['spend'] >= 0 ? '↑' : '↓' }} {{ abs($week['delta']['spend']) }}% vs שעבר</div>
      @endif
    </div>
    <div>
      <div class="k">לידים</div>
      <div class="v">{{ $week['this']['leads'] }}</div>
      @if($week['delta']['leads'] !== null)
        <div class="delta">{{ $week['delta']['leads'] >= 0 ? '↑' : '↓' }} {{ abs($week['delta']['leads']) }}% vs שעבר</div>
      @endif
    </div>
    <div>
      <div class="k">CTR</div>
      <div class="v">{{ number_format($week['this']['ctr'], 2) }}%</div>
    </div>
  </div>
  @endif
</div>
@endif

{{-- OVERVIEW STRIP --}}
@if(count($overview ?? []))
<div class="section-h">🔍 סקירת חשבון</div>
<div class="overview">
  @foreach($overview as $o)
  <div class="ov-item ov-{{ $o['tone'] }}">
    <span class="ov-icon">{{ $o['icon'] }}</span>
    <span class="ov-text">{{ $o['text'] }}</span>
  </div>
  @endforeach
</div>
@endif

{{-- CARDS --}}
@if(count($cards))
<div class="section-h">📌 דורש החלטה ({{ count($cards) }})</div>

@foreach($cards as $card)
<div class="card {{ $card['severity'] }}">
  <div class="card-head">
    <div class="card-icon">{{ $card['icon'] }}</div>
    <div class="card-body">
      <div class="card-title">{{ $card['title'] }}</div>
      <div class="card-detail">{{ $card['detail'] }}</div>
    </div>
  </div>
  <div class="card-actions">
    @foreach($card['actions'] as $action)
    <form method="POST" action="/koshka/card/{{ $card['campaign']['id'] }}" style="display:inline"
          @if(in_array($action['action'], ['pause','budget_pct']) && ($action['param'] ?? 0) <= -20)
          onsubmit="return confirm('{{ $action['label'] }} — בטוח?')"
          @endif>
      @csrf
      <input type="hidden" name="action" value="{{ $action['action'] }}">
      @if(isset($action['param']))<input type="hidden" name="param" value="{{ $action['param'] }}">@endif
      <button class="act-btn {{ $action['style'] ?? '' }}">{{ $action['label'] }}</button>
    </form>
    @endforeach
  </div>
</div>
@endforeach
@else
<div class="section-h">📌 דורש החלטה</div>
<div class="empty">
  <div class="big-icon">✓</div>
  <h3>הכל בסדר</h3>
  <p>אין קמפיינים שדורשים החלטה כרגע.</p>
</div>
@endif

{{-- QUICK ACTIONS --}}
<div class="section-h">⚡ פעולות</div>
<div class="quick">
  <a href="/koshka/all">
    <span class="qi">📋</span>
    <span class="qmain">כל הקמפיינים <span class="qsub">{{ $activeCount }} פעילים · {{ $campaignCount }} סה״כ</span></span>
    <span class="qarrow">←</span>
  </a>
  <a href="/koshka">
    <span class="qi">🔄</span>
    <span class="qmain">רענון נתונים <span class="qsub">נתונים מ-Meta עודכנו ב-cache 2 דק׳</span></span>
    <span class="qarrow">←</span>
  </a>
</div>

</div>
</body>
</html>
