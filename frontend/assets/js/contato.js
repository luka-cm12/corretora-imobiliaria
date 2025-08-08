document.addEventListener('DOMContentLoaded', async function() {
    const contactForm = document.getElementById('contactForm');
    const phoneInput = document.getElementById('phone');
    const axios = require('axios');

    // Adicione ao seu endpoint de contato
    const recaptchaResponse = req.body.recaptcha_response;
    const secretKey = process.env.RECAPTCHA_SECRET_KEY;

    const verificationUrl = `https://www.google.com/recaptcha/api/siteverify?secret=${secretKey}&response=${recaptchaResponse}`;

    const recaptchaResult = await axios.post(verificationUrl);
    if (!recaptchaResult.data.success || recaptchaResult.data.score < 0.5) {
        return res.status(400).json({ 
            success: false,
            message: 'Falha na verificação reCAPTCHA'
        });
    }
    
    // Máscara de telefone
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 11) value = value.substring(0, 11);
            
            // Formatação: (00) 00000-0000
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
            e.target.value = value;
        });
    }

    // Validação em tempo real
    contactForm.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('blur', validateField);
    });

    function validateField(e) {
        const field = e.target;
        const errorElement = field.nextElementSibling;
        
        if (field.required && !field.value.trim()) {
            showError(field, 'Este campo é obrigatório');
            return false;
        }
        
        if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
            showError(field, 'E-mail inválido');
            return false;
        }
        
        if (field.id === 'phone' && !/^\(\d{2}\) \d{4,5}-\d{4}$/.test(field.value)) {
            showError(field, 'Telefone inválido');
            return false;
        }
        
        clearError(field);
        return true;
    }

    function showError(field, message) {
        clearError(field);
        field.classList.add('error');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.textContent = message;
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }

    function clearError(field) {
        field.classList.remove('error');
        const errorElement = field.nextElementSibling;
        if (errorElement && errorElement.classList.contains('error-message')) {
            errorElement.remove();
        }
    }

    // Envio do formulário com AJAX
    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validar todos os campos antes do envio
        let isValid = true;
        contactForm.querySelectorAll('input, select, textarea').forEach(input => {
            if (!validateField({ target: input })) {
                isValid = false;
            }
        });
        
        if (!isValid) {
            alert('Por favor, corrija os erros no formulário.');
            return;
        }

        // Mostrar loading
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        submitBtn.disabled = true;

        try {
            const formData = new FormData(contactForm);
            const response = await fetch('/api/contato', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(Object.fromEntries(formData))
            });

            const data = await response.json();
            
            if (data.success) {
                // Feedback visual de sucesso
                contactForm.reset();
                showFeedback('success', 'Mensagem enviada com sucesso!');
            } else {
                showFeedback('error', data.message || 'Erro ao enviar mensagem.');
            }
        } catch (error) {
            console.error('Erro:', error);
            showFeedback('error', 'Erro de conexão. Tente novamente.');
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    function showFeedback(type, message) {
        // Remove feedbacks anteriores
        const oldFeedback = document.querySelector('.form-feedback');
        if (oldFeedback) oldFeedback.remove();
        
        const feedback = document.createElement('div');
        feedback.className = `form-feedback ${type}`;
        feedback.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        contactForm.prepend(feedback);
        
        // Auto-remover após 5 segundos
        setTimeout(() => feedback.remove(), 5000);
    }
});