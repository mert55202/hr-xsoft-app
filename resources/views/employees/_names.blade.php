@if ($position === null)
    —
@elseif ($people->isEmpty())
    {{ $position->name }}
@else
    @foreach ($people as $person)
        @if ($visibleIds === null || in_array($person->id, $visibleIds, true))
            <a href="{{ route('personnel.show', $person) }}">{{ $person->fullName() }}</a>
        @else
            {{ $person->fullName() }}
        @endif
        <span class="text-secondary">({{ $position->name }})</span>@if (! $loop->last), @endif
    @endforeach
@endif
