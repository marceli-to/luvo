@extends('web.layout.app')
@section('seo_title', 'Shop')
@section('seo_description', 'posters | artprints | postcards and much more')
@section('content')
@if ($products)
  <section>
    <div class="shop-listing">
      @foreach($products as $product)
        @if ($product->previewImage)
          <x-card-shop
            id="{{$product->id}}"
            type="shop" 
            :image="$product->previewImage" 
            title="{{$product->title}}" 
            subtitle="{{$product->subtitle}}"
            price="{{$product->price}}"
            stock="{{$product->stock}}" />
        @endif
      @endforeach
    </div>
  </section>
@endif
@endsection