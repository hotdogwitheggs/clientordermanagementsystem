// Auto-dismiss alerts after 4 seconds
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            alert.style.opacity = 0;
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
});

// Delete confirmation modal (used in designer and admin sections)
function confirmDelete(type, id, name) {
    const modalBody = document.getElementById('deleteModalBody');
    const confirmBtn = document.getElementById('confirmDeleteBtn');

    let message = '';
    let url = '';

    switch (type) {
        case 'service':
            message = `Are you sure you want to delete the service "${name}"? This will also delete all associated order types.`;
            url = `edit_service.php?delete_service=${id}`;
            break;
        case 'order_type':
            message = `Are you sure you want to delete the order type "${name}"?`;
            url = `edit_service.php?delete_order_type=${id}`;
            break;
        default:
            message = 'Are you sure you want to delete this item?';
            url = '#';
    }

    modalBody.textContent = message;
    confirmBtn.setAttribute('href', url);

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Form validation helper (optional enhancement for future use)
function validateRequiredFields(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let valid = true;
    const inputs = form.querySelectorAll('[required]');
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            valid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return valid;
}

// Password toggle (optional UI helper)
function togglePasswordVisibility(fieldId, toggleIconId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(toggleIconId);
    if (field.type === 'password') {
        field.type = 'text';
        if (icon) icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        field.type = 'password';
        if (icon) icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
