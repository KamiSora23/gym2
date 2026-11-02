<x-admin-layout titulo="Nueva venta" icono="fa-cash-register" modulo="ventas"
    subtitulo="Vende productos del catálogo y/o una membresía.">

    <x-slot name="acciones">
        <a href="{{ route('ventas.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Volver</a>
    </x-slot>

    @php
        $sucursalFija = \App\Support\Tenant::sucursalId();
        $catalogo = $productos->map(fn ($p) => [
            'id' => $p->id_producto,
            'nombre' => $p->nombre,
            'precio' => (float) $p->precio,
            'stock' => $p->inventario->groupBy('id_sucursal')->map->sum('cantidad'),
        ])->values();
        $clienteInicial = old('id_cliente', request('cliente'));
    @endphp

    @if ($errors->any())
        @include('admin.partials.errores')
        <div style="height:1rem;"></div>
    @endif

    <form method="POST" action="{{ route('ventas.store') }}" id="form-venta" class="pos-grid">
        @csrf

        <div>
            <div class="form-card">
                <h3><i class="fas fa-user"></i> Cliente</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Cliente</label>
                        <select name="id_cliente" id="id_cliente">
                            <option value="">Venta de mostrador (sin cliente)</option>
                            @foreach ($clientes as $c)
                                <option value="{{ $c->id_cliente }}" @selected($clienteInicial == $c->id_cliente)
                                    data-membresia="{{ $c->membresiaActiva ? $c->membresiaActiva->plan?->nombre.' hasta '.$c->membresiaActiva->fecha_fin->format('d/m/Y') : '' }}">
                                    {{ $c->nombre }} — {{ $c->dni }}
                                </option>
                            @endforeach
                        </select>
                        <span class="muted" id="info-membresia"></span>
                    </div>
                    @unless ($sucursalFija)
                        <div class="form-group">
                            <label>Sucursal</label>
                            <select name="id_sucursal" id="id_sucursal">
                                @foreach ($sucursales as $s)
                                    <option value="{{ $s->id_sucursal }}" @selected(old('id_sucursal') == $s->id_sucursal)>{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endunless
                </div>
            </div>

            <div class="form-card">
                <h3><i class="fas fa-id-card"></i> Membresía</h3>
                @if ($planes->isEmpty())
                    <p class="muted" style="margin:0;">No hay planes activos. Créalos en <a href="{{ route('planes.index') }}">Planes y membresías</a>.</p>
                @else
                    <div class="form-group" style="margin:0;">
                        <select name="id_plan" id="id_plan">
                            <option value="" data-precio="0">Sin membresía</option>
                            @foreach ($planes as $plan)
                                <option value="{{ $plan->id_plan }}" data-precio="{{ $plan->precio }}" @selected(old('id_plan') == $plan->id_plan)>
                                    {{ $plan->nombre }} · {{ $plan->duracion_dias }} días · {{ dinero($plan->precio) }}
                                </option>
                            @endforeach
                        </select>
                        <span class="muted">Si el cliente ya tiene una membresía vigente, la nueva empieza cuando termine la actual.</span>
                    </div>
                @endif
            </div>

            <div class="form-card">
                <h3 style="justify-content:space-between;">
                    <span><i class="fas fa-box"></i> Productos</span>
                    <button type="button" class="btn btn-light btn-sm" id="agregar-linea" @disabled($productos->isEmpty())><i class="fas fa-plus"></i> Agregar</button>
                </h3>
                @if ($productos->isEmpty())
                    <p class="muted" style="margin:0;">No hay productos activos en el catálogo.</p>
                @endif
                <div id="lineas"></div>
            </div>
        </div>

        <div class="form-card" style="position:sticky; top:calc(var(--nav-h) + 1rem);">
            <h3><i class="fas fa-receipt"></i> Resumen</h3>
            <div class="summary-row"><span>Membresía</span><strong id="r-plan">$0.00</strong></div>
            <div class="summary-row"><span>Productos</span><strong id="r-productos">$0.00</strong></div>
            <div style="border-top:1px solid var(--line); margin:.5rem 0; padding-top:.75rem;">
                <span class="muted">TOTAL</span>
                <div class="pos-total" id="r-total">$0.00</div>
            </div>
            <div class="form-group">
                <label>Método de pago</label>
                <select name="metodo_pago">
                    @foreach (\App\Models\Venta::METODOS_PAGO as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('metodo_pago') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Estado del pago</label>
                <select name="estado">
                    <option value="completado" @selected(old('estado') !== 'pendiente')>Pagada</option>
                    <option value="pendiente" @selected(old('estado') === 'pendiente')>Pendiente de cobro</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; padding:.9rem;"><i class="fas fa-check"></i> Registrar venta</button>
        </div>
    </form>

    <template id="tpl-linea">
        <div class="pos-line">
            <select data-campo="producto" required>
                <option value="">Producto…</option>
            </select>
            <input type="number" min="1" value="1" data-campo="cantidad" required>
            <span class="sub">$0.00</span>
            <button type="button" class="btn btn-danger btn-sm btn-icon" data-quitar title="Quitar"><i class="fas fa-xmark"></i></button>
        </div>
    </template>

    <x-slot name="scripts">
        <script>
            (function () {
                const catalogo = @json($catalogo);
                const viejas = @json(array_values(old('items', [])));
                const sucursalFija = @json($sucursalFija);
                const lineas = document.getElementById('lineas');
                const tpl = document.getElementById('tpl-linea');
                const plan = document.getElementById('id_plan');
                const sucursal = document.getElementById('id_sucursal');
                const cliente = document.getElementById('id_cliente');
                const fmt = n => '$' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                let indice = 0;

                const stockDe = p => {
                    const suc = sucursalFija ?? (sucursal ? Number(sucursal.value) : null);
                    return Number(p.stock[suc] ?? 0);
                };

                function opciones(select, seleccionado) {
                    const actual = seleccionado ?? select.value;
                    select.querySelectorAll('option:not(:first-child)').forEach(o => o.remove());
                    catalogo.forEach(p => {
                        const o = new Option(`${p.nombre} · ${fmt(p.precio)} (stock ${stockDe(p)})`, p.id);
                        o.disabled = stockDe(p) <= 0;
                        select.add(o);
                    });
                    select.value = actual;
                }

                function agregar(datos = {}) {
                    const nodo = tpl.content.firstElementChild.cloneNode(true);
                    const sel = nodo.querySelector('[data-campo=producto]');
                    const cant = nodo.querySelector('[data-campo=cantidad]');
                    sel.name = `items[${indice}][id_producto]`;
                    cant.name = `items[${indice}][cantidad]`;
                    indice++;
                    opciones(sel, datos.id_producto ?? '');
                    if (datos.cantidad) cant.value = datos.cantidad;
                    nodo.querySelector('[data-quitar]').addEventListener('click', () => { nodo.remove(); recalcular(); });
                    sel.addEventListener('change', recalcular);
                    cant.addEventListener('input', recalcular);
                    lineas.appendChild(nodo);
                    recalcular();
                }

                function recalcular() {
                    let productos = 0;
                    lineas.querySelectorAll('.pos-line').forEach(l => {
                        const p = catalogo.find(x => String(x.id) === l.querySelector('[data-campo=producto]').value);
                        const cantidad = Number(l.querySelector('[data-campo=cantidad]').value) || 0;
                        const sub = p ? p.precio * cantidad : 0;
                        if (p) l.querySelector('[data-campo=cantidad]').max = stockDe(p);
                        l.querySelector('.sub').textContent = fmt(sub);
                        productos += sub;
                    });
                    const montoPlan = plan ? Number(plan.selectedOptions[0]?.dataset.precio || 0) : 0;
                    document.getElementById('r-plan').textContent = fmt(montoPlan);
                    document.getElementById('r-productos').textContent = fmt(productos);
                    document.getElementById('r-total').textContent = fmt(montoPlan + productos);
                }

                function mostrarMembresia() {
                    const info = cliente.selectedOptions[0]?.dataset.membresia;
                    document.getElementById('info-membresia').textContent = info ? `Membresía vigente: ${info}` : (cliente.value ? 'Sin membresía vigente' : '');
                }

                document.getElementById('agregar-linea')?.addEventListener('click', () => agregar());
                plan?.addEventListener('change', recalcular);
                cliente.addEventListener('change', mostrarMembresia);
                sucursal?.addEventListener('change', () => {
                    lineas.querySelectorAll('[data-campo=producto]').forEach(s => opciones(s));
                    recalcular();
                });

                viejas.forEach(v => agregar(v));
                mostrarMembresia();
                recalcular();
            })();
        </script>
    </x-slot>
</x-admin-layout>
