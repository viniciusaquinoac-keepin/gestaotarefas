document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('tasksChart');
    
    if (ctx && typeof chartData !== 'undefined') {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['A Fazer', 'Em Andamento', 'Em Revisão', 'Concluída'],
                datasets: [{
                    data: [
                        chartData.todo || 0,
                        chartData.in_progress || 0,
                        chartData.review || 0,
                        chartData.done || 0
                    ],
                    backgroundColor: [
                        '#6c757d', // secondary
                        '#0d6efd', // primary
                        '#ffc107', // warning
                        '#198754'  // success
                    ],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#f8f9fa'
                        }
                    }
                }
            }
        });
    }
});
