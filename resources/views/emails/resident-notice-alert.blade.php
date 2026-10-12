<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>জরুরি বিজ্ঞপ্তি</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .email-container {
            max-width: 560px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .email-header {
            background: linear-gradient(135deg, #033364 0%, #0d47a1 100%);
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .email-header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }
        .email-header p {
            margin: 6px 0 0;
            font-size: 13px;
            opacity: 0.85;
        }
        .email-body {
            padding: 32px 28px;
            text-align: left;
        }
        .greeting-text {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .message-text {
            font-size: 15px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 28px;
        }
        .btn-action-wrap {
            text-align: center;
            margin: 20px 0 24px;
        }
        .btn-action {
            display: inline-block;
            background: #033364;
            color: #ffffff !important;
            text-decoration: none;
            padding: 13px 32px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(3, 51, 100, 0.25);
        }
        .btn-action:hover {
            background: #022244;
        }
        .info-note {
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            text-align: center;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 18px 24px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h2>📢 জরুরি বিজ্ঞপ্তি</h2>
            <p>{{ $siteTitle ?? 'টি এস এস ভিলা' }} - হোস্টেল কর্তৃপক্ষ</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting-text">
                প্রিয় শিক্ষার্থী,
            </div>
            <div class="message-text">
                হোস্টেল কর্তৃপক্ষের পক্ষ থেকে নতুন একটি <strong>জরুরি বিজ্ঞপ্তি</strong> প্রকাশিত হয়েছে।
            </div>

            <div class="btn-action-wrap">
                <a href="{{ url('/') }}" class="btn-action">
                    ওয়েবসাইটে নোটিশ বোর্ডে দেখুন ➔
                </a>
            </div>

            <div class="info-note">
                💡 ওয়েবসাইটে গিয়ে নোটিশ বাটনে ক্লিক করে আপনার মোবাইল নম্বর ও পাসওয়ার্ড দিয়ে আজকের বিজ্ঞপ্তি এবং অতীতের সকল বিজ্ঞপ্তির তালিকা দেখতে পারবেন।
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin: 0 0 4px; font-weight: 600; color: #334155;">{{ $siteTitle ?? 'টি এস এস ভিলা' }}</p>
            <p style="margin: 0;">যোগাযোগ: {{ $sitePhone ?? '01977270920' }} | {{ $siteAddress ?? '' }}</p>
        </div>
    </div>
</body>
</html>
