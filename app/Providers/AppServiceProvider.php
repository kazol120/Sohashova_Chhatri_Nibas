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
        //
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
