@if ($members || $assistant)

  @if ($isMobileMenu)

    @if ($members)
      @foreach($members as $member)
        <li>
          <a
          href="{{ localized_route('page.team.member', ['slugTeam' => $team->slug, 'teamMember' => $member, 'slugMember' => \Str::slug($member->firstname .'-'. $member->name)]) }}" 
          title="{{ $member->fullName }}"
          class="">
            {{ $member->fullName }}
          </a>
        </li>
      @endforeach
    @endif

    @if ($assistant)
      <a 
      href="{{ localized_route('page.team.assistant', ['slugTeamAssistant' => $team->slug, 'slug' => __('assistenz'), 'assistant' => $assistant]) }}" 
      title="{{ __('Assistenz') }}"
        class="">
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
          {{__('Team')}} {{ $team->capitalizedSlug }}
        </a>
      </h1>
      <ul>

        @if ($members)
          @foreach($members as $member)
            <li>
              <a
                href="{{ localized_route('page.team.member', ['slugTeam' => $team->slug, 'teamMember' => $member, 'slugMember' => \Str::slug($member->firstname .'-'. $member->name)]) }}" 
                title="{{ $member->fullName }}"
                class="">
                {{ $member->fullName }}
              </a>
            </li>
          @endforeach
        @endif

        @if ($assistant)
          <li>
            <a 
              href="{{ localized_route('page.team.assistant', ['slugTeamAssistant' => $team->slug, 'slug' => __('assistenz'), 'assistant' => $assistant]) }}" 
              title="{{ __('Assistenz') }}"
              class="">
              {{ __('Assistenz') }}
            </a>
          </li>
        @endif

      </ul>
    </nav>

  @endif
@endif