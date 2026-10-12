@php
    $siteName = 'টি এস এস ভিলা (TSS Villa)';
    $sitePhone = '01977270920';
    $siteAddress = 'মহিলা হোস্টেল ও ছাত্রী নিবাস';
    $totalElectricity = (float)($totalElectricity ?? 0);
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>বিদ্যুৎ বিল স্টেটমেন্ট - {{ $resident->name }} ({{ $rangeLabel ?? $selectedYear }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #1e293b;
            background: #fff;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #033364;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header h2 {
            margin: 0;
            color: #033364;
            font-size: 22px;
            font-weight: 800;
        }
        .header p {
            margin: 3px 0 0 0;
            font-size: 11.5px;
            color: #475569;
        }
        .statement-title {
            display: inline-block;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12.5px;
            font-weight: 700;
            color: #033364;
            margin-top: 6px;
        }
        .resident-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 14px;
        }
        .resident-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 20px;
            font-size: 11.5px;
            flex-grow: 1;
        }
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 11.5px;
        }
        .table-custom th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 700;
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            text-align: left;
        }
        .table-custom td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: 700; }
        .footer-sig {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }
        .sig-line {
            border-top: 1px solid #475569;
            width: 180px;
            text-align: center;
            padding-top: 5px;
            font-size: 11px;
            font-weight: 600;
        }
        .no-print {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .btn-print {
            background: #033364;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
        }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ প্রিন্ট করুন (Print Statement)</button>
    </div>

    <!-- Header -->
    <div class="header">
        <h2>{{ $siteName }}</h2>
        <p>মোবাইল: {{ $sitePhone }}</p>
        <div class="statement-title">⚡ বিদ্যুৎ বিল স্টেটমেন্ট — {{ $rangeLabel ?? $selectedYear }}</div>
    </div>

    <!-- Resident Info -->
    <div class="resident-box">
        <div class="resident-info">
            <div><strong>নাম (Name):</strong> {{ $resident->name }}</div>
            <div><strong>মোবাইল (Mobile):</strong> {{ $resident->phone }}</div>
            <div><strong>রুম ও সিট (Room & Seat):</strong> {{ $resident->room_seat_full }}</div>
            <div><strong>ইউজার টাইপ (Type):</strong> {{ $resident->user_type }}</div>
            <div><strong>মাসিক সিট ভাড়া:</strong> ৳ {{ number_format($resident->monthly_amount, 2) }}</div>
            <div><strong>তারিখ:</strong> {{ \Carbon\Carbon::now('Asia/Dhaka')->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    <!-- Monthly Electricity Bill Table -->
    <table class="table-custom">
        <thead>
            <tr>
                <th style="width: 40px;" class="text-center">#</th>
                <th>বিলের মাস (Billing Month)</th>
                <th class="text-center">বিদ্যুৎ বিল (Electricity Bill)</th>
                <th>তারিখ</th>
                <th class="text-center">স্ট্যাটাস</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monthlyLedger as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold">{{ $item['month_year_label'] }}</td>
                    <td class="text-center fw-bold" style="color: #d97706;">
                        @if($item['electricity_bill'] > 0)
                            ⚡ ৳ {{ number_format($item['electricity_bill'], 2) }}
                        @else
                            <span style="color: #64748b; font-weight: normal;">৳ 0.00</span>
                        @endif
                    </td>
                    <td>{{ $item['payment_date'] }}</td>
                    <td class="text-center">
                        @if($item['status'] === 'paid')
                            <span style="color: #166534; font-weight: bold;">Paid</span>
                        @elseif($item['status'] === 'partial')
                            <span style="color: #854d0e; font-weight: bold;">Partial</span>
                        @elseif($item['status'] === 'pending')
                            <span style="color: #991b1b; font-weight: bold;">Pending</span>
                        @else
                            <span style="color: #94a3b8;">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #64748b;">
                        {{ $selectedYear }} সালের কোনো বিলের তথ্য পাওয়া যায়নি।
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($monthlyLedger) > 0)
        <tfoot>
            <tr style="background-color: #fef3c7; font-weight: bold;">
                <td colspan="2" class="text-end" style="border: 1px solid #cbd5e1;">সর্বমোট বিদ্যুৎ বিল (Total Electricity):</td>
                <td class="text-center" style="color: #b45309; font-size: 13px; border: 1px solid #cbd5e1;">
                    ⚡ ৳ {{ number_format($totalElectricity, 2) }}
                </td>
                <td colspan="2" style="border: 1px solid #cbd5e1;"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- Signature Footer -->
    <div class="footer-sig">
        <div class="sig-line">বর্ডারের স্বাক্ষর (Resident Signature)</div>
        <div class="sig-line">কর্তৃপক্ষের স্বাক্ষর (Authorized Signature)</div>
    </div>

</body>
</html>
