<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name', 'Laravel') }}</title>
<link href="{{ mix('assets/dashboard/css/app.css') }}" rel="stylesheet">
</head>
<body>
<div id="app-administration">
  <app-component />
</div>
<script src="{{ mix('assets/dashboard/js/bundle.administration.js') }}" defer></script>
</body>
</html>
