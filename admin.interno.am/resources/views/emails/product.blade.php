<table class="es-content-body" align="center" cellpadding="15" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px">
    <tr>
        <th style="padding:0;Margin:0;padding-left:20px" align="left">{{ $translation['email_image'] ?? '{email_image}' }}</th>
        <th align="left">{{ $translation['email_product'] ?? '{email_product}' }}</th>
        <th align="left">{{ $translation['email_quantity'] ?? '{email_quantity}' }}</th>
        @if(is_null($order->full_reshipment))
            <th align="left">{{ $translation['email_price'] ?? '{email_price}' }}</th>
        @endif
    </tr>

    @if($refund ?? false)
        @if(!empty($order->order_refund->order_refund_history))
            @php
                $netAmount = round(floatval(($order->order_refund->order_refund_history->refund_amount - $order->order_refund->order_refund_history->total_shipping_refund_amount) / (1 + $order->tax_rate / 100)), 2);
                $netShipping = round(floatval($order->order_refund->order_refund_history->total_shipping_refund_amount / (1 + $order->tax_rate / 100)), 2);

                $refundAmount = round(floatval($order->order_refund->order_refund_history->refund_amount), 2);
                $productTax = $refundAmount - $netAmount;
                $shippingTax = round(floatval($order->order_refund->order_refund_history->total_shipping_refund_amount) - $netShipping, 2);

                $totalNetAmount = $netAmount + $netShipping;
                $salesTax = $productTax + $shippingTax;
            @endphp
        @endif
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
                <tr>
                    <td>
                        <img class="adapt-img"
                             src="{{ $baseUrl }}/uploads/images/thumbnail{{ $item->media->path ?? '' }}"
                             alt
                             style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic"
                             width="70">
                    </td>
                    <td>
                        {{ $item->name }} <br>
                        @if($item->attributes)
                            <div style="font-size: 10px !important;">
                                @foreach(explode('<br>', $item->attributes) as $k => $attributeItem)
                                    @if($k === 0)
                                        @foreach(explode('~', $attributeItem) as $attribute)
                                            @php
                                                $attributeValue =  explode(':', $attribute);
                                            @endphp
                                            <div>
                                                <strong>
                                                    {{ $attributeValue[0] ?? '' }} :
                                                </strong>
                                                {{ $attributeValue[1] ?? '' }}
                                            </div>
                                        @endforeach
                                    @else
                                        {!! str_replace('~', ',', $attributeItem) !!} <br>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td align="center">
                        {{ $filteredData['quantity'] }}
                    </td>
                    <td>
                        {{ $filteredData['subtotal']}} {{ $order->order_currency }}
                    </td>
                </tr>
            @endif
        @endforeach
        <tr>
            <td colspan="4" align="left" style="padding:0;Margin:0;padding-top:10px;padding-left:20px;padding-right:20px">
                <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                    <tr>
                        <td class="es-m-p0r" align="center"
                            style="padding:0;Margin:0;width:560px">
                            <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;border-top:2px solid #efefef;border-bottom:2px solid #efefef">
                                <tr>
                                    <td align="right" class="es-m-txt-r" style="padding:0;Margin:0;padding-top:10px;padding-bottom:20px">
                                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                                            @if($order->order_refund->order_refund_history->net_amount > 0)
                                                {{ $translation['pdf_net_amount'] ?? '{pdf_net_amount}' }}:&nbsp;<strong> -{{ $order->order_refund->order_refund_history->net_amount }} {{ $order->order_currency }} </strong><br>
                                            @endif
                                            @if($order->order_refund->order_refund_history->shipping_amount > 0)
                                                {{ $translation['pdf_shipping'] ?? '{pdf_shipping}' }}:&nbsp;<strong>-{{ $order->order_refund->order_refund_history->shipping_amount  }} {{ $order->order_currency }}</strong><br>
                                            @endif
                                            @if($order->order_refund->order_refund_history->total_net_amount > 0)
                                                {{ $translation['pdf_total_net_amount'] ?? '{pdf_total_net_amount}' }}: <strong>-{{ $order->order_refund->order_refund_history->total_net_amount }} {{ $order->order_currency }}</strong><br>
                                            @endif
{{--                                            @if($order->order_refund->order_refund_history->net_amount > 0)--}}
{{--                                                {{ $translation['pdf_discounted'] ?? '{pdf_discounted}' }}:<strong>-{{ $order->total_discount_price }} {{ $order->order_currency }}</strong><br>--}}
{{--                                            @endif--}}
                                            @if($order->order_refund->order_refund_history->zip_fee)
                                                {{ $order->zip_fee_label }}:<strong> -{{ $order->order_refund->order_refund_history->zip_fee }} {{ $order->order_currency }}</strong><br>
                                            @endif
                                            @if($order->order_refund->order_refund_history->sales_tax > 0)
                                                {{ $translation['pdf_sales_tax'] ?? '{pdf_sales_tax}' }}:&nbsp;<strong>-{{ $order->order_refund->order_refund_history->sales_tax }} {{ $order->order_currency }}</strong><br>
                                            @endif
                                            @if($order->order_refund->order_refund_history->total_amount > 0)
                                                {{ $translation['pdf_total_amount'] ?? '{pdf_total_amount}' }}:&nbsp;<strong>-{{ $order->order_refund->order_refund_history->total_amount }} {{ $order->order_currency }}</strong>
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @elseif($showProduct)
        @foreach($order->order_items as $item)
            <tr>
            <td>
                <img class="adapt-img"
                     src="{{ $baseUrl }}/uploads/images/thumbnail{{ $item->media->path ?? '' }}"
                     alt
                     style="display:block;border:0;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic"
                     width="70">
            </td>
            <td>
                {{ $item->name }} <br>
                @if($item->attributes)
                    <div style="font-size: 10px !important;">
                        @foreach(explode('<br>', $item->attributes) as $k => $attributeItem)
                            @if($k === 0)
                                @foreach(explode('~', $attributeItem) as $attribute)
                                    @php
                                        $attributeValue =  explode(':', $attribute);
                                    @endphp
                                    <div>
                                        <strong>
                                            {{ $attributeValue[0] ?? '' }} :
                                        </strong>
                                        {{ $attributeValue[1] ?? '' }}
                                    </div>
                                @endforeach
                            @else
                                {!! str_replace('~', ',', $attributeItem) !!} <br>
                            @endif
                        @endforeach
                    </div>
                @endif
            </td>
            <td align="center">
                {{ $item->quantity }}
            </td>
                @if(is_null($order->full_reshipment))
                    <td>
                        {{ $item->price }} {{ $order->order_currency }}
                    </td>
                @endif
            </tr>
        @endforeach
    @endif
    @if(!($refund ?? false) && is_null($order->full_reshipment))
        <tr>
            <td colspan="4" align="left" style="padding:0;Margin:0;padding-top:10px;padding-left:20px;padding-right:20px">
                <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px">
                    <tr>
                        <td class="es-m-p0r" align="center"
                            style="padding:0;Margin:0;width:560px">
                            <table cellpadding="0" cellspacing="0" width="100%" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;border-top:2px solid #efefef;border-bottom:2px solid #efefef">
                                <tr>
                                    <td align="right" class="es-m-txt-r" style="padding:0;Margin:0;padding-top:10px;padding-bottom:20px">
                                        <p style="Margin:0;-webkit-text-size-adjust:none;-ms-text-size-adjust:none;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:21px;color:#333333;font-size:14px">
                                            @if($order->items_subtotal_price > 0)
                                                {{ $translation['pdf_net_amount'] ?? '{pdf_net_amount}' }}:&nbsp;<strong>{{ $order->items_subtotal_price }} {{ $order->order_currency }}</strong><br>
                                            @endif
                                            @if($order->shipping_price > 0)
                                                {{ $translation['pdf_shipping'] ?? '{pdf_shipping}' }}:&nbsp;<strong>{{ $order->shipping_price }} {{ $order->order_currency }}</strong><br>
                                            @endif
                                            @if($order->total_discount_price > 0)
                                                {{ $translation['pdf_discounted'] ?? '{pdf_discounted}' }}:<strong>{{ $order->total_discount_price }} {{ $order->order_currency }}</strong><br>
                                            @endif
                                            @if($order->zip_fee_label)
                                                {{ $order->zip_fee_label }}:<strong> {{ $order->zip_fee }} {{ $order->order_currency }}</strong><br>
                                            @endif
                                            {{ $translation['pdf_sales_tax'] ?? '{pdf_sales_tax}' }}:&nbsp;<strong>{{ $order->total_tax }} {{ $order->order_currency }}</strong><br>
                                            @if($order->total_price)
                                                {{ $translation['pdf_total_amount'] ?? '{pdf_total_amount}' }}:&nbsp;<strong>{{ $order->total_price }} {{ $order->order_currency }}</strong>
                                            @endif
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    @endif
</table>
@if(!empty($order->order_refund->order_refund_history) && ($refund ?? false))
    <p style="margin: 10px 20px">
        @if(in_array($order->order_refund->order_refund_history->refund_reason, \App\Constants\OrderConstants::REFUND_REASONS))
            {{ $translation['pdf_' . strtolower(str_replace(' ', '_', $order->order_refund->order_refund_history->refund_reason))] ?? '' }}
        @else
            {{ $order->order_refund->order_refund_history->refund_reason ?? '' }}
        @endif
    </p>
@endif
