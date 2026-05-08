<?php

namespace App\Http\Controllers;

use App\Models\BoostSubmission;
use App\Models\Connection;
use App\Services\BoostService;
use Illuminate\Http\Request;

class BoostController extends Controller
{
    public function __construct(public BoostService $boost) {}

    /** Boost form. */
    public function form()
    {
        $conn = Connection::find(session('connection_id'));
        $recent = $conn
            ? BoostSubmission::where('connection_id', $conn->id)->latest()->limit(10)->get()
            : collect();

        return view('boost.form', compact('conn', 'recent'));
    }

    /** Submit URL through all channels. */
    public function submit(Request $r)
    {
        $r->validate([
            'url' => ['required', 'url', 'max:1024'],
        ]);

        $conn = Connection::find(session('connection_id'));
        $url = $r->input('url');

        try {
            $sub = $this->boost->boost($url, $conn, [
                'indexnow' => $r->boolean('indexnow', true),
                'indexing_api' => $r->boolean('indexing_api', true),
                'wayback' => $r->boolean('wayback', true),
                'archive_today' => $r->boolean('archive_today', true),
                'websub' => $r->boolean('websub', true),
                'gist' => $r->boolean('gist', true),
                'bluesky' => $r->boolean('bluesky', true),
                'telegram' => $r->boolean('telegram', true),
            ]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['rate' => $e->getMessage()])->withInput();
        }

        return redirect()->route('boost.show', $sub);
    }

    /** Show boost result page. */
    public function show(BoostSubmission $boost)
    {
        // Restrict to owner if a connection is attached
        $conn = Connection::find(session('connection_id'));
        if ($boost->connection_id && (!$conn || $boost->connection_id !== $conn->id)) {
            abort(403);
        }

        return view('boost.show', ['sub' => $boost, 'conn' => $conn]);
    }

    /** Download IndexNow key file (the user uploads to https://{host}/{key}.txt). */
    public function downloadIndexNowKey(BoostSubmission $boost)
    {
        $key = data_get($boost->indexnow_result, 'key');
        abort_unless($key, 404);

        return response($key, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $key . '.txt"',
        ]);
    }
}
