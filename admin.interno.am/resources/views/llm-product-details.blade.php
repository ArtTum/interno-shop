<div class="product">
    <h1>
        <a href="{{$product['url']}}">{{$product['name']}}</a>
    </h1>
{{--    <h2>{{$product['sub_name']}}</h2>--}}
    <div class="categories">
        <strong>Categories:</strong>
        <ul>
            @foreach ($product['categories'] as $category)
                <li>{{ $category['name'] }}</li>
            @endforeach
        </ul>
    </div>
    <div class="main-image">
        <img src="{{$product['main_image']}}" alt="{{$product['name']}}" width="300"/>
    </div>
{{--    <div class="description">{!! $product['short_description'] !!}</div>--}}
{{--    <div class="properties">{!! $product['properties'] !!}</div>--}}
    @if (!empty($product['color_attributes']) || !empty($product['product_multiselect']))
        <div class="colors">
            <h3>Available Colors:</h3>
            <ul>
                @foreach ($product['color_attributes'] as $color_attribute)
                    <li>
                        <a href="{{$color_attribute['product_url']}}">
                            <span>{{$color_attribute['name']}}</span>
                            <img src="{{$color_attribute['image_url']}}" width="60"/>
                        </a>
                    </li>
                @endforeach
                @foreach ($product['product_multiselect'] as $option)
                    <li>
                        <span>{{$option['name']}}</span>
                        <img src="{{$option['image_url']}}" width="60"/>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
