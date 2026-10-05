<li>
    <article class="org-card">
        <h2 class="h6 mb-1">{{ $position->name }}</h2>
        <p class="mb-1">{{ $position->department?->name ?? 'Departman yok' }}</p>
        <p class="small mb-0 text-secondary">{{ $position->collar->label() }}</p>
        @forelse (($employees[$position->id] ?? collect()) as $person)
            <p class="small mb-0">
                @if ($visibleIds === null || in_array($person->id, $visibleIds, true))
                    <a href="{{ route('personnel.show', $person) }}">{{ $person->fullName() }}</a>
                @else
                    {{ $person->fullName() }}
                @endif
            </p>
        @empty
            <p class="small mb-0 text-secondary">Kadro boş</p>
        @endforelse
    </article>
    @if ($children->has($position->id))
        <ul>
            @foreach ($children[$position->id] as $child)
                @include('positions.node', ['position' => $child, 'children' => $children])
            @endforeach
        </ul>
    @endif
</li>
