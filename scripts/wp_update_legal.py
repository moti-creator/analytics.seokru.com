"""One-shot: append OAuth addendum to www.seokru.com privacy + terms via WP REST API.
Idempotent — skips if marker already present."""
import json, urllib.request, urllib.error, base64, sys

USER = 'n8n_seokru'
PASS = 'gkef aSUL Cg8f nvDl Rhz6 d1gm'
BASE = 'https://www.seokru.com/wp-json/wp/v2/pages'
MARKER = 'seokru-analytics-oauth-addendum'

PRIVACY_ADD = """
<!-- seokru-analytics-oauth-addendum -->
<h3>11. SEOKRU Analytics App (analytics.seokru.com) — Google API Data</h3>
<p>SEOKRU operates a separate application at <a href="https://analytics.seokru.com">analytics.seokru.com</a> ("the App") which connects to users' own Google Analytics 4 and Google Search Console accounts via Google OAuth. This section describes how that App handles Google user data.</p>
<p><b>Google API Services User Data Policy — Limited Use.</b> SEOKRU's use and transfer of information received from Google APIs to any other app adheres to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener">Google API Services User Data Policy</a>, including the Limited Use requirements.</p>
<h4>Scopes requested</h4>
<p>When a user signs in to the App with Google, we request the following read-only scopes:</p>
<ul>
<li><code>https://www.googleapis.com/auth/analytics.readonly</code> — read the user's GA4 properties and traffic metrics (sessions, users, pageviews, conversions, sources, page paths).</li>
<li><code>https://www.googleapis.com/auth/webmasters.readonly</code> — read the user's Search Console sites and query/page/impression/click/position data.</li>
<li>Basic Google profile email and user ID — to identify the user's session.</li>
</ul>
<p>We do not request write access. We cannot modify, delete, or create anything in the user's Google account.</p>
<h4>How we use Google data</h4>
<p>We fetch data from the Google APIs on demand when the user clicks a report or asks a question, compute derived statistics (deltas, percentage changes, rankings) in our application, and display the result as a report, a PDF, or a message to the user's Telegram bot (if they explicitly connected it). We do not use Google data for advertising, cross-user analytics, model training, or any purpose other than serving the specific report the user requested.</p>
<h4>Third-party LLM providers</h4>
<p>To convert the numbers we fetch into readable sentences, we send computed metrics (numbers, labels, dates — never OAuth tokens or personally-identifying credentials) to large-language-model APIs: <b>Groq</b> (Llama 3.3 70B) and <b>Google Gemini</b>. These providers return text. Per their terms, inputs are not used to train public models.</p>
<h4>What we store</h4>
<p>In our own database: the user's Google email and user ID; an OAuth access token and refresh token; the user's selected GA4 property ID and Search Console site URL; generated reports; and queries the user explicitly saves. Google API responses are cached for at most 12 hours per user connection.</p>
<h4>Revoke and delete</h4>
<p>Users can revoke the App's access at any time at <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener">https://myaccount.google.com/permissions</a>. To request full deletion of all App data associated with their account, users email <b>info@seokru.com</b> with subject "Delete my account"; we will delete tokens, connection records, and reports within 7 days and confirm.</p>
<h4>Sharing</h4>
<p>We do not sell, rent, or share Google user data with advertisers or data brokers. We do not use Google data to train ML models. We do not allow humans to read Google user data except with explicit user consent for support cases, for security investigations, or to comply with law.</p>
<p>App-specific questions: <a href="mailto:info@seokru.com">info@seokru.com</a>.</p>
<!-- /seokru-analytics-oauth-addendum -->
"""

TERMS_ADD = """
<!-- seokru-analytics-oauth-addendum -->
<h3>12. SEOKRU Analytics App</h3>
<p>SEOKRU operates a separate free pilot application at <a href="https://analytics.seokru.com">analytics.seokru.com</a> which connects to users' own Google Analytics 4 and Google Search Console accounts via Google OAuth to generate plain-English reports.</p>
<p>Use of the App is subject to these Terms together with any supplementary terms published on the App itself. By connecting a Google account to the App, the user warrants they have authority to grant access to the Google properties connected, and agrees to comply with Google's Terms of Service for GA4 and Search Console.</p>
<p>The App is provided "as is" during the pilot. Reports include narrative text produced by AI models and may contain errors; users should verify critical numbers against the source Google tools before making business decisions. SEOKRU's liability for the App is limited as described in the Limitation of Liability section above.</p>
<p>Read-only scopes used by the App: <code>https://www.googleapis.com/auth/analytics.readonly</code> and <code>https://www.googleapis.com/auth/webmasters.readonly</code>. See the <a href="/legal/privacy/">Privacy Policy</a> for full details on how Google user data is handled.</p>
<!-- /seokru-analytics-oauth-addendum -->
"""

def auth_header():
    token = base64.b64encode(f'{USER}:{PASS}'.encode()).decode()
    return {
        'Authorization': f'Basic {token}',
        'Content-Type': 'application/json',
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 SeokruOAuthSetup/1.0',
        'Accept': 'application/json',
    }

def api(method, url, body=None):
    data = json.dumps(body).encode() if body else None
    req = urllib.request.Request(url, data=data, method=method, headers=auth_header())
    try:
        with urllib.request.urlopen(req, timeout=30) as r:
            return r.status, json.loads(r.read())
    except urllib.error.HTTPError as e:
        return e.code, e.read().decode()

for name, pid, add in [('privacy', 2608, PRIVACY_ADD), ('terms', 2610, TERMS_ADD)]:
    print(f'\n=== {name} (id={pid}) ===')
    code, body = api('GET', f'{BASE}/{pid}?context=edit')
    if code != 200:
        print(f'FETCH FAILED {code}: {body}'); continue
    raw = body['content']['raw']
    print(f'current length: {len(raw)}')
    if MARKER in raw:
        print('already has marker — SKIP'); continue
    new_content = raw + add
    code, body = api('POST', f'{BASE}/{pid}', {'content': new_content})
    if code == 200:
        new_len = len(body.get('content', {}).get('raw', ''))
        print(f'UPDATED. new length: {new_len}')
    else:
        print(f'UPDATE FAILED {code}: {str(body)[:400]}')

print('\nDONE.')
