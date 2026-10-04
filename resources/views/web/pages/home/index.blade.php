@extends('web.layout.app')
@section('content')
@if ($data->publishedImages)
  @foreach($data->publishedImages as $image)
    @if ($loop->first)
      <figure class="visual-wide">
        <x-picture :image="$image" :queries="['min-width: 1600px', 'min-width: 1200px', 'min-width: 900px', null]" :width="[2400,1600,1200,900]" :height="[1500,1000,750,560]" :caption="false" />
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