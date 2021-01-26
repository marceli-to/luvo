@extends('web.layout.app')
@section('seo_title', 'Illustration')
@section('seo_description', 'unique illustrations')
@section('content')

@if ($posts)
    <section class="cards">
      <div class="js-macy">
        @foreach($posts as $post)
          @if ($post->previewImage)
            <x-card
              id="{{$post->id}}"
              type="illu" 
              :image="$post->previewImage" 
              title="{{$post->title}}" 
              subtitle="{{$post->subtitle}}" />
          @endif
        @endforeach
      </div>
    </section>
@endif
@endsection