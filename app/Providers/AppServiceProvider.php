<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Backend\Floor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 1. Explicit override via .env if specified (e.g. PUBLIC_PATH=base, PUBLIC_PATH=public, or PUBLIC_PATH=public_html)
        $envPublic = $_ENV['PUBLIC_PATH'] ?? $_SERVER['PUBLIC_PATH'] ?? env('PUBLIC_PATH');

        if ($envPublic !== null && $envPublic !== '') {
            if (in_array(strtolower($envPublic), ['base', 'root', '/'])) {
                $targetPublicPath = base_path();
            } else {
                $targetPublicPath = base_path($envPublic);
            }
        } else {
            // 2. Smart Server vs Localhost Detection
            $isLocalhost = false;

            // Check HTTP_HOST
            if (isset($_SERVER['HTTP_HOST'])) {
                $host = strtolower($_SERVER['HTTP_HOST']);
                if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false || strpos($host, '.test') !== false || strpos($host, '.local') !== false) {
                    $isLocalhost = true;
                }
            }

            // Check local dev environment paths (Laragon, XAMPP, WAMP, MAMP)
            $basePath = strtolower(base_path());
            if (strpos($basePath, 'laragon') !== false || strpos($basePath, 'xampp') !== false || strpos($basePath, 'wamp') !== false || strpos($basePath, 'mamp') !== false) {
                $isLocalhost = true;
            }

            // On Windows dev machine with local domain/CLI -> treat as local
            if (PHP_OS_FAMILY === 'Windows' && (empty($_SERVER['HTTP_HOST']) || $isLocalhost)) {
                $isLocalhost = true;
            } else if (PHP_OS_FAMILY !== 'Windows' && !isset($_SERVER['HTTP_HOST'])) {
                // On Linux CLI / Cron on production server -> default to production (base_path)
                $isLocalhost = false;
            }

            // On Localhost -> use public/ directory
            // On Live Production Server -> use base_path() directly
            $targetPublicPath = $isLocalhost ? base_path('public') : base_path();
        }

        // Set application public path directly
        $this->app->usePublicPath($targetPublicPath);

        // Bind IoC container path.public key
        $this->app->bind('path.public', function () use ($targetPublicPath) {
            return $targetPublicPath;
        });
    }

    /**
     * Bootstrap any application services.
     */


    public function boot(): void
    {
         View::composer('Frontend.layouts.partials.booking-offcanvas', function ($view) {
        $floors = Floor::with(['rooms' => function ($q) {
            $q->orderBy('room_no', 'asc')->with('seats');
        }])->get();

        $bookings = \App\Models\Backend\RoomBookingHistory::where('status', '!=', 2)
            ->select('floor_number_room_number_roomprice', 'user_type')
            ->get();
        
        $bookedSeatsUserTypes = [];
        foreach ($bookings as $b) {
            $items = is_string($b->floor_number_room_number_roomprice)
                ? (json_decode($b->floor_number_room_number_roomprice, true) ?? [])
                : ($b->floor_number_room_number_roomprice ?? []);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if (isset($item['roomnumber'])) {
                        $bookedSeatsUserTypes[$item['roomnumber']] = $b->user_type ?? 'student';
                    }
                }
            }
        }

        $view->with([
            'floors' => $floors,
            'bookedSeatsUserTypes' => $bookedSeatsUserTypes,
        ]);
    });

    }

    

}
