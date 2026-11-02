<x-admin-layout titulo="Configuración" icono="fa-gear" modulo="configuracion"
    subtitulo="Identidad, colores, contacto y horarios de {{ \App\Support\Tenant::empresa()?->nombre }}.">

    <x-slot name="acciones">
        <a href="{{ url('/') }}" target="_blank" class="btn btn-light"><i class="fas fa-arrow-up-right-from-square"></i> Ver sitio web</a>
    </x-slot>

    @php
        $secciones = [
            'identidad' => ['fa-id-badge', 'Identidad'],
            'colores' => ['fa-palette', 'Colores del panel'],
            'sucursales' => ['fa-store', 'Colores por sucursal'],
            'web' => ['fa-globe', 'Sitio web'],
            'contacto' => ['fa-address-book', 'Contacto y horarios'],
            'redes' => ['fa-share-nodes', 'Redes sociales'],
        ];
        $webPrimario = old('web_color_primario', $c->web_color_primario ?: '#ffc107');
        $webOscuro = old('web_color_oscuro', $c->web_color_oscuro ?: '#0f172a');
    @endphp

    @if ($errors->any())
        @include('admin.partials.errores')
        <div style="height:1rem;"></div>
    @endif

    <form method="POST" action="{{ route('configuracion.store') }}" enctype="multipart/form-data" id="form-config">
        @csrf
        <input type="hidden" name="_seccion" value="identidad" data-seccion-actual>

        <div class="settings-layout">
            <nav class="settings-nav">
                @foreach ($secciones as $id => [$icono, $texto])
                    <a href="#{{ $id }}" data-seccion="{{ $id }}" class="{{ $loop->first ? 'active' : '' }}"><i class="fas {{ $icono }}"></i> {{ $texto }}</a>
                @endforeach
            </nav>

            <div>
                {{-- IDENTIDAD --}}
                <section class="settings-section" id="identidad">
                    <header>
                        <span class="icon"><i class="fas fa-id-badge"></i></span>
                        <div><h3>Identidad</h3><p>Nombre, slogan y logo que ven tu personal y tus clientes.</p></div>
                    </header>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre_gym">Nombre del gimnasio *</label>
                            <input id="nombre_gym" type="text" name="nombre_gym" required value="{{ old('nombre_gym', $c->nombre_gym) }}">
                        </div>
                        <div class="form-group">
                            <label for="slogan">Slogan</label>
                            <input id="slogan" type="text" name="slogan" value="{{ old('slogan', $c->slogan) }}" placeholder="Tu mejor versión empieza aquí">
                        </div>
                    </div>
                    <div class="form-grid" style="margin-top:1rem;">
                        <div>
                            <div class="section-label">Logo</div>
                            <div class="upload-box">
                                <img src="{{ $c->logo_url }}" alt="Logo actual" data-preview-img="logo">
                                <div style="flex:1; min-width:0;">
                                    <input type="file" name="logo" accept="image/*" data-preview-input="logo" style="font-size:.85rem; max-width:100%;">
                                    <p class="muted" style="margin:.35rem 0 0;">PNG, JPG, WEBP o SVG · máx. 2 MB</p>
                                    @if ($c->logo)
                                        <label class="form-check" style="margin-top:.4rem;"><input type="checkbox" name="quitar_logo" value="1"> Quitar logo actual</label>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="section-label">Favicon (ícono de la pestaña)</div>
                            <div class="upload-box">
                                <img src="{{ $c->favicon_url }}" alt="Favicon actual" style="width:48px; height:48px;" data-preview-img="favicon">
                                <div style="flex:1; min-width:0;">
                                    <input type="file" name="favicon" accept=".png,.ico,.svg" data-preview-input="favicon" style="font-size:.85rem; max-width:100%;">
                                    <p class="muted" style="margin:.35rem 0 0;">PNG, ICO o SVG · máx. 512 KB</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- COLORES DEL PANEL --}}
                <section class="settings-section" id="colores">
                    <header>
                        <span class="icon"><i class="fas fa-palette"></i></span>
                        <div><h3>Colores del panel</h3><p>Elige una paleta o ajusta cada tono a la marca de tu empresa. La vista previa cambia al instante.</p></div>
                    </header>
                    @include('admin.partials.editor-tema', ['c' => $c, 'presets' => $presets])
                </section>

                {{-- COLORES POR SUCURSAL --}}
                <section class="settings-section" id="sucursales">
                    <header>
                        <span class="icon"><i class="fas fa-store"></i></span>
                        <div><h3>Colores por sucursal</h3><p>Opcional: cada sede puede tener su propio color principal y de menú. Si no, usa los de la empresa.</p></div>
                    </header>
                    <div class="branch-colors">
                        @forelse ($sucursales as $s)
                            @php $propio = (bool) ($s->color_primario || $s->color_sidebar); @endphp
                            <div class="branch-row" data-branch>
                                <div>
                                    <strong><span class="branch-dot" style="background: {{ $s->color_primario ?? $c->color_primario }}"></span>{{ $s->nombre }}</strong>
                                    <div class="muted">{{ $propio ? 'Usa colores propios' : 'Usa los colores de la empresa' }}</div>
                                </div>
                                <label class="form-check">
                                    <input type="hidden" name="sucursal_colores[{{ $s->id_sucursal }}][propio]" value="0">
                                    <input type="checkbox" name="sucursal_colores[{{ $s->id_sucursal }}][propio]" value="1" @checked($propio) data-branch-toggle> Propios
                                </label>
                                <input type="color" name="sucursal_colores[{{ $s->id_sucursal }}][color_primario]" value="{{ $s->color_primario ?? $c->color_primario ?? '#ff4d4f' }}" title="Color principal" @disabled(! $propio)>
                                <input type="color" name="sucursal_colores[{{ $s->id_sucursal }}][color_sidebar]" value="{{ $s->color_sidebar ?? $c->color_sidebar ?? '#111827' }}" title="Color del menú" @disabled(! $propio)>
                            </div>
                        @empty
                            <p class="muted">No hay sucursales.</p>
                        @endforelse
                    </div>
                    <p class="muted" style="margin:.75rem 0 0;"><i class="fas fa-circle-info"></i> El personal de cada sucursal verá su panel con esos colores. El administrador los ve al elegir esa sucursal en la barra superior.</p>
                </section>

                {{-- SITIO WEB --}}
                <section class="settings-section" id="web">
                    <header>
                        <span class="icon"><i class="fas fa-globe"></i></span>
                        <div><h3>Sitio web público</h3><p>Colores de la página que ven tus visitantes: inicio, tienda, login y registro.</p></div>
                    </header>
                    <div class="theme-editor" style="grid-template-columns: minmax(0,1fr) minmax(260px,1fr);">
                        <div class="color-fields">
                            <label class="form-check" style="padding:.75rem; border:1px solid var(--line); border-radius:12px;">
                                <input type="checkbox" name="web_usa_colores_panel" value="1" @checked(old('web_usa_colores_panel', $c->web_usa_colores_panel)) data-web-sync>
                                Usar los mismos colores del panel
                            </label>
                            <label class="color-field">
                                <input type="color" name="web_color_primario" value="{{ $webPrimario }}" data-web="primario">
                                <span><strong>Color destacado</strong><small>Botones, títulos y resaltados</small></span>
                            </label>
                            <label class="color-field">
                                <input type="color" name="web_color_oscuro" value="{{ $webOscuro }}" data-web="oscuro">
                                <span><strong>Color oscuro</strong><small>Menú superior, fondos y pie de página</small></span>
                            </label>
                        </div>
                        <div>
                            <div class="section-label">Vista previa</div>
                            <div class="web-preview" data-web-preview>
                                <div class="wp-nav"><span><b>{{ $c->nombrePartido()[0] }}</b> {{ $c->nombrePartido()[1] }}</span><span style="opacity:.7; font-weight:500;">Planes · Tienda</span></div>
                                <div class="wp-hero">
                                    <h4>DOMINA TU <span>CUERPO</span></h4>
                                    <span class="wp-btn">Empezar ahora</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- CONTACTO Y HORARIOS --}}
                <section class="settings-section" id="contacto">
                    <header>
                        <span class="icon"><i class="fas fa-address-book"></i></span>
                        <div><h3>Contacto y horarios</h3><p>Se muestran en la sección de contacto del sitio web.</p></div>
                    </header>
                    <div class="form-grid" style="grid-template-columns: repeat(3, minmax(0,1fr));">
                        <div class="form-group"><label>Correo</label><input type="email" name="email" value="{{ old('email', $c->email) }}"></div>
                        <div class="form-group"><label>Teléfono</label><input type="text" name="telefono" value="{{ old('telefono', $c->telefono) }}"></div>
                        <div class="form-group"><label>Dirección</label><input type="text" name="direccion" value="{{ old('direccion', $c->direccion) }}"></div>
                    </div>
                    <div class="section-label" style="margin-top:1.25rem;">Horario de atención</div>
                    <div class="form-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px,1fr));">
                        @foreach (\App\Models\Configuracion::DIAS as $clave => $dia)
                            <div class="form-group">
                                <label>{{ $dia }}</label>
                                <input type="text" name="horario_{{ $clave }}" value="{{ old('horario_'.$clave, $c->{'horario_'.$clave}) }}" placeholder="6:00 AM - 10:00 PM / Cerrado">
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- REDES --}}
                <section class="settings-section" id="redes">
                    <header>
                        <span class="icon"><i class="fas fa-share-nodes"></i></span>
                        <div><h3>Redes sociales</h3><p>Enlaces completos a tus perfiles.</p></div>
                    </header>
                    <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px,1fr));">
                        @foreach (['facebook' => 'fa-facebook', 'instagram' => 'fa-instagram', 'tiktok' => 'fa-tiktok'] as $red => $iconoRed)
                            <div class="form-group">
                                <label><i class="fab {{ $iconoRed }}"></i> {{ ucfirst($red) }}</label>
                                <input type="url" name="{{ $red }}" value="{{ old($red, $c->{$red}) }}" placeholder="https://{{ $red }}.com/tugym">
                            </div>
                        @endforeach
                    </div>
                </section>

                <div class="settings-actions">
                    <a href="{{ route('configuracion.index') }}" class="btn btn-light">Descartar cambios</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar configuración</button>
                </div>
            </div>
        </div>
    </form>

    <x-slot name="scripts">
        <script>
            (function () {
                const form = document.getElementById('form-config');

                // Menú de secciones: resalta la sección visible y la recuerda al guardar.
                const enlaces = form.querySelectorAll('[data-seccion]');
                const actual = form.querySelector('[data-seccion-actual]');
                const marcar = id => {
                    enlaces.forEach(a => a.classList.toggle('active', a.dataset.seccion === id));
                    actual.value = id;
                };
                const observador = new IntersectionObserver(entradas => {
                    entradas.filter(e => e.isIntersecting).forEach(e => marcar(e.target.id));
                }, { rootMargin: '-30% 0px -60% 0px' });
                form.querySelectorAll('.settings-section').forEach(s => observador.observe(s));
                if (location.hash) marcar(location.hash.slice(1));

                // Vista previa de imágenes antes de subirlas.
                form.querySelectorAll('[data-preview-input]').forEach(input => {
                    input.addEventListener('change', () => {
                        const archivo = input.files[0];
                        if (archivo) form.querySelector(`[data-preview-img="${input.dataset.previewInput}"]`).src = URL.createObjectURL(archivo);
                    });
                });

                // Colores propios por sucursal.
                form.querySelectorAll('[data-branch]').forEach(fila => {
                    const toggle = fila.querySelector('[data-branch-toggle]');
                    const colores = fila.querySelectorAll('input[type=color]');
                    toggle.addEventListener('change', () => colores.forEach(c => c.disabled = !toggle.checked));
                    colores[0].addEventListener('input', () => fila.querySelector('.branch-dot').style.background = colores[0].value);
                });

                // Vista previa del sitio web.
                const lum = hex => {
                    const c = [1, 3, 5].map(i => parseInt(hex.substr(i, 2), 16) / 255).map(v => v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4);
                    return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
                };
                const web = form.querySelector('[data-web-preview]');
                const sync = form.querySelector('[data-web-sync]');
                const webPrimario = form.querySelector('[data-web="primario"]');
                const webOscuro = form.querySelector('[data-web="oscuro"]');

                function pintarWeb() {
                    const primario = sync.checked ? form.querySelector('[data-color="color_primario"]').value : webPrimario.value;
                    let oscuro = sync.checked ? form.querySelector('[data-color="color_sidebar"]').value : webOscuro.value;
                    if (lum(oscuro) > 0.45) oscuro = '#0f172a';
                    web.style.setProperty('--wp', primario);
                    web.style.setProperty('--wd', oscuro);
                    web.style.setProperty('--wp-text', (1.05 / (lum(primario) + 0.05)) >= ((lum(primario) + 0.05) / 0.0625) ? '#ffffff' : '#111827');
                    [webPrimario, webOscuro].forEach(i => i.closest('.color-field').style.opacity = sync.checked ? .45 : 1);
                }

                [sync, webPrimario, webOscuro].forEach(el => el.addEventListener('input', pintarWeb));
                sync.addEventListener('change', pintarWeb);
                form.querySelectorAll('[data-color]').forEach(el => el.addEventListener('input', pintarWeb));
                form.querySelectorAll('[data-preset]').forEach(el => el.addEventListener('click', () => setTimeout(pintarWeb)));
                pintarWeb();
            })();
        </script>
    </x-slot>
</x-admin-layout>
