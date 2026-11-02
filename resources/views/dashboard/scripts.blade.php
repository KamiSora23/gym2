<script>
    (function () {
        const body = document.body;
        const primario = getComputedStyle(document.documentElement).getPropertyValue('--primary-color').trim() || '#ff4d4f';

        // ---------- Navegación entre secciones ----------
        function showPage(page) {
            const destino = document.getElementById('page-' + page);
            if (!destino) return;

            document.querySelectorAll('.page-content').forEach(p => p.classList.add('hidden'));
            destino.classList.remove('hidden');

            document.querySelectorAll('.sidebar .menu-item[data-page]').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.page === page);
            });

            const url = new URL(window.location);
            url.searchParams.set('seccion', page);
            history.replaceState(null, '', url);

            body.classList.remove('sidebar-open');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        document.querySelectorAll('[data-page]').forEach(el => {
            el.addEventListener('click', () => showPage(el.dataset.page));
        });

        // ---------- Menú lateral en móvil ----------
        document.getElementById('sidebarToggle')?.addEventListener('click', () => body.classList.toggle('sidebar-open'));
        document.getElementById('sidebarBackdrop')?.addEventListener('click', () => body.classList.remove('sidebar-open'));

        // ---------- Mostrar / ocultar formularios ----------
        document.querySelectorAll('[data-toggle-target]').forEach(btn => {
            btn.addEventListener('click', () => {
                const objetivo = document.getElementById(btn.dataset.toggleTarget);
                if (!objetivo) return;
                objetivo.classList.toggle('hidden');
                if (!objetivo.classList.contains('hidden')) {
                    objetivo.querySelector('input:not([type=hidden]):not([disabled]), select')?.focus();
                }
            });
        });

        // ---------- Confirmación antes de eliminar ----------
        document.querySelectorAll('form[data-confirm]').forEach(form => {
            form.addEventListener('submit', e => {
                if (!confirm(form.dataset.confirm)) e.preventDefault();
            });
        });

        // ---------- Búsqueda en tablas y tarjetas ----------
        document.querySelectorAll('[data-filter-table]').forEach(input => {
            input.addEventListener('input', () => {
                const texto = input.value.toLowerCase().trim();
                document.querySelectorAll('#' + input.dataset.filterTable + ' tbody tr:not(.edit-row)').forEach(fila => {
                    if (fila.querySelector('.empty-state')) return;
                    fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
                });
            });
        });

        document.querySelectorAll('[data-filter-cards]').forEach(input => {
            input.addEventListener('input', () => {
                const texto = input.value.toLowerCase().trim();
                document.querySelectorAll('#' + input.dataset.filterCards + ' [data-search]').forEach(card => {
                    card.style.display = card.dataset.search.includes(texto) ? '' : 'none';
                });
            });
        });

        // ---------- Ocultar alertas automáticamente ----------
        setTimeout(() => {
            document.querySelectorAll('.main-content > .alert').forEach(a => {
                a.style.transition = 'opacity .4s';
                a.style.opacity = '0';
                setTimeout(() => a.remove(), 400);
            });
        }, 5000);

        // ---------- Gráficos ----------
        if (typeof Chart === 'undefined') return;

        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#94a3b8';

        const asistencias = @json($graficoAsistencias);
        const maxAsistencias = Math.max(...asistencias, 0);

        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Asistencias',
                    data: asistencias,
                    backgroundColor: primario,
                    borderRadius: 8,
                    maxBarThickness: 36,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: maxAsistencias < 5 ? 5 : undefined,
                        ticks: { precision: 0 },
                        grid: { color: '#f1f5f9' },
                        border: { display: false },
                    },
                    x: { grid: { display: false }, border: { display: false } },
                },
                plugins: { legend: { display: false } },
            },
        });

        const ingresos = {{ (float) $stats['ingresos_semana'] }};
        const gastos = {{ (float) $stats['gastos_semana'] }};
        const sinDatos = ingresos === 0 && gastos === 0;

        new Chart(document.getElementById('donutChart'), {
            type: 'doughnut',
            data: {
                labels: sinDatos ? ['Sin movimientos'] : ['Ingresos', 'Gastos'],
                datasets: [{
                    data: sinDatos ? [1] : [ingresos, gastos],
                    backgroundColor: sinDatos ? ['#e2e8f0'] : ['#10b981', '#ef4444'],
                    borderWidth: 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: { legend: { display: false }, tooltip: { enabled: !sinDatos } },
            },
        });
    })();
</script>
