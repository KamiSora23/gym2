{{-- Colores y moneda del sitio público según la configuración de la empresa. --}}
<script>window.GYM_MONEDA = @json(moneda());</script>
<style>
    :root { {!! \App\Support\Tema::web($config) !!} }
    .btn-primary, .btn-primary:hover, .btn-primary:focus { color: var(--primary-text) !important; border-color: var(--primary) !important; }
    .btn-primary:hover { background-color: var(--primary-hover) !important; }
    .bg-primary { background-color: var(--primary) !important; color: var(--primary-text) !important; }
    .badge.bg-danger { background-color: var(--primary) !important; color: var(--primary-text) !important; }
</style>
