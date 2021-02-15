<li>
  <a 
    href="{{ localized_route('page.contact') }}" 
    title="{{ __('Kontakt') }}"
    class="{{ request()->routeIs('*page.contact') ? 'is-active' : '' }}">
    {{ __('Kontakt') }}
  </a>
</li>