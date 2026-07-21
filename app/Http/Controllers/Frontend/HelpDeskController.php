<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Backend\RoomBookingHistory;


class HelpDeskController extends Controller

{

public function sendHelpDeskEmail(Request $request)
{
    $request->validate([
        'phone'   => 'required',
        'message' => 'required',
    ]);

    $phone = $this->normalizeBdPhone($request->phone);

    $bookings = RoomBookingHistory::where('phone', $phone)
        ->orderBy('id', 'desc')
        ->get();

    if ($bookings->isEmpty()) {
        return back()->withErrors([
            'phone' => 'No booking information found for this phone number.'
        ])->withInput();
    }
    $latestBooking = $bookings->first();
    $bookingInfo = $this->getRoomBookingInfo($latestBooking);
$data = [
    'guest_name'   => $latestBooking->full_name ?? 'N/A',
    'guest_email'  => $latestBooking->email ?? 'N/A',
    'phone'        => $latestBooking->phone ?? $phone,
    'details'      => $request->message,
    'created_time' => \Carbon\Carbon::now('Asia/Dhaka')->format('d M Y, h:i A'),
    'booking_info' => $bookingInfo,
];
    Mail::send('emails.helpdesk', $data, function ($mail) use ($data) {
        $mail->to('mr2798492@gmail.com')
             ->subject('HelpDesk Request From Booking Guest - ' . $data['phone']);
    });
    return back()->with('success', 'Your request has been sent successfully!');
}

private function normalizeBdPhone($phone)
{
    $phone = preg_replace('/[^0-9]/', '', $phone);

    if (substr($phone, 0, 3) === '880') {
        return '+' . $phone;
    }

    if (substr($phone, 0, 1) === '0') {
        return '+880' . substr($phone, 1);
    }

    return '+880' . $phone;
}


private function getRoomBookingInfo($booking)
{
    $roomsData = $booking->floor_number_room_number_roomprice;

    if (is_array($roomsData)) {
        $rooms = $roomsData;
    } else {
        $rooms = json_decode($roomsData, true);
    }

    if (!is_array($rooms)) {
        return [];
    }

    $grouped = [];

    foreach ($rooms as $room) {
        $floorName  = $room['floornumber'] ?? 'N/A';
        $roomNumber = $room['roomnumber'] ?? null;

        if (!isset($grouped[$floorName])) {
            $grouped[$floorName] = [];
        }

        if ($roomNumber) {
            $grouped[$floorName][] = $roomNumber;
        }
    }

    $bookingInfo = [];

    foreach ($grouped as $floorName => $roomNumbers) {
        $bookingInfo[] = [
            'floor_name'  => $floorName,
            'room_number' => implode(', ', array_unique($roomNumbers)),
        ];
    }

    return $bookingInfo;
}

}
