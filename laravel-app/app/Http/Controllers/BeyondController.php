<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class BeyondController extends Controller
{
    public function home()
    {
        $pubService = app(\App\Services\EventPublicationService::class);
        $homeEvents = $pubService->publishedQuery()
            ->orderBy('event_start_at')
            ->limit(6)
            ->get()
            ->map(function ($event) use ($pubService) {
                $pub = $event->publication;

                return [
                    'slug' => $event->slug,
                    'title' => $pub->public_title ?: $event->name,
                    'summary' => $pub->public_summary,
                    'flyer' => $pubService->publicFlyerUrl($event, $pub),
                    'start' => $event->event_start_at,
                    'venue' => $pub->public_venue ?: $event->venue,
                    'status' => $pubService->computePublicStatus($event, $pub),
                ];
            });

        return view('beyond.home', ['homeEvents' => $homeEvents]);
    }

    public function about()
    {
        try {
            $leaders = \App\Leader::published()->ordered()->get();
        } catch (\Throwable $e) {
            // The About page must render even if the leaders table is missing.
            $leaders = collect();
        }

        return view('beyond.about', ['leaders' => $leaders]);
    }

    public function services()
    {
        return view('beyond.services', [
            'services' => $this->servicesList(),
        ]);
    }

    public function projects()
    {
        return view('beyond.projects', [
            'projects' => [
                [
                    'url' => 'https://www.tiktok.com/@tefurolandmbole/video/7495818139272301829',
                    'title' => 'Project Highlight: Professional Installation',
                ],
                [
                    'url' => 'https://www.tiktok.com/@tefurolandmbole/video/7493245944540974341',
                    'title' => 'Advanced Networking Setup',
                ],
                [
                    'url' => 'https://www.tiktok.com/@tefurolandmbole/video/7492891748327361797',
                    'title' => 'Audio-Visual Excellence',
                ],
            ],
        ]);
    }

    public function gallery()
    {
        return view('beyond.gallery', [
            'items' => \App\GalleryItem::published()->ordered()->get(),
        ]);
    }

    public function contact()
    {
        return view('beyond.contact');
    }

    public function events()
    {
        return view('beyond.events', ['events' => []]);
    }

    private function servicesList()
    {
        return [
            [
                'icon' => 'trending-up',
                'title' => 'Business Development Consulting',
                'description' => 'Strategy, brand positioning, partnerships, and growth programs that turn ambition into measurable traction.',
                'url' => url('/contact') . '?service=business',
            ],
            [
                'icon' => 'package',
                'title' => 'Rental Services',
                'description' => 'Premium AV, staging, and event equipment — delivered, installed, and fully supported for your production.',
                'url' => url('/rentals'),
            ],
            [
                'icon' => 'sparkles',
                'title' => 'Event Planning & Management',
                'description' => 'Corporate, social, and cultural events designed and produced end-to-end — from concept to guest experience.',
                'url' => url('/events'),
            ],
        ];
    }
}
