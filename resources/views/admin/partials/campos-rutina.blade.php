@php
    $v = fn (string $campo) => $editando ? old($campo) : $rutina?->{$campo};
    $ejercicios = $editando
        ? old('ejercicios', [])
        : ($rutina?->ejercicios->map->only(['dia', 'ejercicio', 'series', 'repeticiones', 'descanso'])->values()->all() ?? []);
    $id = 'ej-'.($rutina?->id_rutina ?? 'nueva');
@endphp

<div class="form-grid">
    <div class="form-group"><label>Nombre de la rutina *</label><input type="text" name="nombre" value="{{ $v('nombre') }}" placeholder="Hipertrofia 4 días" required></div>
    <div class="form-group"><label>Objetivo</label><input type="text" name="objetivo" value="{{ $v('objetivo') }}" placeholder="Ganar masa muscular"></div>
</div>
<div class="form-group" style="margin-top:.75rem;"><label>Indicaciones</label><input type="text" name="notas" value="{{ $v('notas') }}" placeholder="Calentar 10 min, técnica antes que peso…"></div>

<div class="section-label" style="margin-top:1rem;">Ejercicios</div>
<div class="rutina-filas" id="{{ $id }}" data-ejercicios='@json($ejercicios)'>
    <div class="rutina-fila rutina-head"><span>Día / bloque</span><span>Ejercicio</span><span>Series</span><span>Reps</span><span>Descanso</span><span></span></div>
</div>
<button type="button" class="btn btn-light btn-sm" style="margin-top:.5rem;" data-agregar-ejercicio="{{ $id }}"><i class="fas fa-plus"></i> Agregar ejercicio</button>

<style>
    .rutina-filas { display: flex; flex-direction: column; gap: .35rem; }
    .rutina-fila { display: grid; grid-template-columns: 1fr 2fr 70px 80px 80px 34px; gap: .35rem; align-items: center; }
    .rutina-fila input { width: 100%; padding: .4rem .5rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: .82rem; }
    .rutina-head span { font-size: .68rem; font-weight: 700; color: var(--text-soft); text-transform: uppercase; }
    @media (max-width: 640px) { .rutina-fila { grid-template-columns: 1fr 1fr; } .rutina-head { display: none; } }
</style>

<script>
    (function () {
        const cont = document.getElementById(@json($id));
        let i = 0;

        function agregar(e = {}) {
            const fila = document.createElement('div');
            fila.className = 'rutina-fila';
            const campo = (n, ph, tipo = 'text') => `<input type="${tipo}" name="ejercicios[${i}][${n}]" placeholder="${ph}" value="${(e[n] ?? '').toString().replace(/"/g, '&quot;')}">`;
            fila.innerHTML = campo('dia', 'Día 1') + campo('ejercicio', 'Sentadilla') + campo('series', '4', 'number') + campo('repeticiones', '10-12') + campo('descanso', '90 s')
                + '<button type="button" class="btn btn-danger btn-sm btn-icon" title="Quitar"><i class="fas fa-xmark"></i></button>';
            fila.querySelector('button').addEventListener('click', () => fila.remove());
            cont.appendChild(fila);
            i++;
        }

        const iniciales = JSON.parse(cont.dataset.ejercicios || '[]');
        (iniciales.length ? iniciales : [{}, {}, {}]).forEach(agregar);
        document.querySelector(`[data-agregar-ejercicio="${cont.id}"]`).addEventListener('click', () => agregar());
    })();
</script>
