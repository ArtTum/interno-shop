<style>
    body {
        background: #fff;
        color: #000;
        margin: 0;
        font-family: 'Open Sans', sans-serif;
        font-size: 9pt;
        line-height: 100%
    }

    h1, h2, h3, h4 {
        font-weight: bold;
        margin: 0
    }

    h1 {
        font-size: 16pt;
        margin: 5mm 0
    }

    h2 {
        font-size: 14pt
    }

    h3, h4 {
        font-size: 12pt
    }

    ol, ul {
        list-style: none;
        margin: 0;
        padding: 0
    }

    li, ul {
        margin-bottom: .75em
    }

    p {
        margin: 0;
        padding: 0;
    }

    p + p {
        margin-top: 1.25em
    }

    a {
        border-bottom: 1px solid;
        text-decoration: none
    }

    table {
        border-collapse: collapse;
        border-spacing: 0;
        page-break-inside: always;
        border: 0;
        margin: 0;
        padding: 0
    }

    th, td {
        vertical-align: top;
        text-align: left
    }

    .text-right {
        text-align: right;
    }

    tr.no-borders, td.no-borders {
        border: 0 !important;
        border-top: 0 !important;
        border-bottom: 0 !important;
        padding: 0 !important;
        width: auto
    }

    td.shop-info {
        width: 40%
    }

    .document-type-label {
        margin-bottom: 10px;
        padding-top: 40px;
    }

    .document-type-label h1 {
        margin: 0;
    }

    table.order-data-addresses {
        width: 100%;
        margin: 10mm 0
    }

    .billing-address {
        width: 70%;
    }

    td.order-data table th {
        font-weight: 400;
    }

    table.order-details {
        width: 100%;
        margin-bottom: 8mm
    }

    .summary-table {
        width: 100%;
        margin-bottom: 8mm
    }

    .price {
        width: 20%;
        text-align: right;
    }

    .order-details tr {
        page-break-inside: always;
        page-break-after: auto
    }

    .order-details td, .order-details th {
        border-bottom: 1px #ccc solid;
        border-top: 1px #ccc solid;
        padding: .375em
    }

    .order-details th {
        font-weight: 700;
        text-align: left
    }

    .order-details thead th {
        color: #fff;
        background-color: #333;
        border-color: #333
    }

    .order-details.summary-table thead th {
        background-color: transparent;
    }

    dl {
        margin: 4px 0
    }

    dt, dd, dd p {
        display: inline;
        font-size: 7pt;
        line-height: 7pt
    }

    dd {
        margin-left: 5px
    }

    dd:after {
        content: "\A";
        white-space: pre
    }

    .w-50 {
        width: 50% !important;
    }

    .mt-5 {
        margin-top: 50mm
    }

    .vertical-bottom {
        vertical-align: bottom
    }

    table.totals {
        width: 100%;
        margin-top: 5mm
    }

    table.totals th, table.totals td {
        border: 0;
        border-top: 1px solid #ccc;
        border-bottom: 1px solid #ccc
    }

    table.totals th.description, table.totals td.price {
        width: 50%
    }

    table.totals tr.order_total td, table.totals tr.order_total th {
        border-top: 2px solid #000;
        border-bottom: 2px solid #000;
        font-weight: 700
    }

    .pagenum:before {
        content: counter(page)
    }

    .pagenum, .pagecount {
        font-family: sans-serif
    }

    #footer {
        text-align: center;
    }
</style>

<div class="document-type-label">
    <h1>{{$translation['offer_doc_title'] . ' ' . $data['id']}}</h1>
</div>

<table class="order-data-addresses">
    <tr>
        <td class="address billing-address">
            <p><strong>{{$translation['offer_full_name']}}</strong>: {{$data['full_name']}}</p>
            <p><strong>{{$translation['offer_country']}}</strong>: {{$data['country']}}</p>
            <p><strong>{{$translation['offer_zip_code']}}</strong>: {{$data['zip_code']}}</p>
            <p><strong>{{$translation['offer_email']}}</strong>: {{$data['email']}}</p>
            <p><strong>{{$translation['offer_phone']}}</strong>: {{$data['phone']}}</p>
            <p><strong>{{$translation['offer_start_date']}}</strong>: {{$data['start_date']}}</p>
            <p><strong>{{$translation['offer_project']}}</strong>: {{$data['project_label']}}</p>
        </td>
        <td class="text-right">
            {{date('d.m.Y', strtotime($data['created_at']))}}
        </td>
    </tr>
</table>

<table class="order-details">
    <thead>
    <tr>
        <th class="product">
            <strong>{{$translation['offer_product']}}</strong>
        </th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td class="product">
            <p><strong>{{$translation['offer_product_label']}}</strong></p>
            <p><strong>{{$translation['offer_area_size']}}</strong>: {{$data['area_size']}}</p>
        </td>
        <td class="text-right vertical-bottom">
            <strong>{{$data['product_price']}}</strong>
        </td>
    </tr>
    <tr>
        <td class="product">
            <p><strong>{{$translation['offer_service_label']}}</strong></p>
            <p><strong>{{$translation['offer_area_size']}}</strong>: {{$data['area_size']}}</p>
        </td>
        <td class="text-right vertical-bottom">
            <strong>{{$data['service_price']}}</strong>
        </td>
    </tr>
    </tbody>
</table>
<hr>
<table class="summary-table order-details">
    <thead>
    <tr>
        <th class="w-50 no-bg"></th>
        <th class="w-50 no-bg"></th>
    </tr>
    </thead>
    <tbody>
    <tr class="no-borders">
        <td class="no-borders"></td>
        <td class="no-borders">
            <table class="totals">
                <tfoot>
                <tr>
                    <td class="no-borders"></td>
                    <th class="no-borders description">{{$translation['offer_net_amount']}}</th>
                    <td class="no-borders price">{{$data['project_price']}}</td>
                </tr>
                <tr>
                    <td class="no-borders"></td>
                    <th class="description">{{$translation['offer_shipping']}}</th>
                    <td class="price">{{$data['shipping_price']}}</td>
                </tr>
                <tr>
                    <td class="no-borders"></td>
                    <th class="description">{{$translation['offer_total_net_amount']}}</th>
                    <td class="price">{{$data['total_net_amount']}}</td>
                </tr>
                <tr>
                    <td class="no-borders"></td>
                    <th class="description">{{$translation['offer_vat'] . ' ' . $settings['vat_rate']}}%</th>
                    <td class="price">{{$data['vat_amount']}}</td>
                </tr>
                <tr>
                    <td class="no-borders"></td>
                    <th class="description">{{$translation['offer_total']}}</th>
                    <td class="price">{{$data['total_amount']}}</td>
                </tr>
                </tfoot>
            </table>
        </td>
    </tr>
    </tbody>
</table>

<div class="mt-5">{!! $settings['validity_text'] !!}</div>
