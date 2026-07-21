<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Guest Support Request</title>
</head>
<body style="margin:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:30px 0;">
    <tr>
        <td align="center">
            <table width="700" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                <tr>
                    <td style="background:#198754;color:#fff;padding:18px 24px;text-align:center;font-size:22px;font-weight:bold;">
                        Guest Support Request
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px;">
                        <h3 style="margin-top:0;color:#222;">Guest Support Request</h3>
                        <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:15px;">
                            <tr>
                                <td style="border-bottom:1px solid #eee;width:160px;"><strong>Guest Name</strong></td>
                                <td style="border-bottom:1px solid #eee;">{{ $guest_name }}</td>
                            </tr>
                            <tr>
                                <td style="border-bottom:1px solid #eee;"><strong>Phone</strong></td>
                                <td style="border-bottom:1px solid #eee;">{{ $phone }}</td>
                            </tr>
                            <tr>
                                <td style="border-bottom:1px solid #eee;"><strong>Email</strong></td>
                                <td style="border-bottom:1px solid #eee;">{{ $guest_email }}</td>
                            </tr>
                            <tr>
                                <td style="border-bottom:1px solid #eee;"><strong>Request Time</strong></td>
                                <td style="border-bottom:1px solid #eee;">{{ $created_time }}</td>
                            </tr>
                        </table>
                        <h3 style="margin:25px 0 10px;color:#222;">Booking Details</h3>
                        <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;font-size:15px;border:1px solid #e5e5e5;">
                            <thead>
                                <tr style="background:#f8f9fa;">
                                    <th align="left" style="border:1px solid #e5e5e5;">Floor Name</th>
                                    <th align="left" style="border:1px solid #e5e5e5;">Room Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($booking_info as $booking)
                                    <tr>
                                        <td style="border:1px solid #e5e5e5;">
                                            {{ $booking['floor_name'] }}
                                        </td>
                                        <td style="border:1px solid #e5e5e5;">
                                            {{ $booking['room_number'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" style="border:1px solid #e5e5e5;text-align:center;">
                                            No room details found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <h3 style="margin:25px 0 10px;color:#222;">Support Message</h3>

                        <div style="background:#f8f9fa;border:1px solid #e9ecef;border-radius:6px;padding:14px;font-size:15px;line-height:1.6;">
                            {!! nl2br(e($details)) !!}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="background:#f1f3f5;text-align:center;padding:14px;font-size:12px;color:#666;">
                        This email was generated from your hotel helpdesk system.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

</body>
</html>