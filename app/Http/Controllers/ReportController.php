<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Connection;
use App\Models\Report;
use App\Services\GoogleService;
use App\Services\GeminiService;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function connectForm()
    {
        $conn = Connection::find(session('connection_id'));
        if (!$conn) return redirect('/');

        $g = new GoogleService($conn);
        $properties = $g->listGa4Properties();
        $sites = $g->listGscSites();

        return view('connect', compact('conn', 'properties', 'sites'));
    }

    public function generate(Request $r)
    {
        $conn = Connection::find(session('connection_id'));
        if (!$conn) return redirect('/');

        $r->validate([
            'ga4_property_id' => 'required',
            'gsc_site_url' => 'required',
        ]);

        $conn->update([
            'ga4_property_id' => $r->ga4_property_id,
            'gsc_site_url' => $r->gsc_site_url,
        ]);

        $end = now()->subDay()->toDateString();
        $start = now()->subDays(7)->toDateString();
        $prevEnd = now()->subDays(8)->toDateString();
        $prevStart = now()->subDays(14)->toDateString();

        $g = new GoogleService($conn);

        $metrics = [
            'period' => ['start' => $start, 'end' => $end],
            'previous' => ['start' => $prevStart, 'end' => $prevEnd],
            'ga4_current' => $g->fetchGa4($r->ga4_property_id, $start, $end),
            'ga4_previous' => $g->fetchGa4($r->ga4_property_id, $prevStart, $prevEnd),
            'gsc_current' => $g->fetchGsc($r->gsc_site_url, $start, $end),
            'gsc_previous' => $g->fetchGsc($r->gsc_site_url, $prevStart, $prevEnd),
        ];

        $narrative = (new GeminiService())->narrate($metrics);

        $report = Report::create([
            'connection_id' => $conn->id,
            'metrics' => $metrics,
            'narrative' => $narrative,
        ]);

        return redirect()->route('report.show', $report->id);
    }

    public function show($id)
    {
        $report = Report::with('connection')->findOrFail($id);
        return view('report', compact('report'));
    }

    public function pdf($id)
    {
        $report = Report::with('connection')->findOrFail($id);
        $pdf = Pdf::loadView('report', compact('report'));
        return $pdf->download("report-{$report->id}.pdf");
    }
}
