<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>TDNet Outreach Dashboard</title>
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;margin:0;padding:0;color:#222;background:#fafafa}
.topbar{background:#fff;border-bottom:1px solid #eee;padding:12px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.brand{font-weight:700;color:#1a73e8;font-size:1.1rem}
.counts{display:flex;gap:8px;flex-wrap:wrap;font-size:.85rem}
.counts span{background:#f3f4f6;padding:4px 10px;border-radius:6px}
.counts span.new{background:#dbeafe;color:#1e40af}
.counts span.sent{background:#d1fae5;color:#065f46}
.counts span.skipped{background:#fef3c7;color:#92400e}
.wrap{max-width:1280px;margin:0 auto;padding:20px 24px}
.toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.toolbar input,.toolbar select{padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;font-size:.88rem;background:#fff}
.toolbar button{padding:7px 14px;border:1px solid #1a73e8;background:#1a73e8;color:#fff;border-radius:6px;font-size:.88rem;font-weight:600;cursor:pointer}
.toolbar button.ghost{background:#fff;color:#1a73e8}
.toolbar button:hover{background:#1557b8;color:#fff}
.toolbar .spacer{flex:1}
table{width:100%;background:#fff;border-collapse:collapse;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden}
th,td{text-align:left;padding:10px 12px;border-bottom:1px solid #f0f0f0;font-size:.88rem}
th{background:#f9fafb;font-weight:600;color:#555;font-size:.8rem;text-transform:uppercase;letter-spacing:.04em}
tr:hover{background:#fafbff;cursor:pointer}
tr.is-sent{background:#f0fdf4}
tr.is-skipped{background:#fffbeb;opacity:.7}
.pill{display:inline-block;padding:2px 8px;border-radius:10px;font-size:.72rem;font-weight:600}
.pill.hospital{background:#ecfeff;color:#0e7490}
.pill.academic{background:#f5f3ff;color:#5b21b6}
.pill.pharma{background:#fdf2f8;color:#9d174d}
.pill.corporate{background:#f3f4f6;color:#374151}
.eq{display:inline-block;padding:2px 7px;border-radius:10px;font-size:.7rem;font-weight:700;letter-spacing:.02em}
.eq.ok{background:#d1fae5;color:#065f46}
.eq.stale{background:#fee2e2;color:#991b1b}
.eq.invalid{background:#fef3c7;color:#92400e}
.eq.unknown{background:#e5e7eb;color:#374151}
.eq-help{cursor:help}
.linkedin{color:#1a73e8;font-size:.78rem;text-decoration:none}
.linkedin:hover{text-decoration:underline}
.status-select{padding:4px 8px;border:1px solid #d1d5db;border-radius:4px;font-size:.78rem;font-weight:600;cursor:pointer;background:#fff}
.status-select.status-new{background:#dbeafe;color:#1e40af;border-color:#93c5fd}
.status-select.status-sent{background:#d1fae5;color:#065f46;border-color:#6ee7b7}
.status-select.status-skipped{background:#fef3c7;color:#92400e;border-color:#fcd34d}
.status-select.status-replied{background:#ede9fe;color:#5b21b6;border-color:#c4b5fd}
.empty{text-align:center;padding:40px 20px;color:#888}

/* Drawer */
.drawer-bg{position:fixed;inset:0;background:rgba(0,0,0,.4);display:none;z-index:50}
.drawer-bg.open{display:block}
.drawer{position:fixed;top:0;right:0;width:560px;max-width:100vw;height:100vh;background:#fff;box-shadow:-4px 0 24px rgba(0,0,0,.1);transform:translateX(100%);transition:transform .25s ease;z-index:60;overflow-y:auto;padding:24px}
.drawer.open{transform:translateX(0)}
.drawer h2{margin:0 0 .15em;font-size:1.15rem}
.drawer .meta{color:#666;font-size:.85rem;margin-bottom:.4em}
.drawer .info{display:grid;grid-template-columns:80px 1fr;gap:6px 12px;font-size:.85rem;margin:14px 0;padding:12px;background:#f9fafb;border-radius:8px}
.drawer .info b{color:#666;font-weight:500}
.drawer .row{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:14px 0}
.drawer label{font-size:.78rem;color:#555;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.drawer select{padding:7px 10px;border:1px solid #d1d5db;border-radius:6px;background:#fff;font-size:.88rem}
.btn{padding:7px 14px;border-radius:6px;font-size:.85rem;font-weight:600;cursor:pointer;border:1px solid #d1d5db;background:#fff;color:#333}
.btn:hover{border-color:#1a73e8;color:#1a73e8}
.btn-primary{background:#1a73e8;color:#fff;border-color:#1a73e8}
.btn-primary:hover{background:#1557b8;color:#fff;border-color:#1557b8}
.btn-success{background:#10b981;color:#fff;border-color:#10b981}
.btn-success:hover{background:#059669;color:#fff;border-color:#059669}
.btn-warn{background:#fff;color:#92400e;border-color:#fcd34d}
.btn-warn:hover{background:#fef3c7;color:#92400e}
.subjects{display:flex;flex-direction:column;gap:6px;margin-top:8px;max-height:260px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;padding:8px;background:#fafafa}
.subject-item{padding:8px 10px;border-radius:6px;cursor:pointer;font-size:.88rem;background:#fff;border:1px solid #e5e7eb;display:flex;justify-content:space-between;gap:8px;align-items:center}
.subject-item:hover{border-color:#1a73e8}
.subject-item.active{background:#dbeafe;border-color:#1a73e8;font-weight:600}
.subject-item .copy-btn{font-size:.7rem;padding:3px 8px}
textarea{width:100%;min-height:280px;padding:10px;font-family:inherit;font-size:.9rem;border:1px solid #cfd8e3;border-radius:8px;resize:vertical}
textarea:focus{outline:none;border-color:#1a73e8;box-shadow:0 0 0 3px rgba(26,115,232,.12)}
.subject-input{width:100%;padding:8px 10px;border:1px solid #cfd8e3;border-radius:8px;font-size:.9rem;font-weight:600}
.actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;border-top:1px solid #eee;padding-top:14px}
.toast{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#222;color:#fff;padding:10px 18px;border-radius:8px;font-size:.85rem;opacity:0;transition:opacity .2s ease;z-index:100;pointer-events:none}
.toast.show{opacity:1}
.spinner{display:inline-block;width:14px;height:14px;border:2px solid #fff;border-top-color:transparent;border-radius:50%;animation:spin .6s linear infinite;vertical-align:middle;margin-right:6px}
@keyframes spin{to{transform:rotate(360deg)}}
.close{position:absolute;top:14px;right:18px;background:none;border:none;font-size:1.6rem;cursor:pointer;color:#888;line-height:1}
.close:hover{color:#222}

/* Table horizontal scroll on narrow viewports */
.table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:8px}
.table-wrap table{min-width:980px}

/* Mobile breakpoint */
@media (max-width:768px){
  .wrap{padding:14px 12px}
  .topbar{padding:10px 14px}
  .brand{font-size:1rem}
  .counts{font-size:.78rem;width:100%}
  .counts span{padding:3px 8px;font-size:.75rem}
  .toolbar{gap:6px}
  .toolbar input,.toolbar select{font-size:.82rem;padding:6px 8px;min-width:0;flex:1 1 130px}
  .toolbar .spacer{display:none}
  .toolbar button{padding:8px 12px;font-size:.85rem;width:100%;margin-top:4px}
  th,td{padding:8px 10px;font-size:.82rem}
  .drawer{width:100vw;padding:16px;padding-top:48px}
  .drawer .info{grid-template-columns:80px 1fr}
  .drawer h2{font-size:1.05rem}
  textarea{min-height:200px}
  .actions{position:sticky;bottom:0;background:#fff;padding:10px 0;margin:14px -16px 0;padding:10px 16px;border-top:1px solid #eee;z-index:1}
  .actions .btn{flex:1 1 calc(50% - 4px);font-size:.78rem;padding:8px 10px}
  #src-modal{width:92vw;padding:18px;max-height:90vh;overflow-y:auto}
}
@media (max-width:480px){
  .topbar{padding:8px 10px;gap:6px}
  .topbar .right{width:100%;display:flex;justify-content:flex-end}
  .counts{order:3}
  .drawer .info{grid-template-columns:1fr;gap:2px}
  .drawer .info b{margin-top:6px}
  .subjects{max-height:200px}
}
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">📬 TDNet Outreach</div>
  <div class="counts">
    <span class="new">New: {{ $counts['new'] }}</span>
    <span class="sent">Sent: {{ $counts['sent'] }}</span>
    <span class="skipped">Skipped: {{ $counts['skipped'] }}</span>
    <span>Replied: {{ $counts['replied'] }}</span>
  </div>
  <div style="display:flex;align-items:center;gap:8px">
    @if(session('tdnet_email'))<span style="font-size:.8rem;color:#666">{{ session('tdnet_email') }}</span>@endif
    <form method="POST" action="/tdnet/logout" style="display:inline">@csrf
      <button class="btn">Sign out</button>
    </form>
  </div>
</div>

<div class="wrap">

<form method="GET" class="toolbar">
  <select name="status">
    <option value="new" {{ request('status','new')==='new'?'selected':'' }}>Status: New</option>
    <option value="sent" {{ request('status')==='sent'?'selected':'' }}>Status: Sent</option>
    <option value="skipped" {{ request('status')==='skipped'?'selected':'' }}>Status: Skipped</option>
    <option value="replied" {{ request('status')==='replied'?'selected':'' }}>Status: Replied</option>
    <option value="all" {{ request('status')==='all'?'selected':'' }}>Status: All</option>
  </select>
  <select name="country">
    <option value="">Country (all)</option>
    @foreach($countries as $c)
      <option value="{{ $c }}" {{ request('country')===$c?'selected':'' }}>{{ $c }}</option>
    @endforeach
  </select>
  <select name="segment">
    <option value="">Segment (all)</option>
    @foreach($segments as $s)
      <option value="{{ $s }}" {{ request('segment')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
    @endforeach
  </select>
  <select name="email_quality">
    <option value="">Email quality (all)</option>
    <option value="ok"      {{ request('email_quality')==='ok'?'selected':'' }}>Email: OK</option>
    <option value="stale"   {{ request('email_quality')==='stale'?'selected':'' }}>Email: Stale</option>
    <option value="invalid" {{ request('email_quality')==='invalid'?'selected':'' }}>Email: Invalid</option>
    <option value="unknown" {{ request('email_quality')==='unknown'?'selected':'' }}>Email: Unknown</option>
  </select>
  <input type="text" name="company" placeholder="Company contains…" value="{{ request('company') }}">
  <button class="ghost" type="submit">Filter</button>
  <div class="spacer"></div>
  <button type="button" class="btn-primary" onclick="openSourceModal()" id="load-more-btn" style="background:#10b981;color:#fff;border-color:#10b981">+ Load leads</button>
</form>

<!-- Source modal -->
<div class="drawer-bg" id="src-bg" onclick="closeSourceModal()"></div>
<div id="src-modal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:12px;padding:24px;box-shadow:0 8px 32px rgba(0,0,0,.2);z-index:70;width:480px;max-width:92vw">
  <h3 style="margin:0 0 .15em;font-size:1.15rem">Source new leads</h3>
  <p style="color:#666;font-size:.85rem;margin:0 0 1.2em">Pulls from Apify Leads Finder using TDNet ICP titles + healthcare/pharma/academic industries. Validated emails only.</p>

  <label style="font-size:.78rem;color:#555;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;letter-spacing:.04em">Country</label>
  <select id="src-country" style="width:100%;padding:8px 10px;border:1px solid #cfd8e3;border-radius:8px;font-size:.9rem;margin-bottom:14px">
    <option value="">All non-US (UK, DE, NL, FR, SE, CH, AU, CA, IL, SG, JP)</option>
    <option value="united kingdom">United Kingdom</option>
    <option value="germany">Germany</option>
    <option value="netherlands">Netherlands</option>
    <option value="france">France</option>
    <option value="sweden">Sweden</option>
    <option value="switzerland">Switzerland</option>
    <option value="australia">Australia</option>
    <option value="canada">Canada</option>
    <option value="israel">Israel</option>
    <option value="singapore">Singapore</option>
    <option value="japan">Japan</option>
    <option value="ireland">Ireland</option>
    <option value="denmark">Denmark</option>
    <option value="norway">Norway</option>
    <option value="finland">Finland</option>
    <option value="belgium">Belgium</option>
    <option value="austria">Austria</option>
    <option value="italy">Italy</option>
    <option value="spain">Spain</option>
    <option value="portugal">Portugal</option>
    <option value="south korea">South Korea</option>
    <option value="hong kong">Hong Kong</option>
    <option value="south africa">South Africa</option>
    <option value="united arab emirates">United Arab Emirates</option>
  </select>

  <label style="font-size:.78rem;color:#555;display:block;margin-bottom:4px;font-weight:600;text-transform:uppercase;letter-spacing:.04em">How many?</label>
  <select id="src-count" style="width:100%;padding:8px 10px;border:1px solid #cfd8e3;border-radius:8px;font-size:.9rem;margin-bottom:18px">
    <option value="10">10 leads</option>
    <option value="20">20 leads</option>
    <option value="50">50 leads</option>
  </select>

  <div style="display:flex;gap:8px;justify-content:flex-end">
    <button class="btn" onclick="closeSourceModal()">Cancel</button>
    <button class="btn btn-primary" onclick="loadMore()" id="src-go">Source leads</button>
  </div>
</div>

@if($leads->isEmpty())
  <div class="empty">No leads yet. Click <b>+ Load leads</b> to source from Apify.</div>
@else
<div class="table-wrap">
<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Name</th>
      <th>Title</th>
      <th>Company</th>
      <th>Country</th>
      <th>Seg</th>
      <th>Email</th>
      <th>Quality</th>
      <th>LinkedIn</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($leads as $i => $lead)
      <tr class="lead-row {{ $lead->status==='sent'?'is-sent':'' }} {{ $lead->status==='skipped'?'is-skipped':'' }}"
          data-id="{{ $lead->id }}"
          onclick="openDrawer({{ $lead->id }})">
        <td>{{ $i + 1 }}</td>
        <td><b>{{ $lead->fullName() ?: '—' }}</b></td>
        <td>{{ $lead->position }}</td>
        <td>{{ $lead->company }}</td>
        <td>{{ $lead->country }}</td>
        <td>@if($lead->segment)<span class="pill {{ $lead->segment }}">{{ ucfirst($lead->segment) }}</span>@endif</td>
        <td style="font-size:.78rem">{{ $lead->email }}</td>
        <td>
          @php $q = $lead->email_quality ?? 'unknown'; @endphp
          <span class="eq {{ $q }} eq-help" title="{{ $lead->email_quality_reason ?? '' }}">{{ strtoupper($q) }}</span>
        </td>
        <td>@if($lead->linkedin_url)<a class="linkedin" href="{{ $lead->linkedin_url }}" target="_blank" onclick="event.stopPropagation()">↗ profile</a>@endif</td>
        <td onclick="event.stopPropagation()">
          <select class="status-select status-{{ $lead->status }}" data-id="{{ $lead->id }}" onchange="setStatus(this)">
            <option value="new"     {{ $lead->status==='new'?'selected':'' }}>New</option>
            <option value="sent"    {{ $lead->status==='sent'?'selected':'' }}>Sent</option>
            <option value="replied" {{ $lead->status==='replied'?'selected':'' }}>Replied</option>
            <option value="skipped" {{ $lead->status==='skipped'?'selected':'' }}>Skipped</option>
          </select>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
</div>
@endif

</div>

<div class="drawer-bg" id="drawer-bg" onclick="closeDrawer()"></div>
<div class="drawer" id="drawer">
  <button class="close" onclick="closeDrawer()">&times;</button>
  <h2 id="d-name">—</h2>
  <div class="meta" id="d-meta">—</div>

  <div class="info" id="d-info"></div>

  <div class="row" style="margin:8px 0 0">
    <button class="btn" onclick="refreshProfile()" id="d-refresh-btn">↻ Refresh from LinkedIn</button>
    <button class="btn" onclick="refindEmail()" id="d-refind-btn">📧 Find better email</button>
  </div>
  <div id="d-refresh-status" style="font-size:.8rem;color:#666;margin:6px 0"></div>

  <div class="row">
    <div>
      <label>Subject style</label>
      <select id="d-style">
        <option value="question">Question</option>
        <option value="statement">Statement</option>
        <option value="pain">Pain</option>
      </select>
    </div>
    <div>
      <label>&nbsp;</label>
      <button class="btn btn-primary" onclick="generate()" id="d-gen">Generate email</button>
    </div>
  </div>

  <div id="d-output" style="display:none">
    <label>Subject (10 options — click to select)</label>
    <input type="text" id="d-subject" class="subject-input" placeholder="Active subject line">
    <div class="subjects" id="d-subjects"></div>

    <div style="margin-top:14px">
      <label>Body
        <button class="btn" style="float:right;font-size:.72rem;padding:3px 10px" onclick="regenBody()">↻ Regenerate body</button>
      </label>
      <textarea id="d-body" placeholder="Email body…"></textarea>
    </div>

    <div class="actions">
      <button class="btn btn-primary" onclick="copyText('d-subject','Subject copied')">📋 Copy subject</button>
      <button class="btn btn-primary" onclick="copyText('d-body','Body copied')">📋 Copy body</button>
      <button class="btn btn-success" onclick="markSent()">✓ Mark sent</button>
      <button class="btn btn-warn" onclick="markSkipped()">⊗ Skip</button>
    </div>
  </div>
</div>

<div class="toast" id="toast">copied</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let currentId = null;

function toast(msg) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'), 1500);
}

async function api(url, opts={}) {
  const r = await fetch(url, {
    method: opts.method || 'POST',
    headers: {
      'X-CSRF-TOKEN': csrf,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: opts.body ? JSON.stringify(opts.body) : undefined,
  });
  return r.json();
}

async function openDrawer(id) {
  currentId = id;
  document.getElementById('drawer-bg').classList.add('open');
  document.getElementById('drawer').classList.add('open');
  document.getElementById('d-output').style.display = 'none';

  const lead = await fetch(`/tdnet/leads/${id}`).then(r=>r.json());

  document.getElementById('d-name').textContent = (lead.first_name || '') + ' ' + (lead.last_name || '');
  document.getElementById('d-meta').textContent = `${lead.position || ''} · ${lead.company || ''} · ${lead.country || ''}`;

  const info = document.getElementById('d-info');
  const q = lead.email_quality || 'unknown';
  const qReason = lead.email_quality_reason || '';
  const refreshed = lead.refreshed_at ? `<span style="color:#888;font-size:.8em"> · refreshed ${new Date(lead.refreshed_at).toLocaleDateString()}</span>` : '';
  info.innerHTML = `
    <b>Email</b><span>${lead.email || '—'} <span class="eq ${q}" title="${escapeHtml(qReason)}">${q.toUpperCase()}</span></span>
    <b>Quality</b><span style="font-size:.8em;color:#666">${qReason ? escapeHtml(qReason) : '—'}</span>
    <b>LinkedIn</b><span>${lead.linkedin_url ? `<a href="${lead.linkedin_url}" target="_blank">${lead.linkedin_url}</a>` : '—'}</span>
    <b>Segment</b><span>${lead.segment || '—'}${refreshed}</span>
    <b>Status</b><span>${lead.status}</span>
  `;
  document.getElementById('d-refresh-status').textContent = '';

  if (lead.email_subject || lead.email_body) {
    document.getElementById('d-output').style.display = 'block';
    document.getElementById('d-subject').value = lead.email_subject || '';
    document.getElementById('d-body').value = lead.email_body || '';
    renderSubjects(lead.subject_variants || [], lead.email_subject);
    if (lead.email_style) document.getElementById('d-style').value = lead.email_style;
  }
}

function closeDrawer() {
  document.getElementById('drawer-bg').classList.remove('open');
  document.getElementById('drawer').classList.remove('open');
  currentId = null;
}

function renderSubjects(list, active) {
  const c = document.getElementById('d-subjects');
  c.innerHTML = '';
  list.forEach(s => {
    const div = document.createElement('div');
    div.className = 'subject-item' + (s === active ? ' active' : '');
    div.innerHTML = `<span style="flex:1">${escapeHtml(s)}</span><button class="btn copy-btn" onclick="event.stopPropagation();navigator.clipboard.writeText(${JSON.stringify(s)});toast('Subject copied')">📋</button>`;
    div.onclick = () => pickSubject(s, div);
    c.appendChild(div);
  });
}

function escapeHtml(s) {
  return String(s).replace(/[&<>"]/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;"}[c]));
}

async function pickSubject(s, el) {
  document.getElementById('d-subject').value = s;
  document.querySelectorAll('.subject-item').forEach(e=>e.classList.remove('active'));
  if (el) el.classList.add('active');
  if (currentId) await api(`/tdnet/leads/${currentId}/subject`, {body:{subject: s}});
}

async function generate() {
  if (!currentId) return;
  const btn = document.getElementById('d-gen');
  const orig = btn.textContent;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span>Generating…';
  const style = document.getElementById('d-style').value;
  try {
    const r = await api(`/tdnet/leads/${currentId}/generate`, {body:{style}});
    if (r.error) { alert(r.error + (r.raw?'\n\n'+r.raw:'')); return; }
    document.getElementById('d-output').style.display = 'block';
    document.getElementById('d-subject').value = r.subjects[0] || '';
    document.getElementById('d-body').value = r.body || '';
    renderSubjects(r.subjects, r.subjects[0]);
    toast('Generated');
  } catch (e) {
    alert('Generate failed: ' + e.message);
  } finally {
    btn.disabled = false;
    btn.textContent = orig;
  }
}

async function refreshProfile() {
  if (!currentId) return;
  const btn = document.getElementById('d-refresh-btn');
  const orig = btn.textContent;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner" style="border-top-color:#1a73e8;border-color:#1a73e8 transparent #1a73e8 #1a73e8"></span>Refreshing…';
  document.getElementById('d-refresh-status').textContent = '';
  try {
    const r = await api(`/tdnet/leads/${currentId}/refresh-profile`);
    if (r.error) { alert(r.error); return; }
    const changed = (r.changes || []).join(', ') || 'no changes';
    document.getElementById('d-refresh-status').textContent = '✓ Updated: ' + changed;
    toast('Refreshed');
    // Reopen drawer with fresh data
    await openDrawer(currentId);
  } catch (e) {
    alert('Refresh failed: ' + e.message);
  } finally {
    btn.disabled = false;
    btn.textContent = orig;
  }
}

async function refindEmail() {
  if (!currentId) return;
  const btn = document.getElementById('d-refind-btn');
  const orig = btn.textContent;
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner" style="border-top-color:#1a73e8;border-color:#1a73e8 transparent #1a73e8 #1a73e8"></span>Finding…';
  document.getElementById('d-refresh-status').textContent = '';
  try {
    const r = await api(`/tdnet/leads/${currentId}/refind-email`);
    if (r.error) { alert(r.error); return; }
    if (!r.ok) {
      document.getElementById('d-refresh-status').textContent = '✗ ' + (r.reason || 'No email found');
      return;
    }
    document.getElementById('d-refresh-status').textContent = `✓ New email: ${r.email} (${r.email_quality})`;
    toast('Email updated');
    await openDrawer(currentId);
  } catch (e) {
    alert('Re-find failed: ' + e.message);
  } finally {
    btn.disabled = false;
    btn.textContent = orig;
  }
}

async function regenBody() {
  if (!currentId) return;
  const subject = document.getElementById('d-subject').value;
  const style = document.getElementById('d-style').value;
  const r = await api(`/tdnet/leads/${currentId}/regenerate-body`, {body:{subject, style}});
  if (r.body) { document.getElementById('d-body').value = r.body; toast('Body regenerated'); }
}

function copyText(id, msg) {
  const el = document.getElementById(id);
  el.select();
  navigator.clipboard.writeText(el.value).then(()=>toast(msg));
}

async function markSent() {
  if (!currentId) return;
  await api(`/tdnet/leads/${currentId}/sent`);
  toast('Marked sent');
  setTimeout(()=>location.reload(), 600);
}

async function markSkipped() {
  if (!currentId) return;
  await api(`/tdnet/leads/${currentId}/skip`);
  toast('Skipped');
  setTimeout(()=>location.reload(), 600);
}

function openSourceModal() {
  document.getElementById('src-bg').classList.add('open');
  document.getElementById('src-modal').style.display = 'block';
}
function closeSourceModal() {
  document.getElementById('src-bg').classList.remove('open');
  document.getElementById('src-modal').style.display = 'none';
}

async function loadMore() {
  const country = document.getElementById('src-country').value;
  const count = parseInt(document.getElementById('src-count').value, 10) || 10;
  const btn = document.getElementById('src-go');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span>Sourcing…';
  try {
    const body = {count};
    if (country) body.country = country;
    const r = await api('/tdnet/source', {body});
    if (r.error) { alert(r.error); btn.disabled=false; btn.textContent='Source leads'; return; }
    location.reload();
  } catch (e) {
    alert('Source failed: ' + e.message);
    btn.disabled = false;
    btn.textContent = 'Source leads';
  }
}

// ESC closes drawer
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeDrawer();
});

// Inline status dropdown on rows
async function setStatus(sel) {
  const id = sel.dataset.id;
  const newStatus = sel.value;
  // Update visual
  sel.className = 'status-select status-' + newStatus;
  try {
    const r = await api(`/tdnet/leads/${id}/status`, {body:{status: newStatus}});
    if (r.error) { alert(r.error); return; }
    toast('Status: ' + newStatus);
    // Refresh row class (sent/skipped row tinting)
    const tr = sel.closest('tr');
    tr.classList.toggle('is-sent', newStatus === 'sent');
    tr.classList.toggle('is-skipped', newStatus === 'skipped');
  } catch (e) {
    alert('Update failed: ' + e.message);
  }
}
</script>

</body>
</html>
