@php
    $displayName = $displayName ?? ($company?->name ?? 'QR');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Imprimir QR — {{ $displayName }}</title>
    <style>
        :root { color-scheme: light; }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f4f5f9;
            color: #0f172a;
        }

        .wn-print-toolbar {
            position: sticky;
            top: 0;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            z-index: 10;
        }
        .wn-print-toolbar h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
        }
        .wn-print-toolbar button {
            border: 0;
            background: #004ac6;
            color: #ffffff;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 18px;
            border-radius: 8px;
            cursor: pointer;
        }
        .wn-print-toolbar button:hover { background: #003899; }

        .wn-print-sheet {
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            margin: 24px auto;
            padding: 15mm;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
            display: grid;
            gap: 6mm;
            grid-template-columns: repeat({{ $cols }}, 1fr);
            grid-template-rows: repeat({{ $rows }}, 1fr);
        }

        .wn-print-cell {
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 6mm 4mm 4mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 3mm;
            text-align: center;
        }

        .wn-print-cell img {
            width: 100%;
            max-width: 70mm;
            height: auto;
            image-rendering: pixelated;
        }

        .wn-print-cell__title {
            font-size: {{ $copies >= 12 ? '9pt' : '11pt' }};
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .wn-print-cell__brand {
            font-size: {{ $copies >= 12 ? '7pt' : '9pt' }};
            color: #64748b;
            margin: 0;
        }

        @media print {
            body { background: #ffffff; }
            .wn-print-toolbar { display: none !important; }
            .wn-print-sheet {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
            }
            .wn-print-cell { border: none; }
        }

        @page {
            size: A4;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="wn-print-toolbar">
        <h1>Imprimir QR · {{ $displayName }} · {{ $copies }} {{ $copies === 1 ? 'copia' : 'copias' }} por hoja</h1>
        <button type="button" onclick="window.print();">Imprimir</button>
    </div>

    <section class="wn-print-sheet" aria-label="Hoja de impresión con códigos QR">
        @for ($i = 0; $i < $copies; $i++)
            <div class="wn-print-cell">
                <img src="{{ $pngUrl }}" alt="Código QR de {{ $displayName }}">
                <p class="wn-print-cell__title">{{ $displayName }}</p>
                <p class="wn-print-cell__brand">webnu.es</p>
            </div>
        @endfor
    </section>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    </script>
</body>
</html>
