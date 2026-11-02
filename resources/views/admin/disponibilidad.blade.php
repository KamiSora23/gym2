<x-admin-layout :titulo="$esPropio ? 'Mi perfil y disponibilidad' : 'Disponibilidad de '.$coach->nombre" icono="fa-user-clock" modulo="entrenamiento"
    subtitulo="Las horas en que el coach puede dar clases. Las clases nuevas deben caber dentro de estas franjas.">

    <x-slot name="acciones">
        <a href="{{ route('entrenamiento.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Entrenamiento</a>
    </x-slot>

    @php
        $franjas = old('franjas', $coach->disponibilidad->map(fn ($f) => [
            'dia_semana' => $f->dia_semana,
            'hora_inicio' => substr($f->hora_inicio, 0, 5),
            'hora_fin' => substr($f->hora_fin, 0, 5),
        ])->values()->all());
    @endphp

    @if ($errors->any())
        @include('admin.partials.errores')
        <div style="height:1rem;"></div>
    @endif

    <form method="POST" action="{{ route('entrenadores.disponibilidad.update', $coach) }}" id="form-disponibilidad">
        @csrf
        @method('PUT')

        <section class="settings-section">
            <header>
                <span class="icon"><i class="fas fa-id-badge"></i></span>
                <div><h3>{{ $coach->nombre }}</h3><p>Datos que ven el administrador, recepción y tus alumnos.</p></div>
            </header>
            <div class="form-grid">
                <div class="form-group"><label>Especialidad</label><input type="text" name="especialidad" value="{{ old('especialidad', $coach->especialidad) }}" placeholder="Crossfit, funcional, yoga…"></div>
                <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="{{ old('telefono', $coach->telefono) }}"></div>
            </div>
            <div class="form-group" style="margin-top:1rem;">
                <label>Presentación</label>
                <textarea name="bio" rows="3" placeholder="Tu experiencia, certificaciones y enfoque de entrenamiento.">{{ old('bio', $coach->bio) }}</textarea>
            </div>
        </section>

        <section class="settings-section">
            <header>
                <span class="icon"><i class="fas fa-user-clock"></i></span>
                <div>
                    <h3>Disponibilidad semanal</h3>
                    <p>Agrega una o varias franjas por día (por ejemplo, mañana y tarde). Sin franjas, se considera disponible siempre.</p>
                </div>
            </header>

            <div class="availability-grid" id="disponibilidad">
                @foreach (\App\Models\Clase::DIAS as $num => $dia)
                    <div class="availability-day" data-dia="{{ $num }}">
                        <div class="availability-head">
                            <strong>{{ $dia }}</strong>
                            <button type="button" class="btn btn-light btn-sm" data-agregar><i class="fas fa-plus"></i> Franja</button>
                        </div>
                        <div class="availability-slots" data-slots>
                            <span class="muted" data-vacio>No disponible</span>
                        </div>
                        @php $clasesDia = $clases->where('dia_semana', $num); @endphp
                        @if ($clasesDia->isNotEmpty())
                            <div class="class-meta" style="margin-top:.5rem;">
                                <i class="fas fa-calendar-check"></i> Clases: {{ $clasesDia->map(fn ($c) => $c->nombre.' '.$c->horario)->implode(' · ') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div style="display:flex; gap:.5rem; flex-wrap:wrap; margin-top:1rem;">
                <span class="muted" style="align-self:center;">Atajos:</span>
                <button type="button" class="btn btn-light btn-sm" data-plantilla="mananas">Lun–Vie 6:00–12:00</button>
                <button type="button" class="btn btn-light btn-sm" data-plantilla="tardes">Lun–Vie 16:00–22:00</button>
                <button type="button" class="btn btn-light btn-sm" data-plantilla="completo">Lun–Sáb 7:00–21:00</button>
                <button type="button" class="btn btn-light btn-sm" data-plantilla="limpiar"><i class="fas fa-eraser"></i> Limpiar todo</button>
            </div>
        </section>

        <div class="settings-actions">
            <a href="{{ route('entrenamiento.index') }}" class="btn btn-light">Cancelar</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar perfil y disponibilidad</button>
        </div>
    </form>

    <style>
        .availability-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: .75rem; }
        .availability-day { border: 1px solid var(--line); border-radius: 14px; padding: .85rem; background: #fff; }
        .availability-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: .6rem; }
        .availability-head strong { color: var(--primary); font-size: .85rem; text-transform: uppercase; letter-spacing: .04em; }
        .availability-slots { display: flex; flex-direction: column; gap: .4rem; }
        .slot { display: flex; align-items: center; gap: .35rem; }
        .slot input { flex: 1; min-width: 118px; padding: .4rem .5rem; border: 1px solid #cbd5e1; border-radius: 9px; font-size: .85rem; }
        @media (max-width: 640px) { .availability-grid { grid-template-columns: 1fr; } .slot input { min-width: 0; } }
        .slot span { color: var(--text-soft); font-size: .8rem; }
    </style>

    <x-slot name="scripts">
        <script>
            (function () {
                const contenedor = document.getElementById('disponibilidad');
                let indice = 0;

                function actualizarVacio(dia) {
                    const slots = dia.querySelector('[data-slots]');
                    slots.querySelector('[data-vacio]').style.display = slots.querySelector('.slot') ? 'none' : '';
                }

                function agregar(numDia, inicio = '07:00', fin = '12:00') {
                    const dia = contenedor.querySelector(`[data-dia="${numDia}"]`);
                    const fila = document.createElement('div');
                    fila.className = 'slot';
                    fila.innerHTML = `
                        <input type="hidden" name="franjas[${indice}][dia_semana]" value="${numDia}">
                        <input type="time" name="franjas[${indice}][hora_inicio]" value="${inicio}" required>
                        <span>a</span>
                        <input type="time" name="franjas[${indice}][hora_fin]" value="${fin}" required>
                        <button type="button" class="btn btn-danger btn-sm btn-icon" title="Quitar"><i class="fas fa-xmark"></i></button>`;
                    fila.querySelector('button').addEventListener('click', () => { fila.remove(); actualizarVacio(dia); });
                    dia.querySelector('[data-slots]').appendChild(fila);
                    indice++;
                    actualizarVacio(dia);
                }

                function limpiar() {
                    contenedor.querySelectorAll('.slot').forEach(s => s.remove());
                    contenedor.querySelectorAll('[data-dia]').forEach(actualizarVacio);
                }

                contenedor.querySelectorAll('[data-dia]').forEach(dia => {
                    dia.querySelector('[data-agregar]').addEventListener('click', () => agregar(dia.dataset.dia));
                });

                const plantillas = {
                    mananas: [[1, 5], '06:00', '12:00'],
                    tardes: [[1, 5], '16:00', '22:00'],
                    completo: [[1, 6], '07:00', '21:00'],
                };

                document.querySelectorAll('[data-plantilla]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        limpiar();
                        const p = plantillas[btn.dataset.plantilla];
                        if (!p) return;
                        for (let d = p[0][0]; d <= p[0][1]; d++) agregar(d, p[1], p[2]);
                    });
                });

                @json($franjas).forEach(f => agregar(f.dia_semana, f.hora_inicio, f.hora_fin));
                contenedor.querySelectorAll('[data-dia]').forEach(actualizarVacio);
            })();
        </script>
    </x-slot>
</x-admin-layout>
