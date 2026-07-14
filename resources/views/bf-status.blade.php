<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>Portfolio Status &amp; SEO Reports — Ben Friedman | SEOKRU</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1a2233;background:#f7f8fb;line-height:1.55}
.topbar{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;background:#12213f;color:#fff}
.topbar .brand{font-weight:700}
.btn-sm{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.25);color:#fff;padding:6px 12px;border-radius:6px;text-decoration:none;font-size:.82rem}
.btn-sm:hover{background:rgba(255,255,255,.24)}
.wrap{max-width:1000px;margin:0 auto;padding:0 20px 72px}
h1{font-size:1.7rem;margin:26px 0 4px}
.sub{color:#5a6478;margin:0 0 8px}
.upd{font-size:.8rem;color:#8a94a8;margin:0 0 24px}
h2{font-size:1.15rem;margin:34px 0 10px;padding-bottom:6px;border-bottom:2px solid #e4e8f0}
h3{font-size:1rem;margin:18px 0 8px;color:#12213f}
table{border-collapse:collapse;width:100%;margin:12px 0 20px;font-size:.9rem;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.05)}
th{background:#1a2b4d;color:#fff;text-align:left;padding:9px 12px;font-size:.74rem;letter-spacing:.03em;text-transform:uppercase}
td{padding:8px 12px;border-top:1px solid #eef1f6;vertical-align:top}
tr:nth-child(even) td{background:#fafbfd}
.pill{display:inline-block;font-size:.72rem;font-weight:700;padding:2px 9px;border-radius:20px;white-space:nowrap}
.p-done{background:#e3f5e9;color:#1a7f43}.p-prog{background:#fff4d6;color:#8a6d1a}.p-none{background:#eef1f6;color:#6b7688}.p-block{background:#fde4e4;color:#a32020}
ul{margin:.3rem 0 1rem;padding-left:1.2rem}li{margin:.3rem 0}
.cols{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:720px){.cols{grid-template-columns:1fr}}
.box{background:#fff;border:1px solid #e4e8f0;border-radius:12px;padding:16px 18px}
.box.done{border-left:4px solid #1a7f43}.box.todo{border-left:4px solid #d99a1a}.box.block{border-left:4px solid #a32020}
.legend{font-size:.8rem;color:#5a6478;margin:6px 0 0}
.num{font-variant-numeric:tabular-nums;font-weight:700;color:#1a73e8}
.back{color:#1a73e8;text-decoration:none;font-size:.9rem}
.note{background:#eef3ff;border:1px solid #c7d7ff;border-radius:10px;padding:12px 16px;font-size:.88rem;color:#1a3a6b;margin:16px 0}
</style>
</head>
<body>
<div class="topbar">
  <div class="brand">SEOKRU · Ben Friedman</div>
  <div><a href="/bf" class="btn-sm">← Back to dashboard</a></div>
</div>

<div class="wrap">
  <h1>Portfolio Status &amp; SEO Reports</h1>
  <p class="sub">Six-site Israel / Law-of-Return portfolio — what's done, what's pending, and the keyword research driving the content.</p>
  <p class="upd">Pilot: crypto-jews.org · Live preview + analytics wired · Last updated 2026-07-14</p>

  <h2>1. Site-by-site status</h2>
  <table>
    <tr><th>Site</th><th>Mission</th><th>Stage</th><th>Live</th></tr>
    <tr><td><b>crypto-jews.org</b></td><td>Free DNA — Sephardic / Anusim</td><td><span class="pill p-prog">Pilot built (preview)</span></td><td>Preview deploy, pending owner sign-off</td></tr>
    <tr><td>1in56million.org</td><td>Free DNA — British-Israelism</td><td><span class="pill p-none">Not started</span></td><td>Old single-page site</td></tr>
    <tr><td>get-out-now.org</td><td>Free flights to Israel</td><td><span class="pill p-none">Not started</span></td><td>Old single-page site</td></tr>
    <tr><td>projectshreeram.org</td><td>Hindu aliyah advocacy</td><td><span class="pill p-none">Not started</span></td><td>Old single-page site</td></tr>
    <tr><td>interfaith-sanctuary.org</td><td>Noahide community</td><td><span class="pill p-none">Not started</span></td><td>Old single-page site</td></tr>
    <tr><td>thelastdate.org</td><td>Nonprofit science matchmaking</td><td><span class="pill p-none">Not started</span></td><td>Old single-page site</td></tr>
  </table>
  <p class="legend"><span class="pill p-done">Done</span> &nbsp;<span class="pill p-prog">In progress</span> &nbsp;<span class="pill p-block">Blocked (needs owner)</span> &nbsp;<span class="pill p-none">Not started</span></p>

  <div class="note"><b>Approach:</b> crypto-jews is the pilot. Once its template is approved it gets cloned to the other five (same design system, SEO structure, consent-gated analytics).</div>

  <h2>2. crypto-jews.org — detailed status</h2>
  <div class="cols">
    <div class="box done">
      <h3>✅ Done</h3>
      <ul>
        <li>Rebuilt from single-file JS-toggle SPA → real multi-page site</li>
        <li>Per-language indexable pages: ES (root), PT, EN, with reciprocal <b>hreflang</b> + x-default (real translations, no Google Translate)</li>
        <li>3 keyword-targeted content pages (Surnames, Marranos/Conversos/Anusim, Sephardic Ancestry) in ES/PT/EN</li>
        <li>Optimized titles + meta descriptions + Open Graph + JSON-LD (Organization, FAQPage, Article)</li>
        <li>sitemap.xml (with hreflang), robots.txt, llms.txt</li>
        <li>1.1&nbsp;MB inline logo → 27&nbsp;KB WebP; favicons; pages ~13&nbsp;KB</li>
        <li>Contact form on free Netlify Forms (consent + honeypot)</li>
        <li>Consent-gated GA4 (Consent Mode v2 + cookie banner)</li>
        <li>Honest claims baked in (DNA ≠ Law-of-Return eligibility)</li>
      </ul>
    </div>
    <div class="box todo">
      <h3>🔜 To do (unblocked)</h3>
      <ul>
        <li>Write real content to replace lorem on all supporting pages</li>
        <li>Localize supporting pages fully across ES/PT/EN</li>
        <li>5–10 SEO articles (pillar + cluster) per keyword plan</li>
        <li>Security headers + asset caching + branded 404</li>
        <li>WebSite + BreadcrumbList schema; real 1200×630 OG image</li>
        <li>Switch homepage lead-forms to Netlify Forms</li>
        <li>Submit sitemaps to GSC + Bing</li>
      </ul>
    </div>
  </div>
  <div class="box block" style="margin-top:16px">
    <h3>🔒 Blocked — needs owner input</h3>
    <ul>
      <li><b>Legal entity</b> + jurisdiction (data controller) → unblocks About + all legal pages</li>
      <li><b>DNA lab / processor</b>, kits-sold?, health-analysis? → DNA-consent page + honesty</li>
      <li><b>Form destination</b> (email / sheet / CRM)</li>
      <li>One-brand-or-six decision · custom-domain email (retire ProtonMail)</li>
    </ul>
  </div>

  <h2>3. Keyword research — crypto-jews (SEMrush, US)</h2>
  <p class="sub" style="font-size:.9rem">Top validated terms driving the content pages. Volume = monthly (US); KD = difficulty. ES/PT target translated equivalents. AI Overviews present on most → GEO/FAQ formatting prioritized.</p>
  <table>
    <tr><th>Keyword</th><th>Vol/mo</th><th>KD</th><th>Target page</th></tr>
    <tr><td>jewish surnames</td><td class="num">12,100</td><td>43</td><td>Surnames</td></tr>
    <tr><td>marrano</td><td class="num">2,900</td><td>28</td><td>Marranos/Conversos/Anusim</td></tr>
    <tr><td>crypto jews</td><td class="num">1,300</td><td>33</td><td>Home / pillar</td></tr>
    <tr><td>crypto judaism</td><td class="num">720</td><td>33</td><td>Marranos page</td></tr>
    <tr><td>sephardic surnames / last names</td><td class="num">1,180</td><td>32–34</td><td>Surnames</td></tr>
    <tr><td>am i jewish</td><td class="num">480</td><td>18</td><td>Ancestry (easy win)</td></tr>
    <tr><td>jewish ancestry</td><td class="num">390</td><td>33</td><td>Home / ancestry</td></tr>
    <tr><td>anusim</td><td class="num">210</td><td>18</td><td>Marranos page</td></tr>
    <tr><td>spanish jewish surnames</td><td class="num">210</td><td>32</td><td>Surnames</td></tr>
    <tr><td>sephardic jewish dna</td><td class="num">140</td><td>25</td><td>DNA / kit</td></tr>
    <tr><td>portuguese / spanish citizenship sephardic</td><td class="num">310</td><td>12–19</td><td>Future citizenship page (low KD)</td></tr>
    <tr><td>sephardic ancestry / am i sephardic / hidden jewish ancestry</td><td class="num">~180</td><td>low</td><td>Ancestry page (long-tail)</td></tr>
  </table>
  <p class="legend">Pending: ES (Mexico + Spain) + deeper DNA/citizenship clusters. Full map lives in the project (keyword-map.md). The <b>Surnames</b> (12.1k) and <b>Marrano</b> (2.9k) terms are the volume prizes; citizenship terms are low-difficulty future wins.</p>

  <h2>4. Technical SEO coverage — llms.txt · schema · sitemap · robots</h2>
  <p class="sub" style="font-size:.9rem">Machine-readable + structured-data foundation per site. crypto-jews is complete on the rebuild (preview); the other five old single-page sites have none of these yet and get the full set on rebuild.</p>
  <table>
    <tr><th>Site</th><th>llms.txt</th><th>Schema (JSON-LD)</th><th>sitemap.xml</th><th>robots.txt</th></tr>
    <tr>
      <td><b>crypto-jews.org</b></td>
      <td><span class="pill p-done">✅ built</span></td>
      <td><span class="pill p-done">✅ Org · FAQPage · Article</span></td>
      <td><span class="pill p-done">✅ + hreflang</span></td>
      <td><span class="pill p-done">✅</span></td>
    </tr>
    <tr><td>1in56million.org</td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td></tr>
    <tr><td>get-out-now.org</td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td></tr>
    <tr><td>projectshreeram.org</td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td></tr>
    <tr><td>interfaith-sanctuary.org</td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td></tr>
    <tr><td>thelastdate.org</td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td><td><span class="pill p-none">—</span></td></tr>
  </table>
  <p class="legend"><b>crypto-jews details:</b> <b>llms.txt</b> — mission + honest DNA note + page index for AI crawlers. <b>Schema</b> — Organization on homepages, FAQPage on FAQ, Article on each content page (WebSite + BreadcrumbList pending). <b>Sitemap</b> — all language URLs with reciprocal hreflang alternates. Goes live on production when the pilot is approved + merged.</p>

  <h2>5. Analytics &amp; Search Console</h2>
  <ul>
    <li><b>GSC:</b> 6 URL-prefix properties added (all domains). Verify each via the Google Analytics method now that GA4 is live.</li>
    <li><b>GA4:</b> one property per site, IDs recorded. crypto-jews (<code>G-NQ0PYCVSCN</code>) is wired consent-gated; the other five get the same on rebuild.</li>
    <li><b>This dashboard (/bf):</b> scoped to the six Friedman properties only.</li>
  </ul>

  <p style="margin-top:30px"><a href="/bf" class="back">← Back to the dashboard</a></p>
</div>
</body>
</html>
