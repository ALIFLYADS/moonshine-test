@props([
    'components' => [],
    'home_route' => null,
    'logo',
    'profile',
])
<!-- Menu horizontal -->
<aside {{ $attributes->merge(['class' => 'layout-menu-horizontal']) }}
       :class="asideMenuOpen && '_is-opened'"
>
    <div class="menu-logo">
        @if($logo ?? false)
            {{ $logo }}
        @else
            @include('moonshine::layouts.shared.logo', ['home_route' => $home_route])
        @endif
    </div>

    <nav class="menu-navigation">
        <x-moonshine::components
            :components="$components"
        />

        {{ $slot ?? '' }}
    </nav>

    <div class="menu-actions">
        @if($profile ?? false)
            {{ $profile }}
        @elseif(config('moonshine.auth.enable', true))
            <x-moonshine::layout.profile />
        @endif

        <div class="menu-inner-divider"></div>

        @if(config('moonshine.use_theme_switcher', true))
            <div class="menu-mode">
                <x-moonshine::layout.theme-switcher :top="true" />
            </div>
        @endif

        <div class="menu-burger">
            @include('moonshine::layouts.shared.burger')
        </div>
    </div>
</aside>
<!-- END: Menu horizontal -->

