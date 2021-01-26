<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@if(trim($__env->yieldContent('seo_title')))@yield('seo_title') – {{config('seo.title')}}@else{{config('seo.title')}}@endif</title>
<meta name="description" content="@if(trim($__env->yieldContent('seo_description')))@yield('seo_description')@else{{config('seo.description')}}@endif">
<meta property="og:title" content="@if(trim($__env->yieldContent('seo_title')))@yield('seo_title') – {{config('seo.title')}}@else{{config('seo.title')}}@endif">
<meta property="og:description" content="@if(trim($__env->yieldContent('seo_description')))@yield('seo_description')@else{{config('seo.description')}}@endif">
<meta property="og:url" content="{{url()->current()}}">
<meta property="og:image" content="@if(trim($__env->yieldContent('og_image')))@yield('og_image')@else{{ asset('assets/img/og.jpg') }}@endif">
<meta property="og:site_name" content="{{config('seo.title')}}">
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="format-detection" content="telephone=no">
<link href="{{ mix('assets/css/app.css') }}" type="text/css" rel="stylesheet" />
<script src="{{ asset('assets/js/modernizr.min.js') }}"></script>
</head>
<body>
<header class="site-header">
  <div>

  </div>
</header>
