/**
 * Funções gerais do site
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar componentes
    initMobileMenu();
    initFormValidation();
    initLightbox();
    
    // Ativar máscaras de telefone
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            const x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,5})(\d{0,4})/);
            e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
        });
    });
    
    // Smooth scroll para links internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                window.scrollTo({
                    top: target.offsetTop - 100,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Atualizar dinamicamente os bairros com base na cidade selecionada
    const cidadeSelect = document.getElementById('cidade');
    if (cidadeSelect) {
        cidadeSelect.addEventListener('change', function() {
            const cidade = this.value;
            const bairroSelect = document.getElementById('bairro');
            
            if (!cidade) {
                bairroSelect.innerHTML = '<option value="">Todos</option>';
                bairroSelect.disabled = true;
                return;
            }
            
            fetch(`api/bairros.php?cidade=${encodeURIComponent(cidade)}`)
                .then(response => response.json())
                .then(bairros => {
                    let options = '<option value="">Todos</option>';
                    bairros.forEach(bairro => {
                        options += `<option value="${bairro}">${bairro}</option>`;
                    });
                    bairroSelect.innerHTML = options;
                    bairroSelect.disabled = false;
                });
        });
    }
});

// Função para animar contadores
function animateCounters() {
    const counters = document.querySelectorAll('.counter');
    const speed = 200;
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText;
        const increment = target / speed;
        
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(animateCounters, 1);
        } else {
            counter.innerText = target + '+';
        }
    });
}

// Observar quando a seção de estatísticas entra na viewport
const statsSection = document.querySelector('.stats-section');
if (statsSection) {
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            animateCounters();
            observer.unobserve(statsSection);
        }
    });
    
    observer.observe(statsSection);
}