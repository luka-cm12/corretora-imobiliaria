/**
 * Validação de formulários
 */

function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                const errorElement = field.nextElementSibling;
                
                // Resetar erros
                field.classList.remove('error');
                if (errorElement && errorElement.classList.contains('error-message')) {
                    errorElement.remove();
                }
                
                // Validar campo
                if (!field.value.trim()) {
                    showError(field, 'Este campo é obrigatório');
                    isValid = false;
                } else if (field.type === 'email' && !validateEmail(field.value)) {
                    showError(field, 'Por favor, insira um email válido');
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Rolando para o primeiro erro
                const firstError = form.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    });
}

function showError(field, message) {
    field.classList.add('error');
    
    const errorElement = document.createElement('span');
    errorElement.className = 'error-message';
    errorElement.textContent = message;
    
    field.parentNode.insertBefore(errorElement, field.nextSibling);
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}