<script>
        const publicView = document.getElementById('public-view');
        const dashboardView = document.getElementById('dashboard-view');
        const loginModal = document.getElementById('login-modal');

        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('current-date').innerText = new Date().toLocaleDateString('es-ES', dateOptions).replace(/^\w/, (c) => c.toUpperCase());

        function toggleModal() { loginModal.classList.remove('hidden'); }
        

        function showPage(page) {
            document.querySelectorAll('.page-content').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.sidebar-menu li').forEach(l => l.classList.remove('active'));
            
            document.getElementById('page-' + page).classList.remove('hidden');
            event.currentTarget.classList.add('active');
        }

        let barChartInst, donutChartInst;

        function initCharts() {
            if(barChartInst) barChartInst.destroy();
            if(donutChartInst) donutChartInst.destroy();

            const ctxBar = document.getElementById('barChart').getContext('2d');
            barChartInst = new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Asistencias',
                        data: [0, 0, 0, 0, 0, 0, 0],
                        backgroundColor: '#ff4d4f',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 1.0, ticks: { stepSize: 0.1 } }
                    },
                    plugins: { legend: { display: false } }
                }
            });

            const ctxDonut = document.getElementById('donutChart').getContext('2d');
            donutChartInst = new Chart(ctxDonut, {
                type: 'doughnut',
                data: {
                    labels: ['Ingresos', 'Gastos'],
                    datasets: [{
                        data: [1500, 440],
                        backgroundColor: ['#10ac84', '#ff6b6b'],
                        borderWidth: 0,
                        cutout: '75%'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });
        }
    </script>