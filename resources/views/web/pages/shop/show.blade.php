@extends('web.layout.app')
@section('seo_title', $product->title . ' – ' . $product->subtitle)
@section('seo_description', substr(strip_tags($product->description),0,255))
@if ($product->publishedImages && $product->publishedImages[0])
@section('og_image', url('/') . ImageHelper::openGraphImage($product->publishedImages[0]))
@endif
@section('content')
<div id="shop">
  <product-view>
    <template>
      <section class="shop">
        <div class="content-grid">
          <header class="page-header page-header--shop is-mobile">
            <h1>
              {{$product->title}}<br>
              <em>{{$product->subtitle}}</em>
            </h1>
          </header>
          <div>
            <div class="swiper-container js-swiper">
              <div class="swiper-wrapper">
                @foreach($product->publishedImages as $image)
                  <div class="swiper-slide">
                    <figure>
                      <x-card-image :image="$image" template="shop" maxWidth="1200" maxHeight="1200" />
                    </figure>
                  </div>
                @endforeach
              </div>
              <div class="swiper-pagination"></div>
            </div>
            <div class="shop-thumbs">
              @foreach($product->publishedImages as $key => $image)
                <figure>
                  <a href="javascript:;" class="js-swiper-thumb" data-idx="{{$key}}">
                    <x-card-image :image="$image" template="shop-preview" maxWidth="400" maxHeight="400" />
                  </a>
                </figure>
              @endforeach
            </div>
          </div>
          <div>
            <header class="page-header page-header--shop">
              <h1>
                {{$product->title}}<br>
                <em>{{$product->subtitle}}</em>
              </h1>
            </header>
            {!!$product->description!!}
            @if ($product->stock < 1)
              <p>
                <span style="text-decoration: line-through">CHF {{\MoneyHelper::round($product->price)}}</span> ({{__('page.sold-out')}})
              </p>
            @else
              <p>CHF {{\MoneyHelper::round($product->price)}}</p>
              <product-add />
            @endif
          </div>
        </div>
      </section>
    </template>
  </product-view>
</div>
@endsection