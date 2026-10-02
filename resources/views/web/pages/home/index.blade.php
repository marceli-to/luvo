@extends('web.layout.app')
@section('content')
@if ($data->publishedImages)
  @foreach($data->publishedImages as $image)
    @if ($loop->first)
      <figure class="visual-wide">
        <picture>
          @foreach([...\App\Support\ImageSupport::modernFormats(), null] as $format)
          <source media="(min-width: 1600px)" @if($format)type="image/{{$format}}" @endif srcset="{{ ImageHelper::cropUrl($image, 2400, 1500, $format) }}">
          <source media="(min-width: 1200px)" @if($format)type="image/{{$format}}" @endif srcset="{{ ImageHelper::cropUrl($image, 1600, 1000, $format) }}">
          <source media="(min-width: 900px)" @if($format)type="image/{{$format}}" @endif srcset="{{ ImageHelper::cropUrl($image, 1200, 750, $format) }}">
          <source @if($format)type="image/{{$format}}" @endif srcset="{{ ImageHelper::cropUrl($image, 900, 560, $format) }}">
          @endforeach
          <img src="{{ ImageHelper::cropUrl($image, 900, 560) }}" width="900" height="560" alt="{{$image->caption}}">
        </picture>
        <a href="javascript:;" class="visual-scroller js-btn-scroll"></a>
      </figure>
    @endif
  @endforeach
@endif
<section class="content-wide">
  <div>
    <article class="home rich-text">
      <h1>{{$data->title}}</h1>
      {!! $data->text !!}
    </article>
  </div>
</section>
@endsection