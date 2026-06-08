<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ $translation['eadt'] ?? '{eadt}' }}</title>
    <link href="{{ public_path('/css/pdf.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        @page {
            margin: 1cm;
        }
    </style>
</head>
<body class="body">
<table class="table-pdf head container">
    <tbody class="tbody">
    <tr class="tr">
        <td class="header td">
            <img src="{{ public_path($general->media->path ?? '') }}" class="img"
                 alt="{{ $general->global_document_setting_translation->name ?? ''}}">
        </td>
        <td class="shop-info td">
            <h3 class="h3">{{ $general->global_document_setting_translation->name ?? ''}}</h3>
            <div>
                {!! $general->global_document_setting_translation->address ?? '' !!}
                <p>{{ $general->global_document_setting_translation->email ?? ''}} |
                    {{ $translation['pdf_tel'] ?? '{pdf_tel}' }}
                    : {{ $general->global_document_setting_translation->phone ?? ''}}</p>
            </div>
        </td>
    </tr>
    </tbody>
</table>
<div class="document-type-label">
    <h1 class="h1">{{ $translation['eadt'] ?? '{eadt}' }}</h1>
</div>
<table class="order-data-addresses table-pdf">
    <tbody class="tbody">
    <tr class="tr">
        <td class="address billing-address td">
            <p class="p">
                <strong>{{ $general->global_document_setting_translation->name ?? ''}}</strong><br>
                {!! $general->global_document_setting_translation->seller_info ?? '' !!}
            </p>
            <hr>
            <div>
                <strong>{{ $translation['front_shipping_address'] ?? '{front_shipping_address}' }}</strong><br>
                @if( $order->order_shipping_address->company )
                    {{ $order->order_shipping_address->company }} <br>
                @endif
                {{ $order->order_shipping_address->name }} {{ $order->order_shipping_address->last_name }} <br>
                {{ $order->order_shipping_address->address }} {{ $order->order_shipping_address->address_2 }} <br>
                {{ $order->order_shipping_address->zip  }}  {{ $order->order_shipping_address->city}} {{ $order->order_shipping_address->state }}
                <br>
                {{ $shippingCountry->name }} <br>
            </div>
        </td>
        <td class="order-data td">
            <table class="order-info table-pdf">
                <tbody class="tbody">
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_invoice_number'] ?? '{pdf_invoice_number}' }}:</th>
                    <td class="td">{{ $invoice['generate_number'] ?? '' }}</td>
                </tr>
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_invoice_date'] ?? '{pdf_invoice_date}' }}:</th>
                    <td class="td">{{ $invoice['updated_at'] ?? '' }}</td>
                </tr>
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_order_number'] ?? '{pdf_order_number}' }}:</th>
                    <td class="td">{{ $order->id }}</td>
                </tr>
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_order_date'] ?? '{pdf_order_date}' }}:</th>
                    <td class="td">{{ \Carbon\Carbon::parse($order->created_at)->format('d.m.Y') }}</td>
                </tr>

                @if(!empty($order->order_billing_address->vat_number))
                    <tr class="tr">
                        <th class="th">{{ $translation['front_vat_id'] ?? '{front_vat_id}' }}:</th>
                        <td class="td">
                            <strong>{{ $order->order_billing_address->vat_number }}</strong>
                        </td>
                    </tr>
                @endif

                @if(!empty($order->order_billing_address->extra_vat_number))
                    @if(($order->order_billing_address->country_code === 'PL') || ($order->order_billing_address->country_code === 'ES'))
                        <tr class="tr">
                            <th class="th">{{ $translation['front_extra_vat_id'] ?? '{front_extra_vat_id}' }}:</th>
                            <td class="td">
                                <strong>{{ $order->order_billing_address->extra_vat_number }}</strong>
                            </td>
                        </tr>
                    @endif
                @endif
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<br>
<div style="text-align: right; margin-bottom: 10px;">
    <strong>{{ $translation['ttkg'] ?? '{ttkg}' }}</strong> {{$gross_weight_total}}{{$weight_unit}}
</div>
@if(!empty($data['count']))
    <div style="text-align: right; margin-bottom: 10px;">
        <strong>{{ $translation['nop'] ?? '{nop}' }}</strong> {{$data['count']}}
    </div>
@endif
<table class="order-details table-pdf all-borders">
    <thead class="thead">
    <tr class="tr">
        <th class="th" colspan="3">{{ $translation['pdf_product'] ?? '{pdf_product}' }}</th>
        <th class="th">{{ $translation['pdf_quantity'] ?? '{pdf_quantity}' }}</th>
        <th class="th nowrap">{{ $translation['pdf_net_weight'] ?? '{pdf_net_weight}' }}</th>
        <th class="th nowrap">{{ $translation['pdf_gross_weight'] ?? '{pdf_gross_weight}' }}</th>
    </tr>
    </thead>
    <tbody class="tbody">
    @php
        $un_numbers_arr = [];
        $un_delivery_description_arr = [];
        $package_group_arr = [];
    @endphp
    @foreach($order->order_items as $item)
        @foreach($item->order_item_parents as $itemParent)
            @php
                $item_meta_data = [];

                if(!empty($itemParent->un_numbers)){
                    $item_meta_data[] = $itemParent->un_numbers;
                    $un_numbers_arr[] = $itemParent->un_numbers;
                }
                if(!empty($itemParent->proper_shipping_name)){
                    $item_meta_data[] = $itemParent->proper_shipping_name;
                    $un_delivery_description_arr[] = $itemParent->proper_shipping_name;
                }
                if(!empty($itemParent->dangerous_goods_class)){
                    $item_meta_data[] = $itemParent->dangerous_goods_class;
                }
                if(!empty($itemParent->package_group)){
                    $item_meta_data[] = $itemParent->package_group;
                    $package_group_arr[] = $itemParent->package_group;
                }
            @endphp
            <tr class="tr my-border">
                <td class="td product" colspan="3">
                    <div>{{ $itemParent->name }}</div>
                    <strong>{{ $translation['pdf_sku'] ?? '{pdf_sku}' }}: </strong>{{ $itemParent->sku }}<br>
                    <div>{{implode(', ', $item_meta_data)}}</div>
                    <strong>{{ $translation['hsc'] ?? '{hsc}' }}: </strong>{{ $itemParent->hs_code }}<br>
                    <strong>{{ $translation['wgt'] ?? '{wgt}' }}: </strong>{{ $itemParent->net_weight }}{{$weight_unit}}
                </td>
                <td class="td quantity">{{ $itemParent->quantity }}</td>
                <td class="td">{{ $itemParent->net_weight * $itemParent->quantity }}{{$weight_unit}}</td>
                <td class="td">{{ $itemParent->gross_weight * $itemParent->quantity }}{{$weight_unit}}</td>
            </tr>
        @endforeach
        @if(!empty($item->extra_products->count()))
            @foreach($item->extra_products as $extraProduct)
                @foreach($extraProduct->order_item_parents as $extraParent)
                    @php
                        $extra_item_meta_data = [];

                        if(!empty($extraParent->un_numbers)){
                            $extra_item_meta_data[] = $extraParent->un_numbers;
                            $un_numbers_arr[] = $extraParent->un_numbers;
                        }
                        if(!empty($extraParent->proper_shipping_name)){
                            $extra_item_meta_data[] = $extraParent->proper_shipping_name;
                            $un_delivery_description_arr[] = $extraParent->proper_shipping_name;
                        }
                        if(!empty($extraParent->dangerous_goods_class)){
                            $extra_item_meta_data[] = $extraParent->dangerous_goods_class;
                        }
                        if(!empty($extraParent->package_group)){
                            $extra_item_meta_data[] = $extraParent->package_group;
                            $package_group_arr[] = $extraParent->package_group;
                        }
                    @endphp
                    <tr class="tr my-border">
                        <td class="td product" colspan="3">
                            <div>{{ $extraParent->name }}</div>
                            <strong>{{ $translation['pdf_sku'] ?? '{pdf_sku}' }}: </strong>{{ $extraParent->sku }}<br>
                            <div>{{implode(', ', $extra_item_meta_data)}}</div>
                            <strong>{{ $translation['hsc'] ?? '{hsc}' }}: </strong>{{ $extraParent->hs_code }}<br>
                            <strong>{{ $translation['wgt'] ?? '{wgt}' }}
                                : </strong>{{ $extraParent->net_weight }}{{$weight_unit}}
                        </td>
                        <td class="td quantity">{{ $extraParent->quantity }}</td>
                        <td class="td">{{ $extraParent->net_weight * $extraParent->quantity }}{{$weight_unit}}</td>
                        <td class="td">{{ $extraParent->gross_weight * $extraParent->quantity }}{{$weight_unit}}</td>
                    </tr>
                @endforeach
            @endforeach
        @endif
    @endforeach

    @if ($un_numbers_arr || $un_delivery_description_arr || $package_group_arr)
        <tr class="grey">
            <td class="td">{{ $translation['unn'] ?? '{unn}' }}</td>
            <td class="td" colspan="2">{{ $translation['shd'] ?? '{shd}' }}</td>
            <td class="td">{{ $translation['pgr'] ?? '{pgr}' }}</td>
            <td class="td">{{ $translation['pdf_net_weight'] ?? '{pdf_net_weight}' }}</td>
            <td class="td">{{ $translation['pdf_gross_weight'] ?? '{pdf_gross_weight}' }}</td>
        </tr>
        <tr>
            <td class="td">{!! implode( '<br>', array_unique( $un_numbers_arr ) ) !!}</td>
            <td class="td" colspan="2">{!! implode( '<br>', array_unique( $un_delivery_description_arr ) ) !!}</td>
            <td class="td">{!! implode( '<br>', array_unique( $package_group_arr ) ) !!}</td>
            <td class="td"></td>
            <td class="td"></td>
        </tr>
    @endif
    </tbody>
</table>
<table class="order-details table-pdf all-borders">
    <tbody>
    <tr class="grey">
        <td class="td">{{ $translation['nop'] ?? '{nop}' }}</td>
        <td class="td">{{ $translation['top'] ?? '{top}' }}</td>
        <td class="td">{{ $translation['lth'] ?? '{lth}' }}</td>
        <td class="td">{{ $translation['wth'] ?? '{wth}' }}</td>
        <td class="td">{{ $translation['hgt'] ?? '{hgt}' }}</td>
        <td class="td">{{ $translation['pdf_gross_weight'] ?? '{pdf_gross_weight}' }}</td>
    </tr>
    <tr class="tr my-border">
        <td class="td">{{$data['count']}}</td>
        <td class="td">{{$data['type']}}</td>
        <td class="td">{{$data['length']}}</td>
        <td class="td">{{$data['width']}}</td>
        <td class="td">{{$data['height']}}</td>
        <td class="td">{{$gross_weight_total}}{{$weight_unit}}</td>
    </tr>
    </tbody>
</table>

<div id="footer">
    @if(!empty($langFooterText))
        @if($baseLanguageCode)
            {!! $langFooterText['footer_text_base'] !!}
        @else
            {!! $langFooterText['footer_text'] !!}
        @endif
    @else
        {!! $general->global_document_setting_translation->footer_text ?? '' !!}
    @endif
</div>
</body>
</html>
