<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 | পেজের মেয়াদ শেষ হয়েছে (Page Expired)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', 'Hind Siliguri', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .error-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            max-width: 500px;
            width: 100%;
            padding: 40px 30px;
            text-align: center;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .icon-wrap {
            width: 80px;
            height: 80px;
            background: #eff6ff;
            color: #2563eb;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 38px;
            margin-bottom: 20px;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.15);
        }
        .error-code {
            display: inline-block;
            background: #fee2e2;
            color: #dc2626;
            font-weight: 700;
            font-size: 13px;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }
        h2 {
            font-size: 22px;
            color: #0f172a;
            margin-bottom: 10px;
            font-weight: 700;
        }
        p {
            color: #64748b;
            font-size: 14.5px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .btn-refresh {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #2563eb;
            color: #ffffff;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            border: none;
            cursor: pointer;
            font-size: 15px;
        }
        .btn-refresh:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }
        .countdown-text {
            margin-top: 18px;
            font-size: 13px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid #cbd5e1;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="error-card">
    <div class="icon-wrap">
        <i class="bi bi-clock-history"></i>
    </div>
    <br>
    <span class="error-code">419 • SESSION EXPIRED</span>
    <h2>সেশনের মেয়াদ শেষ হয়েছে</h2>
    <p>নিরাপত্তার স্বার্থে দীর্ঘক্ষণ নিষ্ক্রিয় থাকার কারণে পেজের সেশন শেষ হয়ে গিয়েছিল। আপনাকে পূর্বের পেজে ফিরিয়ে নেওয়া হচ্ছে...</p>
    
    <div>
        <a href="javascript:void(0);" onclick="handleRefresh();" class="btn-refresh">
            <i class="bi bi-arrow-clockwise"></i>
            <span>এখনই পেজ রিফ্রেশ করুন</span>
        </a>
    </div>

    <div class="countdown-text">
        <div class="spinner"></div>
        <span><strong id="timer">২</strong> সেকেন্ডের মধ্যে স্বয়ংক্রিয়ভাবে রিফ্রেশ হচ্ছে...</span>
    </div>
</div>

<script>
    let timeLeft = 2;
    const timerEl = document.getElementById('timer');
    
    function handleRefresh() {
        if (window.history.length > 1) {
            window.location.reload();
        } else {
            window.location.href = "{{ url('/') }}";
        }
    }

    const interval = setInterval(() => {
        timeLeft--;
        if (timerEl) timerEl.innerText = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(interval);
            handleRefresh();
        }
    }, 1000);
</script>

</body>
</html>
