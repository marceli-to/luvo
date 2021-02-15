@if ($data)
  <nav class="menu-teams">
    <ul>
      @foreach($data as $d)
        <li>
          <a 
            href="{{ localized_route('page.team.' . $d->slug) }}"
            title="{{__('Team')}} {{ $d->capitalizedSlug }}">
            {{__('Team')}} {{ $d->capitalizedSlug }}
          </a>
        </li>
      @endforeach
    </ul>
  </nav>
@endif

