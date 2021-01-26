@extends('web.layout.app')
@section('seo_title', $illustration->title . ' – ' . $illustration->subtitle)
@section('seo_description', substr(strip_tags($illustration->description),0,255))
@if ($illustration->publishedImages && $illustration->publishedImages[0])
@section('og_image', url('/') . ImageHelper::openGraphImage($illustration->publishedImages[0]))
@endif
@section('content')
<section class="content">
  <div class="content-grid">
    <div class="span">
      @if ($illustration->publishedImages)
        @foreach($illustration->publishedImages as $image)
          <x-image :image="$image" :hasCaption="true" :hasLazy="true" template="large" cssClass="is-illu" />
        @endforeach
      @endif
    </div>
    <div class="span">
      <header class="page-header">
        <h1>
          {{$illustration->title}}<br>
          <em>{{$illustration->subtitle}}</em>
        </h1>
      </header>
      {!! $illustration->description !!}
    </div>
  </div>
</section>
@endsection