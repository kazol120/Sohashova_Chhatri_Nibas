<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResidentNoticeAlertMail extends Mailable
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
        $siteTitle  = $webSetting['title'] ?? 'টি এস এস ভিলা';
        $noticeTitle = $this->data['title'] ?? 'জরুরি বিজ্ঞপ্তি';

        return $this->subject("📢 [{$siteTitle}] {$noticeTitle}")
            ->view('emails.resident-notice-alert')
            ->with(array_merge($this->data, [
                'siteTitle'   => $siteTitle,
                'sitePhone'   => $webSetting['phone'] ?? '01977270920',
                'siteAddress' => $webSetting['address'] ?? '',
            ]));
    }
}
