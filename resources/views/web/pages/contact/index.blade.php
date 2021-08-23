@extends('web.layout.app')
@section('seo_title', __('Kontakt'))
@section('page_title', __('Kontakt'))
@section('content')
@php
$image_count = ['desktop' => 0, 'mobile' => 0];
if ($data['contact']->publishedImages)
{
  $image_count = $data['contact']->publishedImages->countBy(function ($image) {
    return $image->device;
  });
}
@endphp
<section class="content">
  <div>
    <div class="grid-2x1">
      <div class="span order-md-2">
        @if ($data['contact']->publishedImages)
          <div class="swiper-container @if (isset($image_count['mobile']) && $image_count['mobile'] > 1) js-swiper-team-horizontal @endif">
            <div class="swiper-wrapper">
              @foreach($data['contact']->publishedImages as $image)
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
          <div class="swiper-container @if (isset($image_count['desktop']) && $image_count['desktop'] > 1) js-swiper-team-vertical @endif">
            <div class="swiper-wrapper">
              @foreach($data['contact']->publishedImages as $image)
                @if ($image->device == 'desktop')  
                  <div class="swiper-slide">
                    <figure class="visual-desktop is-contain">
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
        <article class="contact">
          @if ($data['contact']->address)
            <address>
              {!! $data['contact']->address !!}
            </address>
          @endif
          @if ($data['contact']->map_uri)
            <div>
              <a href="{{$data['contact']->map_uri}}" target="_blank" rel="noopener" class="icon-external anchor-maps">{{__('Google Maps')}}</a>
            </div>
          @endif
          @if ($data['teams'])
            <div class="contact__members">
              <div>
                @if ($data['teams']['vogt'])
                  <div class="contact-member-list">
                    <a href="javascript:;" class="btn-accordeon js-btn-team">
                      {{ __('Team Vogt') }}
                    </a>
                    <div class="contact-member-list-items" style="display:none">
                      @foreach($data['teams']['vogt']->members as $member)
                        <div class="contact-member">
                          <h2>{{ $member->firstname}} {{$member->name}}</h2>{!! $member->credits !!}
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
              <div>
                @if ($data['teams']['luks'])
                  <div class="contact-member-list">
                    <a href="javascript:;" class="btn-accordeon js-btn-team">
                      {{ __('Team Luks') }}
                    </a>
                    <div class="contact-member-list-items" style="display:none">
                      @foreach($data['teams']['luks']->members as $member)
                        <div class="contact-member">
                          <h2>{{ $member->firstname}} {{$member->name}}</h2>{!! $member->credits !!}
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
            </div>
          @endif
          @if ($data['contact']->imprint)
            <div>
              <a href="javascript:;" class="anchor-imprint" data-toggle="next:div">{{__('Impressum')}}</a>
              <div style="display:none" class="contact__imprint">
                {!! $data['contact']->imprint !!}
              </div>
            </div>
          @endif
        </article>
      </div>
    </div>
  </div>
</section>
@endsection