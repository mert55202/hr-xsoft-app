@if ($looseRoots->isNotEmpty())
    <div class="alert alert-warning" role="status">
        Üstü boş pozisyonlar: {{ $looseRoots->pluck('name')->join(', ') }}. Kök tek olmalı. Diğerlerine birinci amir seçin.
    </div>
@endif
@if (($vacantParents ?? collect())->isNotEmpty())
    <div class="alert alert-warning" role="status">
        Önce o pozisyona personel atanmalıdır: {{ $vacantParents->join(', ') }}.
    </div>
@endif
