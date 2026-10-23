<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $invoice->id }} — {{ $orgName }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        p { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 8px 0; border-bottom: 1px solid #ccc; text-align: left; }
        th { font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; color: #555; }
        .right { text-align: right; }
        .muted { color: #555; }
        .total td { border-bottom: 0; font-weight: bold; padding-top: 12px; }
        .meta { margin-top: 16px; }
    </style>
</head>
<body>
    <h1>Invoice #{{ $invoice->id }}</h1>
    <p class="muted">{{ $orgName }} · {{ $invoice->status->label() }}</p>

    <p>
        <strong>{{ $invoice->customer->name }}</strong><br>
        @if ($invoice->customer->address)
            {{ $invoice->customer->address }}<br>
        @endif
        @if ($invoice->customer->email)
            {{ $invoice->customer->email }}
        @endif
    </p>

    <p class="meta">
        Issued {{ $invoice->issued_at?->format('M j, Y') ?? '—' }}
        · Due {{ $invoice->due_at?->format('M j, Y') ?? '—' }}
        @if ($invoice->relationLoaded('job') && $invoice->job)
            · Job {{ $invoice->job->scheduled_at?->format('M j, Y') ?? 'unscheduled' }}
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Qty</th>
                <th class="right">Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ $item->qty }}</td>
                    <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">${{ number_format($item->lineTotal(), 2) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td colspan="3" class="right">Total</td>
                <td class="right">${{ number_format($invoice->total, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($invoice->notes)
        <p class="meta">{{ $invoice->notes }}</p>
    @endif
</body>
</html>
