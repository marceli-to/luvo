<nav class="site-menu-mobile js-menu">
  <ul>
    <li>
      <a href="{{ localized_route('page.home') }}">Home</a>
    </li>
    @if ($data)
      @foreach($data as $d)
        <li>
          <a href="javascript:;" 
            class="js-menu-item-parent {{ request()->routeIs('*page.team.' . $d->slug) || request()->route()->parameter('slugTeam') == $d->slug || request()->routeIs('*.page.team.' . $d->slug . '.assistant') ? 'is-active' : '' }}">
            {{__('Team')}} {{ $d->capitalizedSlug }}
          </a>
          <ul>
            <li>
              <a 
              href="{{ localized_route('page.team.' . $d->slug) }}" 
              title="{{ __('Team') }} {{ $d->capitalizedSlug }}"
              class="{{ request()->routeIs('*page.team.' . $d->slug) ? 'is-active' : '' }}">
              {{__('Über uns')}}
            </a>
            </li>
            <x-menu-team-members teamId="{{$d->id}}" isMobileMenu="1" />
          </ul>
        </li>
      @endforeach
    @endif
    <x-menu-contact />
    <x-menu-language />
  </ul>
</nav>