@if (request()->routeIs('*page.home'))
  <li class="languages">
    <a href="/de/home">D</a>
    <span>/</span>
    <a href="/en/home">E</a>
    {{-- <span>/</span>
    <a href="/fr/home">F</a> --}}
  </li>
@else
  <li class="languages">
    <a href="{{ current_route('de') }}">D</a>
    <span>/</span>
    <a href="{{ current_route('en') }}">E</a>
    {{-- <span>/</span>
    <a href="{{ current_route('fr') }}">F</a> --}}
  </li>
@endif