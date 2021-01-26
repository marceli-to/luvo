<figure class="card is-shop-listing">
  <a href="{{ localized_route('page.shop.show', ['slug' => UrlHelper::slug($title) .'-'. UrlHelper::slug($subtitle), 'product' => $image->product->id]) }}" title="{{$title}} – {{$subtitle}}">
    <x-card-image :image="$image" template="cache" maxWidth="800" maxHeight="800" hasLazy="true" />
    <figcaption>
      <h2>{{$title}}</h2>
      <em>{{$subtitle}}</em><br>
      @if ($stock < 1)
        <span style="text-decoration: line-through">CHF {{$price}}</span> ({{__('page.sold-out')}})
      @else
        <span>CHF {{$price}}</span>
      @endif
    </figcaption>
  </a>
</figure>