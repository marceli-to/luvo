@extends('web.layout.app')
@section('seo_title', __('Kontakt'))
@section('page_title', __('Kontakt'))
@section('content')
<section class="content">
  <div>
    <div class="grid-2x1">
      <div class="span order-md-2">
        @if ($data['contact']->publishedImages)
          @foreach($data['contact']->publishedImages as $image)
            @if ($image->device == 'mobile')
              <figure class="visual-mobile">
                <x-picture :image="$image" :queries="['min-width: 900px', null]" :width="[1200,900]" :height="[750,560]" />
              </figure>
            @endif
            @if ($image->device == 'desktop')
              <figure class="visual-desktop">
                <x-picture :image="$image" :queries="[null]" :width="[1600]" :height="[1920]" />
              </figure>
            @endif
          @endforeach
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
              <a href="{{$data['contact']->map_uri}}" target="_blank" rel="noopener" class="icon-external">{{__('Google Maps')}}</a>
            </div>
          @endif
          @if ($data['contact']->imprint)
            <div>
              <a href="javascript:;" class="icon-chevron-down" data-toggle="next:div">{{__('Impressum')}}</a>
              <div style="display:none">
                {!! $data['contact']->imprint !!}
              </div>
            </div>
          @endif
          @if ($data['teams'])
            <div class="contact__team-members">
              <div>
                @if ($data['teams']['luks'])
                  @foreach($data['teams']['luks']->members as $member)
                    {{ $member->firstname}} {{$member->name}}<br>{!! $member->description !!}
                  @endforeach
                @endif
              </div>
              <div>
                @if ($data['teams']['vogt'])
                  @foreach($data['teams']['vogt']->members as $member)
                  {{ $member->firstname}} {{$member->name}}<br>{!! $member->description !!}
                  @endforeach
                @endif
              </div>
            </div>
          @endif
        </article>
      </div>
    </div>
  </div>
</section>
@endsection