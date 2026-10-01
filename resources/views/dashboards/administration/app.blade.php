<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name', 'Laravel') }}</title>
@vite('resources/sass/dashboard/app.scss')
</head>
<body>
<div id="app-administration"></div>
@vite('resources/js/dashboard/app.js')
</body>
</html>
