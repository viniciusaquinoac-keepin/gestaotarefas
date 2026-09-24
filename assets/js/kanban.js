document.addEventListener('DOMContentLoaded', function() {
    const columns = document.querySelectorAll('.kanban-column');
    
    if (typeof Sortable !== 'undefined' && columns.length > 0) {
        columns.forEach(column => {
            new Sortable(column, {
                group: 'shared',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function (evt) {
                    const itemEl = evt.item;
                    const newStatus = evt.to.dataset.status;
                    const taskId = itemEl.dataset.id;
                    
                    const items = evt.to.querySelectorAll('.kanban-card');
                    let newIndex = Array.from(items).indexOf(itemEl);

                    fetch(`${BASE_URL}/?page=tasks&action=update_status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `task_id=${taskId}&status=${newStatus}&position=${newIndex}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(!data.success) {
                            console.error('Erro ao mover a tarefa.');
                            // Idealmente revertemos visualmente a ação aqui
                        }
                    })
                    .catch(err => console.error(err));
                },
            });
        });
    }
});
