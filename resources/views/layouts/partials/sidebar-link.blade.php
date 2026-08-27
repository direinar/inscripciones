@php
    $activeClass = $active ? 'active' : '';
@endphp

<a class="{{ $activeClass }}" href="{{ route($route, $routeParams ?? []) }}">
    <span class="sidebar-link__icon" aria-hidden="true">{{ $icon }}</span>{{ $label }}
</a>
