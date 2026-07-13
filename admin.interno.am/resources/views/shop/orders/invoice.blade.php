<!doctype html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    <style>
        body {
            margin: 0;
            color: #16223a;
            font-family: dejavusans, sans-serif;
            font-size: 12px;
            line-height: 1.45;
        }

        .header {
            border-bottom: 2px solid #153966;
            padding-bottom: 18px;
        }

        .brand {
            font-size: 30px;
            font-weight: 700;
            letter-spacing: 5px;
        }

        .brand-subtitle {
            color: #657086;
            font-size: 10px;
            letter-spacing: 3px;
            margin-top: 2px;
            text-transform: uppercase;
        }

        .invoice-title {
            color: #153966;
            font-size: 24px;
            font-weight: 700;
            text-align: right;
        }

        .muted {
            color: #667085;
        }

        .top-grid,
        .summary-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .top-grid td,
        .summary-grid td {
            vertical-align: top;
        }

        .box {
            border: 1px solid #d7dfeb;
            border-radius: 8px;
            padding: 14px;
        }

        .box-title {
            color: #153966;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .info-row {
            margin: 4px 0;
        }

        .info-label {
            color: #667085;
        }

        table.items {
            border-collapse: collapse;
            margin-top: 22px;
            width: 100%;
        }

        .items th {
            background: #153966;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 9px 8px;
            text-align: left;
            text-transform: uppercase;
        }

        .items td {
            border-bottom: 1px solid #e3e8f0;
            padding: 10px 8px;
            vertical-align: top;
        }

        .items .number {
            text-align: right;
            white-space: nowrap;
        }

        .product-title {
            font-weight: 700;
        }

        .options {
            color: #48556a;
            font-size: 10px;
            margin-top: 6px;
        }

        .option {
            margin: 2px 0;
        }

        .color-dot {
            border: 1px solid #cbd5e1;
            border-radius: 50%;
            display: inline-block;
            height: 9px;
            margin-right: 4px;
            vertical-align: -1px;
            width: 9px;
        }

        .total-box {
            background: #f4f7fb;
            border: 1px solid #d7dfeb;
            border-radius: 8px;
            margin-top: 16px;
            padding: 14px;
            text-align: right;
        }

        .total-label {
            color: #667085;
            font-size: 11px;
            text-transform: uppercase;
        }

        .total-value {
            color: #153966;
            font-size: 24px;
            font-weight: 700;
            margin-top: 4px;
        }

        .note {
            background: #fff8e6;
            border: 1px solid #f4d58d;
            border-radius: 8px;
            color: #6b4d0f;
            margin-top: 18px;
            padding: 12px 14px;
        }

        .footer {
            border-top: 1px solid #e3e8f0;
            color: #667085;
            font-size: 10px;
            margin-top: 24px;
            padding-top: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
<table class="header" width="100%">
    <tr>
        <td>
            <div class="brand">{{ $company['name'] }}</div>
            <div class="brand-subtitle">{{ $company['subtitle'] }}</div>
        </td>
        <td class="invoice-title">
            {{ $labels['invoice'] }}<br>
            <span class="muted" style="font-size: 12px;">{{ $invoiceNumber }}</span>
        </td>
    </tr>
</table>

<table class="top-grid" style="margin-top: 18px;">
    <tr>
        <td style="width: 49%;">
            <div class="box">
                <div class="box-title">{{ $labels['customer'] }}</div>
                <div class="info-row"><strong>{{ trim(($customer['firstName'] ?? '') . ' ' . ($customer['lastName'] ?? '')) }}</strong></div>
                <div class="info-row"><span class="info-label">{{ $labels['phone'] }}:</span> {{ $customer['phone'] ?? '-' }}</div>
                <div class="info-row"><span class="info-label">{{ $labels['email'] }}:</span> {{ $customer['email'] ?? '-' }}</div>
                <div class="info-row"><span class="info-label">{{ $labels['address'] }}:</span> {{ $customer['address'] ?? '-' }}</div>
                @if(!empty($craftsman))
                    <div class="info-row"><span class="info-label">{{ $labels['craftsman'] }}:</span> {{ collect([$craftsman['code'] ?? null, $craftsman['name'] ?? null])->filter()->implode(' / ') }}</div>
                @endif
            </div>
        </td>
        <td style="width: 2%;"></td>
        <td style="width: 49%;">
            <div class="box">
                <div class="box-title">{{ $labels['order'] }}</div>
                <div class="info-row"><span class="info-label">{{ $labels['orderNumber'] }}:</span> #{{ $order->id }}</div>
                <div class="info-row"><span class="info-label">{{ $labels['invoice'] }}:</span> {{ $invoiceNumber }}</div>
                <div class="info-row"><span class="info-label">{{ $labels['date'] }}:</span> {{ $createdAt }}</div>
                <div class="info-row"><span class="info-label">{{ $labels['phone'] }}:</span> {{ $company['phone'] }}</div>
                <div class="info-row"><span class="info-label">{{ $labels['email'] }}:</span> {{ $company['email'] }}</div>
                <div class="info-row"><span class="info-label">{{ $labels['address'] }}:</span> {{ $company['address'] }}</div>
            </div>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
    <tr>
        <th style="width: 42%;">{{ $labels['product'] }}</th>
        <th style="width: 28%;">{{ $labels['parameters'] }}</th>
        <th class="number" style="width: 8%;">{{ $labels['quantity'] }}</th>
        <th class="number" style="width: 11%;">{{ $labels['unitPrice'] }}</th>
        <th class="number" style="width: 11%;">{{ $labels['total'] }}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($items as $item)
        <tr>
            <td>
                <div class="product-title">{{ $item['title'] }}</div>
                @if(!empty($item['productId']))
                    <div class="muted">ID #{{ $item['productId'] }}</div>
                @endif
                @if(!empty($item['selectedOptionLabel']))
                    <div class="options">{{ $item['selectedOptionLabel'] }}</div>
                @endif
            </td>
            <td>
                @foreach($item['options'] as $option)
                    <div class="option">
                        <span class="muted">{{ $option['label'] }}:</span>
                        @if(!empty($option['color']) && preg_match('/^#[0-9a-fA-F]{3,8}$/', $option['color']))
                            <span class="color-dot" style="background: {{ $option['color'] }}"></span>
                        @endif
                        <strong>{{ $option['value'] }}</strong>
                    </div>
                @endforeach
            </td>
            <td class="number">{{ $item['quantity'] }}</td>
            <td class="number">{{ number_format($item['unitPrice'], 0, '.', ' ') }} ֏</td>
            <td class="number"><strong>{{ number_format($item['lineTotal'], 0, '.', ' ') }} ֏</strong></td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="total-box">
    <div class="total-label">{{ $labels['grandTotal'] }}</div>
    <div class="total-value">{{ number_format($total, 0, '.', ' ') }} ֏</div>
</div>

<div class="note">{{ $deliveryNote }}</div>

<div class="footer">
    {{ $labels['thankYou'] }} · {{ $company['name'] }} · {{ $company['phone'] }}
</div>
</body>
</html>
