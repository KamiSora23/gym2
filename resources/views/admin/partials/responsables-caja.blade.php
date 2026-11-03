{{-- Lista de empleados que pueden operar caja. Con $caja, solo los de su sucursal (o sin sucursal fija). --}}
@php
    $candidatos = $caja
        ? $empleados->filter(fn ($u) => ! $u->id_sucursal || (int) $u->id_sucursal === (int) $caja->id_sucursal)
        : $empleados;
    $marcados = $caja ? $caja->responsables->pluck('id') : collect(old('responsables', []))->map(fn ($v) => (int) $v);
@endphp

<div style="display:flex; flex-wrap:wrap; gap:.4rem;">
    @forelse ($candidatos as $u)
        <label class="chip" style="cursor:pointer; padding:.35rem .7rem;" data-sucursal-empleado="{{ $u->id_sucursal ?? '' }}">
            <input type="checkbox" name="responsables[]" value="{{ $u->id }}" @checked($marcados->contains($u->id)) style="margin-right:.25rem;">
            {{ $u->name }} <span class="muted" style="font-weight:400;">· {{ $u->role?->etiqueta }}{{ $u->sucursal ? ' · '.$u->sucursal->nombre : '' }}</span>
        </label>
    @empty
        <span class="muted">No hay empleados con permiso de caja.</span>
    @endforelse
</div>
