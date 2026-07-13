<!doctype html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0;background:#f4f7fb;color:#16223a;font-family:Arial,sans-serif;">
<div style="max-width:640px;margin:0 auto;padding:28px 16px;">
    <div style="background:#ffffff;border:1px solid #d7dfeb;border-radius:14px;padding:26px;">
        <div style="font-size:24px;font-weight:700;letter-spacing:4px;color:#111827;">{{ $company['name'] }}</div>
        <div style="margin-top:4px;color:#667085;font-size:12px;letter-spacing:2px;text-transform:uppercase;">{{ $company['subtitle'] }}</div>

        <h1 style="margin:26px 0 10px;color:#153966;font-size:22px;">{{ $labels['thankYou'] }}</h1>
        <p style="margin:0 0 18px;color:#48556a;font-size:14px;line-height:1.6;">{{ $labels['emailIntro'] }}</p>

        <div style="background:#f4f7fb;border-radius:10px;padding:16px;margin:18px 0;">
            <div style="font-size:13px;color:#667085;">{{ $labels['orderNumber'] }}</div>
            <div style="font-size:20px;font-weight:700;color:#153966;">#{{ $order->id }}</div>
            <div style="height:12px;"></div>
            <div style="font-size:13px;color:#667085;">{{ $labels['invoice'] }}</div>
            <div style="font-size:20px;font-weight:700;color:#153966;">{{ $invoiceNumber }}</div>
            <div style="margin-top:8px;font-size:13px;color:#667085;">{{ $labels['grandTotal'] }}</div>
            <div style="font-size:22px;font-weight:700;color:#153966;">{{ number_format($total, 0, '.', ' ') }} ֏</div>
        </div>

        <p style="margin:0;color:#6b4d0f;background:#fff8e6;border:1px solid #f4d58d;border-radius:10px;padding:12px 14px;font-size:13px;line-height:1.5;">
            {{ $deliveryNote }}
        </p>
    </div>
</div>
</body>
</html>
