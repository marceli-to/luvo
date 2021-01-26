@extends('web.layout.app')
@section('seo_title', __('page.order-completed'))
@section('seo_description', 'posters | artprints | postcards and much more')
@section('content')
<section class="content">
  <header class="page-header">
    <h1>{{__('page.order-completed')}}</h1>
  </header>
  <div>
    <p>{{__('page.order-completed-notice')}}</p>
  </div>
</section>
@endsection