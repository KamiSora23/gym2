@if ($items->hasPages())
    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
        <span class="muted">Mostrando {{ $items->firstItem() }}–{{ $items->lastItem() }} de {{ $items->total() }}</span>
        <div style="display:flex; gap:.4rem;">
            @if ($items->onFirstPage())
                <span class="btn btn-light btn-sm" style="opacity:.5;"><i class="fas fa-chevron-left"></i> Anterior</span>
            @else
                <a href="{{ $items->previousPageUrl() }}" class="btn btn-light btn-sm"><i class="fas fa-chevron-left"></i> Anterior</a>
            @endif
            <span class="btn btn-light btn-sm" style="pointer-events:none;">{{ $items->currentPage() }} / {{ $items->lastPage() }}</span>
            @if ($items->hasMorePages())
                <a href="{{ $items->nextPageUrl() }}" class="btn btn-light btn-sm">Siguiente <i class="fas fa-chevron-right"></i></a>
            @else
                <span class="btn btn-light btn-sm" style="opacity:.5;">Siguiente <i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
    </div>
@endif
