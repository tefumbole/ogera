@include('timesheet.partials.styles')
@php
    $tsTab = $tsTab ?? '';
    $tabs = [
        ['timesheet.activities', 'Create Activity', 'dripicons-plus', 'tone-blue'],
        ['timesheet.fill', 'Fill Time Sheet', 'dripicons-clock', 'tone-gold'],
        ['timesheet.working-week', 'Working Week', 'dripicons-calendar', 'tone-purple'],
    ];
@endphp
<nav class="ts-nav" aria-label="Employee Timesheet">
    @foreach($tabs as $tab)
        <a href="{{ route($tab[0]) }}" class="{{ $tab[3] }} {{ $tsTab === $tab[0] ? 'is-active' : '' }}">
            <i class="{{ $tab[2] }}"></i> {{ $tab[1] }}
        </a>
    @endforeach
</nav>
