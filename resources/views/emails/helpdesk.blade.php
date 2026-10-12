@php
    $webSettingRecord = \App\Models\Backend\Setting::where('slug', 'web_setting')->first();
    $webSettingData = $webSettingRecord ? json_decode($webSettingRecord->contents, true) : [];
    $dynSiteName = $webSettingData['title'] ?? 'TSS Villa';
    $dynPhone = $webSettingData['phone'] ?? '01977270920';
    $dynAddress = $webSettingData['address'] ?? '';
    $dynEmail = $webSettingData['email'] ?? '';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $dynSiteName }} - Guest Support Request</title>
</head>
<body style="margin:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:30px 0;">
    <tr>
        <td align="center">
            <table width="700" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,0.08);border:1px solid #e5e7eb;">
                <tr>
                    <td style="background:linear-gradient(135deg, #033364, #054a8e);color:#fff;padding:22px 24px;text-align:center;">
                        <h2 style="margin:0;font-size:22px;font-weight:700;">{{ $dynSiteName }}</h2>
                        <p style="margin:6px 0 0;font-size:13.5px;opacity:0.9;">
                            Guest Support & HelpDesk Request (গেস্ট সাপোর্ট মেসেজ)
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px;">
                        <h3 style="margin-top:0;margin-bottom:12px;color:#111827;border-left:4px solid #033364;padding-left:10px;font-size:16px;">Guest Information</h3>
                        <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px;">
                            <tr>
                                <td style="border:1px solid #e5e7eb;background:#f9fafb;width:150px;"><strong>Guest Name</strong></td>
                                <td style="border:1px solid #e5e7eb;">{{ $guest_name }}</td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #e5e7eb;background:#f9fafb;"><strong>Phone</strong></td>
                                <td style="border:1px solid #e5e7eb;">{{ $phone }}</td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #e5e7eb;background:#f9fafb;"><strong>Email</strong></td>
                                <td style="border:1px solid #e5e7eb;">{{ $guest_email }}</td>
                            </tr>
                            <tr>
                                <td style="border:1px solid #e5e7eb;background:#f9fafb;"><strong>Request Time</strong></td>
                                <td style="border:1px solid #e5e7eb;">{{ $created_time }}</td>
                            </tr>
                        </table>

                        <h3 style="margin:22px 0 10px;color:#111827;border-left:4px solid #033364;padding-left:10px;font-size:16px;">Booking & Room Details</h3>
                        <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:14px;border:1px solid #e5e7eb;">
                            <thead>
                                <tr style="background:#f3f4f6;">
                                    <th align="left" style="border:1px solid #e5e7eb;padding:9px 10px;color:#4b5563;">Floor Name</th>
                                    <th align="left" style="border:1px solid #e5e7eb;padding:9px 10px;color:#4b5563;">Room Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($booking_info as $booking)
                                    <tr>
                                        <td style="border:1px solid #e5e7eb;padding:9px 10px;">
                                            {{ $booking['floor_name'] }}
                                        </td>
                                        <td style="border:1px solid #e5e7eb;padding:9px 10px;font-weight:700;">
                                            <span style="background:#111827;color:#fff;padding:3px 8px;border-radius:5px;font-size:12px;">
                                                {{ $booking['room_number'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" style="border:1px solid #e5e7eb;text-align:center;color:#6b7280;padding:12px;">
                                            No active room details found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <h3 style="margin:22px 0 10px;color:#111827;border-left:4px solid #033364;padding-left:10px;font-size:16px;">Support Message</h3>
                        <div style="background:#f8fafc;border:1.5px solid #cbd5e1;border-radius:8px;padding:16px;font-size:14px;line-height:1.6;color:#1e293b;">
                            {!! nl2br(e($details)) !!}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 20px;background:#f9fafb;border-top:1px solid #e5e7eb;text-align:center;font-size:12px;color:#6b7280;line-height:1.6;">
                        <strong>{{ $dynSiteName }}</strong> - নিরাপদ ও আধুনিক ছাত্রী নিবাস<br>
                        @if(!empty($dynAddress))
                            <span>📍 {{ $dynAddress }}</span> |
                        @endif
                        <span>📞 হেল্পলাইন: <strong>{{ $dynPhone }}</strong></span>
                        @if(!empty($dynEmail))
                            | <span>✉️ {{ $dynEmail }}</span>
                        @endif
                        <div style="margin-top:4px;font-size:11px;color:#9ca3af;">
                            &copy; {{ date('Y') }} {{ $dynSiteName }}. সর্বস্বত্ব সংরক্ষিত।
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>