<!doctype html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="robots" content="noindex, nofollow"> <!-- Added to prevent indexing -->
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CRM Doctor911.AM</title>
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
</head>
<body>

{{--With help of div with id "app" we are calling vue--}}
<div id="app"></div>

</body>
</html>
