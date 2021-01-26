@if ($hasLazy)
  <img data-src="/img/{{$template}}/{{$image->name}}{{$query ?? ''}}" class="lazyload" width="{{$width}}" height="{{$height}}" alt="{{$image->caption}}">
@else
  <img src="/img/{{$template}}/{{$image->name}}{{$query ?? ''}}" width="{{$width}}" height="{{$height}}" alt="{{$image->caption}}">
@endif
