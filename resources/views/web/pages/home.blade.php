@extends('web.layout.app')
@section('seo_title', 'Home – Samuel Jordi')
@section('seo_description', 'illustration | design | switzerland')
@section('content')
@if ($posts)
  <section class="cards">
    <div class="js-macy">
      @foreach($posts as $post)
        @if ($post->illustrationImage)
          <x-card
            id="{{$post->id}}"
            type="illu"
            :image="$post->illustrationImage" 
            title="{{$post->illustrationImage->illustration->title}}" 
            subtitle="{{$post->illustrationImage->illustration->subtitle}}" />
        @endif
        @if ($post->productImage)
          <x-card
            id="{{$post->id}}"
            type="shop"
            :image="$post->productImage" 
            title="{{$post->productImage->product->title}}" 
            subtitle="{{$post->productImage->product->subtitle}}" />
        @endif
        @if ($post->newsImage)
          <x-card
            id="{{$post->newsImage->news->id}}"
            type="news"
            :image="$post->newsImage" 
            title="{{$post->newsImage->news->title}}" 
            subtitle="{{$post->newsImage->news->subtitle}}" />
        @endif
      @endforeach
    </div>
  </section>
@endif
@endsection