<?php

namespace App\Http\Controllers;

use App\Models\TdnetLead;
use App\Services\GroqService;
use Illuminate\Http\Request;

class TdnetController extends Controller
{
    public function index(Request $request)
    {
        $q = TdnetLead::query();

        if ($s = $request->query('status')) {
            $q->where('status', $s);
        } else {
            $q->where('status', 'new');
        }
        if ($c = $request->query('country')) {
            $q->where('country', 'like', "%{$c}%");
        }
        if ($co = $request->query('company')) {
            $q->where('company', 'like', "%{$co}%");
        }
        if ($seg = $request->query('segment')) {
            $q->where('segment', $seg);
        }

        $leads = $q->orderByDesc('id')->limit(100)->get();

        $counts = [
            'new'     => TdnetLead::where('status', 'new')->count(),
            'sent'    => TdnetLead::where('status', 'sent')->count(),
            'replied' => TdnetLead::where('status', 'replied')->count(),
            'skipped' => TdnetLead::where('status', 'skipped')->count(),
        ];

        $countries = TdnetLead::select('country')->distinct()->whereNotNull('country')->pluck('country');
        $segments  = TdnetLead::select('segment')->distinct()->whereNotNull('segment')->pluck('segment');

        return view('tdnet.index', compact('leads', 'counts', 'countries', 'segments'));
    }

    public function show(TdnetLead $lead)
    {
        return response()->json($lead);
    }

    public function generate(Request $request, TdnetLead $lead, GroqService $groq)
    {
        $style = $request->input('style', 'question');
        if (!in_array($style, ['question', 'statement', 'pain'])) {
            $style = 'question';
        }

        if (!$groq->available()) {
            return response()->json(['error' => 'Groq not configured'], 503);
        }

        $prompt = $this->buildPrompt($lead, $style);

        $resp = $groq->chat([
            ['role' => 'system', 'content' => 'You are an expert B2B copywriter for TDNet, a research/digital library platform sold to medical, academic, and pharma libraries. You write peer-to-peer cold emails to information professionals. Output strictly valid JSON, no markdown fences.'],
            ['role' => 'user', 'content' => $prompt],
        ], null, 0.7);

        $content = $resp['choices'][0]['message']['content'] ?? '';
        $content = trim(preg_replace('/^```(?:json)?|```$/m', '', $content));
        $data = json_decode($content, true);

        if (!is_array($data) || !isset($data['subjects']) || !isset($data['body'])) {
            return response()->json(['error' => 'LLM returned invalid format', 'raw' => $content], 502);
        }

        $subjects = array_slice(array_values($data['subjects']), 0, 10);
        $body = trim($data['body']);

        $lead->update([
            'subject_variants' => $subjects,
            'email_subject'    => $subjects[0] ?? null,
            'email_body'       => $body,
            'email_style'      => $style,
        ]);

        return response()->json([
            'subjects' => $subjects,
            'body' => $body,
            'style' => $style,
        ]);
    }

    public function regenerateBody(Request $request, TdnetLead $lead, GroqService $groq)
    {
        $style = $request->input('style', $lead->email_style ?? 'question');
        $subject = $request->input('subject', $lead->email_subject);

        $prompt = $this->buildBodyOnlyPrompt($lead, $style, $subject);
        $resp = $groq->chat([
            ['role' => 'system', 'content' => 'You write peer-to-peer cold emails to medical/academic/pharma information professionals on behalf of TDNet. Output plain text body only, no preamble, no signature beyond what is asked.'],
            ['role' => 'user', 'content' => $prompt],
        ], null, 0.8);

        $body = trim($resp['choices'][0]['message']['content'] ?? '');
        $body = preg_replace('/^```.*?\n|```$/s', '', $body);

        $lead->update(['email_body' => $body]);
        return response()->json(['body' => $body]);
    }

    public function pickSubject(Request $request, TdnetLead $lead)
    {
        $subject = $request->input('subject');
        if (!$subject) return response()->json(['error' => 'subject required'], 422);
        $lead->update(['email_subject' => $subject]);
        return response()->json(['ok' => true]);
    }

    public function markSent(TdnetLead $lead)
    {
        $lead->update(['status' => 'sent', 'sent_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function markSkipped(TdnetLead $lead)
    {
        $lead->update(['status' => 'skipped']);
        return response()->json(['ok' => true]);
    }

    public function markReplied(TdnetLead $lead)
    {
        $lead->update(['status' => 'replied']);
        return response()->json(['ok' => true]);
    }

    public function source(Request $request)
    {
        $count = (int) ($request->input('count', 10));
        $count = max(5, min(50, $count));
        \Artisan::call('tdnet:source', ['--count' => $count]);
        return response()->json([
            'ok' => true,
            'output' => \Artisan::output(),
        ]);
    }

    /* ========== prompts ========== */

    protected function buildPrompt(TdnetLead $lead, string $style): string
    {
        $segment = $lead->segment ?? 'corporate';
        $name    = $lead->fullName() ?: 'there';
        $first   = $lead->first_name ?: 'there';
        $title   = $lead->position ?: 'Information Specialist';
        $company = $lead->company ?: 'your organization';
        $country = $lead->country ?: '';

        $segHook = $this->segmentHook($segment);
        $proof   = $this->proofPoint($segment);
        $styleHint = match ($style) {
            'question'  => 'Subjects should be QUESTIONS — short, curious, lowercase OK.',
            'statement' => 'Subjects should be STATEMENTS — concrete, declarative, name a specific outcome.',
            'pain'      => 'Subjects should hint at PAIN — friction the recipient likely feels every week.',
        };

        return <<<PROMPT
Write a cold outreach email for TDNet (tdnet.com) to a prospect.

PROSPECT
- Name: {$name}
- First name: {$first}
- Title: {$title}
- Organization: {$company}
- Country: {$country}
- ICP segment: {$segment}

TDNET CONTEXT
TDNet sells AI-enhanced library discovery + access tools to research/digital libraries. Content-neutral software.
Products: TDNet Discover (single search across STEM subscriptions + internal), TDNet AI (synthesis with citations), Library Portal (drag-and-drop branded site), DataSphere (institutional repository), Remote Access (OpenAthens SSO).

PITCH ANGLE FOR THIS SEGMENT
{$segHook}

NON-US PROOF
{$proof}

CONSTRAINTS
- Subject ≤60 chars. Body 90-130 words.
- Peer-to-peer voice. Recipient is an information professional. Do NOT call them "team" or talk down.
- Mention {$company} or their context, not generic platitudes.
- One concrete claim, one demo CTA (15-min walkthrough). No price talk.
- Plain text body. End with: a one-line CTA + signoff "— [Sales Rep Name]\\nTDNet".
- {$styleHint}
- Generate exactly 10 distinct subject options in the chosen style.

OUTPUT
Strictly valid JSON, no markdown fences:
{
  "subjects": ["...", "...", ...10 total],
  "body": "Hi {$first},\\n\\n... full email body ..."
}
PROMPT;
    }

    protected function buildBodyOnlyPrompt(TdnetLead $lead, string $style, ?string $subject): string
    {
        $first   = $lead->first_name ?: 'there';
        $title   = $lead->position ?: 'Information Specialist';
        $company = $lead->company ?: 'your organization';
        $segment = $lead->segment ?? 'corporate';
        $segHook = $this->segmentHook($segment);
        $proof   = $this->proofPoint($segment);
        $sub     = $subject ?: '(subject to be chosen)';

        return <<<PROMPT
Rewrite the cold-email body for TDNet outreach. New voice, fresh angle, same facts.

PROSPECT: {$first} ({$title}) at {$company} — segment: {$segment}
SUBJECT: {$sub}

PITCH: {$segHook}
PROOF: {$proof}

CONSTRAINTS
- 90-130 words. Plain text. Peer-to-peer. Not generic.
- One concrete claim. One demo CTA (15-min walkthrough). No price.
- Start: "Hi {$first},"  ·  End: short CTA + "— [Sales Rep Name]\\nTDNet"

Output the body only. No subject. No JSON.
PROMPT;
    }

    protected function segmentHook(string $seg): string
    {
        return match ($seg) {
            'pharma' => 'Pharma R&D info pros: TDNet DataSphere (secure repo, SAML/API, certified info-sec) + Discover (single search across STM subs + internal scientific output). Usage analytics support STM-renewal decisions.',
            'academic' => 'Academic medical librarians: Discover (single search across PubMed, ClinicalTrials, SciFinder, Reaxys + internal) + Library Portal (drag-drop branded homepage) + repository for institutional authorship.',
            'hospital' => 'Hospital librarians: Discover speeds clinician lit search across PubMed + internal guidelines (MeSH/Embase indexing). OpenAthens remote access ships built-in for off-site clinicians.',
            default    => 'Corporate librarians / R&D info managers: TDNet unifies external STM subscriptions with internal scientific output via DataSphere repo. SAML, API, OpenAthens, certified info-sec.',
        };
    }

    protected function proofPoint(string $seg): string
    {
        return match ($seg) {
            'pharma'   => 'Pfizer (global pharma) and Philips (NL corporate eLibrary) are TDNet customers — relevant for ex-US pharma R&D info teams.',
            'academic' => 'Philips (NL) runs TDNet across their corporate eLibrary; CHSU and Virtua use it for medical-school + health-system libraries.',
            'hospital' => 'Virtua Health and Nuvance Health use TDNet across hospital library workflows; Philips (NL) is a non-US reference customer.',
            default    => 'Philips (NL) and Pfizer are TDNet reference customers globally.',
        };
    }
}
