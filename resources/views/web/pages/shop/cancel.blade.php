@extends('web.layout.app')
@section('seo_title', __('page.order-canceled'))
@section('seo_description', 'posters | artprints | postcards and much more')
@section('content')
<section class="content">
  <header class="page-header">
    <h1>{{__('page.order-canceled')}}</h1>
  </header>
  <div>
    <p>{!!__('page.order-canceled-notice')!!}</p>
  </div>
</section>
@endsection