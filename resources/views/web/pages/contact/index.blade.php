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
                <x-picture :image="$image" :queries="['min-width: 900px', null]" :width="[1200,900]" :height="[800,600]" />
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
              <a href="javascript:;" class="icon-chevron-down anchor-imprint" data-toggle="next:div">{{__('Impressum')}}</a>
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