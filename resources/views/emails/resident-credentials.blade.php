@php
    $webSettingRecord = \App\Models\Backend\Setting::where('slug', 'web_setting')->first();
    $webSettingData = $webSettingRecord ? json_decode($webSettingRecord->contents, true) : [];
    $dynSiteName = $webSettingData['title'] ?? 'টি এস এস ভিলা (TSS Villa)';
    $dynPhone = $webSettingData['phone'] ?? ($admin_phone ?? '01977270920');
    $dynAddress = $webSettingData['address'] ?? '';
    $dynEmail = $webSettingData['email'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>আপনার {{ $dynSiteName }} লগইন তথ্য</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            color: #1e293b;
        }
        .email-container {
            max-width: 580px;
            margin: 25px auto;
            background: #ffffff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.07);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #033364 0%, #02172c 100%);
            color: #ffffff;
            padding: 28px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 6px 0 0 0;
            font-size: 13.5px;
            opacity: 0.9;
        }
        .content {
            padding: 30px 26px;
        }
        .greeting {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .intro-text {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 22px;
        }
        .credentials-card {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .cred-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        .cred-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .cred-label {
            color: #64748b;
            font-weight: 600;
        }
        .cred-value {
            color: #0f172a;
            font-weight: 700;
            font-family: monospace;
            font-size: 15px;
        }
        .cred-password {
            background: #e0f2fe;
            color: #0369a1;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 2px;
        }
        .btn-login {
            display: block;
            width: 85%;
            margin: 0 auto 20px auto;
            background: #033364;
            color: #ffffff !important;
            text-align: center;
            padding: 14px 20px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(3,51,100,0.25);
        }
        .help-note {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #166534;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .footer {
            background: #f8fafc;
            padding: 18px;
            text-align: center;
            font-size: 12.5px;
            color: #6b7280;
            line-height: 1.6;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ $dynSiteName }}</h1>
            <p>নিরাপদ ও আধুনিক ছাত্রী নিবাস</p>
        </div>
        <div class="content">
            <div class="greeting">
                আসসালামু আলাইকুম, {{ $resident_name ?? 'সম্মানিত শিক্ষার্থী' }}!
            </div>
            <p class="intro-text">
                {{ $dynSiteName }} হোস্টেল ম্যানেজমেন্ট সিস্টেমে আপনার শিক্ষার্থী প্রোফাইল ও লগইন অ্যাক্সেস সম্পর্কিত তথ্যাবলী নিচে দেওয়া হলো। আপনার অ্যাকাউন্টে সাইন ইন করে মিল সার্ভিস, পেমেন্ট রসিদ এবং অন্যান্য সুবিধা উপভোগ করুন:
            </p>

            <div class="credentials-card">
                <div class="cred-item">
                    <span class="cred-label">👤 শিক্ষার্থীর নাম:</span>
                    <span class="cred-value" style="font-family: inherit;">{{ $resident_name }}</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">🏢 রুম ও সিট:</span>
                    <span class="cred-value" style="font-family: inherit;">{{ $room_seat }}</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">📱 লগইন আইডি / ফোন:</span>
                    <span class="cred-value">{{ $phone }}</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">🔑 লগইন পাসওয়ার্ড:</span>
                    <span class="cred-password">{{ $password }}</span>
                </div>
            </div>

            <a href="{{ $login_url ?? url('/') }}" class="btn-login" target="_blank">
                লগইন করুন (Sign In to Dashboard) &rarr;
            </a>

            <div class="help-note">
                💡 <strong>টিপস:</strong> আপনি চাইলে যেকোনো সময় সিস্টেমে লগইন করে আপনার পছন্দমতো নতুন পাসওয়ার্ড পরিবর্তন (Change Password) করতে পারবেন।
            </div>

            <p style="font-size: 12px; color: #64748b; line-height: 1.5; margin: 0; text-align: center;">
                যেকোনো প্রয়োজনে যোগাযোগ করুন: <strong>{{ $dynPhone }}</strong>
            </p>
        </div>
        <div class="footer">
            <strong>{{ $dynSiteName }}</strong> - নিরাপদ ও আধুনিক ছাত্রী নিবাস<br>
            @if(!empty($dynAddress))
                <span>📍 {{ $dynAddress }}</span> |
            @endif
            <span>📞 হেল্পলাইন: <strong>{{ $dynPhone }}</strong></span>
            <div style="margin-top:6px; font-size:11.5px; color:#9ca3af;">
                &copy; {{ date('Y') }} {{ $dynSiteName }}. সর্বস্বত্ব সংরক্ষিত।
            </div>
        </div>
    </div>
</body>
</html>
