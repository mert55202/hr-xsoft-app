@foreach ($navItems as $item)
    @if (! empty($bottom) && empty($item['mobile']))
        @continue
    @endif
    @if (empty($bottom) && empty($item['desktop']))
        @continue
    @endif
    <li class="nav-item">
        <a
            class="nav-link d-flex flex-lg-row flex-column align-items-center gap-1 gap-lg-2 {{ $item['active'] ? 'active' : 'text-body' }}"
            href="{{ route($item['route'], $item['params']) }}"
            @if ($item['active']) aria-current="page" @endif
        >
            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    </li>
@endforeach
