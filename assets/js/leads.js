document.addEventListener('DOMContentLoaded', function() {
    const columns = document.querySelectorAll('.kanban-column');
    
    if (typeof Sortable !== 'undefined' && columns.length > 0) {
        columns.forEach(column => {
            new Sortable(column, {
                group: 'leads',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function (evt) {
                    const itemEl = evt.item;
                    const newStatus = evt.to.dataset.status;
                    const leadId = itemEl.dataset.id;
                    
                    fetch(`${BASE_URL}/?page=leads&action=update_status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `lead_id=${leadId}&status=${newStatus}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(!data.success) {
                            console.error('Erro ao mover o lead.');
                            // Idealmente, recarregar a página ou reverter
                        }
                    })
                    .catch(err => console.error(err));
                },
            });
        });
    }
});
