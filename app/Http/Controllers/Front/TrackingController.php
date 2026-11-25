<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Tracking\MetaTrackingService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function event(Request $request, MetaTrackingService $meta)
    {
        $data = $request->validate([
            'event'    => 'required|string|max:50',
            'event_id' => 'required|string|max:100',
            'url'      => 'nullable|string|max:2000',
            'params'   => 'nullable|array',
        ]);

        if (!$meta->isAllowed($data['event'])) {
            return response()->noContent(422);
        }

        // Only simple scalar custom params are forwarded.
        $params = collect($data['params'] ?? [])
            ->filter(fn ($v) => is_scalar($v))
            ->map(fn ($v) => is_string($v) ? mb_substr($v, 0, 200) : $v)
            ->take(15)->all();

        // Runs after the response is sent so the visitor never waits on Meta.
        defer(fn () => $meta->sendServerEvent($request, $data['event'], $data['event_id'], $params, $data['url'] ?? null));

        return response()->noContent();
    }
}
