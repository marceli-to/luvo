<nav class="site-menu js-menu">
  <a href="javascript:;" class="btn-menu-close js-menu-btn">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><path d="M18 6L6 18M6 6l12 12"/></svg>
  </a>
  <ul>
    <li class="{{ request()->routeIs('*.page.illustration.*') ? 'is-active' : '' }}">
      <a href="{{ localized_route('page.illustration.listing') }}">Illustration</a>
    </li>
    <li class="{{ request()->routeIs('*.page.shop.*') ? 'is-active' : '' }}">
      <a href="{{ localized_route('page.shop.listing') }}">Shop</a>
    </li>
    <li class="{{ request()->routeIs('*.page.news.*') ? 'is-active' : '' }}">
      <a href="{{ localized_route('page.news.listing') }}">News</a>
    </li>
    <li class="{{ request()->routeIs('*.page.about.show') ? 'is-active is-last' : 'is-last' }}">
      <a href="{{ localized_route('page.about.show') }}">About</a>
    </li>
    @if (request()->routeIs('*.page.shop.*'))
      <div class="language">
        <li>
          <a href="{{current_route('de')}}" class="{{locale() == 'de' ? 'is-active' : ''}}">d</a>
        </li>
        <li>
          <a href="{{current_route('en')}}" class="{{locale() == 'en' ? 'is-active' : ''}}">e</a>
        </li>
      </div>
    @endif
  </ul>
</nav>