@extends('web.layout.app')
@section('seo_title', __('Team') . ' ' . $data->capitalizedSlug)
@section('page_title', __('Team') . ' ' . $data->capitalizedSlug)
@section('content')
<x-menu-team-members teamId="{{$data->id}}" isMobileMenu="0" />
@php
$image_count = ['desktop' => 0, 'mobile' => 0];
// if ($data->publishedImages && $data->publishedImages->count() > 0)
// {
//   $image_count = $data->publishedImages->countBy(function ($image) {
//     return $image->device;
//   });
// }
@endphp
<section class="content">
  <div>
    <div class="grid-2x1">
      <div class="span order-md-2">
        @if ($data->publishedImages)
          <div class="swiper-container js-swiper-team-horizontal">
            <div class="swiper-wrapper">
              @foreach($data->publishedImages as $image)
                @if ($image->device == 'mobile')
                  <div class="swiper-slide">
                    <figure class="visual-mobile">
                      <x-picture :image="$image" :queries="['min-width: 900px', null]" :width="[1200,900]" :height="[800,600]" />
                    </figure>
                  </div>
                @endif
              @endforeach
            </div>
            @if ($image_count['mobile'] > 1)
              <div class="swiper-btn-next"></div>
              <div class="swiper-btn-prev"></div>
            @endif
          </div>
          <div class="swiper-container js-swiper-team-vertical">
            <div class="swiper-wrapper">
              @foreach($data->publishedImages as $image)
                @if ($image->device == 'desktop')  
                  <div class="swiper-slide">
                    <figure class="visual-desktop">
                      <x-picture :image="$image" :queries="[null]" :width="[1600]" :height="[1920]" />
                    </figure>
                  </div>
                @endif
              @endforeach
            </div>
            @if ($image_count['desktop'] > 1)
              <div class="swiper-btn-next"></div>
              <div class="swiper-btn-prev"></div>
            @endif
          </div>
        @endif
      </div>
      <div class="span order-md-1 content-scrollable" data-simplebar>
        <article>
          <h1>{{ $data->title }}</h1>
          {!! $data->text !!}
        </article>
      </div>
    </div>
  </div>
</section>
@endsection