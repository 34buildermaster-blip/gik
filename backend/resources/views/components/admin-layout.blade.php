@props(['title' => '34 Build Master Admin', 'auth' => false])

<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
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
        .shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 280px 1fr;
            transition: grid-template-columns .22s ease;
        }
        .shell.is-sidebar-collapsed { grid-template-columns: 92px 1fr; }
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
            transition: padding .22s ease;
        }
        .shell.is-sidebar-collapsed .sidebar { padding: 24px 16px; }
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
        .sidebar-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 28px;
        }
        .sidebar-head .brand { margin-bottom: 0; min-width: 0; }
        .sidebar-toggle {
            width: 42px;
            height: 42px;
            flex: 0 0 auto;
            display: inline-grid;
            place-items: center;
            border: 1px solid rgba(246, 217, 123, .32);
            border-radius: 14px;
            background: rgba(255, 255, 255, .07);
            color: var(--gold);
            cursor: pointer;
        }
        .sidebar-toggle:hover {
            border-color: rgba(246, 217, 123, .62);
            background: rgba(246, 217, 123, .13);
        }
        .sidebar-toggle svg,
        .nav-icon {
            width: 19px;
            height: 19px;
            fill: none;
            stroke: currentColor;
            stroke-linecap: round;
            stroke-linejoin: round;
            stroke-width: 2;
        }
        .shell.is-sidebar-collapsed .brand {
            justify-content: center;
        }
        .shell.is-sidebar-collapsed .brand-title,
        .shell.is-sidebar-collapsed .brand-sub {
            display: none;
        }
        .shell.is-sidebar-collapsed .sidebar-head {
            align-items: center;
            flex-direction: column;
        }
        .shell.is-sidebar-collapsed .sidebar-toggle svg {
            transform: rotate(180deg);
        }
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
            gap: 12px;
            padding: 13px 15px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }
        .nav-label {
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .nav-arrow {
            color: rgba(246, 217, 123, .72);
            font-size: 18px;
            line-height: 1;
        }
        .shell.is-sidebar-collapsed .nav a,
        .shell.is-sidebar-collapsed .logout-button {
            justify-content: center;
            padding: 13px 0;
        }
        .shell.is-sidebar-collapsed .nav-label,
        .shell.is-sidebar-collapsed .nav-arrow {
            display: none;
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
        .preview-shell {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 22px;
            align-items: start;
        }
        .preview-article {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 28px;
            background: #fffaf0;
            box-shadow: 0 24px 80px rgba(17, 36, 22, .08);
        }
        .preview-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(260px, 420px);
            gap: 28px;
            align-items: center;
            padding: clamp(24px, 4vw, 48px);
            background:
                radial-gradient(circle at 86% 16%, rgba(246, 217, 123, .24), transparent 28%),
                linear-gradient(135deg, rgba(5, 57, 32, .98), rgba(17, 36, 22, .98));
            color: white;
        }
        .preview-title {
            margin-top: 16px;
            color: #fffaf0;
            font-size: clamp(36px, 5vw, 68px);
            line-height: 1.08;
        }
        .preview-excerpt {
            max-width: 760px;
            color: rgba(255, 255, 255, .78);
            font-size: 19px;
        }
        .preview-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 22px;
            color: rgba(255, 255, 255, .62);
            font-size: 14px;
            font-weight: 800;
        }
        .preview-cover {
            margin: 0;
            overflow: hidden;
            border: 1px solid rgba(246, 217, 123, .24);
            border-radius: 22px;
            aspect-ratio: 4 / 3;
            background: rgba(255, 255, 255, .08);
        }
        .preview-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .preview-content {
            padding: clamp(24px, 4vw, 54px);
            color: #4d5b50;
            font-size: clamp(17px, 1.2vw, 19px);
            line-height: 1.9;
        }
        .preview-content > * + * { margin-top: 1.35rem; }
        .preview-content h2,
        .preview-content h3,
        .preview-content h4 {
            color: var(--green);
            line-height: 1.25;
        }
        .preview-content h2 { font-size: clamp(30px, 3vw, 44px); }
        .preview-content h3 { font-size: clamp(24px, 2.35vw, 34px); }
        .preview-content h4 { font-size: clamp(20px, 1.9vw, 26px); }
        .preview-content p { margin: 0; }
        .preview-content a {
            color: var(--gold-dark);
            font-weight: 800;
            text-decoration: underline;
            text-underline-offset: .2em;
        }
        .preview-content ul,
        .preview-content ol {
            display: grid;
            gap: .65rem;
            padding-left: 1.6rem;
        }
        .preview-content li::marker { color: var(--gold-dark); font-weight: 800; }
        .preview-content blockquote {
            overflow: hidden;
            border-left: 6px solid var(--gold-dark);
            border-radius: 20px;
            background: rgba(246, 217, 123, .16);
            padding: 20px 22px;
            color: var(--green);
            font-weight: 800;
        }
        .preview-content figure { margin: 2rem 0; }
        .preview-content figcaption {
            margin-top: .75rem;
            color: rgba(77, 91, 80, .72);
            font-size: 14px;
            font-weight: 700;
            text-align: center;
        }
        .preview-content img,
        .preview-content video {
            width: 100%;
            height: auto;
            border-radius: 22px;
            box-shadow: 0 24px 80px rgba(17, 36, 22, .14);
        }
        .preview-content video {
            aspect-ratio: 16 / 9;
            background: var(--deep);
        }
        .preview-content table {
            display: block;
            width: 100%;
            overflow-x: auto;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid rgba(170, 116, 38, .18);
            border-radius: 20px;
            background: white;
        }
        .preview-content th,
        .preview-content td {
            min-width: 160px;
            border-right: 1px solid rgba(170, 116, 38, .14);
            border-bottom: 1px solid rgba(170, 116, 38, .14);
            padding: 14px 16px;
            text-align: left;
        }
        .preview-content th {
            color: #fffaf0;
            background: linear-gradient(145deg, var(--green), var(--deep));
        }
        .preview-content tr:nth-child(even) td {
            background: rgba(253, 240, 163, .16);
        }
        .preview-seo {
            position: sticky;
            top: 24px;
        }
        .preview-seo dl {
            display: grid;
            gap: 16px;
            margin: 20px 0 0;
        }
        .preview-seo dt {
            color: var(--gold-dark);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: .12em;
            text-transform: uppercase;
        }
        .preview-seo dd {
            margin: 6px 0 0;
            color: var(--muted);
            line-height: 1.7;
            overflow-wrap: anywhere;
        }
        @media (max-width: 900px) {
            .shell { grid-template-columns: 1fr; }
            .shell.is-sidebar-collapsed { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
            .shell.is-sidebar-collapsed .sidebar { padding: 20px; }
            .shell.is-sidebar-collapsed .brand-title,
            .shell.is-sidebar-collapsed .brand-sub,
            .shell.is-sidebar-collapsed .nav-label,
            .shell.is-sidebar-collapsed .nav-arrow {
                display: block;
            }
            .shell.is-sidebar-collapsed .sidebar-head {
                align-items: flex-start;
                flex-direction: row;
            }
            .shell.is-sidebar-collapsed .nav a,
            .shell.is-sidebar-collapsed .logout-button {
                justify-content: space-between;
                padding: 13px 15px;
            }
            .main { padding: 20px; }
            .stats, .form-grid { grid-template-columns: 1fr; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .preview-shell,
            .preview-hero {
                grid-template-columns: 1fr;
            }
            .preview-seo { position: static; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/admin-modern.css') }}">
</head>
<body class="{{ $auth ? 'auth-body' : 'admin-body' }}">
    @if ($auth)
        {{ $slot }}
    @else
        <div class="shell" data-admin-shell>
            <aside class="sidebar">
                <div class="sidebar-head">
                    <a class="brand" href="{{ route('admin.dashboard') }}" title="34 Build Master Admin">
                        <span class="brand-mark">34</span>
                        <span class="brand-copy">
                            <span class="brand-title">Build Master</span>
                            <span class="brand-sub">Admin workspace</span>
                        </span>
                    </a>
                    <button class="sidebar-toggle" type="button" data-sidebar-toggle title="ย่อ/ขยายเมนู" aria-label="ย่อ/ขยายเมนู">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"></path></svg>
                    </button>
                </div>
                <nav class="nav">
                    <p class="nav-group-label">เมนู</p>
                    <a class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}" title="แดชบอร์ด">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 13h8V3H3v10z"></path><path d="M13 21h8V11h-8v10z"></path><path d="M13 3v6h8V3h-8z"></path><path d="M3 21h8v-6H3v6z"></path></svg>
                        <span class="nav-label">แดชบอร์ด</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <a class="{{ request()->routeIs('admin.articles.*') ? 'is-active' : '' }}" href="{{ route('admin.articles.index') }}" title="บทความ">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5V5a2 2 0 0 1 2-2h11a3 3 0 0 1 3 3v15H6a2 2 0 0 1-2-1.5z"></path><path d="M8 7h8"></path><path d="M8 11h8"></path><path d="M8 15h5"></path></svg>
                        <span class="nav-label">บทความ</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <a href="{{ route('admin.articles.index', ['status' => 'draft']) }}" title="ฉบับร่าง">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"></path><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"></path></svg>
                        <span class="nav-label">ฉบับร่าง</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <p class="nav-group-label">ทั่วไป</p>
                    <a href="{{ url('/') }}" target="_blank" title="ดูหน้าเว็บ">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z"></path><path d="M3.6 9h16.8"></path><path d="M3.6 15h16.8"></path><path d="M12 3a14 14 0 0 1 0 18"></path><path d="M12 3a14 14 0 0 0 0 18"></path></svg>
                        <span class="nav-label">ดูหน้าเว็บ</span>
                        <span class="nav-arrow">&rsaquo;</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="logout-button" type="submit" title="ออกจากระบบ">
                            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><path d="M16 17l5-5-5-5"></path><path d="M21 12H9"></path></svg>
                            <span class="nav-label">ออกจากระบบ</span>
                            <span class="nav-arrow">&rsaquo;</span>
                        </button>
                    </form>
                </nav>
                <div class="sidebar-bottom">
                    <a class="sidebar-site-card" href="{{ url('/') }}" target="_blank">
                        <strong>เว็บไซต์ 34 Build Master</strong>
                        <span>ตรวจดูเนื้อหาที่เผยแพร่บนหน้าจริง</span>
                        <em>เปิดเว็บไซต์</em>
                    </a>
                </div>
            </aside>
            <main class="main">
                <header class="admin-header">
                    <button class="mobile-menu-button" type="button" data-mobile-menu-toggle aria-label="เปิดเมนู">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"></path><path d="M4 12h16"></path><path d="M4 17h16"></path></svg>
                    </button>
                    <form class="admin-search" method="GET" action="{{ route('admin.articles.index') }}">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                        <input name="q" type="search" value="{{ request('q') }}" placeholder="ค้นหาบทความ..." aria-label="ค้นหาบทความ">
                        <span class="search-hint">Enter</span>
                    </form>
                    <div class="admin-header-actions">
                        <a class="icon-button" href="mailto:34buildmaster@gmail.com" title="อีเมล" aria-label="อีเมล">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3"></rect><path d="m4 7 8 6 8-6"></path></svg>
                        </a>
                        <a class="icon-button" href="{{ route('admin.articles.index', ['status' => 'draft']) }}" title="บทความฉบับร่าง" aria-label="บทความฉบับร่าง">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path><path d="M10 21h4"></path></svg>
                        </a>
                        <div class="user-chip">
                            <span class="user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                            <span class="user-copy">
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ auth()->user()->email }}</span>
                            </span>
                        </div>
                    </div>
                </header>
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
        <button class="sidebar-backdrop" type="button" data-sidebar-backdrop aria-label="ปิดเมนู"></button>
        <script>
            (() => {
                const shell = document.querySelector('[data-admin-shell]');
                const toggle = document.querySelector('[data-sidebar-toggle]');
                const mobileToggle = document.querySelector('[data-mobile-menu-toggle]');
                const backdrop = document.querySelector('[data-sidebar-backdrop]');

                if (!shell) {
                    return;
                }

                const storageKey = '34bm-admin-sidebar-collapsed';
                const applyState = (isCollapsed) => {
                    shell.classList.toggle('is-sidebar-collapsed', isCollapsed);
                    toggle?.setAttribute('aria-expanded', String(!isCollapsed));
                };

                applyState(localStorage.getItem(storageKey) === 'true');

                toggle?.addEventListener('click', () => {
                    const isCollapsed = !shell.classList.contains('is-sidebar-collapsed');
                    applyState(isCollapsed);
                    localStorage.setItem(storageKey, String(isCollapsed));
                });

                const closeMobileMenu = () => document.body.classList.remove('is-mobile-menu-open');
                mobileToggle?.addEventListener('click', () => document.body.classList.toggle('is-mobile-menu-open'));
                backdrop?.addEventListener('click', closeMobileMenu);
                document.querySelectorAll('.sidebar .nav a').forEach((link) => link.addEventListener('click', closeMobileMenu));
                window.addEventListener('resize', () => {
                    if (window.innerWidth > 900) {
                        closeMobileMenu();
                    }
                });
            })();
        </script>
    @endif
</body>
</html>
