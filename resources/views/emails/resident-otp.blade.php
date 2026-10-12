@php
    $webSettingRecord = \App\Models\Backend\Setting::where('slug', 'web_setting')->first();
    $webSettingData = $webSettingRecord ? json_decode($webSettingRecord->contents, true) : [];
    $dynSiteName = $webSettingData['title'] ?? 'টি এস এস ভিলা (TSS Villa)';
    $dynPhone = $webSettingData['phone'] ?? '01977270920';
    $dynAddress = $webSettingData['address'] ?? '';
    $dynEmail = $webSettingData['email'] ?? '';
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? ($dynSiteName . ' - Security Verification Code') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .email-container {
            max-width: 560px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border: 1px solid #e5e9f0;
        }
        .header {
            background: linear-gradient(135deg, #033364 0%, #02172c 100%);
            color: #ffffff;
            padding: 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 4px 0 0 0;
            font-size: 13px;
            opacity: 0.85;
        }
        .content {
            padding: 28px 24px;
            text-align: center;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .instruction {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .otp-box {
            background: #f0f7ff;
            border: 2px dashed #0d6efd;
            border-radius: 10px;
            padding: 16px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #033364;
            display: inline-block;
            margin-bottom: 20px;
        }
        .expiry-note {
            font-size: 12.5px;
            color: #dc2626;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .warning-text {
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.4;
            border-top: 1px solid #e2e8f0;
            padding-top: 16px;
        }
        .footer {
            background: #f8fafc;
            padding: 16px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.5;
            border-top: 1px solid #edf2f7;
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
            <p class="instruction">
                @if(($type ?? 'login') === 'forgot_password')
                    আপনার পাসওয়ার্ড রিসেট করার জন্য নিচে প্রদত্ত ৬ ডিজিটের ওটিপি (OTP) কোডটি ব্যবহার করুন:
                @else
                    আপনার অ্যাকাউন্টে লগইন করার জন্য নিচে প্রদত্ত ৬ ডিজিটের ওটিপি (OTP) কোডটি ব্যবহার করুন:
                @endif
            </p>

            <div class="otp-box">
                {{ $otp }}
            </div>

            <p class="expiry-note">
                ⏱️ এই কোডটি আগামী ১০ মিনিটের জন্য কার্যকর থাকবে।
            </p>

            <p class="warning-text">
                সতর্কতা: এই ওটিপি কোডটি কারো সাথে শেয়ার করবেন না। আপনি যদি এই রিকোয়েস্ট না করে থাকেন, তবে হেল্পলাইনে যোগাযোগ করুন: <strong>{{ $dynPhone }}</strong>
            </p>
        </div>
        <div class="footer">
            <strong>{{ $dynSiteName }}</strong> - সর্বস্বত্ব সংরক্ষিত।<br>
            <span>📞 হেল্পলাইন: <strong>{{ $dynPhone }}</strong></span>
            @if(!empty($dynEmail))
                | <span>✉️ {{ $dynEmail }}</span>
            @endif
        </div>
    </div>
</body>
</html>
