<x-admin-layout titulo="Hola, {{ strtok(Auth::user()->name, ' ') }}" icono="fa-house" modulo="resumen"
    subtitulo="Estas son las secciones a las que tienes acceso.">

    <div class="panel-card">
        <p style="margin:0; color:var(--text-gray);">
            Tu rol es <strong>{{ Auth::user()->role?->etiqueta }}</strong>. Usa el menú de la izquierda para entrar a tus secciones.
            Si necesitas acceso a algo más, pídelo al administrador de tu empresa.
        </p>
    </div>
</x-admin-layout>
