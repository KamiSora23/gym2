/*
 * Sitio público: navegación, animaciones y carrito.
 * El carrito vive en el navegador; al confirmar, el servidor recalcula precios y stock.
 */
document.addEventListener('DOMContentLoaded', function () {
    const CLAVE = 'gym_carrito';
    const moneda = () => (window.GYM_MONEDA || 'S/') + ' ';
    const fmt = n => moneda() + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const escapar = texto => String(texto).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    // Carrito anterior con productos de ejemplo: se descarta.
    localStorage.removeItem('gym_cart');

    let carrito = [];
    try {
        carrito = JSON.parse(localStorage.getItem(CLAVE)) || [];
        if (!Array.isArray(carrito)) carrito = [];
    } catch (e) {
        carrito = [];
    }

    // --- NAVBAR ---
    const navbar = document.getElementById('mainNav');
    if (navbar) {
        window.addEventListener('scroll', () => navbar.classList.toggle('scrolled', window.scrollY > 50));
    }

    // --- APARICIÓN AL HACER SCROLL ---
    const reveals = document.querySelectorAll('.reveal');
    const revelar = () => reveals.forEach(el => {
        if (el.getBoundingClientRect().top < window.innerHeight - 150) el.classList.add('active');
    });
    window.addEventListener('scroll', revelar);
    revelar();

    // --- CARRITO ---
    const guardar = () => {
        localStorage.setItem(CLAVE, JSON.stringify(carrito));
        const contador = document.getElementById('cartCount');
        if (contador) contador.textContent = carrito.reduce((total, i) => total + i.qty, 0);
        pintarCarrito();
        pintarCheckout();
    };

    const total = () => carrito.reduce((suma, i) => suma + i.precio * i.qty, 0);

    document.querySelectorAll('[data-agregar]').forEach(boton => {
        boton.addEventListener('click', () => {
            const d = boton.dataset;
            const id = `${d.tipo}-${d.ref}`;
            const stock = d.stock !== undefined ? Number(d.stock) : Infinity;

            // Solo una membresía por pedido: la nueva reemplaza a la anterior.
            if (d.tipo === 'plan') {
                carrito = carrito.filter(i => i.tipo !== 'plan');
            }

            const existente = carrito.find(i => i.id === id);

            if (existente) {
                if (existente.qty >= stock) {
                    aviso(`No hay más stock de ${d.nombre}.`, true);
                    return;
                }
                existente.qty++;
            } else {
                carrito.push({ id, tipo: d.tipo, ref: Number(d.ref), nombre: d.nombre, precio: Number(d.precio), img: d.img, stock, qty: 1 });
            }

            guardar();
            aviso(`${d.nombre} agregado al carrito`);
        });
    });

    window.cambiarCantidad = (id, delta) => {
        const item = carrito.find(i => i.id === id);
        if (!item) return;
        if (item.tipo === 'plan' && delta > 0) return;
        if (delta > 0 && item.qty >= (item.stock ?? Infinity)) {
            aviso('No hay más stock disponible.', true);
            return;
        }
        item.qty += delta;
        if (item.qty < 1) carrito = carrito.filter(i => i.id !== id);
        guardar();
    };

    window.quitarDelCarrito = id => {
        carrito = carrito.filter(i => i.id !== id);
        guardar();
    };

    // Página del carrito
    function pintarCarrito() {
        const contenedor = document.getElementById('cartContainer');
        if (!contenedor) return;

        if (carrito.length === 0) {
            contenedor.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bag-x display-1 text-muted mb-4"></i>
                    <h3>Tu carrito está vacío</h3>
                    <a href="/shop" class="btn btn-primary mt-3">Ir a la tienda</a>
                    <a href="/#planes" class="btn btn-outline-dark mt-3 ms-2">Ver planes</a>
                </div>`;
            return;
        }

        const filas = carrito.map(i => `
            <div class="cart-item-premium reveal active">
                <img src="${escapar(i.img || '')}" class="cart-item-img" alt="">
                <div class="flex-grow-1">
                    <h6 class="fw-bold mb-0">${escapar(i.nombre)}</h6>
                    <small class="text-muted">${fmt(i.precio)}${i.tipo === 'plan' ? ' · se activa al pagar en recepción' : ''}</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-light btn-sm rounded-pill" onclick="cambiarCantidad('${i.id}', -1)">-</button>
                    <span class="fw-bold">${i.qty}</span>
                    <button class="btn btn-light btn-sm rounded-pill" onclick="cambiarCantidad('${i.id}', 1)" ${i.tipo === 'plan' ? 'disabled' : ''}>+</button>
                </div>
                <div class="text-end" style="min-width: 90px;"><span class="fw-bold">${fmt(i.precio * i.qty)}</span></div>
                <button class="btn text-danger" onclick="quitarDelCarrito('${i.id}')" title="Quitar"><i class="bi bi-trash"></i></button>
            </div>`).join('');

        contenedor.innerHTML = `
            <div class="row g-4">
                <div class="col-lg-8">${filas}</div>
                <div class="col-lg-4">
                    <div class="premium-card p-4 sticky-top" style="top: 100px;">
                        <h5 class="fw-bold mb-4">Resumen</h5>
                        <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>${fmt(total())}</span></div>
                        <div class="d-flex justify-content-between mb-4"><span class="fw-bold fs-5">Total</span><span class="fw-bold fs-5 text-primary">${fmt(total())}</span></div>
                        <a href="/checkout" class="btn btn-primary w-100 py-3">Continuar con el pedido</a>
                        <p class="small text-muted text-center mt-3 mb-0"><i class="bi bi-shop me-1"></i> Pagas y recoges en recepción.</p>
                    </div>
                </div>
            </div>`;
    }

    // Resumen del checkout
    function pintarCheckout() {
        const lista = document.getElementById('checkoutItems');
        if (!lista) return;

        lista.innerHTML = carrito.length
            ? carrito.map(i => `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="small"><div class="fw-bold">${escapar(i.nombre)}</div><div class="text-muted">Cant: ${i.qty}</div></div>
                    <span class="small fw-bold">${fmt(i.precio * i.qty)}</span>
                </div>`).join('')
            : '<p class="text-muted small mb-0">Tu carrito está vacío.</p>';

        document.querySelectorAll('[data-checkout-total]').forEach(el => el.textContent = fmt(total()));

        const campo = document.getElementById('pedidoItems');
        if (campo) campo.value = JSON.stringify(carrito.map(i => ({ tipo: i.tipo, ref: i.ref, qty: i.qty })));

        const boton = document.getElementById('confirmarPedido');
        if (boton) boton.disabled = carrito.length === 0;
    }

    // Tras confirmar un pedido, el servidor indica que se vacíe el carrito.
    if (document.body.dataset.pedidoOk) {
        carrito = [];
    }

    function aviso(mensaje, error = false) {
        const toast = document.createElement('div');
        toast.className = 'position-fixed bottom-0 end-0 m-4 bg-dark text-white px-4 py-3 rounded-pill shadow-lg';
        toast.style.zIndex = '9999';
        toast.innerHTML = `<i class="bi ${error ? 'bi-exclamation-circle text-warning' : 'bi-check-circle text-primary'} me-2"></i> ${escapar(mensaje)}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2800);
    }

    // --- FILTRO DE CATEGORÍAS (tienda) ---
    const botonesCategoria = document.querySelectorAll('.category-btn');
    botonesCategoria.forEach(boton => boton.addEventListener('click', () => {
        botonesCategoria.forEach(b => b.classList.remove('active'));
        boton.classList.add('active');
        const categoria = boton.dataset.category;
        document.querySelectorAll('.product-item').forEach(p => {
            p.style.display = categoria === 'all' || p.dataset.category === categoria ? '' : 'none';
        });
    }));

    guardar();
});
