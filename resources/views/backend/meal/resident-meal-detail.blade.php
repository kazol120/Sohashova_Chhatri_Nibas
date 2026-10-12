<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }} - মাসিক মিলের বিবরণী | টি এস এস ভিলা (TSS Villa)</title>
    
    <!-- Google Fonts & Bootstrap 5 & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Hind Siliguri', 'Segoe UI', Arial, sans-serif;
            background-color: #f8fafc;
            color: #111111;
            margin: 0;
            padding: 0;
        }
        .report-wrapper {
            width: 96%;
            max-width: 96%;
            margin: 15px auto;
            background: #ffffff;
            padding: 20px 25px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .report-header {
            text-align: center;
            margin-bottom: 12px;
        }
        .report-header h3 {
            font-weight: 800;
            margin-bottom: 2px;
            color: #000000;
            font-size: 20px;
        }
        .report-header p {
            margin-bottom: 2px;
            font-size: 13px;
            color: #4b5563;
        }
        .report-header .report-title {
            font-weight: 700;
            font-size: 14px;
            color: #111827;
            margin-top: 4px;
            text-decoration: underline;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 12px;
            border: 1px solid #d1d5db;
        }
        .info-table td, .info-table th {
            padding: 6px 10px;
            border: 1px solid #d1d5db;
        }
        .info-table th {
            background-color: #f3f4f6;
            font-weight: 700;
            color: #1f2937;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 11.5px;
        }
        .report-table th {
            background-color: #e5e7eb !important;
            color: #000000 !important;
            font-weight: 700;
            border: 1px solid #9ca3af;
            padding: 6px 7px;
            text-align: center;
        }
        .report-table td {
            border: 1px solid #d1d5db;
            padding: 4.5px 7px;
            vertical-align: middle;
        }
        .report-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .report-table tfoot td {
            background-color: #f3f4f6 !important;
            font-weight: 700;
            border: 1px solid #9ca3af;
            padding: 6px 7px;
        }
        
        @page {
            size: A4 portrait;
            margin: 6mm 8mm 6mm 8mm;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            html, body {
                background-color: #ffffff !important;
                color: #000000 !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .container, .container-fluid, .report-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
            .report-header {
                margin-bottom: 8px !important;
            }
            .report-header h3 {
                font-size: 19px !important;
                margin-bottom: 1px !important;
            }
            .report-header p {
                font-size: 11px !important;
                margin-bottom: 1px !important;
            }
            .report-header .report-title {
                font-size: 13.5px !important;
                margin-top: 2px !important;
            }
            .info-table {
                width: 100% !important;
                margin-bottom: 8px !important;
                font-size: 11.5px !important;
            }
            .info-table td, .info-table th {
                padding: 4px 8px !important;
            }
            .report-table {
                width: 100% !important;
                margin-bottom: 8px !important;
                font-size: 11px !important;
            }
            .report-table th {
                background-color: #e5e7eb !important;
                color: #000000 !important;
                padding: 4px 6px !important;
                font-size: 11px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .report-table td {
                padding: 3.8px 6px !important;
                font-size: 10.5px !important;
                line-height: 1.25 !important;
            }
            .report-table tr:nth-child(even) td {
                background-color: #f9fafb !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .report-table tfoot td {
                background-color: #f3f4f6 !important;
                padding: 5px 6px !important;
                font-size: 11px !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .signature-section {
                margin-top: 14px !important;
                padding-top: 6px !important;
            }
            tr {
                page-break-inside: avoid !important;
            }
        }
    </style>
</head>
<body>

@php
  $fmtGuestCount = (float)$totalGuestMealsCount == (int)$totalGuestMealsCount ? (int)$totalGuestMealsCount : number_format($totalGuestMealsCount, 1);
  $fmtTotalMeals = (float)$totalMeals == (int)$totalMeals ? (int)$totalMeals : number_format($totalMeals, 1);
  $regMealCost = $totalMealCostOnly ?? ($totalCost - $totalGuestMealsAmount);
@endphp

<div class="container-fluid px-3 py-3">
    <!-- Top Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print" style="max-width: 96%; margin: 0 auto;">
        <button onclick="window.close(); history.back();" class="btn btn-outline-secondary btn-sm fw-bold rounded-pill px-3">
            <i class="fa fa-arrow-left me-1"></i> ফিরে যান
        </button>
        <div class="d-flex gap-2 align-items-center">
            <form action="{{ route('meal-history.resident-detail', $user->id) }}" method="GET" class="d-inline-flex align-items-center gap-2">
                <label class="small fw-bold text-dark mb-0">মাস:</label>
                <input type="month" name="month" class="form-control form-control-sm" value="{{ $selectedMonth }}">
                <button type="submit" class="btn btn-sm btn-dark">দেখুন</button>
            </form>
            <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold rounded-pill px-3 shadow-sm" style="background: #033364; border-color: #033364;">
                <i class="fa fa-print me-1"></i> প্রিন্ট / PDF সেভ করুন
            </button>
        </div>
    </div>

    <!-- Official Transaction Report Sheet -->
    <div class="report-wrapper">
        
        <!-- Header -->
        <div class="report-header">
            <h3>টি এস এস ভিলা (TSS Villa)</h3>
            <p>আবাসিক ছাত্রী নিবাস ও মিল ম্যানেজমেন্ট সিস্টেম | রংপুর</p>
            <div class="report-title">শিক্ষার্থীর মাসিক মিলের বিবরণী (Monthly Meal Report) — {{ $monthFormatted }}</div>
            <p class="text-muted small mt-1">প্রিন্ট ও রিপোর্ট তৈরির সময়: {{ \Carbon\Carbon::now('Asia/Dhaka')->format('d M Y, h:i A') }}</p>
        </div>

        <!-- Resident Metadata Table -->
        <table class="info-table">
            <tr>
                <th style="width: 15%;">শিক্ষার্থীর নাম:</th>
                <td style="width: 35%;"><b>{{ $user->name }}</b></td>
                <th style="width: 15%;">মোবাইল / আইডি:</th>
                <td style="width: 35%;">{{ $user->phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>ফ্লোর ও রুম/সিট:</th>
                <td colspan="3"><b>{{ $roomInfoStr ?? '-' }}</b></td>
            </tr>
            <tr>
                <th>দৈনিক মিলের রেট:</th>
                <td><b>ফুল মিল: ৳ {{ number_format($fullRate, 2) }}</b> | <b>হাফ মিল: ৳ {{ number_format($halfRate, 2) }}</b></td>
                <th>মোট মিল সংখ্যা:</th>
                <td>
                    <b>{{ $fmtTotalMeals }} টি</b> 
                    <span class="small text-muted fw-normal">(ফুল: {{ $totalFullCount }} টি, হাফ: {{ $totalHalfCount }} টি{{ $totalGuestMealsCount > 0 ? ', গেস্ট: ' . $fmtGuestCount . ' টি' : '' }})</span>
                </td>
            </tr>
            <tr>
                <th>মাসের মোট খরচ:</th>
                <td>
                    <b>৳ {{ number_format($totalCost, 2) }}</b>
                    @if($totalGuestMealsCount > 0)
                        <span class="small text-muted fw-normal">(মিল: ৳ {{ number_format($regMealCost, 0) }} + গেস্ট: ৳ {{ number_format($totalGuestMealsAmount, 0) }})</span>
                    @endif
                </td>
                <th>ডিপোজিট ও ব্যালেন্স:</th>
                <td>জমা: ৳ {{ number_format($depositTotal, 0) }} | <b>ব্যালেন্স: ৳ {{ number_format($balance, 0) }}</b></td>
            </tr>
        </table>

        <!-- 30/31 Day Meal Table -->
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th style="width: 110px;">তারিখ (Date)</th>
                    <th style="width: 100px;">বার (Day)</th>
                    <th style="width: 130px;">মিল স্ট্যাটাস</th>
                    <th style="width: 90px;">মিলের সংখ্যা</th>
                    <th style="width: 110px; text-align: right;">দৈনিক খরচ</th>
                    <th>নোট / স্পেশাল ও গেস্ট মিলের বিবরণ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $index => $day)
                <tr>
                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                    <td class="text-center fw-bold">{{ $day->date_formatted }}</td>
                    <td class="text-center">{{ $day->day_name }}</td>
                    <td class="text-center">
                        @if($day->is_meal_on)
                            @if(isset($day->is_half) && $day->is_half)
                                @if(($day->meal_type ?? '') === 'half_night')
                                    <span class="badge bg-info text-white" style="font-size: 10.5px;">🌙 হাফ (রাত)</span>
                                @else
                                    <span class="badge bg-warning text-dark" style="font-size: 10.5px;">☀️ হাফ (দিন)</span>
                                @endif
                            @else
                                <span class="badge bg-success text-white" style="font-size: 10.5px;">✅ ফুল মিল</span>
                            @endif
                        @else
                            @php
                                $dNoteLower = strtolower($day->note ?? '');
                            @endphp
                            @if(str_contains($dNoteLower, 'checked in') || str_contains($dNoteLower, 'resumes tomorrow') || (str_contains($dNoteLower, 'check') && str_contains($dNoteLower, 'in')) || str_contains($dNoteLower, 'চেক-ইন'))
                                <span class="badge bg-success text-white" style="font-size: 10px;">🟢 চেক-ইন (কাল চালু)</span>
                            @elseif(str_contains($dNoteLower, 'leave') || str_contains($dNoteLower, 'checked out') || str_contains($dNoteLower, 'check') || str_contains($dNoteLower, 'চেক-আউট'))
                                <span class="badge bg-danger text-white" style="font-size: 10px;">🚫 চেক-আউট (বন্ধ)</span>
                            @else
                                <span class="text-danger fw-semibold">বন্ধ (OFF)</span>
                            @endif
                        @endif
                    </td>
                    <td class="text-center {{ ($day->meal_count ?? 0) > 0 ? 'fw-bold' : '' }}">
                        @if(($day->meal_count ?? 0) == 0.5)
                            ০.৫ (হাফ)
                        @elseif(($day->meal_count ?? 0) == 1)
                            ১ (ফুল)
                        @else
                            ০
                        @endif
                    </td>
                    <td class="text-end fw-bold">৳ {{ number_format($day->day_cost, 2) }}</td>
                    <td class="small">{{ $day->note ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end">
                        সর্বমোট মিল খরচ (Grand Total Cost):
                        @if($totalGuestMealsCount > 0)
                            <span class="small fw-normal text-secondary d-block">
                                [নিজস্ব মিল: {{ $totalFullCount }} ফুল + {{ $totalHalfCount }} হাফ (৳ {{ number_format($regMealCost, 0) }}) + মেহমানের মিল: {{ $fmtGuestCount }} টি (৳ {{ number_format($totalGuestMealsAmount, 0) }})]
                            </span>
                        @endif
                    </td>
                    <td class="text-center fw-bold text-primary">{{ $fmtTotalMeals }} টি</td>
                    <td class="text-end fw-bold text-danger">৳ {{ number_format($totalCost, 2) }}</td>
                    <td class="text-success small fw-bold">
                        জমা: ৳ {{ number_format($depositTotal, 0) }} | অবশিষ্ট ব্যালেন্স: ৳ {{ number_format($balance, 0) }}
                    </td>
                </tr>
            </tfoot>
        </table>

        <!-- Signature Block -->
        <div class="signature-section d-flex justify-content-between align-items-end border-top">
            <div class="small text-muted">
                ডকুমেন্ট সোর্স: টি এস এস ভিলা অডিট রেকর্ড
            </div>
            <div class="text-center" style="width: 200px;">
                <div style="border-top: 1px solid #374151; padding-top: 4px; font-weight: bold; font-size: 12px;">
                    কর্তৃপক্ষের স্বাক্ষর
                </div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
