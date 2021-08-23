@extends('web.layout.app')
@section('seo_title', 'Home')
@section('seo_description', __('Seit 1999 betreuen wir Privatpersonen, Unternehmen und Organisationen in privatrechtlichen Fragen. Mit Fachkompetenz, Sorgfalt, beherztem persönlichem Engagement, Effizienz und starkem Verantwortungsbewusstsein suchen wir stets die bestmögliche und auf Ihre individuellen Bedürfnisse zugeschnittene Lösung.'))
@section('content')
@if ($data->publishedImages)
  @foreach($data->publishedImages as $image)
    @if ($loop->first)
      <figure class="visual-wide">
        <picture>
          <source media="(min-width: 1600px)" srcset="/img/cache/{{$image->name}}?w=2400&h=1500">        
          <source media="(min-width: 1200px)" srcset="/img/cache/{{$image->name}}?w=1600&h=1000">
          <source media="(min-width: 900px)" srcset="/img/cache/{{$image->name}}?w=1200&h=750">
          <source srcset="/img/cache/{{$image->name}}?w=900&h=560">
          <img src="/img/cache/{{$image->name}}?w=900&h=560" width="900" height="560" alt="{{$image->caption}}">
        </picture>
        <a href="javascript:;" class="visual-scroller js-btn-scroll"></a>
      </figure>
    @endif
  @endforeach
@endif
<section class="content-wide">
  <div>
    <article class="home">
      <h1>{{$data->title}}</h1>
      {!! $data->text !!}
    </article>
  </div>
</section>
@endsection