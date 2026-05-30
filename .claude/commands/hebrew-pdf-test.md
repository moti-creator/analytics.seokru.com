---
description: Test Hebrew PDF rendering via dompdf for a generated report (RTL + font support)
---

Steps:
1. Ask user for report slug (or generate fresh: `/generate/<type>` for a HE-content connection).
2. Hit `GET /r/<slug>/pdf` locally. Save output to `storage/app/test/he-<slug>.pdf`.
3. Open the PDF. Check:
   - Hebrew text renders (not boxes / mojibake)
   - RTL direction correct (right-aligned, punctuation on left)
   - QuickChart images render inline
   - Landscape A4 if it's a keyword_rankings variant
   - `$isPdf` compact layout applied (no nav/header bloat)
4. If glyphs broken: verify `dompdf` font config includes Hebrew TTF (Open Sans Hebrew / Heebo / Rubik). Update `config/dompdf.php` font_dir.
5. If `\/path` escaping shows: confirm `report.blade.php` does `str_replace('\\/', '/')` on LLM output.
6. Report findings + screenshot of issue area if broken.
