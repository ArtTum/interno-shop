<table class="es-content-body" align="center" cellpadding="15" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px">
    <tr>
        <th  style="padding: 5px 20px;" align="left">{{ $translation['email_billing_address'] ?? '{email_billing_address}' }}</th>
        <th  style="padding: 5px 20px;" align="left">{{ $translation['email_shipping_address'] ?? '{email_shipping_address}' }}</th>
    </tr>
    <tr>
        <td style="padding: 5px 20px;">
            {{ $order->order_billing_address->name ?? '' }} {{ $order->order_billing_address->last_name ?? '' }} <br>
            {{ $order->order_billing_address->address ?? '' }}, {{ $order->order_billing_address->address_2 ?? '' }}<br>
            {{ $order->order_billing_address->city ?? '' }}, {{ $order->order_billing_address->state ?? '' }} {{ $order->order_billing_address->zip ?? '' }} <br>
            {{ $order->order_billing_address->country ?? '' }} <br>
            <a href="tel:{{ $order->order_billing_address->phone ?? '' }}">{{ $order->order_billing_address->phone ?? '' }}</a> <br>
            {{ $order->order_billing_address->email ?? '' }}
        </td>
        <td  style="padding: 5px 20px;">
            {{ $order->order_shipping_address->name ?? '' }} {{ $order->order_shipping_address->last_name ?? '' }} <br>
            {{ $order->order_shipping_address->address ?? '' }}, {{ $order->order_shipping_address->address_2 ?? '' }}<br>
            {{ $order->order_shipping_address->city ?? '' }}, {{ $order->order_shipping_address->state ?? '' }} {{ $order->order_shipping_address->zip ?? '' }} <br>
            {{ $order->order_shipping_address->country ?? '' }} <br>
        </td>
    </tr>
</table>
