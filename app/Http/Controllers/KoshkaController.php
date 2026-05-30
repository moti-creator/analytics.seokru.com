<?php

namespace App\Http\Controllers;

use App\Http\Middleware\KoshkaAuth;
use App\Services\InsightEngine;
use App\Services\MetaService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class KoshkaController extends Controller
{
    // ---------- AUTH ----------

    public function authRedirect()
    {
        return Socialite::driver('google')
            ->redirectUrl(url('/koshka/auth/google/callback'))
            ->scopes(['email', 'profile'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function authCallback(Request $request)
    {
        try {
            $g = Socialite::driver('google')
                ->redirectUrl(url('/koshka/auth/google/callback'))
                ->user();
        } catch (\Throwable $e) {
            $request->session()->flash('koshka_login_error', 'Google sign-in failed: ' . $e->getMessage());
            return redirect('/koshka');
        }

        $email = strtolower((string) $g->getEmail());
        if (!$email || !KoshkaAuth::isAllowed($email)) {
            $request->session()->flash('koshka_login_error', "המייל {$email} אינו מורשה.");
            return redirect('/koshka');
        }

        $request->session()->put('koshka_email', $email);
        $request->session()->put('koshka_name', $g->getName() ?: $email);
        return redirect('/koshka');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['koshka_email', 'koshka_name']);
        return redirect('/koshka');
    }

    // ---------- DASHBOARD (insights-first) ----------

    public function index(Request $request)
    {
        $meta = new MetaService();
        if (!$meta->configured()) {
            return view('koshka.index', $this->emptyView('META_ACCESS_TOKEN או META_AD_ACCOUNT_ID לא מוגדרים.'));
        }

        $campaigns = $meta->campaigns();
        $snoozed = $request->session()->get('koshka_snoozed', []);
        $today = $meta->todayStats();
        $week = $meta->weekComparison();
        $month = $meta->monthStats();
        $totalDaily = $meta->activeDailyBudget();
        $engine = new InsightEngine();
        $cards = $engine->generate($campaigns, $snoozed);
        $overview = $engine->accountOverview($campaigns, $week, $month, $today, $totalDaily);

        return view('koshka.index', [
            'today' => $today,
            'week' => $week,
            'month' => $month,
            'overview' => $overview,
            'cards' => $cards,
            'campaignCount' => count($campaigns),
            'activeCount' => count(array_filter($campaigns, fn($c) => ($c['effective_status'] ?? $c['status']) === 'ACTIVE')),
            'userEmail' => $request->session()->get('koshka_email'),
            'userName' => $request->session()->get('koshka_name'),
            'flash' => $request->session()->pull('koshka_flash'),
            'error' => $request->session()->pull('koshka_error'),
        ]);
    }

    protected function emptyView(string $error): array
    {
        return [
            'today' => null, 'week' => null, 'month' => null, 'overview' => [], 'cards' => [],
            'campaignCount' => 0, 'activeCount' => 0,
            'userEmail' => session('koshka_email'), 'userName' => session('koshka_name'),
            'error' => $error, 'flash' => null,
        ];
    }

    // ---------- ADVANCED — full campaign list ----------

    public function all(Request $request)
    {
        $meta = new MetaService();
        if (!$meta->configured()) {
            return view('koshka.all', $this->emptyView('META_ACCESS_TOKEN לא מוגדר.'));
        }
        return view('koshka.all', [
            'campaigns' => $meta->campaigns(),
            'expandCampaign' => $request->query('expand'),
            'expandedAdsets' => $request->query('expand') ? $meta->adsets($request->query('expand')) : [],
            'userEmail' => session('koshka_email'),
            'flash' => $request->session()->pull('koshka_flash'),
            'error' => $request->session()->pull('koshka_error'),
        ]);
    }

    // ---------- INSIGHT CARD ACTIONS ----------

    public function cardAction(Request $request, string $id)
    {
        $request->validate([
            'action' => 'required|in:pause,activate,snooze,budget_pct,budget_add',
            'param'  => 'nullable|numeric',
        ]);

        $meta = new MetaService();
        $action = $request->input('action');
        $param  = (float) $request->input('param', 0);
        $msg = '';

        switch ($action) {
            case 'pause':
                $r = $meta->setCampaignStatus($id, 'PAUSED');
                $msg = isset($r['error']) ? 'שגיאה: ' . $r['error']['message'] : 'הקמפיין הושהה';
                break;
            case 'activate':
                $r = $meta->setCampaignStatus($id, 'ACTIVE');
                $msg = isset($r['error']) ? 'שגיאה: ' . $r['error']['message'] : 'הקמפיין הופעל';
                break;
            case 'snooze':
                $snoozed = $request->session()->get('koshka_snoozed', []);
                $snoozed[$id] = time() + (7 * 86400);
                $request->session()->put('koshka_snoozed', $snoozed);
                $msg = 'הקלף הוסתר לשבוע';
                break;
            case 'budget_pct':
                $r = $meta->adjustCampaignBudgetPct($id, $param);
                $direction = $param > 0 ? 'הוגדל' : 'הוקטן';
                $msg = "תקציב {$direction} ב-" . abs($param) . "% (עודכנו {$r['updated']} ad sets)";
                break;
            case 'budget_add':
                $r = $meta->addCampaignBudget($id, $param);
                $msg = "נוספו ₪{$param} לכל ad set (עודכנו {$r['updated']})";
                break;
        }

        $request->session()->flash('koshka_flash', $msg);
        return redirect('/koshka');
    }

    // ---------- LEGACY ACTIONS (used by /all advanced view) ----------

    public function campaignStatus(Request $request, string $id)
    {
        $request->validate(['status' => 'required|in:ACTIVE,PAUSED,ARCHIVED']);
        $r = (new MetaService())->setCampaignStatus($id, $request->input('status'));
        $request->session()->flash('koshka_flash', isset($r['error']) ? 'שגיאה' : 'סטטוס עודכן');
        return back();
    }

    public function campaignRename(Request $request, string $id)
    {
        $request->validate(['name' => 'required|string|max:200']);
        $r = (new MetaService())->renameCampaign($id, $request->input('name'));
        $request->session()->flash('koshka_flash', isset($r['error']) ? 'שגיאה' : 'שם עודכן');
        return back();
    }

    public function campaignDuplicate(Request $request, string $id)
    {
        $r = (new MetaService())->duplicateCampaign($id);
        $request->session()->flash('koshka_flash', isset($r['error']) ? 'שגיאה' : 'קמפיין שוכפל');
        return back();
    }

    public function bulkPause(Request $request)
    {
        $meta = new MetaService(); $n = 0;
        foreach ($meta->campaigns() as $c) {
            if ($c['status'] === 'ACTIVE') { $meta->setCampaignStatus($c['id'], 'PAUSED'); $n++; }
        }
        $request->session()->flash('koshka_flash', "הושהו {$n} קמפיינים");
        return back();
    }

    public function bulkActivate(Request $request)
    {
        $meta = new MetaService(); $n = 0;
        foreach ($meta->campaigns() as $c) {
            if ($c['status'] === 'PAUSED') { $meta->setCampaignStatus($c['id'], 'ACTIVE'); $n++; }
        }
        $request->session()->flash('koshka_flash', "הופעלו {$n} קמפיינים");
        return back();
    }

    public function adsetStatus(Request $request, string $id)
    {
        $request->validate(['status' => 'required|in:ACTIVE,PAUSED']);
        $r = (new MetaService())->setAdsetStatus($id, $request->input('status'));
        $request->session()->flash('koshka_flash', isset($r['error']) ? 'שגיאה' : 'Ad Set עודכן');
        return back();
    }

    public function adsetBudget(Request $request, string $id)
    {
        $request->validate(['amount' => 'required|numeric|min:1|max:10000', 'type' => 'required|in:daily,lifetime']);
        $r = (new MetaService())->setAdsetBudget($id, (float)$request->input('amount'), $request->input('type'));
        $request->session()->flash('koshka_flash', isset($r['error']) ? 'שגיאה' : "תקציב עודכן ל-₪" . $request->input('amount'));
        return back();
    }

    public function adsetAds(Request $request, string $id)
    {
        return response()->json((new MetaService())->ads($id));
    }

    public function adPreview(Request $request, string $id)
    {
        $format = $request->query('format', 'MOBILE_FEED_STANDARD');
        $html = (new MetaService())->previewHtml($id, $format);
        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    public function adsetSchedule(Request $request, string $id)
    {
        $request->validate(['start_time' => 'nullable|date', 'end_time' => 'nullable|date']);
        $r = (new MetaService())->setAdsetSchedule($id,
            $request->input('start_time') ? date('c', strtotime($request->input('start_time'))) : null,
            $request->input('end_time') ? date('c', strtotime($request->input('end_time'))) : null,
        );
        $request->session()->flash('koshka_flash', isset($r['error']) ? 'שגיאה' : 'תאריכים עודכנו');
        return back();
    }
}
