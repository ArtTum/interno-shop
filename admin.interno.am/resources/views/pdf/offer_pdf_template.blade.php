<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ $translation['offer_page_title'] ?? '{offer_page_title}' }} </title>
    <link href="{{ public_path('/css/pdf.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body class="proforma body">
<table class="table-pdf head container">
    <tbody class="tbody">
    <tr class="tr">
        <td class="header td">
            <img src="{{ public_path('/icon/Logo-.png') }}" class="img" alt="Logo">
        </td>
        <td class="shop-info td"></td>
    </tr>
    </tbody>
</table>
<div class="document-type-label">
    <h1 class="h1">
        {{ $translation['offer_page_title'] ?? '{offer_page_title}' }} {{ $offer->id }}
    </h1>
</div>
<table class="order-data-addresses table-pdf">
    <tbody class="tbody">
    <tr class="tr">
        <td class="address billing-address td"></td>
        <td class="order-data td">
            <table class="order-info table-pdf">
                <tbody class="tbody">
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_offer_number'] ?? '{pdf_offer_number}' }}:</th>
                    <td class="td">{{ $offer->id }}</td>
                </tr>
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_offer_date'] ?? '{pdf_offer_date}' }}:</th>
                    <td class="td">
                        {{ \Carbon\Carbon::parse($offer->created_at)->format('d.m.Y') }}
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<br>
@php
    $netAmount = 0;
@endphp
<table class="order-details table-pdf">
    <thead class="thead">
    <tr class="tr">
        <th class="th">{{ $translation['pdf_product'] ?? '{pdf_product}' }}</th>
        <th class="th" align="right">{{ $translation['pdf_quantity'] ?? '{pdf_quantity}' }}</th>
        <th class="th" align="right">{{ $translation['pdf_net_weight'] ?? '{pdf_net_weight}' }}</th>
        <th class="th" align="right">{{ $translation['pdf_gross_weight'] ?? '{pdf_gross_weight}' }}</th>
        <th class="th" align="right">{{ $translation['pdf_price'] ?? '{pdf_price}' }}</th>
    </tr>
    </thead>
    <tbody class="tbody">
    @foreach($products as $item)
        <tr class="tr product-item my-border">
            <td class="td ">
                <div class="item-name">{{ $item['name'] }}</div>
            </td>
            <td class="td" align="right">{{ $item['quantity'] }}</td>
            <td class="td " align="right">{{ $item['net_weight'] }}</td>
            <td class="td " align="right">{{ $item['gross_weight'] }}</td>
            <td class="td " align="right">
                {{ $item['price'] }} <strong> {{ $offer->currency->code }}</strong>
            </td>
        </tr>
        @php
            $netAmount += $item['price'];
        @endphp
    @endforeach
    </tbody>
</table>
<table width="100%">
    <tr>
        <td style="width: 40%"></td>
        <td>
            <table class="totals table-pdf order-details">
                <tfoot class="tfoot">
                    <tr class="tr my-border">
                        <th class="th description">{{ $translation['pdf_net_amount'] ?? '{pdf_net_amount}' }}</th>
                        <td align="right" class="td price">{{ $netAmount }} {{ $offer->currency->code }}</td>
                    </tr>
                    @if($offer->shipping_cost)
                    <tr class="tr my-border">
                        <th class="th description">{{ $translation['pdf_shipping'] ?? '{pdf_shipping}' }}</th>
                        <td align="right" class="td price">{{ $offer->shipping_cost }} {{ $offer->currency->code }}</td>
                    </tr>
                    @endif
                    <tr class="tr my-border">
                        <th class="th description">{{ $translation['pdf_total_net_amount'] ?? '{pdf_total_net_amount}' }}</th>
                        <td align="right" class="td price">
                            {{ $offer->shipping_cost ? ($offer->shipping_cost + $netAmount) : $netAmount }} {{ $offer->currency->code }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </td>
    </tr>
</table>

</body>
</html>
