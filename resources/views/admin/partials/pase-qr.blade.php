{{-- Pase de acceso QR del socio en su ficha: imprimir carnet o generar uno nuevo. Requiere $cliente y $puedeEditar. --}}
@php $codigo = $cliente->codigoAcceso(); @endphp

<div class="form-card" style="display:flex; flex-wrap:wrap; gap:1.5rem; align-items:center;">
    <div id="carnet-qr" style="display:flex; gap:1rem; align-items:center; padding:1rem; border:1px dashed #cbd5e1; border-radius:14px; background:#fff;">
        <div style="width:150px; height:150px;">{!! \App\Support\CodigoQr::svg($codigo, 150) !!}</div>
        <div>
            <img src="{{ $config->logo_url }}" alt="" style="height:30px; width:auto; margin-bottom:.4rem;">
            <div style="font-weight:800; font-size:1.05rem;">{{ $cliente->nombre }}</div>
            @unless (str_starts_with((string) $cliente->dni, 'TEMP-'))<div class="muted">DNI {{ $cliente->dni }}</div>@endunless
            <div style="font-family:monospace; letter-spacing:.08em; margin-top:.35rem; font-size:.85rem;">{{ $codigo }}</div>
            <div class="muted" style="font-size:.75rem; margin-top:.25rem;">{{ $config->nombre_gym }} · Pase de acceso</div>
        </div>
    </div>
    <div style="flex:1; min-width:220px;">
        <h3 style="margin-top:0;"><i class="fas fa-qrcode"></i> Pase de acceso QR</h3>
        <p class="muted">El socio lo ve en «Mi cuenta» desde su celular. Si no usa la app, imprímele este carnet.</p>
        <div style="display:flex; flex-wrap:wrap; gap:.5rem;">
            <button type="button" class="btn btn-light" onclick="imprimirCarnet()"><i class="fas fa-print"></i> Imprimir carnet</button>
            @if ($puedeEditar)
                <form method="POST" action="{{ route('clientes.codigo', $cliente) }}" data-confirm="¿Generar un nuevo pase? El código actual dejará de funcionar (úsalo si el socio lo perdió o lo compartió).">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-light"><i class="fas fa-rotate"></i> Generar nuevo código</button>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
    // Abre una ventana solo con el carnet y la imprime (sin plantillas en texto).
    function imprimirCarnet() {
        const ventana = window.open('', '_blank', 'width=520,height=420');
        const doc = ventana.document;
        doc.title = 'Carnet de acceso';
        const estilo = doc.createElement('style');
        estilo.textContent = 'body{font-family:Inter,Arial,sans-serif;margin:24px}.muted{color:#64748b}';
        doc.head.appendChild(estilo);
        doc.body.appendChild(doc.importNode(document.getElementById('carnet-qr'), true));
        setTimeout(() => { ventana.focus(); ventana.print(); }, 300);
    }
</script>
