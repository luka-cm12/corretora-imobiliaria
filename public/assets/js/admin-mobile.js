/**
 * Admin Mobile Functionality
 * Melhorias para uso da área administrativa em dispositivos móveis
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ===== MOBILE MENU TOGGLE =====
    createMobileMenuToggle();
    handleSidebarToggle();
    
    // ===== FORM IMPROVEMENTS =====
    improveMobileForms();
    
    // ===== CADASTRO IMOVEIS MOBILE =====
    improveCadastroImoveis();
    
    // ===== TABLE IMPROVEMENTS =====
    improveMobileTables();
    
    // ===== VIEWPORT DETECTION =====
    handleViewportChanges();
});

/**
 * Criar botão de menu mobile se não existir
 */
function createMobileMenuToggle() {
    if (document.querySelector('.mobile-menu-toggle')) return;
    
    const toggleButton = document.createElement('button');
    toggleButton.className = 'mobile-menu-toggle';
    toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
    toggleButton.setAttribute('aria-label', 'Abrir menu de navegação');
    
    document.body.insertBefore(toggleButton, document.body.firstChild);
}

/**
 * Gerenciar abertura/fechamento da sidebar em mobile
 */
function handleSidebarToggle() {
    const toggleButton = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.admin-sidebar, .sidebar');
    const overlay = createOverlay();
    
    if (!toggleButton || !sidebar) return;
    
    toggleButton.addEventListener('click', function() {
        const isOpen = sidebar.classList.contains('active');
        
        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });
    
    // Fechar ao clicar no overlay
    overlay.addEventListener('click', closeSidebar);
    
    // Fechar ao clicar em links da sidebar (mobile)
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                setTimeout(closeSidebar, 100);
            }
        });
    });
    
    function openSidebar() {
        sidebar.classList.add('active');
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        toggleButton.innerHTML = '<i class="fas fa-times"></i>';
    }
    
    function closeSidebar() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = '';
        toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
    }
}

/**
 * Criar overlay para sidebar mobile
 */
function createOverlay() {
    let overlay = document.querySelector('.sidebar-overlay');
    
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }
    
    return overlay;
}

/**
 * Melhorias nos formulários para mobile
 */
function improveMobileForms() {
    // Adicionar classes responsivas aos form-row
    const formRows = document.querySelectorAll('.form-row');
    formRows.forEach(row => {
        // Converter grid em flexbox para mobile
        if (window.innerWidth <= 768) {
            row.style.display = 'flex';
            row.style.flexDirection = 'column';
        }
    });
    
    // Melhorar inputs de arquivo
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const label = this.nextElementSibling || this.previousElementSibling;
            if (label && this.files.length > 0) {
                const fileName = this.files.length === 1 
                    ? this.files[0].name 
                    : `${this.files.length} arquivos selecionados`;
                
                // Criar ou atualizar indicador
                let indicator = this.parentNode.querySelector('.file-indicator');
                if (!indicator) {
                    indicator = document.createElement('small');
                    indicator.className = 'file-indicator';
                    indicator.style.color = '#28a745';
                    indicator.style.display = 'block';
                    indicator.style.marginTop = '5px';
                    this.parentNode.appendChild(indicator);
                }
                indicator.textContent = fileName;
            }
        });
    });
    
    // Auto-resize para textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
    
    // Validação visual em tempo real
    const requiredInputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#28a745';
            }
        });
        
        input.addEventListener('focus', function() {
            this.style.borderColor = '#007bff';
        });
    });
}

/**
 * Melhorias específicas para cadastro de imóveis mobile
 */
function improveCadastroImoveis() {
    // Aplicar máscaras automaticamente
    applyMobileMasks();
    
    // Melhorar grid de características
    improveCaracteristicasGrid();
    
    // Adicionar validação visual
    addMobileValidation();
    
    // Otimizar upload de imagens
    optimizeImageUpload();
}

/**
 * Aplicar máscaras para campos específicos
 */
function applyMobileMasks() {
    // Máscara de preço
    const precoInputs = document.querySelectorAll('input[name="preco"], #preco');
    precoInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = (value/100).toFixed(2).replace('.', ',');
            value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
            e.target.value = 'R$ ' + value;
        });
        
        // Placeholder melhorado
        input.placeholder = 'R$ 0,00';
    });
    
    // Máscara de área
    const areaInputs = document.querySelectorAll('input[name="area"], #area');
    areaInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d,]/g, '');
            e.target.value = value;
        });
        input.placeholder = 'Ex: 120,50';
    });
}

/**
 * Melhorar grid de características para mobile
 */
function improveCaracteristicasGrid() {
    const caracteristicasGrid = document.querySelector('.caracteristicas-grid');
    if (!caracteristicasGrid) return;
    
    // Reorganizar layout para mobile
    if (window.innerWidth <= 768) {
        caracteristicasGrid.style.display = 'grid';
        caracteristicasGrid.style.gridTemplateColumns = '1fr';
        caracteristicasGrid.style.gap = '0.5rem';
        
        // Melhorar labels dos checkboxes
        const labels = caracteristicasGrid.querySelectorAll('label');
        labels.forEach(label => {
            label.style.display = 'flex';
            label.style.alignItems = 'center';
            label.style.padding = '12px 16px';
            label.style.backgroundColor = '#f8f9fa';
            label.style.borderRadius = '8px';
            label.style.border = '2px solid transparent';
            label.style.cursor = 'pointer';
            label.style.transition = 'all 0.3s ease';
            label.style.minHeight = '48px';
            label.style.gap = '12px';
            
            // Melhorar checkbox
            const checkbox = label.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.style.width = '20px';
                checkbox.style.height = '20px';
                checkbox.style.margin = '0';
                checkbox.style.flexShrink = '0';
                
                // Adicionar feedback visual
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        label.style.backgroundColor = '#e7f3ff';
                        label.style.borderColor = '#007bff';
                        label.style.fontWeight = '600';
                    } else {
                        label.style.backgroundColor = '#f8f9fa';
                        label.style.borderColor = 'transparent';
                        label.style.fontWeight = 'normal';
                    }
                });
            }
            
            // Hover effect
            label.addEventListener('mouseenter', function() {
                if (!this.querySelector('input').checked) {
                    this.style.backgroundColor = '#e9ecef';
                    this.style.borderColor = '#dee2e6';
                }
            });
            
            label.addEventListener('mouseleave', function() {
                if (!this.querySelector('input').checked) {
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.borderColor = 'transparent';
                }
            });
        });
    }
}

/**
 * Adicionar validação visual mobile
 */
function addMobileValidation() {
    const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
    
    requiredFields.forEach(field => {
        // Adicionar ícone de obrigatório
        const label = field.closest('.form-group')?.querySelector('label');
        if (label && !label.querySelector('.required-icon')) {
            const icon = document.createElement('span');
            icon.className = 'required-icon';
            icon.innerHTML = ' <span style="color: #dc3545;">*</span>';
            label.appendChild(icon);
        }
        
        // Validação em tempo real
        field.addEventListener('blur', function() {
            validateField(this);
        });
        
        field.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                clearFieldError(this);
            }
        });
    });
}

/**
 * Validar campo específico
 */
function validateField(field) {
    const value = field.value.trim();
    const isEmpty = value === '';
    
    if (isEmpty && field.hasAttribute('required')) {
        showFieldError(field, 'Este campo é obrigatório');
        return false;
    }
    
    // Validações específicas
    if (field.type === 'email' && value && !isValidEmail(value)) {
        showFieldError(field, 'Digite um e-mail válido');
        return false;
    }
    
    if (field.name === 'preco' && value && !value.includes('R$')) {
        showFieldError(field, 'Digite um valor válido');
        return false;
    }
    
    clearFieldError(field);
    return true;
}

/**
 * Mostrar erro no campo
 */
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.style.borderColor = '#dc3545';
    field.style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.1)';
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error mobile-error';
    errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
    errorDiv.style.cssText = `
        color: #dc3545;
        font-size: 12px;
        margin-top: 4px;
        padding: 6px 8px;
        background: rgba(220, 53, 69, 0.1);
        border-radius: 4px;
        border-left: 3px solid #dc3545;
        display: flex;
        align-items: center;
        gap: 4px;
    `;
    
    field.parentNode.appendChild(errorDiv);
}

/**
 * Limpar erro do campo
 */
function clearFieldError(field) {
    field.style.borderColor = '#e1e5e9';
    field.style.boxShadow = 'none';
    
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

/**
 * Otimizar upload de imagens para mobile
 */
function optimizeImageUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    
    fileInputs.forEach(input => {
        // Melhorar aparência
        input.style.cssText = `
            padding: 16px;
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            min-height: 60px;
            width: 100%;
        `;
        
        // Feedback visual de seleção
        input.addEventListener('change', function() {
            const fileCount = this.files.length;
            
            if (fileCount > 0) {
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '#d4edda';
                
                // Mostrar informações dos arquivos
                let info = input.parentNode.querySelector('.upload-info');
                if (!info) {
                    info = document.createElement('div');
                    info.className = 'upload-info';
                    input.parentNode.appendChild(info);
                }
                
                info.innerHTML = `
                    <div style="
                        margin-top: 8px;
                        padding: 8px 12px;
                        background: #d4edda;
                        border: 1px solid #c3e6cb;
                        border-radius: 4px;
                        color: #155724;
                        font-size: 13px;
                    ">
                        <i class="fas fa-check-circle"></i>
                        ${fileCount} arquivo(s) selecionado(s)
                        ${fileCount === 1 ? `<br><small>${this.files[0].name}</small>` : ''}
                    </div>
                `;
            }
        });
    });
}

/**
 * Validar e-mail
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Melhorias nas tabelas para mobile
 */
function improveMobileTables() {
    const tables = document.querySelectorAll('.responsive-table table');
    
    tables.forEach(table => {
        if (window.innerWidth <= 768) {
            makeTableResponsive(table);
        }
    });
}

/**
 * Converter tabela para layout mobile-friendly
 */
function makeTableResponsive(table) {
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
    const rows = table.querySelectorAll('tbody tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        
        cells.forEach((cell, index) => {
            if (headers[index] && !cell.getAttribute('data-label')) {
                cell.setAttribute('data-label', headers[index]);
            }
        });
    });
    
    // Adicionar CSS para exibição mobile
    if (!document.querySelector('#mobile-table-styles')) {
        const style = document.createElement('style');
        style.id = 'mobile-table-styles';
        style.textContent = `
            @media (max-width: 768px) {
                .responsive-table table,
                .responsive-table thead,
                .responsive-table tbody,
                .responsive-table th,
                .responsive-table td,
                .responsive-table tr {
                    display: block;
                }
                
                .responsive-table thead tr {
                    position: absolute;
                    top: -9999px;
                    left: -9999px;
                }
                
                .responsive-table tr {
                    border: 1px solid #ccc;
                    margin-bottom: 10px;
                    padding: 10px;
                    background: #fff;
                    border-radius: 5px;
                }
                
                .responsive-table td {
                    border: none;
                    position: relative;
                    padding: 8px 8px 8px 120px !important;
                    min-height: 30px;
                    text-align: left !important;
                }
                
                .responsive-table td:before {
                    content: attr(data-label) ": ";
                    position: absolute;
                    left: 8px;
                    width: 100px;
                    font-weight: bold;
                    color: #333;
                    white-space: nowrap;
                }
                
                .responsive-table .actions {
                    flex-direction: row;
                    gap: 5px;
                    justify-content: flex-end;
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Gerenciar mudanças de viewport
 */
function handleViewportChanges() {
    let resizeTimer;
    
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            const sidebar = document.querySelector('.admin-sidebar, .sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            // Fechar sidebar se mudou para desktop
            if (window.innerWidth > 768) {
                if (sidebar) sidebar.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
            
            // Recriar responsividade das tabelas
            improveMobileTables();
        }, 250);
    });
}

/**
 * Adicionar feedback tátil para botões (vibração em dispositivos suportados)
 */
function addHapticFeedback() {
    if ('vibrate' in navigator) {
        const buttons = document.querySelectorAll('.btn, button');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                navigator.vibrate(50); // Vibração leve de 50ms
            });
        });
    }
}

/**
 * Melhorar acessibilidade para toque
 */
function improveTouchAccessibility() {
    // Adicionar indicador visual para elementos focáveis
    const focusableElements = document.querySelectorAll('a, button, input, select, textarea');
    
    focusableElements.forEach(element => {
        element.addEventListener('focus', function() {
            this.style.outline = '2px solid #007bff';
            this.style.outlineOffset = '2px';
        });
        
        element.addEventListener('blur', function() {
            this.style.outline = '';
            this.style.outlineOffset = '';
        });
    });
}

// Inicializar melhorias adicionais
document.addEventListener('DOMContentLoaded', function() {
    addHapticFeedback();
    improveTouchAccessibility();
});

/**
 * Service Worker para cache offline (opcional)
 */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/admin-sw.js')
            .then(function(registration) {
                console.log('Admin SW registered: ', registration);
            })
            .catch(function(registrationError) {
                console.log('Admin SW registration failed: ', registrationError);
            });
    });
}