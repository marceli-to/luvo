<picture>
  @foreach($queries as $k => $q)
    @foreach($formats as $format)
      <source @if ($q != null)media="{{$q}}" @endif type="image/{{$format}}" srcset="{{ $src($k, $format) }}">
    @endforeach
    @if ($q != null)
      <source media="{{$q}}" srcset="{{ $src($k) }}">
    @else
      <source srcset="{{ $src($k) }}">
      <img src="{{ $src($k) }}" width="{{$width[$k]}}" height="{{$height[$k]}}" alt="{{$image->caption}}">
    @endif
  @endforeach
</picture>
@if ($image->caption)
<figcaption>{{$image->caption}}</figcaption>
@endif
