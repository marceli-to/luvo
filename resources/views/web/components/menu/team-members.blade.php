@if ($members || $assistant)
  @if ($isMobileMenu)
    @if ($members)
      @foreach($members as $member)
        <li>
          <a
          href="{{ localized_route('page.team.member', ['slugTeam' => $team->slug, 'teamMember' => $member, 'slugMember' => \Str::slug($member->firstname .'-'. $member->name)]) }}" 
          title="{{ $member->fullName }}"
          class="{{ request()->routeIs('*page.team.member') && (null != request()->route()->parameter('teamMember.id') && request()->route()->parameter('teamMember.id') == $member->id) ? 'is-active' : '' }}">
          {{ $member->fullName }}
          </a>
        </li>
      @endforeach
    @endif
    @if ($assistant)
      <a 
        href="{{ localized_route('page.team.' . $team->slug . '.assistant') }}" 
        title="{{ __('Assistenz') }}"
        class="{{ request()->routeIs('*page.team.' . $team->slug . '.assistant') ? 'is-active' : '' }}">
        {{ __('Assistenz') }}
      </a>
    @endif
  @else
    <nav class="menu-team">
      <h1>
        <a 
          href="{{ localized_route('page.team.' .  $team->slug) }}" 
          title="{{ __('Team') }} {{ $team->capitalizedSlug }}"
          class="{{ request()->routeIs('*page.team.' . $team->slug) ? 'is-active' : '' }}">
          {{ __('Team') }} {{ $team->capitalizedSlug }}
        </a>
      </h1>
    <ul @if ($members->count() > 1) class="is-grid has-{{ $members->count() }}" @endif>
        @if ($members)
          @foreach($members as $member)
            <li>
              <a
                href="{{ localized_route('page.team.member', ['slugTeam' => $team->slug, 'teamMember' => $member, 'slugMember' => \Str::slug($member->firstname . '-' . $member->name)]) }}" 
                title="{{ $member->fullName }}"
                class="{{ request()->routeIs('*page.team.member') && (null != request()->route()->parameter('teamMember.id') && request()->route()->parameter('teamMember.id') == $member->id) ? 'is-active' : '' }}">
                {{ $member->fullName }}
              </a>
            </li>
          @endforeach
        @endif
        @if ($assistant)
          <li class="is-assistance">
            <a 
              href="{{ localized_route('page.team.' . $team->slug . '.assistant') }}" 
              title="{{ __('Assistenz') }}"
              class="{{ request()->routeIs('*page.team.' . $team->slug . '.assistant') ? 'is-active' : '' }}">
              {{ __('Assistenz') }}
            </a>
          </li>
        @endif
      </ul>
      <a href="{{ localized_route('page.home') }}" class="btn-back">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 41 41"><style>.cross-st0{fill:#181716}</style><path class="cross-st0" d="M40.6 1.4l-1-1.1-19.1 19.1L1.1 0 0 1.1l19.4 19.4L.3 39.6l1.1 1 19.1-19.1 18.8 18.8 1-1-18.8-18.8z"/></svg>
      </a>
    </nav>
  @endif
@endif