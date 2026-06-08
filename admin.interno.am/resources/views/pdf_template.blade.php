<html lang="en-US">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ $individual->document_setting_translation->document_title ?? '' }}</title>
    <link href="{{ public_path('/css/pdf.css') }}" rel="stylesheet" type="text/css"/>
</head>
<body class="proforma body">
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
                    {{ $translation['pdf_tel'] ?? '{pdf_tel}' }}: {{ $general->global_document_setting_translation->phone ?? ''}}</p>
            </div>
        </td>
    </tr>
    </tbody>
</table>
<div class="document-type-label">
    @if((!empty($incotermText) && $data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_INVOICE) ||
        (!empty($incotermText) && $data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PROFORMA)
    )
        <p class="p accent-red">{{ $incotermText }}</p>
    @endif

    <h1 class="h1">
        {{ $individual->document_setting_translation->document_title ?? '' }}
        {{ $data['document_setting_id'] == 1 && $data['generate_number'] ? '- ' . $data['generate_number'] : '' }}
        {{ $data['document_setting_id'] == 3 && $data['document_number'] ? '- ' . $data['document_number'] : '' }}
    </h1>
    <p class="p">{{ $individual->document_setting_translation->text_below_title ?? '' }}</p>
        @if(($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_INVOICE) ||
        ($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PROFORMA)
    )
            @if($order->payment_method_child === 'PayPal')
                <p class="p accent-red">
                    {!! $translation['pdf_invoice_paypal_notice'] ?? '{pdf_invoice_paypal_notice}' !!}
                </p>
            @endif
            @if($order->payment_method_child === 'Klarna')
                <p class="p accent-red">
                    {!! $translation['pdf_invoice_klarna_notice'] ?? '{pdf_invoice_klarna_notice}' !!}
                </p>
            @endif
        @endif
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
                @if($differentAddresses)
                    <strong>{{ $translation['front_billing_address'] ?? '{front_billing_address}' }}</strong><br>
                    @if( $order->order_billing_address->company )
                        {{ $order->order_billing_address->company }} <br>
                    @endif
                    {{ $order->order_billing_address->name }} {{ $order->order_billing_address->last_name }} <br>
                    {{ $order->order_billing_address->address }} {{ $order->order_billing_address->address_2 }} <br>
                    {{ $order->order_billing_address->zip  }}  {{ $order->order_billing_address->city}} {{ $order->order_billing_address->state }}
                    <br>
                    {{ $billingCountry->name }} <br>
                    <strong>{{ $translation['front_shipping_address'] ?? '{front_shipping_address}' }}</strong><br>
                    @if( $order->order_shipping_address->company )
                        {{ $order->order_shipping_address->company }} <br>
                    @endif
                    {{ $order->order_shipping_address->name }} {{ $order->order_shipping_address->last_name }} <br>
                    {{ $order->order_shipping_address->address }} {{ $order->order_shipping_address->address_2 }} <br>
                    {{ $order->order_shipping_address->zip  }}  {{ $order->order_shipping_address->city}} {{ $order->order_shipping_address->state }}
                    <br>
                    {{ $shippingCountry->name }} <br>
                @else
                    @if( $order->order_billing_address->company )
                        {{ $order->order_billing_address->company }} <br>
                    @endif
                    {{ $order->order_billing_address->name }} {{ $order->order_billing_address->last_name }} <br>
                    {{ $order->order_billing_address->address }} {{ $order->order_billing_address->address_2 }} <br>
                    {{ $order->order_billing_address->zip  }}  {{ $order->order_billing_address->city}} {{ $order->order_billing_address->state }}
                    <br>
                    {{ $billingCountry->name }} <br>
                @endif
            </div>
        </td>
        <td class="order-data td">
            <table class="order-info table-pdf">
                <tbody class="tbody">
                @if(!empty($order->order_refund->order_refund_history) && $data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_CREDIT_NOTE))
                    <tr class="tr">
                        <th class="th">{{ $translation['pdf_credit_note_number'] ?? '{pdf_credit_note_number}' }}:</th>
                        <td class="td">{{ $data['generate_number'] }}</td>
                    </tr>
                    @if($individual->display_credit_note_date)
                        <tr class="tr">
                            <th class="th">{{ $translation['pdf_credit_note_date'] ?? '{pdf_credit_note_date}' }}:</th>
                            <td class="td">{{ \Carbon\Carbon::parse($data['updated_at'])->format('d.m.Y') }}</td>
                        </tr>
                    @endif
                @endif
                @if(($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_CREDIT_NOTE) && !empty($order->order_refund->order_refund_history))
                    <tr class="tr">
                        <th class="th">{{ $translation['pdf_refund_date'] ?? '{pdf_refund_date}' }}:</th>
                        <td class="td">{{ \Carbon\Carbon::parse($order->order_refund->order_refund_history->updated_at ?? '')->format('d.m.Y') }}  </td>
                    </tr>
                @endif

                @if(($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_INVOICE))
                    <tr class="tr">
                        <th class="th">{{ $translation['pdf_invoice_number'] ?? '{pdf_invoice_number}' }}: </th>
                        <td class="td">{{ $data['generate_number'] }}</td>
                    </tr>
                @endif
                @if(($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_INVOICE))
                    <tr class="tr">
                        <th class="th">{{ $translation['pdf_invoice_date'] ?? '{pdf_invoice_date}' }}: </th>
                        <td class="td">{{ \Carbon\Carbon::parse($data['updated_at'])->format('d.m.Y') }}</td>
                    </tr>
                @endif
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_order_number'] ?? '{pdf_order_number}' }}:</th>
                    <td class="td">{{ $order->id }}</td>
                </tr>
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_order_date'] ?? '{pdf_order_date}' }}:</th>
                    <td class="td">{{ \Carbon\Carbon::parse($order->created_at)->format('d.m.Y') }}</td>
                </tr>
                @if(($data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP) && $individual->show_original_invoice_number))
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_original_invoice_number'] ?? '{pdf_original_invoice_number}' }}: </th>
                    <td class="td">{{ $originalInvoiceNumber }}</td>
                </tr>

                <tr class="tr">
                    <th class="th">{{ $translation['pdf_date_of_original'] ?? '{pdf_date_of_original}' }}: </th>
                    <td class="td">{{ \Carbon\Carbon::parse($order->created_at)->format('d.m.Y') }}</td>
                </tr>
                @endif
                @if(($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_INVOICE) || ($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP))
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_shipping_method'] ?? '{pdf_shipping_method}' }}: </th>
                    <td class="td">
                        @if($baseLanguageCode)
                            {{ $order->shipping_method_name_base }}
                        @else
                            {{ $order->shipping_method_name }}
                        @endif
                    </td>
                </tr>
                @endif
                @if($individual->display_phone_number)
                    <tr class="tr">
                        <th class="th">{{ $translation['front_phone'] ?? '{front_phone}' }}: </th>
                        <td class="td">
                            {{ $order->order_billing_address->phone }} <br>
                        </td>
                    </tr>
                @endif
                @if($individual->display_email_address)
                    <tr class="tr">
                        <th class="th">{{ $translation['front_email'] ?? '{front_email}' }}: </th>
                        <td class="td">
                            {{ $order->order_billing_address->email }} <br>
                        </td>
                    </tr>
                @endif
                @if(($data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP))
                <tr class="tr">
                    <th class="th">{{ $translation['pdf_payment_method'] ?? '{pdf_payment_method}' }}: </th>
                    @if($baseLanguageCode)
                        <td class="td">
                            {{ $order->payment_method_child_base ? $order->payment_method_child : '' }}
                        </td>
                    @else
                        <td class="td">
                            {{ $order->payment_method_child ? $order->payment_method_child : '' }}
                            @if($order->cashback_amount > 0 && $order->cashback_amount < $order->total_price)
                                , {{ $translation['pmc'] ?? '{pmc}' }}
                            @endif
                        </td>
                    @endif
                </tr>
                @endif
                @if((!empty($order->order_billing_address->vat_number) && $data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP))
                    <tr class="tr">
                        <th class="th">{{ $translation['front_vat_id'] ?? '{front_vat_id}' }}: </th>
                        <td class="td">
                            <strong>{{ $order->order_billing_address->vat_number }}</strong>
                        </td>
                    </tr>
                @endif

                @if((!empty($order->order_billing_address->extra_vat_number) && $data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP))
                  @if(($order->order_billing_address->country_code === 'PL') || ($order->order_billing_address->country_code === 'ES'))
                      <tr class="tr">
                          <th class="th">{{ $translation['front_extra_vat_id'] ?? '{front_extra_vat_id}' }}: </th>
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
<table class="order-details table-pdf">
    <thead class="thead">
    <tr class="tr">
        <th class="th">{{ $translation['pdf_product'] ?? '{pdf_product}' }}</th>
        <th class="th" align="right">{{ $translation['pdf_quantity'] ?? '{pdf_quantity}' }}</th>
        @if(($data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP))
        <th class="th" align="right">{{ $translation['pdf_price'] ?? '{pdf_price}' }}</th>
        @endif
    </tr>
    </thead>
    <tbody class="tbody">

        @if(($data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_CREDIT_NOTE ))
            @foreach($order->order_items as $item)
                @if(($data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP))
                    <tr class="tr product-item my-border">
                        <td class="td product">
                            <div class="item-name">{{ $item->name }}</div>
                            <div class="item-meta">
                                @if($item->attributes)
                                    <ol class="wc-item-meta">
                                        @foreach(explode('<br>', $item->attributes) as $k => $attributeItem)
                                            @if($k === 0)
                                                @foreach(explode('~', $attributeItem) as $attribute)
                                                    @php
                                                        $attributeValue =  explode(':', $attribute);
                                                    @endphp
                                                    <li>
                                                        <strong>
                                                            {{ $attributeValue[0] ?? '' }} :
                                                        </strong>
                                                        {{ $attributeValue[1] ?? '' }}
                                                    </li>
                                                @endforeach
                                            @else
                                                {!! str_replace('~', ',', $attributeItem) !!} <br>
                                            @endif
                                        @endforeach
                                    </ol>
                                @endif
                            </div>
                            <br>
                            <div class="sku">
                                {{ $translation['pdf_sku'] ?? '{pdf_sku}' }}: {{ $item->sku }}
                            </div>
                        </td>
                        <td class="td quantity" align="right">{{ $item->quantity }}</td>
                        <td class="td price" align="right">
                            {{ $item->price }} <strong> {{ $order->order_currency }}</strong>
                            @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                <br>({{ ( number_format($item->price / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                            @endif
                        </td>
                    </tr>
                @else
                    <tr class="tr product-item my-border">
                        <td class="td product"  style="background: #ececec; padding:6px 12px">
                            @foreach($item->order_item_parents as $temParents)
                                -{{ $temParents->quantity / $item->quantity }}x {{ $temParents->name }}<br>
                            @endforeach
                        </td>
                        <td class="td quantity" style="color: {{ $item->quantity > 1 ? 'red' : 'bleck'}} " align="right">{{ $item->quantity }}</td>
                    </tr>
                    @if(!empty($item->extra_products->count()))
                    <tr class="tr product-item my-border">
                        <td class="td product"  style="background: #ececec; padding:6px 12px">
                            @foreach($item->extra_products as $extraProduct)
                                @foreach($extraProduct->order_item_parents as $extraParent)
                                    -{{ $extraParent->quantity / $item->quantity  }}x {{ $extraParent->name }}<br>
                                @endforeach
                            @endforeach
                        </td>
                        <td class="td quantity" style="color: {{ $item->quantity > 1 ? 'red' : 'black'}} " align="right">{{ $item->quantity }}</td>
                    </tr>
                    @endif
                @endif
            @endforeach
        @elseif($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_CREDIT_NOTE )
            @foreach($order->order_items as $item)
                @php
                    $orderRefundHistoryRefundItems = $order->order_refund->order_refund_history->refund_items ?? [];
                    $filteredData = [];
                    if(!empty($orderRefundHistoryRefundItems)) {
                        $filteredData = array_filter($orderRefundHistoryRefundItems, function ($refundItem) use ($item) {
                            return isset($refundItem["order_item_id"]) && $refundItem["order_item_id"] === $item->id;
                        });

                        $filteredData = reset($filteredData);
                        if (!$filteredData) {
                            $filteredData = [];
                        }
                    }
                @endphp
                @if(!empty($filteredData) && !empty($filteredData['quantity']))
                    <tr class="tr product-item my-border">
                        <td class="td product">
                            <div class="item-name">{{ $item->name }}</div>
                            <div class="item-meta">
                                @if($item->attributes)
                                    <ol class="wc-item-meta">
                                        @foreach(explode('<br>', $item->attributes) as $k => $attributeItem)
                                            @if($k === 0)
                                                @foreach(explode('~', $attributeItem) as $attribute)
                                                    @php
                                                        $attributeValue =  explode(':', $attribute);
                                                    @endphp
                                                    <li>
                                                        <strong>
                                                            {{ $attributeValue[0] ?? '' }} :
                                                        </strong>
                                                        {{ $attributeValue[1] ?? '' }}
                                                    </li>
                                                @endforeach
                                            @else
                                                {!! str_replace('~', ',', $attributeItem) !!} <br>
                                            @endif
                                        @endforeach
                                    </ol>
                                @endif
                            </div>
                            <br>
                            <div class="sku">
                                {{ $translation['pdf_sku'] ?? '{pdf_sku}' }}: {{ $item->sku }}
                            </div>
                        </td>
                        <td class="td quantity" align="right">{{ $filteredData['quantity'] }}</td>
                        <td class="td price" align="right">
                            {{ $filteredData['subtotal']}} <strong> {{ $order->order_currency }}</strong>
                            @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                <br>({{ ( number_format($filteredData['subtotal']  / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                            @endif
                        </td>
                    </tr>
                @endif

            @endforeach
        @endif
    </tbody>
</table>
@if(($data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP))
    <table class="table-pdf">
        <tr>
            <td width="50%"></td>
            <td width="50%">
                @if(!empty($order->order_refund->order_refund_history) && ($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_CREDIT_NOTE))
                    @php
                        $netAmount = round(floatval(($order->order_refund->order_refund_history->refund_amount - $order->order_refund->order_refund_history->total_shipping_refund_amount) / (1 + $taxRate / 100)), 2);
                        $netShipping = round(floatval($order->order_refund->order_refund_history->total_shipping_refund_amount / (1 + $taxRate / 100)), 2);

                        $refundAmount = round(floatval($order->order_refund->order_refund_history->refund_amount), 2);
                        $productTax = $refundAmount - $netAmount;
                        $shippingTax = round(floatval($order->order_refund->order_refund_history->total_shipping_refund_amount) - $netShipping, 2);

                        $totalNetAmount = $netAmount + $netShipping;
                        $salesTax = $productTax + $shippingTax;
                    @endphp
                @endif
            </td>
        </tr>
    </table>
    <table width="100%">
        <tr>
            <td style="width: 40%"></td>
            <td>
                <table class="totals table-pdf order-details">
                    <tfoot class="tfoot">
                    @if(!empty($order->order_refund->order_refund_history) && ($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_CREDIT_NOTE))
                        @if($order->order_refund->order_refund_history->net_amount > 0)
                            <tr class="tr my-border">
                                <th class="th description">{{ $translation['pdf_net_amount'] ?? '{pdf_net_amount}' }}</th>
                                <td align="right" class="td price">
                                    -{{ $order->order_refund->order_refund_history->net_amount }}<strong> {{ $order->order_currency }}</strong>
                                    @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                        <br>(-{{ ( number_format($order->order_refund->order_refund_history->net_amount / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if($order->order_refund->order_refund_history->shipping_amount > 0)
                            <tr class="tr my-border">
                                <th class="th description">{{ $translation['pdf_shipping'] ?? '{pdf_shipping}' }}</th>
                                <td align="right" class="td price">
                                    -{{ $order->order_refund->order_refund_history->shipping_amount }}<strong> {{ $order->order_currency }}</strong>
                                    @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                        <br> -({{ ( number_format($order->order_refund->order_refund_history->shipping_amount / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                    @endif
                                </td>
                            </tr>
                        @endif
                        @if($order->order_refund->order_refund_history->total_net_amount > 0))
                            <tr class="tr my-border">
                                <th class="th description">{{ $translation['pdf_total_net_amount'] ?? '{pdf_total_net_amount}' }}</th>
                                <td align="right" class="td price">
                                    @if($totalNetAmount > 0)
                                        -{{ $order->order_refund->order_refund_history->total_net_amount }}<strong> {{ $order->order_currency }}</strong>
                                        @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                            <br> -({{ ( number_format($order->order_refund->order_refund_history->total_net_amount / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endif

                        @if($order->order_refund->order_refund_history->sales_tax > 0)
                        <tr class="tr my-border">
                            <th class="th description">
                                @if(($salesTax??0) > 0)
                                    {{ $translation['pdf_sales_tax'] ?? '{pdf_sales_tax}' }} {{($salesTax ?? '') ?  $taxRate .'%' : '' }}
                                @else
                                    {{ $translation['pdf_sales_tax'] ?? '{pdf_sales_tax}' }} {{($order->total_tax > 0) ?  $taxRate .'%' : '' }}
                                @endif
                            </th>
                            <td align="right" class="td price">
                                -{{ $order->order_refund->order_refund_history->sales_tax }}<strong> {{ $order->order_currency }}</strong>
                                @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                    <br> -({{ ( number_format($order->order_refund->order_refund_history->sales_tax / $currency->gbp_rate, 2) ) }}<strong>GBP</strong>)
                                @endif
                            </td>
                        </tr>
                        @endif

                        <tr class="tr my-border">
                            <th class="th description">{{ $translation['pdf_total_amount'] ?? '{pdf_total_amount}' }}</th>
                            <td align="right" class="td price">
                                -{{ $order->order_refund->order_refund_history->total_amount }}<strong> {{ $order->order_currency }}</strong>
                                @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                    <br> -({{ ( number_format($order->order_refund->order_refund_history->total_amount / $currency->gbp_rate, 2) ) }}<strong>GBP</strong>)
                                @endif
                            </td>
                        </tr>
                    @else
                        <tr class="tr my-border">
                            <th class="th description">{{ $translation['pdf_net_amount'] ?? '{pdf_net_amount}' }}</th>
                            <td align="right" class="td price">
                                {{ $order->items_subtotal_price }}<strong> {{ $order->order_currency }}</strong>
                                @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                    <br>({{ ( number_format($order->items_subtotal_price / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                @endif
                            </td>
                        </tr>
                        @if($order->shipping_price > 0)
                            <tr class="tr my-border">
                                <th class="th description">{{ $translation['pdf_shipping'] ?? '{pdf_shipping}' }}</th>
                                <td align="right" class="td price">
                                    {{ $order->shipping_price }}<strong> {{ $order->order_currency }}</strong>
                                    @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                        <br> ({{ ( number_format($order->shipping_price / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr class="tr my-border">
                            <th class="th description">{{ $translation['pdf_total_net_amount'] ?? '{pdf_total_net_amount}' }}</th>
                            <td align="right" class="td price">
                                {{ number_format($order->items_subtotal_price + $order->shipping_price, 2) }}<strong> {{ $order->order_currency }}</strong>
                                @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                    <br> ({{ ( number_format(($order->items_subtotal_price + $order->shipping_price) / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                @endif
                            </td>
                        </tr>
                        @if($order->zip_fee > 0)
                            <tr class="tr my-border">
                                <th class="th description">
                                    {{ $order->zip_fee_label }}
                                </th>
                                <td align="right" class="td price">
                                    {{ $order->zip_fee }}<strong> {{ $order->order_currency }}</strong>
                                    @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                        <br>({{ ( number_format($order->zip_fee / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr class="tr my-border">
                            <th class="th description">
                                {{ $translation['pdf_sales_tax'] ?? '{pdf_sales_tax}' }} {{($order->total_tax > 0) ?  $taxRate .'%' : '' }}
                            </th>
                            <td align="right" class="td price">
                                {{ $order->total_tax }}<strong> {{ $order->order_currency }}</strong>
                                @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                    <br>({{ ( number_format($order->total_tax / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                @endif
                            </td>
                        </tr>

                        @if($order->total_discount_price && $order->total_discount_price > 0)
                            <tr class="tr my-border">
                                <th class="th description">{{ $translation['pdf_discounted'] ?? '{pdf_discounted}' }}</th>
                                <td align="right" class="td price">
                                    {{ $order->total_discount_price }}<strong> {{ $order->order_currency }}</strong>
                                    @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                        <br>({{ ( number_format($order->total_discount_price / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <tr class="tr my-border">
                            <th class="th description">{{ $translation['pdf_total_amount'] ?? '{pdf_total_amount}' }}</th>
                            <td align="right" class="td price">
                                {{ $order->total_price }}<strong> {{ $order->order_currency }}</strong>
                                @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                    <br> ({{ ( number_format($order->total_price / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                @endif
                            </td>
                        </tr>
                        @if($order->cashback_amount > 0 && $order->cashback_amount < $order->total_price)
                            <tr class="tr my-border">
                                <th class="th description">{{ $translation['pmc'] ?? '{pmc}' }}</th>
                                <td align="right" class="td price">
                                    -{{ $order->cashback_amount }}<strong> {{ $order->order_currency }}</strong>
                                    @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                        <br> (-{{ ( number_format($order->cashback_amount / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                    @endif
                                </td>
                            </tr>
                            <tr class="tr my-border">
                                <th class="th description">{{ $translation['pay'] ?? 'To pay' }}</th>
                                <td align="right" class="td price">
                                    {{ ($order->total_price - $order->cashback_amount) }}<strong> {{ $order->order_currency }}</strong>
                                    @if($order->order_shipping_address->country_code === 'GB' && $order->order_currency !== 'GBP')
                                        <br> (-{{ ( number_format(($order->total_price - $order->cashback_amount) / $currency->gbp_rate, 2) ) }}<strong> GBP</strong>)
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endif

                    </tfoot>
                </table>
            </td>
        </tr>
    </table>
@endif
@if(($order->note && ($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_INVOICE)) || ($order->note && ($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PROFORMA)))
    {{ $order->note }}
@endif

@if(!empty($order->order_refund->order_refund_history) && ($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_CREDIT_NOTE))
<p>
    @if(in_array($order->order_refund->order_refund_history->refund_reason, \App\Constants\OrderConstants::REFUND_REASONS))
        {{ $translation['pdf_' . strtolower(str_replace(' ', '_', $order->order_refund->order_refund_history->refund_reason))] }}
    @else
        {{ $order->order_refund->order_refund_history->refund_reason ?? '' }}
    @endif
</p>
@endif
@if(
    ($order->total_tax == 0 && !empty($order->order_billing_address->vat_number) && $data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP) ||
    ($order->total_tax == 0 && $order->order_shipping_address->country_code === 'CH' && $data['document_setting_id'] !== \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_PACKING_SLIP)
)
    <p><strong>{{ $translation['front_vat_id'] ?? '{front_vat_id}' }}: </strong>{{ $order->order_billing_address->vat_number }}</p>
    <p>{{ $translation['pdf_invoice_tax_free_text'] ?? '{pdf_invoice_tax_free_text}' }}</p>
@endif

@if($order->order_shipping_address->country_code == "GB" && $gbNoTaxAmount && ($data['document_setting_id'] === \App\Constants\OrderConstants::ORDER_DOCUMENT_TYPE_INVOICE))
    @if(($order->order_currency != 'GBP' && $currency->gbp_rate && ($order->total_price / $currency->gbp_rate < $gbNoTaxAmount)) || ($order->order_currency === 'GBP' && $order->total_price < $gbNoTaxAmount ))
        @if($order->order_billing_address->vat_number != '')
           {{ $translation['front_vat_id'] ?? '{front_vat_id}' }} {{ $order->order_billing_address->vat_number }} <br>
           {{ $translation['pdf_invoice_uk_reverse_charge_text'] ?? '{pdf_invoice_uk_reverse_charge_text}' }}
        @endif
        @if($order->order_billing_address->vat_number != '')
            {{ $translation['pdf_exchange_rate'] ?? '{pdf_exchange_rate}' }} {{$gbNoTaxAmount }} <br>
        @endif
   @endif
@endif

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
