@if(!empty($data))
    <table class="es-content-body" align="center" border="1" cellpadding="15" cellspacing="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-collapse:collapse;border-spacing:0px;background-color:#FFFFFF;width:600px">
        <tr>
            <th style="padding:0;Margin:0;padding-left:20px" align="left">ID</th>
            <th align="left">SKU</th>
            <th align="left">Name</th>
            <th align="left">Status</th>
            <th align="left">Product</th>
        </tr>
        @foreach($data as $product)
            <tr>
                <td>#{{ $product['id'] }}</td>
                <td>{{ $product['sku'] }}</td>
                <td>{{ $product['name'] }}</td>
                <td>{{ $product['stock_status'] }}</td>
                <td><a href="{{ $product['link'] }}" target="_blank">Show product</a></td>
            </tr>
        @endforeach
    </table>
@else
    <h1>There is not out of stock products</h1>
@endif

