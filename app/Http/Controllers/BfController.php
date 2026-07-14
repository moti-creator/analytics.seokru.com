<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Connection;
use App\Models\Report;
use App\Services\GoogleService;
use App\Services\ReportBuilder;

/**
 * Ben Friedman portfolio — a scoped copy of the dashboard limited to the six
 * Friedman domains (config/friedman.php). Reuses the existing Google OAuth
 * connection, GoogleService and ReportBuilder, but renders its OWN view
 * (bf.blade.php) and generates through its OWN routes so it never shows or
 * builds data for any non-Friedman site. Does not affect /analytics or /tdnet.
 */
class BfController extends Controller
{
    protected function domains(): array
    {
        return config('friedman.domains', []);
    }

    /** True when the GA4 name or GSC url belongs to a Friedman domain. */
    protected function inScope(?string $haystack): bool
    {
        if (!$haystack) return false;
        foreach ($this->domains() as $d) {
            if (stripos($haystack, $d) !== false) return true;
        }
        return false;
    }

    /**
     * Filter the connection's GA4 + GSC lists to Friedman domains and make sure
     * the connection's ACTIVE selection is one of them (defaulting to the first
     * in-scope option). Returns [$properties, $sites]. Mutates + saves $conn if
     * the active selection was out of scope.
     */
    protected function applyScope(Connection $conn): array
    {
        $properties = [];
        $sites = [];
        try {
            $g = new GoogleService($conn);
            $properties = array_values(array_filter(
                $g->listGa4Properties(),
                fn ($p) => $this->inScope($p['name'] ?? '')
            ));
            $sites = array_values(array_filter(
                $g->listGscSites(),
                fn ($s) => $this->inScope($s['url'] ?? '')
            ));
        } catch (\Throwable $e) {
            // fall through with empty lists
        }

        $ga4InScope = $conn->ga4_property_id
            && collect($properties)->contains(fn ($p) => $p['id'] === $conn->ga4_property_id);
        $gscInScope = $conn->gsc_site_url
            && collect($sites)->contains(fn ($s) => $s['url'] === $conn->gsc_site_url);

        if (!$ga4InScope && !$gscInScope) {
            // Fresh entry (nothing in-scope selected): default BOTH to the first
            // Friedman domain that has data, so GA4 + GSC line up on one site.
            foreach ($this->domains() as $d) {
                $ga4 = collect($properties)->first(fn ($p) => stripos($p['name'] ?? '', $d) !== false);
                $gsc = collect($sites)->first(fn ($s) => stripos($s['url'] ?? '', $d) !== false);
                if ($ga4 || $gsc) {
                    $conn->ga4_property_id = $ga4['id'] ?? null;
                    $conn->gsc_site_url = $gsc['url'] ?? null;
                    $conn->save();
                    break;
                }
            }
        } else {
            // Mid-selection: drop only an out-of-scope side, keep in-scope picks.
            $mutated = false;
            if (!$ga4InScope && $conn->ga4_property_id) { $conn->ga4_property_id = null; $mutated = true; }
            if (!$gscInScope && $conn->gsc_site_url) { $conn->gsc_site_url = null; $mutated = true; }
            if ($mutated) $conn->save();
        }

        return [$properties, $sites];
    }

    public function landing()
    {
        $conn = session('connection_id') ? Connection::find(session('connection_id')) : null;
        if (!$conn) {
            session(['post_auth_redirect' => '/bf']);
            return redirect('/auth/google');
        }

        [$properties, $sites] = $this->applyScope($conn);

        // Recent reports scoped to Friedman: reports store the site inside the
        // metrics JSON (no column), so filter by matching a Friedman domain in
        // the serialized metrics/title. Cross-site reports never appear here.
        $recent = Report::where('connection_id', $conn->id)
            ->latest()->take(30)
            ->get(['id', 'slug', 'type', 'title', 'metrics', 'created_at'])
            ->filter(function ($r) {
                $hay = $r->title . ' ' . json_encode($r->metrics);
                return $this->inScope($hay);
            })
            ->take(5)
            ->values();

        $hasProperty = $conn->ga4_property_id || $conn->gsc_site_url;

        return view('bf', [
            'conn' => $conn,
            'hasProperty' => $hasProperty,
            'properties' => $properties,
            'sites' => $sites,
            'types' => ReportBuilder::TYPES,
            'recent' => $recent,
            'scopeLabel' => config('friedman.label', 'Portfolio'),
        ]);
    }

    /** Status & SEO reports page — what's done, what's pending, keyword research. */
    public function status()
    {
        $conn = session('connection_id') ? Connection::find(session('connection_id')) : null;
        if (!$conn) {
            session(['post_auth_redirect' => '/bf/status']);
            return redirect('/auth/google');
        }
        return view('bf-status', ['conn' => $conn]);
    }

    /** Property switch from the /bf top bar — returns to /bf. */
    public function property(Request $r)
    {
        $conn = Connection::find(session('connection_id'));
        if (!$conn) return redirect('/bf');

        $conn->update([
            'ga4_property_id' => $r->ga4_property_id ?: null,
            'gsc_site_url' => $r->gsc_site_url ?: null,
        ]);

        return redirect('/bf');
    }

    /**
     * Generate a report from /bf. Forces an in-scope Friedman property first, so
     * a report can never be built for another site. Sends the viewer back to /bf
     * via ?from=bf.
     */
    public function generate(string $type)
    {
        $conn = Connection::find(session('connection_id'));
        if (!$conn) {
            session(['post_auth_redirect' => '/bf']);
            return redirect('/auth/google');
        }
        abort_unless(isset(ReportBuilder::TYPES[$type]), 404);

        $this->applyScope($conn);
        $conn->refresh();

        $built = (new ReportBuilder($conn))->build($type);

        $report = Report::create([
            'connection_id' => $conn->id,
            'type' => $built['type'],
            'title' => $built['title'],
            'metrics' => $built['metrics'],
            'narrative' => $built['narrative'],
        ]);

        return redirect(route('report.show', $report) . '?from=bf');
    }
}
