<picture>
  @foreach($queries as $k => $q)
    @if ($q != null)
      <source media="{{$q}}" srcset="/img/cache/{{$image->name}}?w={{$width[$k]}}&h={{$height[$k]}}&c={{$coords}}">
    @else
      <source srcset="/img/cache/{{$image->name}}?w={{$width[$k]}}&h={{$height[$k]}}&c={{$coords}}">
      <img src="/img/cache/{{$image->name}}?w={{$width[$k]}}&h={{$height[$k]}}&c={{$coords}}" width="{{$width[$k]}}" height="{{$height[$k]}}" alt="{{$image->caption}}">
    @endif
  @endforeach
</picture>
@if ($image->caption)
<figcaption>{{$image->caption}}</figcaption>
@endif

