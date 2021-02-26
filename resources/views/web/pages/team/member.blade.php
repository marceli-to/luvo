@extends('web.layout.app')
@section('seo_title', __('Team'))
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
                <x-picture :image="$image" :queries="['min-width: 900px', null]" :width="[1200,900]" :height="[750,560]" />
              </figure>
            @endif
            @if ($image->device == 'desktop')
              <figure class="visual-desktop">
                <x-picture :image="$image" :queries="['min-height: 900px', 'min-height: 600px', null]" :width="[1600,1200,1200]" :height="[1920,1440,1440]" />
              </figure>
            @endif
          @endforeach
        @endif
      </div>
      <div class="span order-md-1 content-scrollable" data-simplebar>
        <article class="member">
          <h1>{{ $data->fullname }}</h1>
          @if ($data->credits)
            <div class="member__credits">
              {!! $data->credits !!}
            </div>
          @endif
          @if ($data->credits)
            <div class="member__description">
              {!! $data->description !!}
            </div>
          @endif
          @if ($data->area)
            <div class="member__list">
              <a href="javascript:;" class="btn-accordeon js-btn-member-list">
                <span>{{ __('Tätigkeitsgebiete') }}</span>
              </a>
              <div style="display:none">
                {!! $data->area !!}
              </div>
            </div>
          @endif
          @if ($data->biography)
            <div class="member__list">
              <a href="javascript:;" class="btn-accordeon js-btn-member-list">
                <span>{{ __('Werdegang') }}</span>
              </a>
              <div style="display:none">
                {!! $data->biography !!}
              </div>
            </div>
          @endif
          @if ($data->membership)
            <div class="member__list">
              <a href="javascript:;" class="btn-accordeon js-btn-member-list">
                <span>{{ __('Mitgliedschaften') }}</span>
              </a>
              <div style="display:none">
                {!! $data->membership !!}
              </div>
            </div>
          @endif
          @if ($data->publications  && count($data->publications) > 0)
            <div class="member__list">
              <a href="javascript:;" class="btn-accordeon js-btn-member-list">
                <span>{{ __('Publikationen') }}</span>
              </a>
              <div style="display:none">
                <ul>
                @foreach($data->publications as $publication)
                  <li>
                    <a href="javascript:;" class="btn-accordeon is-nested js-btn-member-list">
                      <span>{{ $publication->title }}</span>
                    </a>
                    <div style="display:none">
                      {!! $publication->description !!}
                      {!! $publication->articles !!}
                    </div>
                  </li>
                @endforeach
                </ul>
              </div>
            </div>
          @endif
        </article>
      </div>
    </div>
  </div>
</section>
@endsection