<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking Confirmation</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, Helvetica, sans-serif; color:#333;">
    <div style="width:100%; background:#f4f6f9; padding:20px 10px;">
        <div style="max-width:700px; margin:0 auto; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 6px 24px rgba(0,0,0,0.08);">

            <!-- Header -->
            <div style="background:linear-gradient(135deg, #033364, #054a8e); padding:20px; color:#fff;">
                <h2 style="margin:0; font-size:22px; font-weight:700;">New Room Booking Received</h2>
                <p style="margin:8px 0 0; font-size:13px; opacity:0.95;">
                    A new room booking has been submitted successfully.
                </p>
            </div>

            <!-- Content -->
            <div style="padding:20px;">

                <!-- Guest Information -->
                <div style="margin-bottom:20px;">
                    <h3 style="margin:0 0 10px; font-size:16px; color:#111827; border-left:4px solid #033364; padding-left:10px;">Guest Information</h3>
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; width:140px; font-size:13px;"><strong>Full Name</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $full_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Email</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Phone</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Father's Name</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $father_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Mother's Name</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $mother_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Father's NID</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $father_nid ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Mother's NID</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $mother_nid ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Booking Details -->
                <div style="margin-bottom:20px;">
                    <h3 style="margin:0 0 10px; font-size:16px; color:#111827; border-left:4px solid #033364; padding-left:10px;">Booking Details</h3>
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; width:140px; font-size:13px;"><strong>Booking Date Time</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $create_at ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Floor</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $floor_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Check In</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $check_in ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Check Out</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $check_out ?? '-' }}</td>
                        </tr>
                    </table>
                </div>
            <!-- Room Details -->
				<div style="margin-bottom:20px;">
				    <h3 style="margin:0 0 10px; font-size:16px; color:#111827; border-left:4px solid #033364; padding-left:10px;">Room Details</h3>
				    @php
				        $roomJson = is_array($room_json) ? $room_json : json_decode($room_json ?? '[]', true);
				        $days = 1;
				        if (!empty($check_in) && !empty($check_out)) {
				            $days = max(1, \Carbon\Carbon::parse($check_in)->diffInDays(\Carbon\Carbon::parse($check_out)));
				        }
				    @endphp

				    <div style="background:#eef5fc; border:1px solid #bcd8f5; border-radius:10px; padding:14px; margin-bottom:12px; text-align:center;">
				        <div style="font-size:13px; color:#6b7280; margin-bottom:4px;">Total Days</div>
				        <div style="font-size:22px; font-weight:800; color:#033364;">{{ $days }} Days</div>
				        <div style="font-size:13px; color:#6b7280; margin-top:8px; margin-bottom:4px;">Total Amount (Days × Room Price)</div>
				        <div style="font-size:24px; font-weight:800; color:#033364;">৳ {{ number_format((float)($daybytotalamount ?? 0), 2) }}</div>
				    </div>

				    <!-- Room Table -->
				    <div style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
				        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
				            <tr style="background:#f3f4f6;">
				                <th style="padding:9px 10px; border-bottom:2px solid #e5e7eb; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Floor</th>
				                <th style="padding:9px 10px; border-bottom:2px solid #e5e7eb; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Room</th>
				                <th style="padding:9px 10px; border-bottom:2px solid #e5e7eb; text-align:right; font-size:12px; color:#6b7280; text-transform:uppercase;">Price</th>
				            </tr>

				            @foreach($roomJson as $index => $room)
				            <tr style="background:{{ $index % 2 === 0 ? '#ffffff' : '#f9fafb' }};">
				                <td style="padding:10px; border-bottom:1px solid #f3f4f6; font-size:13px; color:#374151;">{{ $room['floornumber'] ?? '-' }}</td>
				                <td style="padding:10px; border-bottom:1px solid #f3f4f6;">
				                    <span style="background:#111827; color:#fff; padding:4px 10px; border-radius:6px; font-weight:700; font-size:13px;">
				                        {{ $room['roomnumber'] ?? '-' }}
				                    </span>
				                </td>
				                <td style="padding:10px; border-bottom:1px solid #f3f4f6; font-size:13px; color:#374151; text-align:right;">
				                    ৳ {{ number_format((float)($room['price'] ?? 0), 2) }}
				                </td>
				            </tr>
				            @endforeach

				            <tr style="background:#f9fafb;">
				                <td colspan="2" style="padding:10px; font-weight:700; font-size:13px; color:#111827; border-top:2px solid #e5e7eb;">Total</td>
				                <td style="padding:10px; font-weight:700; font-size:14px; color:#033364; border-top:2px solid #e5e7eb; text-align:right;">
				                    ৳ {{ number_format((float)($total_amount ?? 0), 2) }}
				                </td>
				            </tr>
				        </table>
				    </div>
				</div>

                <!-- Location Information -->
                <div style="margin-bottom:20px;">
                    <h3 style="margin:0 0 10px; font-size:16px; color:#111827; border-left:4px solid #033364; padding-left:10px;">Location Information</h3>
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; width:140px; font-size:13px;"><strong>Division</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $division_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>District</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $district_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Thana</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $thana_name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <!-- Payment Information -->
                <div style="margin-bottom:20px;">
                    <h3 style="margin:0 0 10px; font-size:16px; color:#111827; border-left:4px solid #033364; padding-left:10px;">Payment Information</h3>
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; width:140px; font-size:13px;">
                                <strong>Payment Cash</strong>
                            </td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb;">
                                @if($payment_type == 'cash')
                                    <span style="background:#dcfce7; color:#166534; padding:5px 12px; border-radius:8px; font-weight:700; border:1px solid #86efac; font-size:13px;">
                                        ✔ Cash
                                    </span>
                                @else
                                    <span style="background:#f3f4f6; color:#6b7280; padding:5px 12px; border-radius:8px; border:1px solid #e5e7eb; font-size:13px;">
                                        Cash
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;">
                                <strong>Payment Online</strong>
                            </td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb;">
                                @if($payment_type == 'online' && strtolower($pay_method) == 'bkash')
                                    <span style="background:#ffe4ef; color:#c2185b; padding:5px 12px; border-radius:8px; font-weight:700; border:1px solid #f9a8d4; margin-right:6px; font-size:13px;">
                                        ✔ bKash
                                    </span>
                                @else
                                    <span style="background:#f3f4f6; color:#6b7280; padding:5px 12px; border-radius:8px; border:1px solid #e5e7eb; margin-right:6px; font-size:13px;">
                                        bKash
                                    </span>
                                @endif
                                @if($payment_type == 'online' && strtolower($pay_method) == 'nagad')
                                    <span style="background:#fff7ed; color:#c2410c; padding:5px 12px; border-radius:8px; font-weight:700; border:1px solid #fdba74; font-size:13px;">
                                        ✔ Nagad
                                    </span>
                                @else
                                    <span style="background:#f3f4f6; color:#6b7280; padding:5px 12px; border-radius:8px; border:1px solid #e5e7eb; font-size:13px;">
                                        Nagad
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;">
                                <strong>Transaction ID</strong>
                            </td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">
                                {{ $trx ?? '-' }}
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Guest Image -->
                @php
                    $embeddedImage = null;
                    if (!empty($image_file)) {
                        $fullImagePath = public_path('bookingsimage/' . $image_file);
                        if (file_exists($fullImagePath)) {
                            $embeddedImage = $message->embed($fullImagePath);
                        }
                    }
                @endphp

                @if($embeddedImage)
                    <div style="margin-bottom:20px;">
                        <h3 style="margin:0 0 10px; font-size:16px; color:#111827; border-left:4px solid #033364; padding-left:10px;">Guest Image</h3>
                        <div style="padding:10px; border:1px solid #e5e7eb; border-radius:10px; background:#fafafa;">
                            <img src="{{ $embeddedImage }}" alt="Guest Image" style="max-width:200px; width:100%; height:auto; border-radius:10px; display:block;">
                        </div>
                    </div>
                @endif

                <!-- Status -->
                <div style="margin-top:20px; padding:14px; background:#eef5fc; border:1px solid #bcd8f5; border-radius:10px; color:#033364; font-size:13px;">
                    <strong>Booking Status:</strong> A new room booking has been received and saved successfully.
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:16px; background:#f9fafb; border-top:1px solid #e5e7eb; text-align:center; font-size:12px; color:#6b7280;">
                This is an automated booking notification email.
            </div>
        </div>
    </div>
</body>
</html>