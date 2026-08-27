<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('status') | 34 Build Master</title>
    <link rel="icon" href="{{ asset('brand-logo.png') }}" type="image/png">
    <style>
        * { box-sizing: border-box; }
        html { background: #f4f6f4; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                linear-gradient(rgba(18, 107, 73, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(18, 107, 73, .035) 1px, transparent 1px),
                #f4f6f4;
            background-size: 48px 48px;
            color: #17251f;
            font-family: "LINE Seed Sans TH", "Kanit", Tahoma, Arial, sans-serif;
        }
        main {
            width: min(100%, 720px);
            border-top: 5px solid #126b49;
            background: #fff;
            padding: clamp(32px, 7vw, 72px);
            box-shadow: 0 22px 70px rgba(23, 37, 31, .1);
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
            color: inherit;
            text-decoration: none;
        }
        .brand img {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
        }
        .brand span { display: grid; gap: 2px; }
        .brand strong { font-size: 14px; text-transform: uppercase; }
        .brand small { color: #126b49; font-size: 10px; font-weight: 700; text-transform: uppercase; }
        .status {
            margin: 0 0 12px;
            color: #126b49;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .14em;
        }
        h1 {
            max-width: 620px;
            margin: 0;
            font-size: clamp(36px, 7vw, 64px);
            line-height: 1.12;
            letter-spacing: 0;
        }
        .message {
            max-width: 580px;
            margin: 20px 0 0;
            color: #66736c;
            font-size: 17px;
            line-height: 1.75;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 36px;
        }
        .button {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #126b49;
            border-radius: 999px;
            background: #126b49;
            color: #fff;
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }
        .button.secondary { background: transparent; color: #126b49; }
        .reference {
            margin: 48px 0 0;
            border-top: 1px solid #e4e9e6;
            padding-top: 18px;
            color: #89938e;
            font-size: 11px;
        }
        @media (max-width: 560px) {
            body { padding: 0; place-items: stretch; }
            main { min-height: 100vh; box-shadow: none; }
            .brand { margin-bottom: 56px; }
            .actions { align-items: stretch; flex-direction: column; }
        }
    </style>
</head>
<body>
    <main>
        <a class="brand" href="{{ url('/') }}" aria-label="34 Build Master">
            <img src="{{ asset('brand-logo.png') }}" alt="">
            <span><strong>Build Master</strong><small>Construction</small></span>
        </a>
        <p class="status">ERROR @yield('status')</p>
        <h1>@yield('heading')</h1>
        <p class="message">@yield('message')</p>
        <div class="actions">
            <a class="button" href="{{ url('/') }}">กลับหน้าหลัก</a>
            @hasSection('secondary_url')
                <a class="button secondary" href="@yield('secondary_url')">@yield('secondary_label')</a>
            @endif
        </div>
        <p class="reference">34 Build Master Construction · ระบบไม่ได้แสดงรายละเอียดทางเทคนิคเพื่อความปลอดภัย</p>
    </main>
</body>
</html>
