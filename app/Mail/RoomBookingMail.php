<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoomBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        $mail = $this->subject('New Room Booking Received')
            ->view('emails.roombookingmail')
            ->with($this->payload);

        // Image থাকলে attachment হিসেবে attach করি
        if (!empty($this->payload['image_file'])) {
            $imagePath = public_path('bookingsimage/' . $this->payload['image_file']);
            if (file_exists($imagePath)) {
                $mail->attach($imagePath, [
                    'as'   => $this->payload['image_file'],
                    'mime' => mime_content_type($imagePath),
                ]);
            }
        }

        return $mail;
    }
}