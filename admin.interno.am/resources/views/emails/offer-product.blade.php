<table class="es-content-body" align="center" cellpadding="15" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px">
    <tr>
        <th align="left">{{ $translation['email_product'] ?? '{email_product}' }}</th>
        <th align="left">{{ $translation['email_quantity'] ?? '{email_quantity}' }}</th>
        <th align="left">{{ $translation['email_price'] ?? '{email_price}' }}</th>
    </tr>
    @foreach($products as $item)
        <tr>
            <td>
                {{ $item['name'] }}
            </td>
            <td align="center">
                {{ $item['quantity'] }}
            </td>
            <td>
                {{ $item['price'] }} {{ $currency }}
            </td>
        </tr>
    @endforeach
</table>
