document.addEventListener('DOMContentLoaded', function() {
    // Simulação de dados do imóvel (substituir por API real)
    const propertyData = {
        id: "123ABC",
        title: "Apartamento Luxuoso com 3 Quartos",
        address: "Av. Paulista, 1000 - São Paulo/SP",
        neighborhood: "Bela Vista",
        fullAddress: "Av. Paulista, 1000 - Apartamento 1501, São Paulo - SP, 01310-100",
        price: 1250000,
        type: "Apartamento",
        description: "Este apartamento luxuoso na Avenida Paulista oferece 130m² de área privativa, 3 dormitórios sendo 1 suíte master, 2 vagas de garagem, e acabamentos de alto padrão. Localizado no 15º andar com vista deslumbrante para a cidade, o edifício conta com piscina, academia, salão de festas e portaria 24h.",
        features: {
            "Área Total": "130 m²",
            "Área Útil": "110 m²",
            "Quartos": "3",
            "Banheiros": "2",
            "Suítes": "1",
            "Vagas": "2",
            "Andar": "15",
            "Condomínio": "R$ 2.500",
            "IPTU": "R$ 1.200"
        },
        characteristics: [
            { name: "Piscina", icon: "pool" },
            { name: "Academia", icon: "fitness_center" },
            { name: "Portaria 24h", icon: "security" },
            { name: "Salão de Festas", icon: "celebration" },
            { name: "Elevador", icon: "elevator" },
            { name: "Varanda", icon: "balcony" },
            { name: "Mobiliado", icon: "weekend" },
            { name: "Pet Friendly", icon: "pets" }
        ],
        images: [
            { url: "/assets/images/property-1.jpg", main: true },
            { url: "/assets/images/property-2.jpg" },
            { url: "/assets/images/property-3.jpg" },
            { url: "/assets/images/property-4.jpg" },
            { url: "/assets/images/property-5.jpg" }
        ],
        location: {
            lat: -23.563210,
            lng: -46.654200
        }
    };

    // Preencher dados do imóvel
    document.getElementById('property-title').textContent = propertyData.title;
    document.getElementById('breadcrumb-property-title').textContent = propertyData.title;
    document.getElementById('property-address').innerHTML = `<i class="fas fa-map-marker-alt"></i> ${propertyData.address}`;
    document.getElementById('property-code').innerHTML = `<i class="fas fa-barcode"></i> Código: <span>${propertyData.id}</span>`;
    document.getElementById('property-price').innerHTML = `
        <span class="price">${formatCurrency(propertyData.price)}</span>
        <span class="cond">ou 12x de ${formatCurrency(propertyData.price / 12)} sem juros</span>
    `;
    document.getElementById('property-description').textContent = propertyData.description;
    document.getElementById('property-neighborhood').textContent = propertyData.neighborhood;
    document.getElementById('property-full-address').textContent = propertyData.fullAddress;
    document.getElementById('property-id').value = propertyData.id;

    // Preencher características
    const featuresList = document.getElementById('property-features');
    for (const [key, value] of Object.entries(propertyData.features)) {
        const li = document.createElement('li');
        li.innerHTML = `<span>${key}</span><span>${value}</span>`;
        featuresList.appendChild(li);
    }

    // Preencher amenities
    const characteristicsGrid = document.getElementById('property-characteristics');
    propertyData.characteristics.forEach(item => {
        const div = document.createElement('div');
        div.className = 'feature-item';
        div.innerHTML = `<i class="material-icons">${item.icon}</i><span>${item.name}</span>`;
        characteristicsGrid.appendChild(div);
    });

    // Configurar galeria de imagens
    const mainImage = document.getElementById('main-property-image');
    const thumbnailContainer = document.getElementById('thumbnail-container');
    const galleryItems = [];

    propertyData.images.forEach((image, index) => {
        galleryItems.push({
            src: image.url,
            thumb: image.url
        });

        if (image.main) {
            mainImage.src = image.url;
        }

        const thumbnail = document.createElement('div');
        thumbnail.className = 'thumbnail';
        thumbnail.innerHTML = `<img src="${image.url}" alt="Imóvel ${index + 1}">`;
        thumbnail.addEventListener('click', () => {
            mainImage.src = image.url;
        });
        thumbnailContainer.appendChild(thumbnail);
    });

    // Inicializar LightGallery
    mainImage.addEventListener('click', () => {
        lightGallery(document.getElementById('property-gallery'), {
            dynamic: true,
            dynamicEl: galleryItems,
            download: false,
            zoom: true
        });
    });

    // Inicializar mapa
    function initMap() {
        const location = propertyData.location;
        const map = new google.maps.Map(document.getElementById('property-map'), {
            center: location,
            zoom: 15,
            styles: [
                {
                    "featureType": "poi",
                    "stylers": [{ "visibility": "off" }]
                }
            ]
        });

        new google.maps.Marker({
            position: location,
            map: map,
            title: propertyData.title
        });

        // Adicionar círculo para destacar a área
        new google.maps.Circle({
            strokeColor: '#3498db',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#3498db',
            fillOpacity: 0.2,
            map: map,
            center: location,
            radius: 300
        });
    }

    // Formatar moeda
    function formatCurrency(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    }

    // Formulário de interesse
    const interestForm = document.getElementById('interestForm');
    if (interestForm) {
        interestForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(interestForm);
            const data = Object.fromEntries(formData.entries());
            
            // Simular envio (substituir por fetch real)
            console.log('Dados do formulário:', data);
            alert('Seu interesse foi registrado! Um corretor entrará em contato em breve.');
            interestForm.reset();
            
            /*
            // Exemplo com fetch:
            fetch('/api/interesse', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Seu interesse foi registrado!');
                    interestForm.reset();
                } else {
                    alert('Erro ao enviar: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar formulário');
            });
            */
        });
    }

    // Máscara para telefone
    const phoneInput = document.getElementById('interest-phone');
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

    // Inicializar o mapa quando a API estiver carregada
    window.initMap = initMap;
});