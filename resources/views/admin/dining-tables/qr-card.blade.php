<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Card — {{ $table->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@500;700&family=Fraunces:opsz,wght@9..144,600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #1c1917;
            --muted: #78716c;
            --paper: #fffdf8;
            --accent: #b45309;
            --rule: #e7e5e4;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            background: #f5f5f4;
            color: var(--ink);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
        }
        .toolbar {
            width: 100%;
            max-width: 420px;
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .toolbar a, .toolbar button {
            appearance: none;
            border: 1px solid var(--rule);
            background: #fff;
            color: var(--ink);
            border-radius: 0.5rem;
            padding: 0.55rem 0.9rem;
            font: inherit;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
        }
        .toolbar .primary { background: var(--ink); color: #fff; border-color: var(--ink); }
        .card {
            width: 100%;
            max-width: 420px;
            background: var(--paper);
            border: 1px solid var(--rule);
            border-radius: 1.25rem;
            padding: 2rem 1.75rem 1.75rem;
            text-align: center;
            box-shadow: 0 18px 40px rgba(28, 25, 23, 0.08);
        }
        .eyebrow {
            font-size: 0.7rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--accent);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        h1 {
            font-family: 'Fraunces', serif;
            font-size: 2rem;
            margin: 0 0 0.35rem;
            font-weight: 600;
        }
        .subtitle {
            color: var(--muted);
            font-size: 0.95rem;
            margin: 0 0 1.5rem;
        }
        .qr-wrap {
            display: inline-flex;
            padding: 1rem;
            background: #fff;
            border: 1px solid var(--rule);
            border-radius: 1rem;
            margin-bottom: 1.25rem;
        }
        .qr-wrap svg { display: block; width: 240px; height: 240px; }
        .hint {
            font-size: 0.9rem;
            color: var(--muted);
            margin: 0 0 1rem;
            line-height: 1.45;
        }
        .url {
            font-size: 0.7rem;
            word-break: break-all;
            color: #a8a29e;
            line-height: 1.4;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none !important; }
            .card {
                box-shadow: none;
                border: 1px solid #d6d3d1;
                max-width: none;
                width: 100%;
                min-height: 100vh;
                border-radius: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('admin.diningTables.index') }}">← Back</a>
        <button type="button" class="primary" onclick="window.print()">Print / Save PDF</button>
        <a href="{{ route('admin.diningTables.qrSvg', $table) }}">Download SVG</a>
    </div>

    <article class="card">
        <div class="eyebrow">Scan to order</div>
        <h1>{{ $table->name }}</h1>
        <p class="subtitle">Digital menu · live kitchen tracking</p>
        <div class="qr-wrap">{!! $qrSvg !!}</div>
        <p class="hint">Point your camera at the code to open the menu for this table.</p>
        <div class="url">{{ $menuUrl }}</div>
    </article>
</body>
</html>

