{{-- Lector de pases QR: cámara del equipo o lector USB (escribe el código y pulsa Enter). --}}
<form method="POST" action="{{ route('asistencias.qr') }}" class="form-card" id="form-qr">
    @csrf
    <h3><i class="fas fa-qrcode"></i> Escanear pase QR</h3>
    <div style="display:flex; gap:.75rem; flex-wrap:wrap; align-items:end;">
        <div class="form-group" style="flex:1; min-width:220px; margin:0;">
            <label>Código del socio</label>
            <input type="text" name="codigo" id="codigo-qr" autocomplete="off" autofocus placeholder="Escanea con el lector o la cámara…" required>
        </div>
        <button type="button" class="btn btn-light" id="btn-camara"><i class="fas fa-camera"></i> Usar cámara</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-right-to-bracket"></i> Registrar</button>
    </div>
    <p class="muted" style="margin:.6rem 0 0;">Si el socio ya está dentro, el escaneo registra su salida.</p>
    <div id="camara-qr" style="max-width:360px; margin-top:.75rem; display:none;"></div>
    <p id="camara-error" class="muted" style="display:none; color:#b91c1c;"></p>
</form>

<script>
    (function () {
        const boton = document.getElementById('btn-camara');
        const zona = document.getElementById('camara-qr');
        const aviso = document.getElementById('camara-error');
        const campo = document.getElementById('codigo-qr');
        let lector = null;

        const fallo = texto => { aviso.textContent = texto; aviso.style.display = 'block'; };

        const iniciar = () => {
            zona.style.display = 'block';
            lector = new Html5Qrcode('camara-qr');
            lector.start({ facingMode: 'environment' }, { fps: 10, qrbox: 220 }, texto => {
                lector.stop().finally(() => {
                    campo.value = texto;
                    document.getElementById('form-qr').submit();
                });
            }).catch(() => fallo('No se pudo abrir la cámara. Revisa el permiso del navegador (la cámara solo funciona con https o en localhost).'));
        };

        boton.addEventListener('click', () => {
            aviso.style.display = 'none';

            if (lector) {
                lector.stop().catch(() => {});
                lector = null;
                zona.style.display = 'none';
                return;
            }

            if (window.Html5Qrcode) return iniciar();

            const script = document.createElement('script');
            script.src = 'https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js';
            script.onload = iniciar;
            script.onerror = () => fallo('No se pudo cargar el lector de cámara. Usa un lector USB o escribe el código.');
            document.head.appendChild(script);
        });
    })();
</script>
