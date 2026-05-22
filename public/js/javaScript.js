document.addEventListener('DOMContentLoaded', function() {
    // --- CONFIGURATION & DATA ---
    const products = [
        { id: 1, name: 'Guantes Pro Grip', price: 25.99, img: 'img/img1.jpg', cat: 'Accesorios', badge: 'Nuevo', rating: 5 },
        { id: 2, name: 'Shaker Elite 700ml', price: 12.50, img: 'img/img2.jpg', cat: 'Accesorios', badge: 'Pop', rating: 4 },
        { id: 3, name: 'Cinturón Cuero Pro', price: 45.00, img: 'img/img3.jpg', cat: 'Pesas', badge: 'Elite', rating: 5 },
        { id: 4, name: 'Mancuernas Hex 10kg', price: 89.99, img: 'img/img1.jpg', cat: 'Pesas', badge: 'Sale', rating: 4 },
        { id: 5, name: 'Proteína Whey Isolate', price: 55.00, img: 'img/img2.jpg', cat: 'Suplementos', badge: 'Premium', rating: 5 },
        { id: 6, name: 'Creatina Monohidrato', price: 35.00, img: 'img/img3.jpg', cat: 'Suplementos', badge: 'Pure', rating: 5 },
        { id: 7, name: 'Banda Resistencia X', price: 15.00, img: 'img/img1.jpg', cat: 'Accesorios', badge: '', rating: 4 },
        { id: 8, name: 'Mat Yoga Eco-Flex', price: 29.99, img: 'img/img2.jpg', cat: 'Accesorios', badge: 'Eco', rating: 5 },
        { id: 9, name: 'Pre-Workout Blast', price: 3.50, img: 'img/img3.jpg', cat: 'Suplementos', badge: 'Focus', rating: 4 },
        { id: 10, name: 'Soga Pro Speed', price: 10.00, img: 'img/img1.jpg', cat: 'Cardio', badge: 'Fast', rating: 5 }
    ];

    let cart = JSON.parse(localStorage.getItem('gym_cart')) || [];

    // --- NAVBAR SCROLL ---
    const navbar = document.getElementById('mainNav');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    // --- SCROLL REVEAL ---
    const reveals = document.querySelectorAll('.reveal');
    const revealOnScroll = () => {
        reveals.forEach(el => {
            const windowHeight = window.innerHeight;
            const elementTop = el.getBoundingClientRect().top;
            const elementVisible = 150;
            if (elementTop < windowHeight - elementVisible) {
                el.classList.add('active');
            }
        });
    };
    window.addEventListener('scroll', revealOnScroll);
    revealOnScroll(); // Initial check

    // --- PLAN DETAILS TOGGLE ---
    window.togglePlanDetails = (plan) => {
        const el = document.getElementById(`details-${plan}`);
        if (el) el.classList.toggle('show');
    };

    // --- SHOP RENDERING ---
    const productsGrid = document.getElementById('productsGrid');
    const indexProductsGrid = document.getElementById('indexProductsGrid');

    const createProductCard = (p) => `
        <div class="col-md-6 col-lg-3 reveal">
            <div class="premium-card product-card p-0 position-relative">
                ${p.badge ? `<span class="position-absolute top-0 start-0 m-3 badge bg-primary text-dark fw-bold rounded-pill z-3" style="font-size: 10px; letter-spacing: 0.5px;">${p.badge}</span>` : ''}
                <div class="product-img-container position-relative overflow-hidden">
                    <img src="${p.img}" alt="${p.name}" class="img-fluid">
                    <div class="product-actions position-absolute w-100 h-100 top-0 start-0 d-flex align-items-center justify-content-center opacity-0 transition-all bg-dark bg-opacity-25">
                         <button class="btn btn-primary rounded-pill px-4 py-2 fw-bold" onclick="addToCart(${p.id})">
                            <i class="bi bi-cart-plus me-2"></i>Añadir
                         </button>
                    </div>
                </div>
                <div class="p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 1px;">${p.cat}</small>
                        <div class="text-warning" style="font-size: 12px;">
                            ${Array(5).fill(0).map((_, i) => `<i class="bi bi-star${i < p.rating ? '-fill' : ''}"></i>`).join('')}
                        </div>
                    </div>
                    <h6 class="fw-bold mb-3" style="font-size: 0.95rem; letter-spacing: -0.3px;">${p.name}</h6>
                    <div class="d-flex align-items-center justify-content-between mt-auto">
                        <span class="fw-bold text-dark fs-5">$${p.price.toFixed(2)}</span>
                        <button class="btn btn-outline-dark-ui btn-sm rounded-circle d-lg-none" onclick="addToCart(${p.id})">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    if (productsGrid) {
        productsGrid.innerHTML = products.map(createProductCard).join('');
    }
    if (indexProductsGrid) {
        indexProductsGrid.innerHTML = products.slice(0, 4).map(createProductCard).join('');
    }

    // --- CART LOGIC ---
    window.addToCart = (id) => {
        const product = products.find(p => p.id === id);
        if (!product) {
            // Check for membership plans if not found in products
            const plans = {
                'basico': { id: 'p1', name: 'Plan Básico', price: 29.00, img: 'img/img1.jpg' },
                'fitness': { id: 'p2', name: 'Plan Fitness', price: 49.00, img: 'img/img2.jpg' },
                'elite': { id: 'p3', name: 'Plan Elite', price: 89.00, img: 'img/img3.jpg' }
            };
            // This is called from plan buttons
            return; 
        }
        
        const existing = cart.find(item => item.id === id);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({ ...product, qty: 1 });
        }
        updateCart();
        showToast(`¡${product.name} agregado!`);
    };

    // Special handler for plan buttons
    document.querySelectorAll('.add-to-cart-plan').forEach(btn => {
        btn.addEventListener('click', function() {
            const planType = this.dataset.plan;
            const plans = {
                'basico': { id: 'plan-b', name: 'Plan Básico', price: 29.00, img: 'img/img1.jpg' },
                'fitness': { id: 'plan-f', name: 'Plan Fitness', price: 49.00, img: 'img/img2.jpg' },
                'elite': { id: 'plan-e', name: 'Plan Elite', price: 89.00, img: 'img/img3.jpg' }
            };
            const plan = plans[planType];
            const existing = cart.find(item => item.id === plan.id);
            if (existing) {
                existing.qty++;
            } else {
                cart.push({ ...plan, qty: 1 });
            }
            updateCart();
            showToast(`¡${plan.name} agregado!`);
        });
    });

    const updateCart = () => {
        localStorage.setItem('gym_cart', JSON.stringify(cart));
        const count = document.getElementById('cartCount');
        if (count) {
            count.textContent = cart.reduce((acc, item) => acc + item.qty, 0);
        }
        renderCartPage();
    };

    const renderCartPage = () => {
        const container = document.getElementById('cartContainer');
        if (!container) return;

        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-bag-x display-1 text-muted mb-4"></i>
                    <h3>Tu carrito está vacío</h3>
                    <a href="/shop" class="btn btn-primary mt-3">Ir a la tienda</a>
                </div>
            `;
            return;
        }

        let total = 0;
        const itemsHtml = cart.map(item => {
            total += item.price * item.qty;
            return `
                <div class="cart-item-premium reveal active">
                    <img src="${item.img}" class="cart-item-img" alt="">
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-0">${item.name}</h6>
                        <small class="text-muted">$${item.price.toFixed(2)}</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-light btn-sm rounded-pill" onclick="changeQty('${item.id}', -1)">-</button>
                        <span class="fw-bold">${item.qty}</span>
                        <button class="btn btn-light btn-sm rounded-pill" onclick="changeQty('${item.id}', 1)">+</button>
                    </div>
                    <div class="text-end" style="min-width: 80px;">
                        <span class="fw-bold">$${(item.price * item.qty).toFixed(2)}</span>
                    </div>
                    <button class="btn text-danger" onclick="removeItem('${item.id}')"><i class="bi bi-trash"></i></button>
                </div>
            `;
        }).join('');

        container.innerHTML = `
            <div class="row g-4">
                <div class="col-lg-8">${itemsHtml}</div>
                <div class="col-lg-4">
                    <div class="premium-card p-4 sticky-top" style="top: 100px;">
                        <h5 class="fw-bold mb-4">Resumen</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span>$${total.toFixed(2)}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold fs-5">Total</span>
                            <span class="fw-bold fs-5 text-primary">$${total.toFixed(2)}</span>
                        </div>
                        <a href="/checkout" class="btn btn-primary w-100 py-3">Pagar ahora</a>
                    </div>
                </div>
            </div>
        `;
    };

    window.changeQty = (id, delta) => {
        const item = cart.find(i => i.id == id || i.id === id);
        if (item) {
            item.qty += delta;
            if (item.qty < 1) removeItem(id);
            else updateCart();
        }
    };

    window.removeItem = (id) => {
        cart = cart.filter(i => i.id != id && i.id !== id);
        updateCart();
    };

    // --- CHECKOUT STEPS ---
    let currentStep = 1;
    window.nextStep = (step) => {
        const currentEl = document.getElementById(`step-${currentStep}`);
        const nextEl = document.getElementById(`step-${step}`);
        
        if (currentEl) {
            currentEl.classList.remove('active');
            currentEl.classList.add('completed');
        }
        if (nextEl) {
            nextEl.classList.add('active');
            currentStep = step;
        }
    };

    window.goToStep = (step) => {
        if (step < currentStep) {
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            document.getElementById(`step-${step}`).classList.remove('completed');
            document.getElementById(`step-${step}`).classList.add('active');
            currentStep = step;
        }
    };

    // --- REGISTER LOGIC ---
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = registerForm.querySelector('button');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando cuenta...';
            btn.disabled = true;
            
            setTimeout(() => {
                showToast('¡Cuenta creada! Bienvenida/o a la familia Impulso.');
                // Here we would sync with the admin system in the future
                window.location.href = 'login.html';
            }, 2000);
        });
    }

    // --- LOGIN LOGIC ---
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const btn = loginForm.querySelector('button');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Iniciando...';
            btn.disabled = true;
            
            setTimeout(() => {
                showToast('¡Sesión iniciada con éxito!');
                window.location.href = 'index.html';
            }, 1500);
        });
    }

    // --- UTILS ---
    const showToast = (msg) => {
        const toast = document.createElement('div');
        toast.className = 'fixed-bottom mb-4 me-4 ms-auto bg-dark text-white p-3 rounded-pill text-center shadow-lg fade-in';
        toast.style.width = 'fit-content';
        toast.style.zIndex = '9999';
        toast.innerHTML = `<i class="bi bi-check-circle text-primary me-2"></i> ${msg}`;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    };

    // Initialize
    updateCart();
    renderCartPage();
});

// FILTRO DE CATEGORIAS

const botones = document.querySelectorAll(".category-btn");
const productos = document.querySelectorAll(".product-item");

botones.forEach(boton => {

    boton.addEventListener("click", () => {

        // quitar active
        botones.forEach(btn => {
            btn.classList.remove("active");
        });

        // activar boton actual
        boton.classList.add("active");

        // categoria
        const categoria = boton.dataset.category;

        productos.forEach(producto => {

            if(
                categoria === "all" ||
                producto.dataset.category === categoria
            ){

                producto.style.display = "block";

            }else{

                producto.style.display = "none";

            }

        });

    });

});