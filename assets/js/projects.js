/* JavaScript para Módulo de Gestão de Obras */

document.addEventListener('DOMContentLoaded', function() {
    // Quick Form Toggles
    const toggleBtns = document.querySelectorAll('[data-toggle-form]');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetId = this.getAttribute('data-toggle-form');
            const targetEl = document.getElementById(targetId);
            if (targetEl) {
                if (targetEl.classList.contains('d-none')) {
                    targetEl.classList.remove('d-none');
                } else {
                    targetEl.classList.add('d-none');
                }
            }
        });
    });

    // Auto-calculate Total Budget in forms if Material and Labor are filled
    const matInput = document.getElementById('target_material_budget');
    const labInput = document.getElementById('target_labor_budget');
    const totInput = document.getElementById('target_budget');

    function calcTotalBudget() {
        if (matInput && labInput && totInput) {
            const m = parseFloat(matInput.value) || 0;
            const l = parseFloat(labInput.value) || 0;
            totInput.value = (m + l).toFixed(2);
        }
    }

    if (matInput && labInput) {
        matInput.addEventListener('input', calcTotalBudget);
        labInput.addEventListener('input', calcTotalBudget);
    }

    // Auto-calculate months based on start_date and estimated_end_date (and vice-versa)
    const startDateInputs = document.querySelectorAll('input[name="start_date"]');
    const endDateInputs = document.querySelectorAll('input[name="estimated_end_date"]');
    const monthsInputs = document.querySelectorAll('input[name="total_months"]');

    function updateMonthsFromDates(container) {
        const startEl = container.querySelector('input[name="start_date"]');
        const endEl = container.querySelector('input[name="estimated_end_date"]');
        const monthsEl = container.querySelector('input[name="total_months"]');

        if (startEl && endEl && monthsEl && startEl.value && endEl.value) {
            const d1 = new Date(startEl.value + 'T00:00:00');
            const d2 = new Date(endEl.value + 'T00:00:00');
            if (d2 > d1) {
                const diffDays = (d2 - d1) / (1000 * 60 * 60 * 24);
                const months = Math.max(1, Math.round(diffDays / 30.4375));
                monthsEl.value = months;
            }
        }
    }

    function updateEndDateFromMonths(container) {
        const startEl = container.querySelector('input[name="start_date"]');
        const endEl = container.querySelector('input[name="estimated_end_date"]');
        const monthsEl = container.querySelector('input[name="total_months"]');

        if (startEl && endEl && monthsEl && startEl.value && monthsEl.value) {
            const months = parseInt(monthsEl.value);
            if (months > 0) {
                const d1 = new Date(startEl.value + 'T00:00:00');
                d1.setMonth(d1.getMonth() + months);
                const yyyy = d1.getFullYear();
                const mm = String(d1.getMonth() + 1).padStart(2, '0');
                const dd = String(d1.getDate()).padStart(2, '0');
                endEl.value = `${yyyy}-${mm}-${dd}`;
            }
        }
    }

    // Bind event listeners to form containers
    const projectForms = document.querySelectorAll('form');
    projectForms.forEach(form => {
        const startEl = form.querySelector('input[name="start_date"]');
        const endEl = form.querySelector('input[name="estimated_end_date"]');
        const monthsEl = form.querySelector('input[name="total_months"]');

        if (startEl && endEl && monthsEl) {
            startEl.addEventListener('change', () => updateMonthsFromDates(form));
            endEl.addEventListener('change', () => updateMonthsFromDates(form));
            monthsEl.addEventListener('input', () => updateEndDateFromMonths(form));
        }
    });
});
