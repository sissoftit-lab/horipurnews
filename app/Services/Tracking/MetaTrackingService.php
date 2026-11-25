<?php

namespace App\Services\Tracking;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Meta Pixel (browser) + Conversions API (server) event tracking.
 * Browser and server send the same event_id so Meta de-duplicates them.
 */
class MetaTrackingService
{
    public function enabled(): bool
    {
        return (bool) config('tracking.meta.enabled') && config('tracking.meta.pixel_id');
    }

    public function pixelId(): ?string
    {
        return config('tracking.meta.pixel_id');
    }

    public function serverEnabled(): bool
    {
        return $this->enabled() && config('tracking.meta.access_token');
    }

    public function isAllowed(string $event): bool
    {
        return in_array($event, config('tracking.allowed_events'), true);
    }

    /** Send one event to the Conversions API. Never throws. */
    public function sendServerEvent(Request $request, string $event, string $eventId, array $custom = [], ?string $sourceUrl = null): void
    {
        if (!$this->serverEnabled()) {
            return;
        }

        $cfg = config('tracking.meta');

        $payload = [
            'data' => [[
                'event_name'       => $event,
                'event_time'       => time(),
                'event_id'         => $eventId,
                'event_source_url' => $sourceUrl ?: ($request->headers->get('referer') ?: url()->current()),
                'action_source'    => 'website',
                'user_data'        => $this->userData($request),
                'custom_data'      => (object) $custom,
            ]],
            'access_token' => $cfg['access_token'],
        ];
        if (!empty($cfg['test_event_code'])) {
            $payload['test_event_code'] = $cfg['test_event_code'];
        }

        try {
            $res = Http::timeout($cfg['timeout'])->asJson()->post(
                "https://graph.facebook.com/{$cfg['api_version']}/{$cfg['pixel_id']}/events",
                $payload
            );
            if ($res->failed()) {
                Log::warning('Meta CAPI error', ['status' => $res->status(), 'body' => $res->body()]);
            }
        } catch (\Throwable $e) {
            Log::warning('Meta CAPI exception: '.$e->getMessage());
        }
    }

    private function userData(Request $request): array
    {
        $u = [
            'client_ip_address' => $request->ip(),
            'client_user_agent' => (string) $request->userAgent(),
        ];
        if ($fbp = $request->cookie('_fbp')) {
            $u['fbp'] = $fbp;
        }
        if ($fbc = $request->cookie('_fbc')) {
            $u['fbc'] = $fbc;
        }

        $user = auth()->user();
        if ($user && !empty($user->email)) {
            $u['em'] = [hash('sha256', strtolower(trim($user->email)))];
        }
        if ($user && $user->getKey()) {
            $u['external_id'] = [hash('sha256', (string) $user->getKey())];
        }

        return $u;
    }
}
