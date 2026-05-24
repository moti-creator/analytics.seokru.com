<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>ניהול קמפיינים — קושקה יוגה</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:system-ui,sans-serif;background:#f0f2f5;color:#1c1e21}

/* layout */
.app{display:flex;flex-direction:column;height:100vh;max-width:800px;margin:0 auto}

/* topbar */
.topbar{background:#fff;border-bottom:1px solid #ddd;padding:12px 16px;display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.topbar .brand{display:flex;align-items:center;gap:8px;font-weight:700;font-size:1rem;color:#1a1a1a}
.topbar .brand .emoji{font-size:1.3rem}
.topbar .user{display:flex;align-items:center;gap:10px;font-size:.82rem;color:#888}
.topbar .user a{color:#1a73e8;text-decoration:none}
.topbar .user a:hover{text-decoration:underline}

/* messages area */
.messages{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px}

/* message bubbles */
.msg{display:flex;gap:10px;align-items:flex-end;max-width:85%}
.msg.user{align-self:flex-start;flex-direction:row-reverse}
.msg.assistant{align-self:flex-end}
.bubble{padding:10px 14px;border-radius:16px;font-size:.92rem;line-height:1.55;white-space:pre-wrap;word-wrap:break-word}
.msg.user .bubble{background:#1a73e8;color:#fff;border-bottom-right-radius:4px}
.msg.assistant .bubble{background:#fff;color:#1c1e21;border-bottom-left-radius:4px;box-shadow:0 1px 4px rgba(0,0,0,.10)}
.msg.assistant .bubble table{border-collapse:collapse;width:100%;font-size:.85rem;margin:.6em 0}
.msg.assistant .bubble th,.msg.assistant .bubble td{border:1px solid #e5e7eb;padding:5px 8px;text-align:right}
.msg.assistant .bubble th{background:#f9fafb;font-weight:600}
.msg.assistant .bubble h2,.msg.assistant .bubble h3{font-size:.95rem;margin:.6em 0 .3em;color:#1a73e8}
.msg.assistant .bubble ul,.msg.assistant .bubble ol{padding-right:1.4em;margin:.4em 0}

/* tool indicator */
.tool-indicator{font-size:.78rem;color:#888;display:flex;align-items:center;gap:5px;padding:4px 0;align-self:flex-end}
.tool-indicator .dot{width:6px;height:6px;border-radius:50%;background:#1a73e8;animation:pulse 1.2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.3}}

/* typing indicator */
.typing{align-self:flex-end}
.typing .bubble{background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.10);padding:12px 16px}
.typing-dots{display:flex;gap:4px;align-items:center}
.typing-dots span{width:7px;height:7px;border-radius:50%;background:#bbb;animation:bounce .9s infinite}
.typing-dots span:nth-child(2){animation-delay:.15s}
.typing-dots span:nth-child(3){animation-delay:.3s}
@keyframes bounce{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-6px)}}

/* input bar */
.input-bar{background:#fff;border-top:1px solid #ddd;padding:12px 16px;display:flex;gap:10px;align-items:flex-end;flex-shrink:0}
#input{flex:1;border:1px solid #ddd;border-radius:20px;padding:10px 16px;font-size:.92rem;font-family:inherit;resize:none;min-height:44px;max-height:140px;line-height:1.5;outline:none;overflow-y:auto;direction:rtl}
#input:focus{border-color:#1a73e8}
#send-btn{background:#1a73e8;color:#fff;border:none;border-radius:50%;width:44px;height:44px;display:flex;align-items:center;justify-content:center;cursor:pointer;flex-shrink:0;transition:background .15s}
#send-btn:hover:not(:disabled){background:#1558b0}
#send-btn:disabled{background:#bbb;cursor:default}

/* welcome message */
.welcome{text-align:center;padding:32px 16px;color:#888}
.welcome .emoji{font-size:2.5rem;margin-bottom:.5em}
.welcome h2{font-size:1.1rem;color:#444;margin-bottom:.3em}
.welcome p{font-size:.88rem;line-height:1.6}
.suggestions{display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-top:1.2em}
.suggestion{background:#fff;border:1px solid #ddd;border-radius:20px;padding:7px 14px;font-size:.82rem;cursor:pointer;color:#1a73e8;transition:all .15s}
.suggestion:hover{background:#f0f4ff;border-color:#1a73e8}
</style>
</head>
<body>
<div class="app">
  <div class="topbar">
    <div class="brand">
      <span class="emoji">🧘</span>
      <span>קושקה יוגה — ניהול קמפיינים</span>
    </div>
    <div class="user">
      <span>{{ $userEmail }}</span>
      <form method="POST" action="/koshka/logout" style="display:inline">
        @csrf
        <button type="submit" style="background:none;border:none;cursor:pointer;color:#1a73e8;font-size:.82rem;font-family:inherit;padding:0">יציאה</button>
      </form>
    </div>
  </div>

  <div class="messages" id="messages">
    <div class="welcome" id="welcome">
      <div class="emoji">📊</div>
      <h2>שלום, {{ $userName ?: $userEmail }}!</h2>
      <p>אני כאן לעזור לך לנהל את קמפיינות המטא של הסטודיו.<br>שאלי אותי כל שאלה בעברית.</p>
      <div class="suggestions">
        <span class="suggestion" onclick="sendSuggestion(this)">מה מצב הקמפיינים הפעילים?</span>
        <span class="suggestion" onclick="sendSuggestion(this)">כמה הוצאנו החודש?</span>
        <span class="suggestion" onclick="sendSuggestion(this)">איזה קמפיין הכי טוב ב-30 יום האחרונים?</span>
        <span class="suggestion" onclick="sendSuggestion(this)">תציגי לי את כל הקמפיינים עם ה-CPL שלהם</span>
      </div>
    </div>
  </div>

  <div class="input-bar">
    <textarea id="input" placeholder="כתבי שאלה..." rows="1" onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
    <button id="send-btn" onclick="sendMessage()" title="שלח">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
      </svg>
    </button>
  </div>
</div>

<script>
const messagesEl = document.getElementById('messages');
const inputEl = document.getElementById('input');
const sendBtn = document.getElementById('send-btn');
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// conversation history sent to backend on each request
let history = [];

function autoResize(el) {
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 140) + 'px';
}

function handleKey(e) {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault();
    sendMessage();
  }
}

function sendSuggestion(el) {
  inputEl.value = el.textContent;
  sendMessage();
}

function scrollToBottom() {
  messagesEl.scrollTop = messagesEl.scrollHeight;
}

function appendMessage(role, html, isHtml = false) {
  const welcome = document.getElementById('welcome');
  if (welcome) welcome.remove();

  const div = document.createElement('div');
  div.className = 'msg ' + role;
  const bubble = document.createElement('div');
  bubble.className = 'bubble';
  if (isHtml) {
    bubble.innerHTML = html;
  } else {
    bubble.textContent = html;
  }
  div.appendChild(bubble);
  messagesEl.appendChild(div);
  scrollToBottom();
  return div;
}

function appendTyping() {
  const div = document.createElement('div');
  div.className = 'msg assistant typing';
  div.id = 'typing';
  div.innerHTML = '<div class="bubble"><div class="typing-dots"><span></span><span></span><span></span></div></div>';
  messagesEl.appendChild(div);
  scrollToBottom();
}

function removeTyping() {
  const t = document.getElementById('typing');
  if (t) t.remove();
}

async function sendMessage() {
  const text = inputEl.value.trim();
  if (!text || sendBtn.disabled) return;

  inputEl.value = '';
  inputEl.style.height = 'auto';
  sendBtn.disabled = true;

  appendMessage('user', text);
  history.push({ role: 'user', content: text });

  appendTyping();

  try {
    const resp = await fetch('/koshka/chat', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ messages: history }),
    });

    removeTyping();

    if (!resp.ok) {
      const err = await resp.json().catch(() => ({ message: 'שגיאת שרת' }));
      appendMessage('assistant', 'שגיאה: ' + (err.message || resp.status));
      sendBtn.disabled = false;
      return;
    }

    const data = await resp.json();
    const reply = data.reply || '';

    // Show tool activity if any
    if (data.tool_calls?.length) {
      const tools = data.tool_calls.map(t => t.tool).join(', ');
      const indicator = document.createElement('div');
      indicator.className = 'tool-indicator';
      indicator.innerHTML = `<span class="dot"></span> שלפתי נתונים: ${tools}`;
      messagesEl.appendChild(indicator);
    }

    appendMessage('assistant', reply, true);
    history.push({ role: 'assistant', content: reply });

  } catch (e) {
    removeTyping();
    appendMessage('assistant', 'שגיאת חיבור. בדקי אינטרנט ונסי שוב.');
  }

  sendBtn.disabled = false;
  inputEl.focus();
}

inputEl.focus();
</script>
</body>
</html>
