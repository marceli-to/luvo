<picture>
  @foreach($queries as $k => $q)
    @if ($q != null)
      <source media="{{$q}}" srcset="/img/crop/{{$image->name}}/{{$width[$k]}}/{{$height[$k]}}/{{$coords}}">
    @else
      <source srcset="/img/crop/{{$image->name}}/{{$width[$k]}}/{{$height[$k]}}/{{$coords}}">
      <img src="/img/crop/{{$image->name}}/{{$width[$k]}}/{{$height[$k]}}/{{$coords}}" width="{{$width[$k]}}" height="{{$height[$k]}}" alt="{{$image->caption}}">
    @endif
  @endforeach
</picture>
@if ($image->caption)
<figcaption>{{$image->caption}}</figcaption>
@endif

