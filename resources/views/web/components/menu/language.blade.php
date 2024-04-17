@if (request()->routeIs('*page.home'))
  <li class="languages">
    <a href="/de/home" class="{{ app()->getLocale() == 'en' ? 'inactive' : ''}}">D</a>
    <span>/</span>
    <a href="/en/home" class="{{ app()->getLocale() == 'de' ? 'inactive' : ''}}">E</a>
    <span>/</span>
    <a href="/fr/home">F</a>
  </li>
@else
  <li class="languages">
    <a href="{{ current_route('de') }}" class="{{ app()->getLocale() == 'en' ? 'inactive' : ''}}">D</a>
    <span>/</span>
    <a href="{{ current_route('en') }}" class="{{ app()->getLocale() == 'de' ? 'inactive' : ''}}">E</a>
    <span>/</span>
    <a href="{{ current_route('fr') }}">F</a>
  </li>
@endif