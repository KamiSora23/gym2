<x-admin-layout titulo="Configuración de la plataforma" icono="fa-sliders" modulo="plataforma-config"
    subtitulo="Identidad y colores del panel de super administración. No afecta a las empresas: cada una tiene su propia configuración.">

    @if ($errors->any())
        @include('admin.partials.errores')
        <div style="height:1rem;"></div>
    @endif

    <form method="POST" action="{{ route('plataforma.configuracion.store') }}" enctype="multipart/form-data">
        @csrf

        <section class="settings-section">
            <header>
                <span class="icon"><i class="fas fa-id-badge"></i></span>
                <div><h3>Identidad de la plataforma</h3><p>Nombre y logo que ven los super administradores.</p></div>
            </header>
            <div class="form-grid" style="grid-template-columns: repeat(3, minmax(0,1fr));">
                <div class="form-group"><label>Nombre de la plataforma *</label><input type="text" name="nombre_gym" value="{{ old('nombre_gym', $plataforma->nombre_gym) }}" required></div>
                <div class="form-group"><label>Correo de soporte</label><input type="email" name="email" value="{{ old('email', $plataforma->email) }}"></div>
                <div class="form-group"><label>Teléfono de soporte</label><input type="text" name="telefono" value="{{ old('telefono', $plataforma->telefono) }}"></div>
            </div>
            <div class="upload-box" style="margin-top:1rem; max-width:520px;">
                <img src="{{ $plataforma->logo_url }}" alt="Logo de la plataforma">
                <div><input type="file" name="logo" accept="image/*" style="font-size:.85rem;"><p class="muted" style="margin:.35rem 0 0;">PNG, JPG, WEBP o SVG · máx. 2 MB</p></div>
            </div>
        </section>

        <section class="settings-section">
            <header>
                <span class="icon"><i class="fas fa-palette"></i></span>
                <div><h3>Colores del panel de plataforma</h3><p>Usa un tema distinto al de las empresas para saber siempre en qué modo estás.</p></div>
            </header>
            @include('admin.partials.editor-tema', ['c' => $plataforma, 'presets' => $presets])
        </section>

        <div class="settings-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar configuración</button>
        </div>
    </form>
</x-admin-layout>
