@extends('web.layout.app')
@section('seo_title', 'About')
@section('seo_description', 'the tidy mess creator')
@section('content')
<section class="about content">
  <div class="content-grid">
    <div class="span">
      <div class="swiper-container js-swiper">
        <div class="swiper-wrapper">
          @foreach($about->publishedImages as $image)
            <div class="swiper-slide">
              <figure>
                <x-card-image :image="$image" template="shop" maxWidth="1200" maxHeight="1200" />
              </figure>
            </div>
          @endforeach
        </div>
        <div class="swiper-pagination"></div>
      </div>
      <div class="about-thumbs">
        @foreach($about->publishedImages as $key => $image)
          <figure>
            <a href="javascript:;" class="js-swiper-thumb" data-idx="{{$key}}">
              <x-card-image :image="$image" template="shop-preview" maxWidth="400" maxHeight="400" />
            </a>
          </figure>
        @endforeach
      </div>
    </div>
    <div class="span">
      {!!$about->text!!}
    </div>
  </div>
</div>
</section>
@endsection