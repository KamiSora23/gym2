<x-admin-layout titulo="Configuración" icono="fa-gear" modulo="configuracion"
    subtitulo="Identidad, contacto y horarios de la empresa. Se muestran en el panel y en el sitio público.">

    <x-slot name="acciones">
        <button type="submit" form="form-config" class="btn btn-primary"><i class="fas fa-save"></i> Guardar cambios</button>
    </x-slot>

    <form method="POST" action="{{ route('configuracion.store') }}" enctype="multipart/form-data" id="form-config">
            @csrf

            

            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-circle-exclamation"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="config-grid">

                <div class="config-card">
                    <h3><i class="fas fa-sliders"></i> Información general</h3>

                    <div class="form-group">
                        <label for="nombre_gym">Nombre del gimnasio</label>
                        <input id="nombre_gym" type="text" name="nombre_gym" required
                            value="{{ old('nombre_gym', $config->nombre_gym) }}" placeholder="GYM IMPULSO">
                    </div>

                    <div class="form-group">
                        <label for="slogan">Slogan</label>
                        <input id="slogan" type="text" name="slogan"
                            value="{{ old('slogan', $config->slogan) }}" placeholder="Tu mejor versión empieza aquí">
                    </div>

                    <div class="form-group">
                        <label for="email">Correo de contacto</label>
                        <input id="email" type="email" name="email"
                            value="{{ old('email', $config->email) }}" placeholder="info@gym.com">
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input id="telefono" type="text" name="telefono"
                            value="{{ old('telefono', $config->telefono) }}" placeholder="999 999 999">
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input id="direccion" type="text" name="direccion"
                            value="{{ old('direccion', $config->direccion) }}" placeholder="Av. Principal 123">
                    </div>
                </div>

                <div class="config-card">
                    <h3><i class="fas fa-clock"></i> Horario de atención</h3>

                    <div class="horario-grid">
                        @foreach (\App\Models\Configuracion::DIAS as $clave => $dia)
                            <span>{{ $dia }}</span>
                            <div class="form-group" style="margin:0;">
                                <input type="text" name="horario_{{ $clave }}"
                                    value="{{ old('horario_'.$clave, $config->{'horario_'.$clave}) }}"
                                    placeholder="6:00 AM - 10:00 PM / Cerrado">
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="config-card">
                    <h3><i class="fas fa-image"></i> Branding</h3>

                    <div class="preview-box">
                        <img src="{{ $config->logo_url }}" alt="Logo actual">
                        <div style="font-size:.85rem; color:var(--text-gray);">Logo actual<br><small>PNG, JPG, WEBP o SVG · máx. 2 MB</small></div>
                    </div>

                    <div class="form-group">
                        <label for="logo">Cambiar logo</label>
                        <input id="logo" type="file" name="logo" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="favicon">Favicon <small style="font-weight:400;">(PNG, ICO o SVG)</small></label>
                        <input id="favicon" type="file" name="favicon" accept=".png,.ico,.svg">
                    </div>
                </div>

                <div class="config-card">
                    <h3><i class="fas fa-palette"></i> Colores</h3>

                    @foreach (['color_primario' => 'Color principal', 'color_sidebar' => 'Color del menú lateral', 'color_secundario' => 'Color secundario'] as $campo => $etiqueta)
                        <div class="form-group">
                            <div class="color-row">
                                <label for="{{ $campo }}">{{ $etiqueta }}</label>
                                <input id="{{ $campo }}" type="color" name="{{ $campo }}"
                                    value="{{ old($campo, $config->{$campo}) }}">
                            </div>
                        </div>
                    @endforeach

                    <p style="font-size:.8rem; color:var(--text-soft); margin:0;">Los colores se aplican al panel al guardar.</p>
                </div>

                <div class="config-card full-width">
                    <h3><i class="fas fa-share-nodes"></i> Redes sociales</h3>

                    <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                        @foreach (['facebook' => 'fa-facebook', 'instagram' => 'fa-instagram', 'tiktok' => 'fa-tiktok'] as $red => $icono)
                            <div class="form-group">
                                <label for="{{ $red }}"><i class="fab {{ $icono }}"></i> {{ ucfirst($red) }}</label>
                                <input id="{{ $red }}" type="url" name="{{ $red }}"
                                    value="{{ old($red, $config->{$red}) }}" placeholder="https://{{ $red }}.com/tugym">
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar cambios</button>
            </div>
        </form>
</x-admin-layout>
