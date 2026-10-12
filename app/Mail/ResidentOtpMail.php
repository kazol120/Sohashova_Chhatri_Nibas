<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResidentOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $webSetting = \App\Services\SettingService::getSettingContentBySlug('web_setting');
        $siteTitle = $webSetting['title'] ?? 'TSS Villa';

        $subject = ($this->data['type'] ?? '') === 'forgot_password'
            ? 'Password Reset Verification Code - ' . $siteTitle
            : 'Login Verification Code - ' . $siteTitle;

        return $this->subject($subject)
            ->view('emails.resident-otp')
            ->with($this->data);
    }
}
