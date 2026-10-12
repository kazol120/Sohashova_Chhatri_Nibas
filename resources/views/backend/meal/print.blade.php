<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>দৈনিক মিল শিট - {{ $selectedDate }} | {{ $webSetting['title'] ?? 'টি এস এস ভিলা (TSS Villa)' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 10mm 10mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', 'Nikosh', 'SolaimanLipi', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
            background: #fff;
            font-size: 11px;
            line-height: 1.3;
        }
        .container {
            width: 100%;
            max-width: 980px;
            margin: 0 auto;
            padding: 10px;
        }
        
        /* Action buttons (hidden in print) */
        .no-print-bar {
            background: #f1f5f9;
            padding: 10px 16px;
            border-radius: 6px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #cbd5e1;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-primary {
            background: #033364;
            color: #fff;
        }
        .btn-secondary {
            background: #64748b;
            color: #fff;
        }

        /* Header */
        .report-header {
            text-align: center;
            border-bottom: 2px solid #033364;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .report-header h2 {
            margin: 0;
            color: #033364;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }
        .report-header .sub-header {
            font-size: 11px;
            color: #475569;
            margin: 2px 0;
        }
        .report-header .doc-title {
            display: inline-block;
            background: #033364;
            color: #fff;
            font-size: 12.5px;
            font-weight: 700;
            padding: 3px 14px;
            border-radius: 20px;
            margin-top: 4px;
        }
        .report-header .print-time {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 3px;
        }

        /* Summary Grid */
        .summary-box-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }
        .stat-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 6px 8px;
            text-align: center;
            background: #f8fafc;
        }
        .stat-card .stat-title {
            font-size: 10px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
        .stat-card .stat-val {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin: 2px 0 0 0;
        }

        /* Rate Highlight Bar */
        .rate-bar {
            background: #fffbeb;
            border: 1px solid #fef08a;
            border-radius: 4px;
            padding: 6px 12px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #854d0e;
        }

        /* Table */
        table.meal-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10.5px;
        }
        table.meal-table th, table.meal-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 6px;
            text-align: left;
        }
        table.meal-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            font-size: 10.5px;
        }
        table.meal-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        .text-center { text-align: center !important; }
        .text-end { text-align: right !important; }
        .fw-bold { font-weight: 700; }

        /* Badges */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 9.5px;
            font-weight: 700;
            border-radius: 3px;
        }
        .badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde047; }
        .badge-info { background: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .badge-secondary { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        /* Signature block */
        .signature-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .sig-block {
            text-align: center;
            width: 28%;
        }
        .sig-line {
            border-top: 1px solid #64748b;
            margin-top: 35px;
            padding-top: 3px;
            font-weight: 700;
            font-size: 10.5px;
            color: #334155;
        }

        @media print {
            .no-print-bar {
                display: none !important;
            }
            body {
                background: #fff;
                padding: 0;
            }
            .container {
                max-width: 100%;
                padding: 0;
            }
            table.meal-table thead {
                display: table-header-group;
            }
            table.meal-table tfoot {
                display: table-row-group !important;
            }
            table.meal-table tr {
                page-break-inside: avoid;
            }
            table.meal-table th {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .stat-card, .rate-bar, .status-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Top Action Bar (Hidden in Print) -->
    <div class="no-print-bar">
        <div>
            <strong>দৈনিক মিল শিট প্রিন্ট প্রিভিউ</strong> — {{ $dateFormatted }}
        </div>
        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ প্রিন্ট করুন / PDF সেভ করুন
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                ✕ বন্ধ করুন
            </button>
        </div>
    </div>

    <!-- Official Header -->
    <div class="report-header">
        <h2>{{ $webSetting['title'] ?? 'টি এস এস ভিলা (TSS Villa)' }}</h2>
        <div class="sub-header">
            {{ $webSetting['address'] ?? 'রংপুর' }} 
            @if(!empty($webSetting['phone'])) | মোবাইল: {{ $webSetting['phone'] }} @endif
            @if(!empty($webSetting['email'])) | ইমেইল: {{ $webSetting['email'] }} @endif
        </div>
        <div>
            <span class="doc-title">দৈনিক মিল ম্যানেজমেন্ট শিট ও রিপোর্ট</span>
        <div class="print-time">
            তারিখ: <strong>{{ $dateFormatted }}</strong> | রিপোর্ট তৈরির সময়: {{ \Carbon\Carbon::now('Asia/Dhaka')->format('d M Y, h:i A') }}
        </div>
    </div>

    <!-- Rate & Special Meal Bar -->
    <div class="rate-bar">
        <div>
            <span>💵 <strong>দৈনিক মিল রেট:</strong></span>
            <span style="margin-left: 8px;">ফুল মিল: <strong>৳ {{ number_format($fullRate, 2) }}</strong></span>
            <span style="margin-left: 10px;">হাফ মিল: <strong>৳ {{ number_format($halfRate, 2) }}</strong></span>
        </div>
        <div>
            @if($isSpecial)
                <span class="status-badge badge-warning">⭐ স্পেশাল মিল শিডিউল: {{ $specialTitle ?: 'Special Meal' }}</span>
            @else
                <span class="status-badge badge-secondary">নিয়মিত মিল রেট (Regular Rate)</span>
            @endif
        </div>
    </div>

    <!-- Summary Box Grid -->
    <div class="summary-box-grid" style="grid-template-columns: repeat(5, 1fr);">
        <div class="stat-card">
            <div class="stat-title">মোট সদস্য (Members)</div>
            <div class="stat-val">{{ $summary['total_member'] }} জন</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">ফুল মিল চালু (Full ON)</div>
            <div class="stat-val" style="color: #15803d;">{{ $summary['total_full_on'] ?? $summary['total_full'] }} টি</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">হাফ মিল চালু (Half ON)</div>
            <div class="stat-val" style="color: #b45309;">
                {{ $summary['total_half_on'] ?? 0 }} টি
                <span style="font-size: 9px; font-weight: normal; color: #64748b; display: block;">(☀️ {{ $summary['total_day_half'] ?? 0 }} দিন | 🌙 {{ $summary['total_night_half'] ?? 0 }} রাত)</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-title">গেস্ট মিল (Guest Meals)</div>
            <div class="stat-val" style="color: #0284c7;">
                {{ (float)($totalGuestMealsCount ?? 0) == (int)($totalGuestMealsCount ?? 0) ? (int)($totalGuestMealsCount ?? 0) : ($totalGuestMealsCount ?? 0) }} টি
                @if(($totalGuestMealsAmount ?? 0) > 0)
                    <span style="font-size: 9px; font-weight: normal; color: #64748b; display: block;">(৳ {{ number_format($totalGuestMealsAmount, 0) }})</span>
                @endif
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-title">মিল বন্ধ (Total OFF)</div>
            <div class="stat-val" style="color: #b91c1c;">{{ $summary['total_off'] }} জন</div>
        </div>
    </div>

    @php
        $mealMap = collect($meals)->keyBy('user_id');
        $totalMembersCount = count($users);
        $totalFullCount = 0;
        $totalHalfDayCount = 0;
        $totalHalfNightCount = 0;
        $totalOffCount = 0;
    @endphp

    <!-- Member Table -->
    <table class="meal-table">
        <thead>
            <tr>
                <th style="width: 35px;" class="text-center">#</th>
                <th style="width: 180px;">মেম্বারের নাম (Member Name)</th>
                <th style="width: 95px;">ফোন নম্বর</th>
                <th style="width: 75px;">ফ্লোর</th>
                <th style="width: 60px;" class="text-center">রুম</th>
                <th style="width: 70px;" class="text-center">সিট নং</th>
                <th style="width: 75px;" class="text-center">বুকিং</th>
                <th style="width: 155px;" class="text-center">মিলের স্ট্যাটাস</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $idx => $user)
                @php
                    $meal = $mealMap->get($user->id);
                    $isOff = $meal ? (bool)$meal->is_off : false;
                    $isHalf = $meal ? (bool)$meal->half_meal : false;
                    $isFull = !$isHalf && ($meal ? (bool)$meal->full_meal : false);
                    $mealNote = strtolower($meal ? ($meal->note ?? '') : '');
                    $isHalfDay = $isHalf && ($mealNote === 'day' || $mealNote === 'half_day');
                    $isHalfNight = $isHalf && ($mealNote === 'night' || $mealNote === 'half_night');
                    if ($isHalf && !$isHalfDay && !$isHalfNight) {
                        $isHalfDay = true;
                    }

                    if ($isFull && !$isOff) {
                        $totalFullCount++;
                    } elseif ($isHalf && !$isOff) {
                        if ($isHalfNight) $totalHalfNightCount++;
                        else $totalHalfDayCount++;
                    } else {
                        $totalOffCount++;
                    }
                @endphp
                <tr>
                    <td class="text-center" style="color: #64748b;">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>
                        <strong style="color: #0f172a;">{{ $user->name }}</strong>
                    </td>
                    <td>{{ $user->phone ?? '—' }}</td>
                    <td>{{ $user->booking_floor ?? '—' }}</td>
                    <td class="text-center">{{ $user->booking_room ?? '—' }}</td>
                    <td class="text-center">{{ $user->booking_seat ?? '—' }}</td>
                    <td class="text-center">
                        @if(isset($user->active_booking) && $user->active_booking)
                            <span style="color: #15803d; font-weight: 600;">#{{ $user->active_booking->id }}</span>
                        @else
                            <span style="color: #94a3b8;">No</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($isFull && !$isOff)
                            <span class="status-badge badge-success">✓ Full Meal (ON)</span>
                        @elseif($isHalf && $isHalfNight && !$isOff)
                            <span class="status-badge badge-info">🌙 Half (Night)</span>
                        @elseif($isHalf && !$isOff)
                            <span class="status-badge badge-warning">☀️ Half (Day)</span>
                        @elseif(str_contains($mealNote, 'checked in') || str_contains($mealNote, 'resumes tomorrow') || (str_contains($mealNote, 'check') && str_contains($mealNote, 'in')))
                            <span class="status-badge badge-success">🟢 চেক-ইন (কাল চালু)</span>
                        @elseif(str_contains($mealNote, 'leave') || str_contains($mealNote, 'checked out') || str_contains($mealNote, 'check'))
                            <span class="status-badge badge-danger">🚫 চেক-আউট (মিল বন্ধ)</span>
                        @elseif(str_contains($mealNote, 'zero deposit'))
                            <span class="status-badge badge-danger">🚫 Zero Deposit</span>
                        @else
                            <span class="status-badge badge-danger">✕ Meal OFF</span>
                        @endif

                        @if(isset($guestMealsByUser[$user->id]) && $guestMealsByUser[$user->id]->isNotEmpty())
                            @foreach($guestMealsByUser[$user->id] as $gm)
                                <div style="margin-top: 2px;">
                                    <span class="status-badge badge-info" style="font-size: 8.5px; padding: 1px 4px;">
                                        👥 Guest Meal: {{ (float)$gm->meal_count == (int)$gm->meal_count ? (int)$gm->meal_count : $gm->meal_count }} টি ({{ $gm->guest_name }})
                                    </span>
                                </div>
                            @endforeach
                        @endif
                    </td>
                </tr>
            @endforeach

            <!-- Grand Total Summary Row (Appears once only at the end of the entire table on the last page) -->
            <tr style="background: #f1f5f9; font-weight: 700; page-break-inside: avoid;">
                <td colspan="4" class="text-end" style="color: #033364; font-size: 11px;">
                    সর্বমোট সারসংক্ষেপ (Grand Total Summary):
                </td>
                <td colspan="4" style="font-size: 10.5px;">
                    <span style="color: #15803d;">ফুল মিল: {{ $totalFullCount }} টি</span> | 
                    <span style="color: #b45309;">হাফ মিল: {{ $totalHalfDayCount + $totalHalfNightCount }} টি</span> (ডে: {{ $totalHalfDayCount }}, নাইট: {{ $totalHalfNightCount }}) | 
                    <span style="color: #0284c7;">গেস্ট মিল: {{ (float)($totalGuestMealsCount ?? 0) == (int)($totalGuestMealsCount ?? 0) ? (int)($totalGuestMealsCount ?? 0) : ($totalGuestMealsCount ?? 0) }} টি</span> | 
                    <span style="color: #b91c1c;">মিল বন্ধ: {{ $totalOffCount }} জন</span> | 
                    <strong>মোট মিল: {{ $totalActiveMeals + ($totalGuestMealsCount ?? 0) }} টি</strong> (দৈনিক খরচ: ৳ {{ number_format($totalDailyCost + ($totalGuestMealsAmount ?? 0), 2) }})
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="sig-block">
            <div class="sig-line">প্রস্তুতকারীর স্বাক্ষর</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">মিল ম্যানেজার / বাবুর্চি</div>
        </div>
        <div class="sig-block">
            <div class="sig-line">কর্তৃপক্ষ / হোস্টেল সুপার</div>
        </div>
    </div>
</div>

</body>
</html>
