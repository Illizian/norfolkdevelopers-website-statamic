<?php

namespace App\Tags;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Statamic\Tags\Tags;

class Meetup extends Tags
{
    protected function getEvents(string $group, int $page, int $limit)
    {
        if ($cache = Cache::get('meetup-events')) {
            return $cache;
        }

        $res = Http::get('https://cors-it-is.shaun.now.sh/', [
            'url' => "https://api.meetup.com/$group/events",
            'sign' => true,
            'photo-host' => 'public',
            'page' => $page,
        ]);

        if (!$res->ok()) {
            Log::error('Error fetching Events from Meetup API');
            return [];
        }

        // Extract events, sort by date, and retrieve the limit
        $events = collect($res->json())
            ->sortBy('time')
            ->take($limit)
            ->map(function ($event) {
                // Extract API props
                $date = $event['local_date']; // "2020-10-16"
                $time = $event['local_time']; // "17:30"
                $duration = $event['duration']; // 7200000

                // Provide Start/End dates as Carbon instances
                $event['start'] = Carbon::createFromFormat('Y-m-d H:i', "$date $time");
                $event['end'] = Carbon::createFromFormat('Y-m-d H:i', "$date $time")->addMilliseconds($duration);

                return $event;
            });

        $ttl = now()->secondsUntilEndOfDay();
        Cache::put('meetup-events', $events, $ttl);

        return $events;
    }

    /**
     * The {{ meetup }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        return $this->events();
    }

    /**
     * The {{ meetup:events }} tag.
     *
     * @return string|array
     */
    public function events()
    {
        return $this->getEvents(
            $this->params->get('group', 'Norfolk-Developers-NorDev'),
            $this->params->int('page', 1),
            $this->params->int('limit', 9)
        );
    }
}
