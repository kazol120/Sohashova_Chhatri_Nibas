@php
    $webSettingRecord = \App\Models\Backend\Setting::where('slug', 'web_setting')->first();
    $webSettingData = $webSettingRecord ? json_decode($webSettingRecord->contents, true) : [];
    $dynSiteName = $webSettingData['title'] ?? 'TSS Villa';
    $dynPhone = $webSettingData['phone'] ?? '01977270920';
    $dynAddress = $webSettingData['address'] ?? '';
    $dynEmail = $webSettingData['email'] ?? '';

    if (!function_exists('formatEmailRoomNumber')) {
        function formatEmailRoomNumber($rStr, $rType = '') {
            $rStr = trim((string)$rStr);
            if ($rStr === '') return '-';

            $block = 'A';
            $roomNum = 1;
            $seatNum = '';
            $isDouble = false;

            $rTypeLower = strtolower(trim((string)$rType));
            $roomTypeKnown = false;
            if (str_contains($rTypeLower, 'single') || str_contains($rTypeLower, 'singel')) {
                $isDouble = false;
                $roomTypeKnown = true;
            } elseif (str_contains($rTypeLower, 'double') || str_contains($rTypeLower, 'doubel')) {
                $isDouble = true;
                $roomTypeKnown = true;
            }

            // Match B-15-Block-1 or B-15-Block or B-15-1 or B-15
            if (preg_match('/^([A-Za-z]+)[\-_](\d+)(?:[\-_]Block)?(?:[\-_](\d+))?$/i', $rStr, $m)) {
                $block = strtoupper($m[1]);
                $roomNum = (int)$m[2];
                if (!empty($m[3]) && (int)$m[3] > 0) {
                    $seatNum = '-' . (int)$m[3];
                    if (!$roomTypeKnown && (int)$m[3] >= 2) $isDouble = true;
                }
            }
            // Match 15-Block-B-1 or 15-Block B-1 or 15-Block B
            elseif (preg_match('/^(\d+)[\-_ ]*Block[\-_ ]*([A-Za-z]+)(?:[\-_ ]*(\d+))?$/i', $rStr, $m)) {
                $roomNum = (int)$m[1];
                $block = strtoupper($m[2]);
                if (!empty($m[3]) && (int)$m[3] > 0) {
                    $seatNum = '-' . (int)$m[3];
                    if (!$roomTypeKnown && (int)$m[3] >= 2) $isDouble = true;
                }
            }
            // Match Block-B-15-1 or Block B 15-1
            elseif (preg_match('/^Block[\-_ ]*([A-Za-z]+)[\-_ ]*(\d+)(?:[\-_ ]*(\d+))?$/i', $rStr, $m)) {
                $block = strtoupper($m[1]);
                $roomNum = (int)$m[2];
                if (!empty($m[3]) && (int)$m[3] > 0) {
                    $seatNum = '-' . (int)$m[3];
                    if (!$roomTypeKnown && (int)$m[3] >= 2) $isDouble = true;
                }
            }
            // Match 15-B-1 or 15-B
            elseif (preg_match('/^(\d+)[\-_]([A-Za-z]+)(?:[\-_](\d+))?$/i', $rStr, $m)) {
                $roomNum = (int)$m[1];
                $block = strtoupper($m[2]);
                if (!empty($m[3]) && (int)$m[3] > 0) {
                    $seatNum = '-' . (int)$m[3];
                    if (!$roomTypeKnown && (int)$m[3] >= 2) $isDouble = true;
                }
            } else {
                return $rStr;
            }

            // Single room — force NO seat suffix and NO Double badge
            if (!$isDouble) {
                return '<span style="vertical-align:middle;">' . "{$roomNum}-Block {$block}" . '</span>';
            }

            // Double room — show Double badge and seat suffix
            $label = "{$roomNum}-Block {$block}{$seatNum}";
            return '<span style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; padding:2px 6px; border-radius:4px; font-size:11px; font-weight:700; margin-right:6px; vertical-align:middle; text-transform:none;">Double</span><span style="vertical-align:middle;">' . $label . '</span>';
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $dynSiteName }} - Room Booking Confirmation</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family:Arial, Helvetica, sans-serif; color:#333;">
    <div style="width:100%; background:#f4f6f9; padding:20px 10px;">
        <div style="max-width:700px; margin:0 auto; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 6px 24px rgba(0,0,0,0.08);">

            <!-- Header -->
            <div style="background:linear-gradient(135deg, #033364, #054a8e); padding:22px 20px; color:#fff; text-align:center;">
                <h2 style="margin:0; font-size:24px; font-weight:700;">{{ $dynSiteName }}</h2>
                <p style="margin:6px 0 0; font-size:13.5px; opacity:0.95;">
                    New Room Booking Received (নতুন রুম বুকিং বিবরণ)
                </p>
            </div>

            <!-- Content -->
            <div style="padding:20px;">
                <!-- Guest Information -->
                <div style="margin-bottom:20px;">
                    <h3 style="margin:0 0 10px; font-size:16px; color:#111827; border-left:4px solid #033364; padding-left:10px;">Guest Information</h3>
                    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                        @php
                            $userTypeLower = strtolower(trim(str_replace('_', ' ', $user_type ?? '')));
                            $isProfessional = ($userTypeLower === 'working professional' || $userTypeLower === 'professional');
                            $isStudent = !$isProfessional;
                        @endphp
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; width:140px; font-size:13px;"><strong>Full Name</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $full_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Email</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $email ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Blood Group</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $blood_group ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Phone</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $phone ?? '-' }}</td>
                        </tr>
                        @php
                            $dispPassword = $temp_password ?? ($password ?? null);
                        @endphp
                        @if(!empty($dispPassword))
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Login Password</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">
                                <span style="background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:5px; font-weight:800; font-family:monospace; font-size:14px; letter-spacing:1.5px;">
                                    {{ $dispPassword }}
                                </span>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>User Type</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px; text-transform: capitalize;">{{ $user_type ?? 'Student' }}</td>
                        </tr>
                        @if($isStudent)
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Institution Name</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $institution_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Education System</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $education_level ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Class / Semester</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $education_class ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Father's Name</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $father_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Father's Phone</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $father_phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Mother's Name</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $mother_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Mother's Phone</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $mother_phone ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Father's NID</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $father_nid ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Mother's NID</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $mother_nid ?? '-' }}</td>
                        </tr>
                        @endif
                        @if($isProfessional)
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>Workplace Name</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $workplace_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; background:#f9fafb; font-size:13px;"><strong>NID</strong></td>
                            <td style="padding:9px 10px; border:1px solid #e5e7eb; font-size:13px;">{{ $nid ?? '-' }}</td>
                        </tr>
                        @endif
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
                    </table>
                </div>

            <!-- Room Details -->
				<div style="margin-bottom:20px;">
				    <h3 style="margin:0 0 10px; font-size:16px; color:#111827; border-left:4px solid #033364; padding-left:10px;">Room & Booking Fee Details</h3>
				    @php
				        $roomJson = is_array($room_json) ? $room_json : json_decode($room_json ?? '[]', true);
				        $calcAdv = 0;
				        foreach($roomJson as $rItem) {
				            $calcAdv += (float)($rItem['advance_price'] ?? 0);
				        }
				        $advVal = isset($advance_total) ? (float)$advance_total : $calcAdv;
				        $devVal = isset($dev_fee) ? (float)$dev_fee : (isset($development_fee) ? (float)$development_fee : 0.00);
				        $bookingTotalVal = isset($booking_total) ? (float)$booking_total : ($advVal + $devVal);
				        $monthlyRentVal = isset($monthly_amount) ? (float)$monthly_amount : (float)($total_amount ?? 0);
				    @endphp

				    <div style="background:#eef5fc; border:1px solid #bcd8f5; border-radius:10px; padding:14px; margin-bottom:12px; text-align:center;">
				        <div style="font-size:13px; color:#6b7280; margin-bottom:4px; font-weight:bold;">Total Initial Booking Fee (মোট বুকিং ফি)</div>
				        <div style="font-size:24px; font-weight:800; color:#033364;">৳ {{ number_format($bookingTotalVal, 2) }}</div>
				        <div style="font-size:12px; color:#4b5563; margin-top:4px;">
				            @if($devVal > 0)
				                (Advance Deposit: ৳ {{ number_format($advVal, 2) }} + Development Fee: ৳ {{ number_format($devVal, 2) }})
				            @else
				                (Advance Deposit: ৳ {{ number_format($advVal, 2) }})
				            @endif
				        </div>
				        <div style="font-size:12px; color:#033364; font-weight:bold; margin-top:4px;">
				            * Monthly Seat Rent Rate: ৳ {{ number_format($monthlyRentVal, 2) }} / Month (মাসিক ভাড়া - শুধু প্রতি মাসে সংগ্রহ করা হবে, বুকিং ফির সাথে যোগ হবে না)
				        </div>
				    </div>

				    <!-- Room Table -->
				    <div style="border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;">
				        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
				            <tr style="background:#f3f4f6;">
				                <th style="padding:9px 10px; border-bottom:2px solid #e5e7eb; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Floor</th>
				                <th style="padding:9px 10px; border-bottom:2px solid #e5e7eb; text-align:left; font-size:12px; color:#6b7280; text-transform:uppercase;">Room</th>
				                <th style="padding:9px 10px; border-bottom:2px solid #e5e7eb; text-align:right; font-size:12px; color:#6b7280; text-transform:uppercase;">Monthly Rent</th>
				                <th style="padding:9px 10px; border-bottom:2px solid #e5e7eb; text-align:right; font-size:12px; color:#6b7280; text-transform:uppercase;">Advance Deposit</th>
				            </tr>

				            @foreach($roomJson as $index => $room)
				            @php
				                $rMonthlyPrice = (float)($room['price'] ?? 0);
				                if ($rMonthlyPrice <= 0 && $monthlyRentVal > 0) {
				                    $rMonthlyPrice = $monthlyRentVal;
				                }
				            @endphp
				            <tr style="background:{{ $index % 2 === 0 ? '#ffffff' : '#f9fafb' }};">
				                <td style="padding:10px; border-bottom:1px solid #f3f4f6; font-size:13px; color:#374151;">{{ $room['floornumber'] ?? '-' }}</td>
				                <td style="padding:10px; border-bottom:1px solid #f3f4f6;">
				                    <span style="background:#111827; color:#fff; padding:4px 10px; border-radius:6px; font-weight:700; font-size:13px; display:inline-flex; align-items:center;">
				                        {!! formatEmailRoomNumber($room['roomnumber'] ?? '-', $room['room_type'] ?? '') !!}
				                    </span>
				                </td>
				                <td style="padding:10px; border-bottom:1px solid #f3f4f6; font-size:13px; color:#374151; text-align:right;">
				                    ৳ {{ number_format($rMonthlyPrice, 2) }}
				                </td>
				                <td style="padding:10px; border-bottom:1px solid #f3f4f6; font-size:13px; color:#9ca3af; text-align:right;">
				                    -
				                </td>
				            </tr>
				            @endforeach

				            <tr style="background:#ffffff;">
				                <td colspan="3" style="padding:8px 10px; font-size:13px; color:#4b5563; border-top:1px solid #e5e7eb;">Advance Deposit (এডভান্স জামানত)</td>
				                <td style="padding:8px 10px; font-weight:700; font-size:13px; color:#111827; border-top:1px solid #e5e7eb; text-align:right;">
				                    ৳ {{ number_format($advVal, 2) }}
				                </td>
				            </tr>
				            @if($devVal > 0)
				            <tr style="background:#ffffff;">
				                <td colspan="3" style="padding:8px 10px; font-size:13px; color:#4b5563;">Development Fee (ডেভেলপমেন্ট ফি)</td>
				                <td style="padding:8px 10px; font-weight:700; font-size:13px; color:#111827; text-align:right;">
				                    ৳ {{ number_format($devVal, 2) }}
				                </td>
				            </tr>
				            @endif
				            <tr style="background:#f9fafb;">
				                <td colspan="3" style="padding:10px; font-weight:700; font-size:13px; color:#111827; border-top:2px solid #033364;">Total Initial Booking Fee (মোট বুকিং ফি)</td>
				                <td style="padding:10px; font-weight:700; font-size:15px; color:#033364; border-top:2px solid #033364; text-align:right;">
				                    ৳ {{ number_format($bookingTotalVal, 2) }}
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

                @if(!empty($image_file))
                    <div style="margin-bottom:20px;">
                        <h3 style="margin:0 0 10px; font-size:16px; color:#111827; border-left:4px solid #033364; padding-left:10px;">Guest Image</h3>
                        <div style="padding:10px; border:1px solid #e5e7eb; border-radius:10px; background:#fafafa; font-size:13px; color:#6b7280;">
                            📎 Guest image is attached to this email.
                        </div>
                    </div>
                @endif

                <!-- Status -->
                <div style="margin-top:20px; padding:14px; background:#eef5fc; border:1px solid #bcd8f5; border-radius:10px; color:#033364; font-size:13px;">
                    <strong>Booking Status:</strong> A new room booking has been received and saved successfully.
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:18px 20px; background:#f9fafb; border-top:1px solid #e5e7eb; text-align:center; font-size:12.5px; color:#6b7280; line-height:1.6;">
                <strong>{{ $dynSiteName }}</strong> - নিরাপদ ও আধুনিক ছাত্রী নিবাস<br>
                @if(!empty($dynAddress))
                    <span>📍 {{ $dynAddress }}</span> |
                @endif
                <span>📞 হেল্পলাইন: <strong>{{ $dynPhone }}</strong></span>
                @if(!empty($dynEmail))
                    | <span>✉️ {{ $dynEmail }}</span>
                @endif
                <div style="margin-top:6px; font-size:11.5px; color:#9ca3af;">
                    &copy; {{ date('Y') }} {{ $dynSiteName }}. সর্বস্বত্ব সংরক্ষিত।
                </div>
            </div>
        </div>
    </div>
</body>
</html>