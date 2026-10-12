@php
    $b = $residentData['basic'];
    $p = $residentData['personal'];
    $rMoves = $residentData['room_movements'];
    $mPayments = $residentData['monthly_payments'];
    $pSummary = $residentData['payment_summary'];
    $mSummary = $residentData['meal_summary'];
    $mCostLedger = $residentData['monthly_cost_ledger'] ?? [];
    $yTotals = $residentData['year_totals'] ?? [];
    $selYear = $residentData['selected_year'] ?? date('Y');
    $siteName = $webSetting['title'] ?? 'টি এস এস ভিলা (TSS Villa)';
    $sitePhone = $webSetting['phone'] ?? '01977270920';
    $siteAddress = $webSetting['address'] ?? '';
    $siteEmail = $webSetting['email'] ?? '';
    
    // User type signature logic
    $uTypeStr = strtolower($p['user_type'] ?? '');
    $isProf = (str_contains($uTypeStr, 'job') || str_contains($uTypeStr, 'profession') || str_contains($uTypeStr, 'কর্মজীবী') || str_contains($uTypeStr, 'পেশাজীবী') || str_contains($uTypeStr, 'passenger'));
    $sigLabel = $isProf ? 'কর্মজীবীর স্বাক্ষর' : 'শিক্ষার্থীর স্বাক্ষর';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>শিক্ষার্থী লাইফটাইম স্টেটমেন্ট - {{ $p['full_name'] }} ({{ $selYear }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
            background: #fff;
            font-size: 11.5px;
            line-height: 1.35;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #033364;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #033364;
            font-size: 21px;
            font-weight: 800;
            letter-spacing: 0.3px;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 11px;
            color: #475569;
        }
        .issue-time {
            font-size: 10.5px;
            color: #64748b;
            margin-top: 3px;
        }
        .section-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #033364;
            border-left: 3.5px solid #033364;
            padding-left: 7px;
            margin: 10px 0 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 11px;
        }
        table th, table td {
            border: 1px solid #cbd5e1;
            padding: 3.5px 6px;
            text-align: left;
        }
        table th {
            background: #f1f5f9;
            font-weight: 700;
            color: #334155;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .print-btn-bar {
            background: #f8fafc;
            padding: 8px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 12px;
            text-align: right;
        }
        
        /* Two box summary grid */
        .summary-grid {
            display: flex;
            gap: 12px;
            margin-bottom: 8px;
        }
        .summary-box {
            flex: 1;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            overflow: hidden;
        }
        .summary-box-header {
            background: #f1f5f9;
            font-weight: bold;
            font-size: 11.5px;
            padding: 5px 8px;
            border-bottom: 1px solid #cbd5e1;
            color: #033364;
        }
        .summary-box table {
            margin-bottom: 0;
            font-size: 11px;
        }
        .summary-box table th, .summary-box table td {
            border: none;
            border-bottom: 1px solid #e2e8f0;
            padding: 4px 8px;
        }
        .summary-box table tr:last-child th, .summary-box table tr:last-child td {
            border-bottom: none;
        }

        .signatures {
            margin-top: 32px;
            display: flex;
            justify-content: space-between;
            padding: 0 40px;
            font-size: 11px;
        }
        .sign-line {
            text-align: center;
            border-top: 1.5px solid #475569;
            width: 190px;
            padding-top: 5px;
            font-weight: 700;
            color: #1e293b;
        }

        @media print {
            .print-btn-bar {
                display: none !important;
            }
            body {
                padding: 0;
            }
            table, tr, .summary-grid, .signatures {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <button onclick="window.print()" style="background:#033364;color:#fff;border:none;padding:6px 14px;border-radius:4px;font-weight:bold;cursor:pointer;font-size:11px;">
            🖨️ প্রিন্ট করুন (Print / Save as PDF)
        </button>
    </div>

    <!-- Header -->
    <div class="header">
        <h2>{{ $siteName }}</h2>
        <p>{{ $siteAddress }} | হেল্পলাইন: {{ $sitePhone }} @if(!empty($siteEmail)) | ইমেইল: {{ $siteEmail }} @endif</p>
        <div class="issue-time">ইস্যুর তারিখ ও সময়: {{ \Carbon\Carbon::now('Asia/Dhaka')->format('d M Y, h:i A') }}</div>
    </div>

    <!-- 1. Personal & Allocation Details -->
    <div class="section-title">১. শিক্ষার্থীর পরিচিতি ও অবস্থান বিবরণী</div>
    <table>
        <tr>
            <th style="width: 16%;">শিক্ষার্থীর নাম:</th>
            <td style="width: 34%;"><strong>{{ $p['full_name'] }}</strong></td>
            <th style="width: 16%;">বর্তমান অবস্থান:</th>
            <td style="width: 34%;"><strong>{{ $b->room_seat_full }}</strong></td>
        </tr>
        <tr>
            <th>মোবাইল নম্বর:</th>
            <td>{{ $p['phone'] }}</td>
            <th>লগইন পাসওয়ার্ড:</th>
            <td><strong>{{ $p['password'] }}</strong></td>
        </tr>
        <tr>
            <th>প্রতিষ্ঠান/কর্মক্ষেত্র:</th>
            <td>{{ $p['institution_name'] }}</td>
            <th>ভর্তির তারিখ:</th>
            <td><strong>{{ $p['admission_date'] }}</strong></td>
        </tr>
        <tr>
            <th>পিতার নাম ও ফোন:</th>
            <td>{{ $p['father_name'] }} ({{ $p['father_phone'] }})</td>
            <th>স্ট্যাটাস / রিলিজ:</th>
            <td>{{ ($p['is_deleted'] ?? false) ? 'ডিলেট / ইনঅ্যাক্টিভ (' . ($p['deleted_date'] ?? 'Deleted') . ')' : ($p['release_date'] ?? 'বর্তমান শিক্ষার্থী (Active)') }}</td>
        </tr>
        <tr>
            <th>স্থায়ী ঠিকানা:</th>
            <td colspan="3">{{ $p['address'] }}, {{ $p['thana'] }}, {{ $p['district'] }}</td>
        </tr>
    </table>

    <!-- 2. Monthly All-In-One Cost & Expense Ledger -->
    <div class="section-title">২. মাসিক খরচের পূর্ণাঙ্গ বিবরণী ({{ $selYear }} সাল) [ভাড়া + বিদ্যুৎ বিল + মিল চার্জ + চেঞ্জ ফি + বকেয়া]</div>
    <table>
        <thead>
            <tr style="background:#e0e7ff;">
                <th class="text-center" style="width: 4%;">#</th>
                <th>মাস</th>
                <th class="text-right">রুম ভাড়া</th>
                <th class="text-right">বিদ্যুৎ বিল</th>
                <th class="text-center">মিল খাওয়া (চার্জ)</th>
                <th class="text-center">চেঞ্জ ফি</th>
                <th class="text-right">পূর্বের বকেয়া</th>
                <th class="text-right" style="background:#e2e8f0; font-weight:bold;">সর্বমোট খরচ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mCostLedger as $idx => $row)
            @php
                $isActivePrint = (!empty($row['is_current_month']) || !empty($row['is_selected_month']));
            @endphp
            <tr style="{{ $isActivePrint ? 'background:#f0fdf4;' : '' }}">
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $row['month_year_label'] }}</strong>
                    @if($isActivePrint)
                        <span style="display:inline-block; font-size: 8.5px; font-weight: bold; background: #16a34a; color: #fff; padding: 1px 4px; border-radius: 8px; margin-left: 3px;">Active</span>
                    @endif
                </td>
                <td class="text-right">৳ {{ number_format($row['room_rent'], 2) }}</td>
                <td class="text-right">৳ {{ number_format($row['electricity_bill'], 2) }}</td>
                <td class="text-center">{{ $row['meal_count'] }} টি (৳ {{ number_format($row['meal_charge'], 2) }})</td>
                <td class="text-center">{{ $row['room_change_fee'] > 0 ? '৳ ' . number_format($row['room_change_fee'], 2) : '-' }}</td>
                <td class="text-right">৳ {{ number_format($row['prev_due'], 2) }}</td>
                <td class="text-right fw-bold" style="background:#f8fafc; font-weight:bold;">৳ {{ number_format($row['total_cost'], 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="color: #64748b; padding: 5px;">কোনো মাসিক খরচের রেকর্ড পাওয়া যায়নি।</td>
            </tr>
            @endforelse
            @if(!empty($residentData['range_totals']))
            @php $rt = $residentData['range_totals']; @endphp
            <tr style="background: #e2e8f0; font-weight: bold; border-top: 1.5px solid #033364;">
                <td colspan="2" class="text-center">মোট খরচ ({{ $residentData['range_label'] ?? 'নির্বাচিত রেঞ্জ' }}):</td>
                <td class="text-right">৳ {{ number_format($rt['rent_billed'] ?? 0, 2) }}</td>
                <td class="text-right">৳ {{ number_format($rt['electricity'] ?? 0, 2) }}</td>
                <td class="text-center">{{ $rt['meals_count'] ?? 0 }} টি (৳ {{ number_format($rt['meal_charge'] ?? 0, 2) }})</td>
                <td class="text-center">{{ ($rt['room_change_fee'] ?? 0) > 0 ? '৳ ' . number_format($rt['room_change_fee'], 2) : '-' }}</td>
                <td class="text-right">৳ {{ number_format($rt['prev_due'] ?? 0, 2) }}</td>
                <td class="text-right" style="background:#f1f5f9; font-weight:bold;">৳ {{ number_format($rt['combined_cost'] ?? 0, 2) }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- 3. Room Movement History -->
    <div class="section-title">৩. রুম ও সিট পরিবর্তনের ইতিহাস (Room Movement History)</div>
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 4%;">#</th>
                <th>তারিখ</th>
                <th>পূর্বের রুম ও সিট</th>
                <th>নতুন রুম ও সিট</th>
                <th class="text-right">পূর্বের ভাড়া</th>
                <th class="text-right">নতুন ভাড়া</th>
                <th class="text-right">চেঞ্জ ফি</th>
                <th>মন্তব্য</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rMoves as $idx => $rm)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $rm->created_at ? $rm->created_at->format('d M Y') : '-' }}</td>
                <td>{{ $rm->old_floor }} - {{ $rm->old_room_seat }}</td>
                <td><strong>{{ $rm->new_floor }} - {{ $rm->new_room_seat }}</strong></td>
                <td class="text-right">৳ {{ number_format($rm->old_monthly_amount, 2) }}</td>
                <td class="text-right">৳ {{ number_format($rm->new_monthly_amount, 2) }}</td>
                <td class="text-right">৳ {{ number_format($rm->fee_amount, 2) }}</td>
                <td>{{ $rm->remarks ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center" style="color: #64748b; padding: 4px;">
                    রুম পরিবর্তনের কোনো রেকর্ড নেই (ভর্তির পর থেকে একই রুমে অবস্থান করছেন)।
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. Overall Lifetime Summary: 2 Clear Boxes -->
    <div class="section-title">৪. সর্বমোট আর্থিক ও মিল বিবরণী (Overall Financial Summary)</div>
    <div class="summary-grid">
        <!-- Box 1: Meal Summary -->
        <div class="summary-box">
            <div class="summary-box-header">🍱 মিলের হিসাব বিবরণী (Meal Summary)</div>
            <table>
                <tr>
                    <td style="width: 60%;">মোট মিল ব্যালেন্স ডিপোজিট:</td>
                    <td class="text-right" style="width: 40%; font-weight: bold; color: #033364;">৳ {{ number_format($mSummary['total_deposit'], 2) }}</td>
                </tr>
                <tr>
                    <td>(-) খাবার বাবদ মোট কর্তন ({{ $mSummary['total_count'] ?? $mSummary['total_full_meals'] }} টি মিল{{ !empty($mSummary['guest_meals_count']) && $mSummary['guest_meals_count'] > 0 ? ': ' . ($mSummary['regular_meals_count'] ?? 0) . ' রেগুলার + ' . ($mSummary['guest_meals_count'] ?? 0) . ' গেস্ট' : '' }}):</td>
                    <td class="text-right" style="color: #991b1b; font-weight: bold;">- ৳ {{ number_format($mSummary['total_cost'] ?? 0, 2) }}</td>
                </tr>
                <tr style="background: #f0fdf4;">
                    <th style="color: #166534; font-weight: bold;">= বর্তমান অবশিষ্ট মিল জমা (ব্যালেন্স):</th>
                    <td class="text-right" style="color: {{ ($mSummary['balance'] ?? 0) >= 0 ? '#166534' : '#991b1b' }}; font-weight: 800; font-size: 11px;">
                        ৳ {{ number_format($mSummary['balance'] ?? 0, 2) }}
                    </td>
                </tr>
            </table>
        </div>

        <!-- Box 2: Grand Total Summary -->
        <div class="summary-box">
            <div class="summary-box-header">💰 সর্বমোট জমা সারসংক্ষেপ (Grand Total Summary)</div>
            <table>
                <tr>
                    <td style="width: 60%;">ভর্তির এডভান্স জামানত (Advance Deposit):</td>
                    <td class="text-right" style="width: 40%; font-weight: bold;">৳ {{ number_format($pSummary['advance_paid'], 2) }}</td>
                </tr>
                @if(!empty($pSummary['dev_fee_paid']) && $pSummary['dev_fee_paid'] > 0)
                <tr>
                    <td>ডেভেলপমেন্ট ফি (Development Fee):</td>
                    <td class="text-right" style="font-weight: bold;">৳ {{ number_format($pSummary['dev_fee_paid'], 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td>বর্তমান অবশিষ্ট মিল জমা (ব্যালেন্স):</td>
                    <td class="text-right" style="font-weight: bold; color: #166534;">৳ {{ number_format($mSummary['balance'] ?? 0, 2) }}</td>
                </tr>
                <tr style="background: #e0e7ff;">
                    <th style="color: #033364; font-weight: bold;">= সর্বমোট জমাকৃত অর্থ (Grand Total Paid):</th>
                    <td class="text-right" style="color: #033364; font-weight: 800; font-size: 11.5px;">
                        ৳ {{ number_format($pSummary['grand_total_deposit'] ?? 0, 2) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Signatures -->
    <div class="signatures">
        <div class="sign-line">
            {{ $sigLabel }}
        </div>
        <div class="sign-line">
            কর্তৃপক্ষের স্বাক্ষর ও সিল
        </div>
    </div>

</body>
</html>
