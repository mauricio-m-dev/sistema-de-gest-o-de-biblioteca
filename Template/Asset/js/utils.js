export function formatDate(dateStr) {
    if (!dateStr) return "-";
    const parts = dateStr.split("-");
    return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : dateStr;
}

export function formatCurrency(value) {
    return parseFloat(value || 0).toFixed(2).replace('.', ',');
}

export function maskCPF(input) {
    let value = input.value.replace(/\D/g, "");
    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d)/, "$1.$2");
    value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    input.value = value;
}

export function showFeedback(inputElement, feedbackElement, message, isSuccess) {
    feedbackElement.innerText = message;
    if (isSuccess) {
        feedbackElement.style.color = "green";
        inputElement.style.border = "2px solid green";
        inputElement.classList.remove('input-error');
    } else {
        feedbackElement.style.color = "red";
        inputElement.style.border = "2px solid red";
        inputElement.setAttribute('data-invalid', 'true');
    }
}

// Controle de UI (Tabs e Modals)
export const UI = {
    showSection: (id) => {
        document.querySelectorAll('.section').forEach(s => s.classList.remove('active-section'));
        const target = document.getElementById(id);
        if (target) target.classList.add('active-section');
    },
    openModal: (id) => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'block';
    },
    closeModal: (id) => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }
};

// Torna global para funcionar com o 'onclick' do HTML antigo
window.showSection = UI.showSection;
window.openModal = UI.openModal;
window.closeModal = UI.closeModal;
window.maskCPF = maskCPF;