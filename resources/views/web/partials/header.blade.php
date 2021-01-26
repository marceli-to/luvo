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
<meta property="og:image" content="@if(trim($__env->yieldContent('og_image')))@yield('og_image')@else{{ asset('assets/img/sajo-og.jpg') }}@endif">
<meta property="og:site_name" content="{{config('seo.title')}}">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">
<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
<meta name="msapplication-TileColor" content="#da532c">
<meta name="theme-color" content="#ffffff">
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="stripe-public-key" content="{{\Config::get('payment.stripe.public_key')}}" />
<meta name="format-detection" content="telephone=no">
<link href="{{ asset('assets/css/app.css') }}" type="text/css" rel="stylesheet" />
<script src="{{ asset('assets/js/modernizr.min.js') }}"></script>
@if (request()->routeIs('*.page.shop.payment.card'))
<script src="https://js.stripe.com/v3/"></script>
@endif
@if (request()->routeIs('*.page.shop.payment.paypal'))
<script src="https://www.paypal.com/sdk/js?client-id=AeCaG8qN43-7-gJHFo6OGxmHbJ9sUKosufEW5pGnZPtvgwE_J1NQMYgZZW73NrLjnyN1YrGkzMAvqQyt&currency=CHF"></script>
@endif
</head>
<body>
<header class="site-header">
  <div>
    <div class="logo-wrapper">
      <a href="{{localized_route('page.home')}}" class="logo">
        @include('web.partials.logo.elefant-' . mt_rand(1,4))
        <div class="logo__name">
          <span>samuel jordi</span>
          <span>luksundvogt.ch</span>
        </div>
      </a>
    </div>
    <div class="site-menu-wrapper">
      <x-cart-icon />
      @include('web.partials.menu')
    </div>
    <a href="javascript:;" class="btn-menu js-menu-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
    </a>
  </div>
</header>
