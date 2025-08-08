// upload.js - Sistema de Upload para Imagens de Imóveis
document.addEventListener('DOMContentLoaded', function() {
    // Elementos DOM
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');
    const previewContainer = document.getElementById('previewContainer');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const saveOrderBtn = document.getElementById('saveOrderBtn');
    const propertyId = document.getElementById('propertyId').value;
    
    // Variáveis de estado
    let uploadedFiles = [];
    let mainImageId = null;

    // Inicializar SortableJS para ordenação
    if (previewContainer) {
        new Sortable(previewContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: updateImageOrders
        });
    }

    // Event Listeners
    if (fileInput) {
        fileInput.addEventListener('change', handleFileSelect);
    }

    if (uploadForm) {
        uploadForm.addEventListener('submit', handleFormSubmit);
    }

    if (saveOrderBtn) {
        saveOrderBtn.addEventListener('click', saveImageOrder);
    }

    // Carregar imagens existentes ao entrar na página
    loadExistingImages();

    // Função para carregar imagens já cadastradas
    async function loadExistingImages() {
        try {
            const response = await fetch(`/api/properties/${propertyId}/images`);
            const images = await response.json();
            
            if (images && images.length > 0) {
                images.forEach(image => {
                    addImageToPreview({
                        id: image.id,
                        name: image.name,
                        url: image.url,
                        isMain: image.isMain,
                        order: image.order
                    });
                    
                    if (image.isMain) {
                        mainImageId = image.id;
                    }
                });
            }
        } catch (error) {
            console.error('Erro ao carregar imagens:', error);
            showFeedback('error', 'Erro ao carregar imagens existentes');
        }
    }

    // Manipular seleção de arquivos
    function handleFileSelect(e) {
        const files = e.target.files;
        
        if (files.length === 0) return;
        
        // Verificar número máximo de imagens (máx 12)
        if (uploadedFiles.length + files.length > 12) {
            showFeedback('error', 'Máximo de 12 imagens por imóvel');
            return;
        }
        
        // Processar cada arquivo
        Array.from(files).forEach(file => {
            if (!file.type.match('image.*')) {
                showFeedback('error', `Arquivo ${file.name} não é uma imagem`);
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) { // 5MB
                showFeedback('error', `Imagem ${file.name} muito grande (máx 5MB)`);
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = (event) => {
                const fileData = {
                    id: 'temp-' + Math.random().toString(36).substr(2, 9),
                    file: file,
                    name: file.name,
                    url: event.target.result,
                    isMain: false,
                    order: uploadedFiles.length
                };
                
                uploadedFiles.push(fileData);
                addImageToPreview(fileData);
            };
            
            reader.readAsDataURL(file);
        });
        
        // Resetar input para permitir novos uploads do mesmo arquivo
        fileInput.value = '';
    }

    // Adicionar imagem ao container de pré-visualização
    function addImageToPreview(imageData) {
        const imageElement = document.createElement('div');
        imageElement.className = 'preview-item';
        imageElement.dataset.id = imageData.id;
        imageElement.dataset.order = imageData.order;
        
        imageElement.innerHTML = `
            <img src="${imageData.url}" alt="Preview">
            <div class="preview-actions">
                <button type="button" class="btn-set-main ${imageData.isMain ? 'active' : ''}" 
                    data-id="${imageData.id}" title="Definir como imagem principal">
                    <i class="fas fa-star"></i>
                </button>
                <button type="button" class="btn-remove-image" 
                    data-id="${imageData.id}" title="Remover imagem">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="preview-footer">
                <span class="image-name">${imageData.name}</span>
                <span class="image-size">${formatFileSize(imageData.file?.size || 0)}</span>
            </div>
        `;
        
        previewContainer.appendChild(imageElement);
        
        // Adicionar eventos aos botões
        imageElement.querySelector('.btn-set-main').addEventListener('click', setAsMainImage);
        imageElement.querySelector('.btn-remove-image').addEventListener('click', removeImage);
    }

    // Definir imagem principal
    function setAsMainImage(e) {
        const imageId = e.currentTarget.dataset.id;
        
        // Remover classe 'active' de todos os botões
        document.querySelectorAll('.btn-set-main').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Adicionar classe 'active' ao botão clicado
        e.currentTarget.classList.add('active');
        
        // Atualizar estado
        mainImageId = imageId;
        
        // Atualizar no array uploadedFiles
        uploadedFiles.forEach(file => {
            file.isMain = (file.id === imageId);
        });
        
        showFeedback('success', 'Imagem principal definida com sucesso');
    }

    // Remover imagem
    function removeImage(e) {
        const imageId = e.currentTarget.dataset.id;
        const isConfirmed = confirm('Tem certeza que deseja remover esta imagem?');
        
        if (!isConfirmed) return;
        
        // Remover do DOM
        document.querySelector(`.preview-item[data-id="${imageId}"]`)?.remove();
        
        // Remover do array uploadedFiles
        uploadedFiles = uploadedFiles.filter(file => file.id !== imageId);
        
        // Se era a imagem principal, definir uma nova (se houver)
        if (mainImageId === imageId) {
            mainImageId = uploadedFiles.length > 0 ? uploadedFiles[0].id : null;
            document.querySelector('.btn-set-main')?.classList.add('active');
        }
        
        // Atualizar ordens
        updateImageOrders();
    }

    // Atualizar ordens após reordenar
    function updateImageOrders() {
        const items = previewContainer.querySelectorAll('.preview-item');
        
        items.forEach((item, index) => {
            const imageId = item.dataset.id;
            item.dataset.order = index;
            
            // Atualizar no array uploadedFiles
            const fileIndex = uploadedFiles.findIndex(file => file.id === imageId);
            if (fileIndex !== -1) {
                uploadedFiles[fileIndex].order = index;
            }
        });
    }

    // Salvar ordenação no backend
    async function saveImageOrder() {
        const imagesOrder = Array.from(previewContainer.querySelectorAll('.preview-item')).map(item => ({
            id: item.dataset.id,
            order: item.dataset.order,
            isMain: item.querySelector('.btn-set-main').classList.contains('active')
        }));
        
        try {
            const response = await fetch(`/api/properties/${propertyId}/images/order`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({ images: imagesOrder })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showFeedback('success', 'Ordem das imagens salva com sucesso');
                
                // Atualizar IDs temporários com IDs reais (para novos uploads)
                data.updatedImages.forEach(updatedImage => {
                    const tempIndex = uploadedFiles.findIndex(file => file.id === updatedImage.tempId);
                    if (tempIndex !== -1) {
                        uploadedFiles[tempIndex].id = updatedImage.id;
                    }
                });
            } else {
                showFeedback('error', data.message || 'Erro ao salvar ordem');
            }
        } catch (error) {
            console.error('Erro ao salvar ordem:', error);
            showFeedback('error', 'Erro ao salvar ordem das imagens');
        }
    }

    // Enviar formulário de upload
    async function handleFormSubmit(e) {
        e.preventDefault();
        
        if (uploadedFiles.length === 0) {
            showFeedback('error', 'Selecione pelo menos uma imagem');
            return;
        }
        
        // Mostrar barra de progresso
        progressContainer.style.display = 'block';
        progressBar.style.width = '0%';
        progressText.textContent = '0%';
        
        try {
            // Processar cada arquivo
            for (let i = 0; i < uploadedFiles.length; i++) {
                const fileData = uploadedFiles[i];
                
                // Pular arquivos já enviados (com ID que não começa com 'temp-')
                if (!fileData.id.startsWith('temp-')) continue;
                
                const formData = new FormData();
                formData.append('image', fileData.file);
                formData.append('isMain', fileData.isMain);
                formData.append('order', fileData.order);
                
                const xhr = new XMLHttpRequest();
                
                // Configurar progresso
                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        const percentComplete = Math.round((event.loaded / event.total) * 100);
                        progressBar.style.width = `${percentComplete}%`;
                        progressText.textContent = `${percentComplete}%`;
                    }
                });
                
                await new Promise((resolve, reject) => {
                    xhr.onreadystatechange = () => {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);
                                
                                // Atualizar ID temporário com ID real
                                const tempId = fileData.id;
                                fileData.id = response.id;
                                
                                // Atualizar no DOM
                                const previewItem = document.querySelector(`.preview-item[data-id="${tempId}"]`);
                                if (previewItem) {
                                    previewItem.dataset.id = response.id;
                                }
                                
                                resolve();
                            } else {
                                reject(new Error('Erro no upload'));
                            }
                        }
                    };
                    
                    xhr.open('POST', `/api/properties/${propertyId}/images`, true);
                    xhr.setRequestHeader('Authorization', `Bearer ${localStorage.getItem('token')}`);
                    xhr.send(formData);
                });
            }
            
            showFeedback('success', 'Upload de imagens concluído com sucesso!');
            saveOrderBtn.style.display = 'block'; // Mostrar botão de salvar ordem
            
        } catch (error) {
            console.error('Erro no upload:', error);
            showFeedback('error', 'Erro ao enviar imagens. Tente novamente.');
        } finally {
            progressContainer.style.display = 'none';
        }
    }

    // Mostrar feedback para o usuário
    function showFeedback(type, message) {
        // Remove feedbacks anteriores
        const oldFeedback = document.querySelector('.feedback-message');
        if (oldFeedback) oldFeedback.remove();
        
        const feedback = document.createElement('div');
        feedback.className = `feedback-message ${type}`;
        feedback.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        
        uploadForm.prepend(feedback);
        
        // Auto-remover após 5 segundos
        setTimeout(() => feedback.remove(), 5000);
    }

    // Formatar tamanho do arquivo
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});