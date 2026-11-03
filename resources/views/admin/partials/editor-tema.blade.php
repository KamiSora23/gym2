{{--
    Editor de colores del panel con paletas predefinidas y vista previa en vivo.
    Parámetros: $c (Configuracion), $presets (array de Tema::PRESETS)
--}}
@php
    $campos = [
        'color_primario' => ['Color principal', 'Botones, menú activo y resaltados'],
        'color_secundario' => ['Color de acento', 'Detalles y degradados'],
        'color_sidebar' => ['Menú lateral', 'Fondo de la barra de navegación'],
        'color_fondo' => ['Fondo del panel', 'Color de fondo de las páginas'],
    ];
    $valor = function ($campo) use ($c) {
        $color = \App\Support\Tema::hex($c->{$campo}) ?? (\App\Models\Configuracion::VALORES_POR_DEFECTO[$campo] ?? '#ffffff');

        // El acento blanco era el antiguo valor por defecto: se muestra el acento por defecto.
        if ($campo === 'color_secundario' && \App\Support\Tema::luminancia($color) > 0.9) {
            $color = \App\Models\Configuracion::VALORES_POR_DEFECTO['color_secundario'];
        }

        return old($campo, $color);
    };
@endphp

<div class="theme-editor" data-theme-editor>
    <input type="hidden" name="tema" value="{{ old('tema', $c->tema) }}" data-tema-actual>

    <div>
        <div class="section-label">Paletas predefinidas</div>
        <div class="preset-grid">
            @foreach ($presets as $clave => $preset)
                <button type="button" class="preset {{ old('tema', $c->tema) === $clave ? 'selected' : '' }}" data-preset="{{ $clave }}"
                    data-colores='@json(collect($preset)->except('nombre'))'>
                    <span class="preset-swatches">
                        <i style="background: {{ $preset['color_sidebar'] }}"></i>
                        <i style="background: {{ $preset['color_primario'] }}"></i>
                        <i style="background: {{ $preset['color_secundario'] }}"></i>
                        <i style="background: {{ $preset['color_fondo'] }}"></i>
                    </span>
                    <span>{{ $preset['nombre'] }}</span>
                </button>
            @endforeach
        </div>

        <div class="section-label" style="margin-top:1.25rem;">Tonos personalizados</div>
        <div class="color-fields">
            @foreach ($campos as $campo => [$etiqueta, $ayuda])
                <label class="color-field">
                    <input type="color" name="{{ $campo }}" value="{{ $valor($campo) }}" data-color="{{ $campo }}">
                    <span>
                        <strong>{{ $etiqueta }}</strong>
                        <small>{{ $ayuda }}</small>
                    </span>
                    <input type="text" value="{{ $valor($campo) }}" maxlength="7" class="hex-input" data-hex="{{ $campo }}" aria-label="Código {{ $etiqueta }}">
                </label>
            @endforeach
        </div>
        <p class="muted" style="margin:.75rem 0 0;"><i class="fas fa-circle-info"></i> El color del texto se ajusta solo para que siempre sea legible sobre cada fondo.</p>
    </div>

    <div>
        <div class="section-label">Vista previa</div>
        <div class="theme-preview" data-preview>
            <div class="tp-sidebar">
                <div class="tp-logo"></div>
                <div class="tp-item active"><i class="fas fa-chart-line"></i> Resumen</div>
                <div class="tp-item"><i class="fas fa-cart-shopping"></i> Ventas</div>
                <div class="tp-item"><i class="fas fa-users"></i> Clientes</div>
                <div class="tp-item"><i class="fas fa-dumbbell"></i> Entrenamiento</div>
            </div>
            <div class="tp-main">
                <div class="tp-title"><span class="tp-icon"><i class="fas fa-chart-line"></i></span> Resumen</div>
                <div class="tp-cards">
                    <div class="tp-card"><small>VENTAS HOY</small><b>{{ moneda() }} 1,250</b></div>
                    <div class="tp-card accent"><small>CLIENTES</small><b>148</b></div>
                </div>
                <div class="tp-hero">Resumen operativo</div>
                <div style="display:flex; gap:.4rem;">
                    <span class="tp-btn">Nueva venta</span>
                    <span class="tp-btn ghost">Cancelar</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const editor = document.currentScript.previousElementSibling;
        const preview = editor.querySelector('[data-preview]');
        const temaActual = editor.querySelector('[data-tema-actual]');

        const lum = hex => {
            const c = [1, 3, 5].map(i => parseInt(hex.substr(i, 2), 16) / 255)
                .map(v => v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4);
            return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
        };
        const texto = hex => (1.05 / (lum(hex) + 0.05)) >= ((lum(hex) + 0.05) / 0.0625) ? '#ffffff' : '#111827';

        function pintar() {
            const v = campo => editor.querySelector(`[data-color="${campo}"]`).value;
            const sidebar = v('color_sidebar');
            const oscuro = lum(sidebar) < 0.45;
            preview.style.setProperty('--p', v('color_primario'));
            preview.style.setProperty('--p-text', texto(v('color_primario')));
            preview.style.setProperty('--a', v('color_secundario'));
            preview.style.setProperty('--s', sidebar);
            preview.style.setProperty('--s-text', oscuro ? '#cbd5e1' : '#475569');
            preview.style.setProperty('--bg', v('color_fondo'));
        }

        editor.querySelectorAll('[data-color]').forEach(input => {
            const hex = editor.querySelector(`[data-hex="${input.dataset.color}"]`);
            input.addEventListener('input', () => {
                hex.value = input.value;
                temaActual.value = '';
                editor.querySelectorAll('.preset').forEach(p => p.classList.remove('selected'));
                pintar();
            });
            hex.addEventListener('input', () => {
                if (/^#[0-9a-fA-F]{6}$/.test(hex.value)) {
                    input.value = hex.value.toLowerCase();
                    input.dispatchEvent(new Event('input'));
                }
            });
        });

        editor.querySelectorAll('[data-preset]').forEach(btn => {
            btn.addEventListener('click', () => {
                const colores = JSON.parse(btn.dataset.colores);
                Object.entries(colores).forEach(([campo, color]) => {
                    const input = editor.querySelector(`[data-color="${campo}"]`);
                    if (input) {
                        input.value = color;
                        editor.querySelector(`[data-hex="${campo}"]`).value = color;
                    }
                });
                editor.querySelectorAll('.preset').forEach(p => p.classList.toggle('selected', p === btn));
                temaActual.value = btn.dataset.preset;
                pintar();
            });
        });

        pintar();
    })();
</script>
