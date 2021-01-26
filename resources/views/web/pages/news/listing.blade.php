@extends('web.layout.app')
@section('seo_title', 'News')
@section('seo_description', 'news from the studio')
@section('content')
@if ($news)
  <section class="cards">
    <div class="js-macy">
      @foreach($news as $n)
        @if ($n->previewImage)
          <x-card
            id="{{$n->previewImage->news->id}}"
            type="news" 
            :image="$n->previewImage" 
            title="{{$n->title}}" 
            subtitle="{{$n->subtitle}}" />
        @endif
      @endforeach
    </div>
  </section>
@endif
@endsection