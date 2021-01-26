@extends('web.layout.app')
@section('seo_title', $news->title . ' – ' . $news->subtitle)
@section('seo_description', substr(strip_tags($news->text),0,255))
@if ($news->publishedImages && $news->publishedImages[0])
@section('og_image', url('/') . ImageHelper::openGraphImage($news->publishedImages[0]))
@endif
@section('content')
<section class="content">
  <header class="page-header">
    <h1 class="is-news">
      {{$news->subtitle}}<br><em>{{$news->title}}</em>
    </h1>
  </header>
  <div class="content-grid">
    <div class="span">
      @if ($news->publishedImages)
        @foreach($news->publishedImages as $image)
          <x-image :image="$image" :hasCaption="true" :hasLazy="true" template="large" />
        @endforeach
      @endif
    </div>
    <div class="span">
      {!! $news->text !!}
    </div>
  </div>
</section>
@endsection