@extends('web.layout.app')
@section('seo_title', __('Assistenz'))
@section('page_title', __('Team') .' '. $data->team->capitalizedSlug)
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
                <x-picture :image="$image" :queries="['min-width: 900px', null]" :width="[1200,900]" :height="[800,600]" />
              </figure>
            @endif
            @if ($image->device == 'desktop')
              <figure class="visual-desktop is-contain">
                <x-picture :image="$image" :queries="[null]" :width="[1600]" :height="[1920]" />
              </figure>
            @endif
          @endforeach
        @endif
      </div>
      <div class="span order-md-1 content-scrollable" data-simplebar>
        <article class="assistant">
          <div class="assistant__description">
            {!! $data->description !!}
          </div>
          {!! $data->assistants !!}
        </article>
      </div>
    </div>
  </div>
</section>
@endsection