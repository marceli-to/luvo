@extends('web.layout.app')
@section('seo_title', __('Assistenz'))
@section('page_title', __('Team'))
@section('content')
<x-menu-team-members teamId="{{$data->team->id}}" isMobileMenu="0" />
<section class="content">
  <div>
    <div class="grid-2x1">
      <div class="span order-md-2">
        @if ($data->publishedImages)
          @foreach($data->publishedImages as $image)
            @if ($image->device == 'mobile')
              <figure class="visual-mobile">
                <x-picture :image="$image" :queries="['min-width: 900px', null]" :width="[1200,900]" :height="[750,560]" />
              </figure>
            @endif
            @if ($image->device == 'desktop')
              <figure class="visual-desktop">
                <x-picture :image="$image" :queries="['min-width: 1200px', 'min-width: 900px', null]" :width="[1600,1200,540]" :height="[1920,1440,648]" />
              </figure>
            @endif
          @endforeach
        @endif
      </div>
      <div class="span order-md-1">
        {!! $data->description !!}
      </div>
    </div>
  </div>
</section>
@endsection