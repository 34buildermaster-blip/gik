@props(['title' => '34 Build Master Admin', 'auth' => false])

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #053920;
            --deep: #112416;
            --gold: #f6d97b;
            --gold-dark: #aa7426;
            --paper: #fffaf0;
            --muted: #5b675e;
            --line: rgba(170, 116, 38, .22);
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: #fbf7ec;
            color: var(--deep);
            font-family: "Prompt", system-ui, sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        .shell { min-height: 100vh; display: grid; grid-template-columns: 280px 1fr; }
        .sidebar {
            background:
                radial-gradient(circle at 20% 0%, rgba(246, 217, 123, .18), transparent 30%),
                linear-gradient(180deg, rgba(5, 57, 32, .98), rgba(17, 36, 22, .98));
            color: white;
            padding: 28px 22px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow: auto;
        }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 34px; }
        .brand-mark {
            width: 50px;
            height: 50px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(246, 217, 123, .55);
            border-radius: 16px;
            color: #fdf0a3;
            background: rgba(17, 36, 22, .8);
            font-weight: 900;
        }
        .brand-title { display: block; font-weight: 900; text-transform: uppercase; letter-spacing: .12em; line-height: 1.1; }
        .brand-sub { display: block; color: var(--gold); font-size: 12px; text-transform: uppercase; letter-spacing: .2em; }
        .nav { display: grid; gap: 8px; }
        .nav a, .logout-button {
            width: 100%;
            border: 1px solid rgba(246, 217, 123, .16);
            border-radius: 18px;
            background: rgba(255, 255, 255, .05);
            color: rgba(255, 255, 255, .82);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 13px 15px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        .nav a:hover, .logout-button:hover {
            border-color: rgba(246, 217, 123, .5);
            color: var(--gold);
        }
        .main { min-width: 0; padding: 30px; }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
        }
        .eyebrow {
            color: var(--gold-dark);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        h1 { margin: 8px 0 0; font-size: clamp(34px, 5vw, 58px); line-height: 1.05; color: var(--green); }
        h2 { margin: 0; color: var(--green); font-size: clamp(24px, 3vw, 34px); }
        h3 { margin: 0; color: var(--green); }
        p { line-height: 1.75; }
        .card {
            border: 1px solid var(--line);
            border-radius: 24px;
            background: rgba(255, 255, 255, .78);
            box-shadow: 0 24px 80px rgba(17, 36, 22, .08);
        }
        .panel { padding: 24px; }
        .grid { display: grid; gap: 18px; }
        .stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .stat-number { font-size: 42px; font-weight: 900; color: var(--gold-dark); }
        .muted { color: var(--muted); }
        .button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 0;
            border-radius: 999px;
            padding: 0 20px;
            background: linear-gradient(135deg, #aa7426, #f6d97b 55%, #fdf0a3);
            color: var(--deep);
            font: inherit;
            font-weight: 900;
            cursor: pointer;
            white-space: nowrap;
        }
        .button.secondary {
            background: transparent;
            border: 1px solid rgba(170, 116, 38, .35);
            color: var(--green);
        }
        .button.danger {
            background: #8f1d1d;
            color: white;
        }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 780px; }
        th, td { padding: 16px; text-align: left; border-bottom: 1px solid rgba(170, 116, 38, .15); vertical-align: middle; }
        th { color: var(--gold-dark); font-size: 13px; letter-spacing: .12em; text-transform: uppercase; }
        .thumb { width: 112px; height: 76px; object-fit: cover; border-radius: 16px; background: #e8dfca; }
        .empty-thumb { display: grid; place-items: center; color: var(--muted); font-size: 12px; }
        .badge {
            display: inline-flex;
            border-radius: 999px;
            padding: 6px 12px;
            background: rgba(5, 57, 32, .08);
            color: var(--green);
            font-size: 13px;
            font-weight: 800;
        }
        .badge.published { background: rgba(246, 217, 123, .42); color: #6a410a; }
        .form { display: grid; gap: 18px; }
        .field { display: grid; gap: 8px; }
        .field label { color: var(--green); font-weight: 800; }
        input, textarea, select {
            width: 100%;
            border: 1px solid rgba(170, 116, 38, .24);
            border-radius: 16px;
            background: rgba(255, 255, 255, .88);
            color: var(--deep);
            font: inherit;
            padding: 14px 16px;
            outline: none;
        }
        textarea { min-height: 220px; resize: vertical; }
        input:focus, textarea:focus, select:focus {
            border-color: var(--gold-dark);
            box-shadow: 0 0 0 4px rgba(246, 217, 123, .18);
        }
        .rich-editor {
            overflow: hidden;
            border: 1px solid rgba(170, 116, 38, .24);
            border-radius: 20px;
            background: rgba(255, 255, 255, .88);
        }
        .rich-toolbar {
            position: sticky;
            top: 0;
            z-index: 2;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
            border-bottom: 1px solid rgba(170, 116, 38, .18);
            background: rgba(255, 250, 240, .96);
            padding: 10px;
        }
        .rich-toolbar button,
        .rich-toolbar select,
        .rich-toolbar input[type="color"] {
            width: auto;
            min-height: 36px;
            border: 1px solid rgba(170, 116, 38, .25);
            border-radius: 12px;
            background: white;
            color: var(--green);
            font: inherit;
            font-size: 13px;
            font-weight: 800;
            padding: 7px 10px;
            cursor: pointer;
        }
        .rich-toolbar button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            min-width: 38px;
            padding: 0;
        }
        .rich-toolbar button strong,
        .rich-toolbar button em,
        .rich-toolbar button u,
        .rich-toolbar button s {
            font-size: 14px;
            line-height: 1;
        }
        .tool-icon {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }
        .rich-toolbar button.is-loading .tool-icon,
        .rich-toolbar button.is-loading strong,
        .rich-toolbar button.is-loading em,
        .rich-toolbar button.is-loading u,
        .rich-toolbar button.is-loading s {
            display: none;
        }
        .rich-toolbar button.is-loading::after {
            content: "";
            width: 15px;
            height: 15px;
            border: 2px solid rgba(5, 57, 32, .2);
            border-top-color: var(--green);
            border-radius: 999px;
            animation: spin .8s linear infinite;
        }
        .rich-toolbar input[type="color"] {
            width: 42px;
            padding: 4px;
        }
        .rich-toolbar .toolbar-font-select {
            min-width: 132px;
        }
        .rich-toolbar .toolbar-size-select {
            min-width: 78px;
        }
        .rich-toolbar button:hover,
        .rich-toolbar button.is-active,
        .rich-toolbar select:hover {
            border-color: rgba(170, 116, 38, .55);
            background: rgba(246, 217, 123, .24);
        }
        .rich-toolbar button:disabled {
            cursor: progress;
            opacity: .62;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .rich-canvas {
            min-height: 420px;
            overflow-x: auto;
            padding: 22px;
            outline: none;
            line-height: 1.85;
        }
        .rich-canvas:focus {
            box-shadow: inset 0 0 0 4px rgba(246, 217, 123, .15);
        }
        .rich-canvas.is-dragging {
            box-shadow: inset 0 0 0 4px rgba(246, 217, 123, .42);
            background: rgba(246, 217, 123, .1);
        }
        .rich-canvas h2,
        .rich-canvas h3,
        .rich-canvas h4 {
            margin: 24px 0 10px;
            color: var(--green);
            line-height: 1.35;
        }
        .rich-canvas p { margin: 0 0 16px; }
        .rich-canvas ul,
        .rich-canvas ol {
            padding-left: 28px;
        }
        .rich-canvas blockquote {
            margin: 20px 0;
            border-left: 5px solid var(--gold-dark);
            border-radius: 14px;
            background: rgba(246, 217, 123, .16);
            padding: 16px 18px;
            color: var(--green);
            font-weight: 700;
        }
        .rich-canvas figure {
            margin: 22px 0;
        }
        .rich-canvas figure img,
        .rich-canvas figure video {
            width: 100%;
            border-radius: 16px;
            box-shadow: 0 18px 58px rgba(17, 36, 22, .12);
        }
        .rich-canvas figure video {
            background: #112416;
            aspect-ratio: 16 / 9;
        }
        .rich-canvas figcaption {
            margin-top: 8px;
            color: var(--muted);
            font-size: 13px;
            text-align: center;
        }
        .rich-canvas table {
            min-width: 100%;
            margin: 22px 0;
            overflow: hidden;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid rgba(170, 116, 38, .22);
            border-radius: 16px;
            background: white;
        }
        .rich-canvas th,
        .rich-canvas td {
            border-bottom: 1px solid rgba(170, 116, 38, .15);
            border-right: 1px solid rgba(170, 116, 38, .12);
            padding: 12px 14px;
            vertical-align: top;
        }
        .rich-canvas th {
            background: rgba(5, 57, 32, .08);
            color: var(--green);
            font-size: 14px;
            letter-spacing: 0;
            text-transform: none;
        }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .full { grid-column: 1 / -1; }
        .alert {
            margin-bottom: 18px;
            border-radius: 18px;
            padding: 14px 16px;
            background: rgba(246, 217, 123, .28);
            color: var(--green);
            font-weight: 800;
        }
        .errors {
            margin-bottom: 18px;
            border-radius: 18px;
            padding: 14px 18px;
            background: #fff1f1;
            color: #8f1d1d;
        }
        .errors ul { margin-bottom: 0; }
        .auth-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at 20% 20%, rgba(246, 217, 123, .28), transparent 32%),
                linear-gradient(135deg, #053920, #112416);
        }
        .auth-card { width: min(100%, 500px); padding: 30px; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
        .row-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .pagination { margin-top: 20px; }
        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
            .main { padding: 20px; }
            .stats, .form-grid { grid-template-columns: 1fr; }
            .topbar { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    @if ($auth)
        {{ $slot }}
    @else
        <div class="shell">
            <aside class="sidebar">
                <a class="brand" href="{{ route('admin.dashboard') }}">
                    <span class="brand-mark">34</span>
                    <span>
                        <span class="brand-title">Build Master</span>
                        <span class="brand-sub">Construction</span>
                    </span>
                </a>
                <nav class="nav">
                    <a href="{{ route('admin.dashboard') }}">แดชบอร์ด <span>&rsaquo;</span></a>
                    <a href="{{ route('admin.articles.index') }}">บทความ <span>&rsaquo;</span></a>
                    <a href="{{ url('/') }}" target="_blank">ดูหน้าเว็บ <span>&rsaquo;</span></a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit">ออกจากระบบ <span>&rsaquo;</span></button>
                    </form>
                </nav>
            </aside>
            <main class="main">
                @if (session('success'))
                    <div class="alert">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="errors">
                        <strong>ตรวจสอบข้อมูลอีกครั้ง</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    @endif
</body>
</html>
