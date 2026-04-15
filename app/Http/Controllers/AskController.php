<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Connection;
use App\Models\Report;
use App\Services\GoogleService;
use App\Services\AgentService;
use Barryvdh\DomPDF\Facade\Pdf;

class AskController extends Controller
{
    public function form()
    {
        $conn = Connection::find(session('connection_id'));
        if (!$conn) return redirect('/');

        $g = new GoogleService($conn);
        $properties = $g->listGa4Properties();
        $sites = $g->listGscSites();

        return view('ask', compact('conn', 'properties', 'sites'));
    }

    public function run(Request $r)
    {
        $conn = Connection::find(session('connection_id'));
        if (!$conn) return redirect('/');

        $r->validate([
            'prompt' => 'required|string|max:2000',
            'ga4_property_id' => 'nullable|string',
            'gsc_site_url' => 'nullable|string',
        ]);

        $conn->update([
            'ga4_property_id' => $r->ga4_property_id ?: $conn->ga4_property_id,
            'gsc_site_url' => $r->gsc_site_url ?: $conn->gsc_site_url,
        ]);

        $agent = new AgentService($conn);
        $result = $agent->run($r->prompt);

        $report = Report::create([
            'connection_id' => $conn->id,
            'type' => 'ask',
            'title' => \Str::limit($r->prompt, 80),
            'metrics' => ['prompt' => $r->prompt, 'tool_calls' => $result['tool_calls'], 'iterations' => $result['iterations']],
            'narrative' => $result['narrative'],
        ]);

        return redirect()->route('report.show', $report->id);
    }
}
