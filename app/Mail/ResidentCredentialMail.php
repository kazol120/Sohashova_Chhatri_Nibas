<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResidentCredentialMail extends Mailable
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
        $siteTitle = $webSetting['title'] ?? 'টি এস এস ভিলা';

        return $this->subject('আপনার ' . $siteTitle . ' লগইন তথ্য ও পাসওয়ার্ড (Login Credentials - ' . $siteTitle . ')')
            ->view('emails.resident-credentials')
            ->with($this->data);
    }
}
