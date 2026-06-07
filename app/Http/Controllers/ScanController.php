<?php

namespace App\Http\Controllers;

use App\Enums\ScanStatus;
use App\Jobs\GenerateMusicRecommendations;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'scene_description' => 'required|string|max:2000',
        ]);

        $scan = Scan::create([
            'user_id' => Auth::id(),
            'job_id' => (string) Str::uuid(),
            'status' => ScanStatus::Pending,
            'description' => $data['scene_description'],
            'royalty_free_only' => $request->boolean('royalty_free_only'),
        ]);

        GenerateMusicRecommendations::dispatch($scan->id);

        return response()->json([
            'id' => $scan->id,
            'status' => $scan->status->value,
        ], 201);
    }

    public function show(Scan $scan)
    {
        abort_if($scan->user_id !== Auth::id(), 403);

        return response()->json([
            'id' => $scan->id,
            'status' => $scan->status->value,
            'result' => $scan->result,
        ]);
    }
}
