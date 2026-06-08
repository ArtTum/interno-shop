<html>

<head>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1251">
</head>

<body>
<div>
    <strong>{{ $tntSetting->deleted_labels_text }}</strong>
    <ul>
        @foreach($deletedLabels as $deletedLabel)
            @foreach($deletedLabel->parcel_number as $parcelNumber)
                <li>{{ $parcelNumber }}</li>
            @endforeach
        @endforeach
    </ul>
    <p>
        @if($hasPdf)
            <strong>Documents for: {{ date( 'd.m.y' ) }}</strong><br>
            <a href="{{ $tntFileUrl }}" target='_blank'>{{ $tntFileUrl }}</a>
        @else
            <strong>Documents for: {{ date( 'd.m.y' ) }}</strong><br>
        @endif
    </p>
</div>
</body>

</html>
